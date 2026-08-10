# -*- coding: utf-8 -*-
"""
FDE 资讯写入器 —— 资源沉淀版

把一份 JSON 资讯列表：
  1) 追加沉淀到 data/fde-archive.json（本站永久归档，按 slug 去重，历史全保留）
  2) 同步刷新 data/content.json 的 aiFdeNews（只保留最新 20 条，供页脚展示）

用法:
    python fde_news_write.py <news.json>

news.json 格式（数组）:
[
  {
    "title":   {"zh": "...", "en": "..."},
    "summary": {"zh": "一句话摘要", "en": "..."},
    "body":    {"zh": "本站编译整理的正文，段落之间用空行分隔", "en": "..."},   // 可选但强烈建议
    "category":{"zh": "行业趋势", "en": "Industry Trend"},
    "source":  {"zh": "来源媒体", "en": "Source"},
    "date":    "2026-08-10",
    "link":    "https://原文链接"
  }
]

规则:
- slug 由 link 稳定哈希生成 -> fde-YYYYMMDD-xxxxxxxx，同一条原文重复抓取会更新而非重复入库。
- 归档全量保留，按 date 倒序。
- content.json 的 aiFdeNews = 归档最前 20 条（含 slug，前台链接到本站详情页）。
- 写入前自动备份 content.json.bak / fde-archive.json.bak。
"""
import hashlib
import io
import json
import os
import re
import shutil
import sys
from datetime import date

HERE = os.path.dirname(os.path.abspath(__file__))
DATA_DIR = os.path.normpath(os.path.join(HERE, "..", "data"))
CONTENT = os.path.join(DATA_DIR, "content.json")
ARCHIVE = os.path.join(DATA_DIR, "fde-archive.json")

FOOTER_ITEMS = 20
REQUIRED_BI = ("title", "summary", "category", "source")


def fail(msg):
    sys.stderr.write("ERROR: %s\n" % msg)
    sys.exit(1)


def make_slug(item):
    """基于原文链接 + 当前语言生成稳定 slug，保证同一原文不重复入库。"""
    lang = item.get("lang") or "zh"
    base = (item.get("link") or "") + "|" + (item.get("title", {}).get(lang) or "") + "|" + lang
    digest = hashlib.sha1(base.encode("utf-8")).hexdigest()[:8]
    d = re.sub(r"[^0-9]", "", item.get("date") or "")[:8] or date.today().strftime("%Y%m%d")
    return "fde-%s-%s" % (d, digest)


def lang_of(it):
    return it.get("lang") or "zh"


def validate(items):
    if not isinstance(items, list):
        fail("news.json 顶层必须是数组")
    if not items:
        fail("news.json 为空")
    cleaned = []
    today = date.today().isoformat()
    for idx, it in enumerate(items):
        if not isinstance(it, dict):
            fail("第 %d 项不是对象" % idx)
        lang = lang_of(it)
        for key in REQUIRED_BI:
            v = it.get(key)
            if not isinstance(v, dict):
                fail("第 %d 项的 %s 必须是 {zh,en} 对象" % (idx, key))
            # 当前语言字段必须非空；另一语言允许为空（单一语言流）
            if not v.get(lang):
                fail("第 %d 项的 %s 的 %s 字段不能为空（lang=%s）" % (idx, key, lang, lang))
        if not it.get("date"):
            fail("第 %d 项缺少 date" % idx)
        if not re.match(r"^\d{4}-\d{2}-\d{2}$", it["date"]):
            fail("第 %d 项 date 必须是 YYYY-MM-DD 格式" % idx)
        link = it.get("link") or ""
        if not link.startswith("http"):
            fail("第 %d 项的 link 必须是 http/https 外部原文链接" % idx)

        body = it.get("body") or {}
        if not isinstance(body, dict):
            body = {"zh": str(body), "en": ""}
        body = {"zh": body.get("zh") or "", "en": body.get("en") or ""}
        # 当前语言正文缺失时用摘要兜底，保证详情页不空白
        if not body.get(lang):
            body[lang] = it["summary"][lang]

        rec = {
            "slug": "",
            "lang": lang,
            "title": it["title"],
            "summary": it["summary"],
            "body": body,
            "category": it["category"],
            "source": it["source"],
            "date": it["date"],
            "link": link,
            "archivedAt": it.get("archivedAt") or today,
        }
        rec["slug"] = it.get("slug") or make_slug(rec)
        cleaned.append(rec)
    return cleaned


