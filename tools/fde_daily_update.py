# -*- coding: utf-8 -*-
"""
FDE 资讯每日自动更新器（自包含 · RSS 驱动 · 无需人工介入）

每天由自动化任务触发执行：
  1. 读取 data/fde-archive.json，拿到各语言「已存在链接集合」用于去重。
  2. 抓取中/英文科技·AI·企业类 RSS 源，解析最近 N 天（默认 3 天）的条目。
  3. 按 FDE 相关性打分、过滤、排序，并按归一化链接去重（跨天重复也不再入库）。
  4. 生成 tools/_daily.json，调用 fde_news_write.py 合并沉淀进归档 + 刷新页脚。
  5. 输出本次「新增 / 跳过(已存在) / 跳过(过期) / 不相关」统计。
     —— 当天确实没有新鲜资讯时新增为 0 属正常，不算错误。

设计要点（为什么能「真正实现每天更新」）：
  * 完全自包含，只依赖标准库 + 网络 RSS，不依赖智能体当场做 WebSearch。
  * 滚动窗口（相对于今天，而非固定 7 天）天然具备「补跑」能力：
    某天自动化没跑成功，第二天仍会抓取最近 N 天，自动补齐缺口。
  * 链接级去重独立于写入器的 slug 逻辑，避免同源不同日期被当成两条。

用法:
    python fde_daily_update.py [--lang zh|en|both] [--days N] [--dry-run] [--cap N]
依赖: 仅 Python 标准库（urllib / xml / html / ssl）。
"""
import argparse
import datetime as dt
import html
import io
import json
import os
import re
import ssl
import subprocess
import sys
import xml.etree.ElementTree as ET
from email.utils import parsedate_to_datetime
from urllib.request import Request, urlopen

HERE = os.path.dirname(os.path.abspath(__file__))
DATA_DIR = os.path.normpath(os.path.join(HERE, "..", "data"))
ARCHIVE = os.path.join(DATA_DIR, "fde-archive.json")
DAILY = os.path.join(HERE, "_daily.json")
WRITE_PY = os.path.join(HERE, "fde_news_write.py")

# 沙箱内部分 HTTPS 证书不被信任，放宽校验（仅用于公开 RSS 抓取）
SSL_CTX = ssl.create_default_context()
SSL_CTX.check_hostname = False
SSL_CTX.verify_mode = ssl.CERT_NONE

UA = {"User-Agent": "Mozilla/5.0 (compatible; FDE-News-Bot/1.0)"}

# 每个语言一组 RSS/Atom 源（公开、无需密钥）。失效源会被逐个容错跳过。
FEEDS = {
    "en": [
        "https://hnrss.org/frontpage",
        "https://techcrunch.com/category/artificial-intelligence/feed/",
        "https://venturebeat.com/feed/",
        "https://www.theverge.com/rss/ai-artificial-intelligence/index.xml",
        "https://feeds.arstechnica.com/arstechnica/index",
        "https://www.zdnet.com/news/rss.xml",
        "https://blog.google/technology/ai/rss/",
        "https://openai.com/blog/rss.xml",
        "https://techcrunch.com/feed/",
    ],
    "zh": [
        "https://www.36kr.com/feed",
        "https://www.qbitai.com/feed",
        "https://www.infoq.cn/feed",
        "https://www.leiphone.com/rss",
        "https://www.thepaper.cn/rss.jsp",
        "https://www.jiqizhixin.com/rss",
    ],
}

# 强相关词：命中即视为 FDE 相关（无论其他）
STRONG = {
    "en": [
        "forward deployed", "forward-deployed", " fde", "ai agent", "agentic",
        "enterprise ai", "ai deployment", "palantir", "openai deployment",
        "anthropic", "claude", "chatgpt", "gemini", "copilot", " rag", "llm",
        "large language model", "generative ai", "ai startup", "ai funding",
        "machine learning", "deploy ",
    ],
    "zh": [
        "前沿部署", "fde", "智能体", "agent", "大模型", "企业 ai", "企业ai",
        "ai 部署", "部署", "palantir", "openai", "anthropic", "copilot", "rag",
        "生成式", "机器学习", "招聘", "薪酬", "薪资",
        "阿里", "腾讯", "百度", "字节", "华为",
    ],
}
# 弱相关词：仅用于打分，单靠它们不足以入选
WEAK = {
    "en": ["ai", "artificial intelligence", "model", "startup", "funding",
           "valuation", "cloud", "data", "software", "enterprise", "robot", "chip", "gpu"],
    "zh": ["人工智能", "模型", "创业", "融资", "估值", "云", "数据", "软件", "企业", "科技", "智能"],
}

# (中文分类, 英文分类): 触发关键词
CAT_RULES = [
    (("薪酬观察", "Compensation"), ["薪酬", "薪资", "salary", "pay", "compensation"]),
    (("资本动向", "Capital & M&A"), ["融资", "估值", "ipo", "funding", "raise", "valuation", "并购", "billion", "million"]),
    (("人才市场", "Talent Market"), ["招聘", "hire", "role", "岗位", "jobs", "career", "talent", "人才"]),
    (("落地案例", "Enterprise Adoption"), ["部署", "落地", "交付", "deployment", "embed", "enterprise", "客户", "customer"]),
    (("政策合规", "Regulation"), ["法规", "政策", "policy", "regulation", "law", "法案"]),
    (("安全", "AI Security"), ["安全", "security", "safety", "攻击", "breach", "漏洞"]),
]
DEFAULT_CAT = {"zh": "行业趋势", "en": "Industry Trend"}

# 跳过「每日早讯/晚报」类大杂烩聚合稿：标题信号太杂，对 FDE 栏目价值低
DIGEST_RE = re.compile(
    r"(早讯|午讯|早报|午报|晚报|日报|周报|要闻|速览|3分钟|盘点|汇总|晨报|"
    r"1氪|开门红|热点导览|今日热点|晚间消息|财讯|早间)"
)
# 摘要/正文兜底最大长度，避免整篇长文灌入归档
SUMMARY_CAP = 220


# --------------------------------------------------------------------------- #
# 工具函数
# --------------------------------------------------------------------------- #
def local(tag):
    return tag.split("}")[-1] if "}" in tag else tag


def strip_html(s):
    if not s:
        return ""
    s = re.sub(r"<[^>]+>", " ", s)
    s = html.unescape(s)
    s = re.sub(r"\s+", " ", s).strip()
    return s


def fetch_text(url):
    try:
        req = Request(url, headers=UA)
        with urlopen(req, timeout=12, context=SSL_CTX) as r:
            raw = r.read(400000)
        return raw.decode("utf-8", "ignore")
    except Exception as e:
        sys.stderr.write("  feed fail %s: %s\n" % (url, repr(e)[:90]))
        return ""


def extract_entries(xml_bytes, lang):
    out = []
    try:
        root = ET.fromstring(xml_bytes)
    except Exception:
        return out
    container = None
    for tag in ("channel", "feed"):
        for el in root.iter():
            if local(el.tag) == tag:
                container = el
                break
        if container is not None:
            break
    if container is None:
        container = root
    nodes = [el for el in container.iter() if local(el.tag) in ("item", "entry")]
    for node in nodes:
        title = text_of(node, "title")
        link = link_of(node)
        summary = (text_of(node, "description") or text_of(node, "summary")
                   or text_of(node, "content"))
        date = (text_of(node, "pubDate") or text_of(node, "published")
                or text_of(node, "updated") or text_of(node, "date"))
        out.append({"title": title, "link": link, "summary": summary, "date": date})
    return out


def text_of(node, name):
    for el in node.iter():
        if local(el.tag) == name and el.text:
            return el.text
    return ""


def link_of(node):
    for el in node.iter():
        if local(el.tag) == "link":
            href = el.get("href") or el.text
            if href:
                return href.strip()
    return ""


def parse_date(s):
    if not s:
        return None
    s = s.strip()
    try:
        d = parsedate_to_datetime(s)
        if d:
            return d.replace(tzinfo=None)
    except Exception:
        pass
    m = re.search(r"(\d{4}-\d{2}-\d{2})", s)
    if m:
        try:
            return dt.datetime.strptime(m.group(1), "%Y-%m-%d")
        except Exception:
            pass
    return None


def norm_link(u):
    u = (u or "").strip()
    u = re.sub(r"[?&]utm_[^=]*=[^&]*", "", u)
    u = re.sub(r"[?&]spm=[^&]*", "", u)
    u = re.sub(r"#.*$", "", u)
    u = u.rstrip("/")
    return u.lower()


def score_text(text, lang):
    text = text.lower()
    strong_hits = [k for k in STRONG[lang] if k in text]
    weak_hits = [k for k in WEAK[lang] if k in text]
    has_strong = bool(strong_hits)
    title_strong = any(k in text[:120] for k in strong_hits)
    # 强相关词按「是否命中 + 标题加权」计分；弱相关词只计「出现过的不同词」个数，避免重复词刷分
    score = (4 if title_strong else 2) * (1 if strong_hits else 0) + len(weak_hits)
    return has_strong, score


def relevant(text, lang):
    has_strong, score = score_text(text, lang)
    if has_strong:
        return True
    return score >= 2  # 至少 2 个不同弱相关词（如「大模型」+「融资」）才算相关


def infer_category(text, lang):
    text = text.lower()
    for (zc, ec), kws in CAT_RULES:
        if any(k in text for k in kws):
            return zc if lang == "zh" else ec
    return DEFAULT_CAT[lang]


def domain_of(url):
    m = re.match(r"https?://([^/]+)/?", url or "")
    return m.group(1).replace("www.", "") if m else "RSS"


def load_archive_links():
    if not os.path.exists(ARCHIVE):
        return {"zh": set(), "en": set()}
    try:
        d = json.load(io.open(ARCHIVE, encoding="utf-8"))
    except Exception:
        return {"zh": set(), "en": set()}
    items = d.get("items", []) if isinstance(d, dict) else (d if isinstance(d, list) else [])
    out = {"zh": set(), "en": set()}
    for it in items:
        if not isinstance(it, dict):
            continue
        lang = it.get("lang") or "zh"
        link = it.get("link")
        if link:
            out.setdefault(lang, set()).add(norm_link(link))
    return out