def load_archive():
    if not os.path.exists(ARCHIVE):
        return []
    try:
        with io.open(ARCHIVE, "r", encoding="utf-8") as f:
            data = json.load(f)
    except Exception:
        return []
    if isinstance(data, dict) and isinstance(data.get("items"), list):
        return data["items"]
    if isinstance(data, list):
        return data
    return []


def merge_archive(existing, incoming):
    """按 slug 合并：已存在则更新内容（保留最早 archivedAt），否则新增。"""
    by_slug = {}
    order = []
    for it in existing:
        if not isinstance(it, dict) or not it.get("slug"):
            continue
        by_slug[it["slug"]] = it
        order.append(it["slug"])

    added, updated = 0, 0
    for it in incoming:
        slug = it["slug"]
        if slug in by_slug:
            old = by_slug[slug]
            it["archivedAt"] = old.get("archivedAt") or it["archivedAt"]
            by_slug[slug] = it
            updated += 1
        else:
            by_slug[slug] = it
            order.append(slug)
            added += 1

    merged = [by_slug[s] for s in order]
    merged.sort(key=lambda x: (x.get("date", ""), x.get("archivedAt", "")), reverse=True)
    return merged, added, updated


def main():
    if len(sys.argv) < 2:
        fail("用法: python fde_news_write.py <news.json>")
    src = sys.argv[1]
    if not os.path.exists(src):
        fail("找不到文件: %s" % src)
    if not os.path.exists(CONTENT):
        fail("找不到 content.json: %s" % CONTENT)

    with io.open(src, "r", encoding="utf-8") as f:
        incoming = json.load(f)

    incoming = validate(incoming)

    # 1) 合并归档
    archive, added, updated = merge_archive(load_archive(), incoming)
    if os.path.exists(ARCHIVE):
        shutil.copyfile(ARCHIVE, ARCHIVE + ".bak")
    with io.open(ARCHIVE, "w", encoding="utf-8") as f:
        json.dump(
            {"updatedAt": date.today().isoformat(), "total": len(archive), "items": archive},
            f, ensure_ascii=False, indent=2,
        )
        f.write("\n")

    # 2) 刷新 content.json 的 aiFdeNews（中文）/ aiFdeNewsEn（英文）各最新 20 条
    def build_footer(items):
        out = []
        for i, it in enumerate(items[:FOOTER_ITEMS]):
            out.append({
                "id": "ai-%d" % i,
                "slug": it["slug"],
                "title": it["title"],
                "summary": it["summary"],
                "category": it["category"],
                "source": it["source"],
                "date": it["date"],
                "link": it["link"],
            })
        return out

    zh_items = [it for it in archive if (it.get("lang") or "zh") == "zh"]
    en_items = [it for it in archive if it.get("lang") == "en"]
    footer_zh = build_footer(zh_items)
    footer_en = build_footer(en_items)

    with io.open(CONTENT, "r", encoding="utf-8") as f:
        data = json.load(f)
    shutil.copyfile(CONTENT, CONTENT + ".bak")
    data["aiFdeNews"] = footer_zh
    data["aiFdeNewsEn"] = footer_en
    with io.open(CONTENT, "w", encoding="utf-8") as f:
        json.dump(data, f, ensure_ascii=False, indent=2)
        f.write("\n")

    print("OK: 归档 %d 条（新增 %d / 更新 %d），页脚 中文 %d / 英文 %d"
          % (len(archive), added, updated, len(footer_zh), len(footer_en)))
    for it in zh_items[:3]:
        print("  - [zh][%s] %s  <%s>" % (it["date"], it["title"]["zh"], it["slug"]))
    for it in en_items[:3]:
        print("  - [en][%s] %s  <%s>" % (it["date"], it["title"]["en"], it["slug"]))


if __name__ == "__main__":
    main()