# --------------------------------------------------------------------------- #
# 单语言抓取
# --------------------------------------------------------------------------- #
def run_lang(lang, days, existing_links, today, cap):
    stats = {"fetched": 0, "dup": 0, "old": 0, "digest": 0, "irrelevant": 0, "added": 0}
    results = []
    seen = set()
    cutoff_old = today - dt.timedelta(days=days)

    for feed in FEEDS[lang]:
        xml = fetch_text(feed)
        if not xml:
            continue
        src = domain_of(feed)
        for e in extract_entries(xml, lang):
            link = e.get("link")
            if not link or not link.startswith("http"):
                continue
            nl = norm_link(link)
            if nl in existing_links or nl in seen:
                stats["dup"] += 1
                continue
            d = parse_date(e.get("date"))
            if d is None:
                d = today
            if d.date() > today.date():
                stats["old"] += 1  # 其实是「未来」，跳过
                continue
            if d < cutoff_old:
                stats["old"] += 1
                continue
            title = strip_html(e.get("title", "")).strip()
            if not title:
                continue
            # 跳过每日大杂烩聚合稿（标题信号太杂）
            if DIGEST_RE.search(title):
                stats["digest"] += 1
                continue
            summary_raw = strip_html(e.get("summary", "")).strip()
            if summary_raw:
                summary = summary_raw[:SUMMARY_CAP] + ("…" if len(summary_raw) > SUMMARY_CAP else "")
            else:
                summary = title[:SUMMARY_CAP]  # 源无摘要时退回标题
            stats["fetched"] += 1
            text = (title + " " + summary).lower()
            if not relevant(text, lang):
                stats["irrelevant"] += 1
                continue
            has_strong, score = score_text(text, lang)
            cat = infer_category(text, lang)
            if lang == "zh":
                title_d, summary_d, cat_d, src_d = (
                    {"zh": title, "en": ""}, {"zh": summary, "en": ""},
                    {"zh": cat, "en": ""}, {"zh": src, "en": ""})
            else:
                title_d, summary_d, cat_d, src_d = (
                    {"zh": "", "en": title}, {"zh": "", "en": summary},
                    {"zh": "", "en": cat}, {"zh": "", "en": src})
            results.append({
                "lang": lang,
                "title": title_d,
                "summary": summary_d,
                "category": cat_d,
                "source": src_d,
                "date": d.strftime("%Y-%m-%d"),
                "link": link,
                "_score": (score, has_strong),
            })
            seen.add(nl)

    results.sort(key=lambda x: (x["_score"][0], x["_score"][1], x["date"]), reverse=True)
    if cap and len(results) > cap:
        results = results[:cap]
    for it in results:
        it.pop("_score", None)
        stats["added"] += 1
    return results, stats


# --------------------------------------------------------------------------- #
# 主流程
# --------------------------------------------------------------------------- #
def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--lang", choices=["zh", "en", "both"], default="both")
    ap.add_argument("--days", type=int, default=3, help="抓取最近 N 天（滚动窗口，自带补跑能力）")
    ap.add_argument("--cap", type=int, default=12, help="每种语言单次最多新增条数")
    ap.add_argument("--dry-run", action="store_true", help="只打印，不写入归档")
    args = ap.parse_args()

    today = dt.datetime.now()
    existing = load_archive_links()
    langs = ["zh", "en"] if args.lang == "both" else [args.lang]

    all_new = []
    for lang in langs:
        print("== 语言 %s ==" % lang)
        items, stats = run_lang(lang, args.days, existing.setdefault(lang, set()), today, args.cap)
        for it in items:
            existing[lang].add(norm_link(it["link"]))
        all_new += items
        print("  抓取 %d · 聚合稿跳过 %d · 已存在 %d · 过期 %d · 不相关 %d · 新增 %d"
              % (stats["fetched"], stats["digest"], stats["dup"], stats["old"], stats["irrelevant"], stats["added"]))
        for it in items[:5]:
            t = it["title"].get(lang, "")
            print("    + [%s] %s  <%s>" % (it["date"], t[:60], it["source"].get(lang, "")))
        if len(items) > 5:
            print("    ... 另有 %d 条" % (len(items) - 5))

    if args.dry_run:
        print("\n[DRY-RUN] 未写入归档。本应新增 %d 条。" % len(all_new))
        if all_new:
            print(json.dumps(all_new[:3], ensure_ascii=False, indent=2))
        return

    if not all_new:
        print("\n本次无新增（窗口内无新鲜且相关的资讯）。归档保持不变，属正常。")
        return

    with io.open(DAILY, "w", encoding="utf-8") as f:
        json.dump(all_new, f, ensure_ascii=False, indent=2)
    print("\n已生成 %s（%d 条），调用写入器..." % (DAILY, len(all_new)))

    rc = subprocess.call([sys.executable, WRITE_PY, DAILY])
    try:
        os.remove(DAILY)
        print("临时文件 %s 已清理。" % DAILY)
    except Exception:
        pass
    sys.exit(rc)


if __name__ == "__main__":
    main()
