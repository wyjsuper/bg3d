# -*- coding: utf-8 -*-
"""
一次性种子脚本：把 content.json 现有 aiFdeNews 沉淀成本站归档，并补写正文 body。
生成 tools/_seed.json 后交给 fde_news_write.py 写入。
"""
import io
import json
import os
import subprocess
import sys

HERE = os.path.dirname(os.path.abspath(__file__))
CONTENT = os.path.normpath(os.path.join(HERE, "..", "data", "content.json"))
SEED = os.path.join(HERE, "_seed.json")

# 每条的补充正文（按标题关键词匹配）：段1 事件细节，段2 数据与背景
DETAIL = {
    "长安汽车": (
        "此次合作采取「联合团队+脱产集训」的组织形式。长安汽车内部选拔 24 名业务与技术骨干脱产两个月，腾讯派出 10 位产品、研发、交付专家驻场，双方组成一支 34 人的联合 FDE 团队，直接坐进业务部门办公。",
        "团队围绕软件研发、合同智审、智能客服等 7 大核心场景构建 Agent，目标不是交付一套系统，而是在两个月内把「怎么把 AI 用起来」的方法论留在长安内部——集训结束后，这 24 名业务骨干本身就成为企业内部的 FDE 种子。",
        "This is a joint-team, full-time bootcamp model: Changan seconded 24 business and technical staff for two months while Tencent embedded 10 product, engineering and delivery specialists on site, forming a 34-person joint FDE team seated inside business units.",
        "The team builds agents across seven core scenarios including software R&D, contract review and intelligent customer service. The goal is not shipping a system but leaving the know-how inside Changan — after the bootcamp the 24 staff become the company's own FDE seeds.",
    ),
    "AgenticCareers": (
        "AgenticCareers 是专门追踪 AI 原生岗位的招聘数据平台。截至 2026 年 8 月，其数据库共收录 36 个在招 FDE 岗位，分布在 21 家公司，最活跃的雇主为 Databricks、Brex 与 Clera。",
        "薪资区间中位数为 15.2 万–24 万美元，头部企业 Ramp 开出 18.9 万–33 万美元。值得注意的是仅 11% 的岗位接受完全远程——FDE 的「驻场」属性在招聘条件上被明确固化，这与传统软件工程师岗位形成鲜明反差。",
        "AgenticCareers tracks AI-native roles. As of August 2026 its database lists 36 open FDE positions across 21 companies, with Databricks, Brex and Clera the most active employers.",
        "Median pay lands at $152K–$240K, with Ramp topping the range at $189K–$330K. Notably only 11% of listings accept fully remote work — the on-site nature of FDE is written directly into hiring terms, in sharp contrast with traditional engineering roles.",
    ),
    "The Deployment Company": (
        "The Deployment Company 是一家专为「把 FDE 嵌入企业」而设立的合资公司，由 TPG 领投，Advent、贝恩资本、博枫联合领投，募资规模超过 40 亿美元。高盛、麦肯锡、凯捷作为创始合作伙伴加入。",
        "公司同时完成对 Tomoro 的收购，一次性获得约 150 名资深 FDE。这标志着 FDE 从「模型厂商内部的一个职能」升级为「独立募资、独立估值的商业实体」——交付能力本身正在被资本单独定价。",
        "The Deployment Company is a joint venture built specifically to embed FDEs inside enterprises, raising over $4B led by TPG with Advent, Bain Capital and Brookfield co-leading. Goldman Sachs, McKinsey and Capgemini joined as founding partners.",
        "It simultaneously acquired Tomoro, bringing roughly 150 senior FDEs in one move. This marks FDE graduating from an internal function at model vendors into a separately funded, separately valued business entity — delivery capability is now priced on its own.",
    ),
    "Ode": (
        "Ode 由 Anthropic 与黑石、Hellman & Friedman 共同成立，2026 年 7 月正式落地，投资方还包括高盛、General Atlantic、阿波罗与新加坡政府投资公司 GIC。",
        "与 OpenAI 的 The Deployment Company 形成直接对照：两大模型厂商在同一季度分别拉上顶级私募资本，成立专门做企业交付的独立公司。这说明模型能力的竞争已经外溢到「谁能把模型装进企业流程」的竞争。",
        "Ode was founded by Anthropic together with Blackstone and Hellman & Friedman, formally launched in July 2026, with Goldman Sachs, General Atlantic, Apollo and GIC also investing.",
        "It mirrors OpenAI's The Deployment Company almost exactly: within a single quarter both leading model vendors partnered with top-tier private capital to spin up dedicated enterprise-delivery companies. Competition has spilled from model capability into who can actually wire models into enterprise workflows.",
    ),
    "Salesforce": (
        "Salesforce 公开承诺组建千人规模的 FDE 队伍，并配套六周入职培训体系，用于推动 Agentforce 在客户侧的实际落地。谷歌云 CEO 同期宣布将再招数百名 FDE，并在 GTM 部门单独设立 AI 组织。",
        "对 SaaS 厂商而言，Agent 类产品的销售逻辑与传统订阅制不同：客户买的不是席位，而是「场景跑通」。这迫使厂商把交付人力前置到售前，FDE 因此从成本项变成了增长项。",
        "Salesforce publicly committed to building a 1,000-person FDE organisation with a six-week onboarding programme to drive real-world Agentforce adoption. Google Cloud's CEO separately announced hundreds more FDE hires and a dedicated AI organisation inside GTM.",
        "For SaaS vendors, agent products don't sell like seat-based subscriptions — customers buy a working scenario, not licences. That pushes delivery headcount into pre-sales, turning FDE from a cost line into a growth line.",
    ),
    "不足 41%": (
        "Gartner 预计 2026 年全球 AI 总投入将达到 2.52 万亿美元，超过九成企业开展过 AI 试点，但能真正进入生产环境的比例不足四成。",
        "报告同时给出一个更尖锐的判断：到 2027 年，超过四成缺乏持续迭代能力的智能体项目将退出业务体系。换言之，一次性交付的 Agent 项目在两年内会被自然淘汰，能否建立迭代机制成为项目存活的分水岭。",
        "Gartner projects global AI spending will reach $2.52 trillion in 2026. Over 90% of enterprises have run AI pilots, yet fewer than 40% reach production.",
        "The report adds a sharper call: by 2027, more than 40% of agent projects lacking continuous iteration capability will be retired. One-shot agent deployments will wash out within two years — whether an iteration loop exists becomes the survival line.",
    ),
    "Palantir Q2": (
        "Palantir 2026 年二季度营收大幅增长并上调全年指引，其长期坚持的 FDE 组织模式随财报再度进入市场视野，A 股相关概念随之升温。",
        "国联民生证券研报指出，FDE 有望成为连接模型厂商、软件服务商与行业客户的关键组织形态；A 股万兴科技被点名卡位「场景交付」环节。资本市场对 FDE 的定价逻辑，正在从「人力外包」转向「交付资产」。",
        "Palantir posted strong Q2 2026 revenue growth and raised full-year guidance, putting its long-standing FDE operating model back in the spotlight and lifting related A-share names.",
        "A Guolian Minsheng Securities note argues FDE could become the key organisational bridge between model vendors, software integrators and industry clients, singling out Wondershare for its position in scenario delivery. Capital markets are repricing FDE from staffing arbitrage to delivery assets.",
    ),
    "钉钉六星": (
        "典铭云赛作为钉钉六星服务商，把传统的「调研—方案—汇报—签约—实施」五步流程，压缩为「场景发现—现场演示—快速搭建—交付确认」四步。",
        "关键变化在于顺序：客户在调研阶段就能看到可运行的效果，而不是等到实施期。交付周期因此从 2–4 周压缩到 1–3 天。这种「先跑起来再谈合同」的方式，本质上是把 FDE 的现场构建能力当作销售工具使用。",
        "Dianming Yunsai, a six-star DingTalk partner, collapsed the classic five-step cycle (survey → proposal → review → contract → implementation) into four: scenario discovery → live demo → rapid build → delivery sign-off.",
        "The critical change is sequencing — clients see something running during discovery rather than after signing. Delivery time dropped from 2–4 weeks to 1–3 days. Building on site becomes the sales motion itself.",
    ),
    "IDC": (
        "IDC 观察到中国市场的 FDE 实践正在快速分化。北京、武汉已将 FDE 相关内容写入智能体专项政策，地方政府开始把这一角色纳入产业扶持范畴。",
        "但 IDC 同时提醒：部分服务商只是给传统驻场实施换了个新标签。真正拉开差距的是交付逻辑本身——能否说清与传统实施的实质区别，能否在项目结束后留下可复用的资产，而不是留下一份验收报告。",
        "IDC notes that FDE practice in China is diverging fast. Beijing and Wuhan have written FDE into dedicated agent policies, bringing the role into local industrial support programmes.",
        "IDC also warns that some vendors have merely relabelled traditional on-site implementation. The real differentiator is delivery logic — whether a firm can articulate a substantive difference from legacy implementation, and whether reusable assets remain after sign-off rather than just an acceptance report.",
    ),
    "联想": (
        "「佛山市人工智能赋能制造业数智服务平台」已进入交付阶段。联想将架构师与工程师以 FDE 模式直接驻扎生产车间，与产线人员同场作业。",
        "后方设立「擎天创新实验室」承接前线反馈，持续迭代工业智能体。这种「前线驻场 + 后方实验室」的双层结构，是制造业场景下 FDE 落地的典型形态：现场解决具体问题，后方沉淀通用能力。",
        "The Foshan AI-for-manufacturing digital service platform has entered delivery. Lenovo embedded architects and engineers directly on the shop floor in FDE mode, working alongside production staff.",
        "A back-line Qingtian Innovation Lab absorbs frontline feedback and iterates the industrial agents. This front-line-plus-lab structure is the typical FDE shape in manufacturing: solve concrete problems on site, distil general capability behind the line.",
    ),
    "腾讯研究院": (
        "腾讯研究院发布的 FDE 行业观察报告指出，AI 落地的核心矛盾正在发生位移：从「模型能不能做到」，转向「组织怎么把它用起来」。",
        "报告给出的判断标准很直接——FDE 与传统驻场的本质区别，在于项目结束后能否沉淀为可复用资产。如果交付物只是一次性的定制系统，那么无论现场投入多少人力，都仍属于传统实施范畴。",
        "A Tencent Research Institute report on FDE argues the central tension in AI adoption has shifted from whether the model can do it to how the organisation actually uses it.",
        "Its test is blunt: the substantive difference between FDE and traditional on-site implementation is whether reusable assets remain after the project ends. If the deliverable is a one-off custom system, it is legacy implementation regardless of headcount deployed.",
    ),
    "瓴羊": (
        "瓴羊 FDE 团队进场雅迪后，横跨十几个部门开展了 78 场深度驻场调研，团队成员反复收听用户热线录音，以还原真实的服务断点。",
        "项目分两期落地：一体化工作台、坐席辅助与热线机器人，服务覆盖 4 万家门店的客服体系。78 场调研这一数字本身说明了 FDE 的成本结构——大量时间花在理解业务，而不是写代码。",
        "After entering Yadea, the Lingyang FDE team ran 78 deep on-site interviews across more than a dozen departments, repeatedly listening to hotline recordings to reconstruct real service breakpoints.",
        "Delivery came in two phases — a unified workbench, agent assist and a hotline bot — covering a service system spanning 40,000 stores. The 78-interview figure itself reveals the FDE cost structure: most time goes into understanding the business, not writing code.",
    ),
    "85% 技术服务商": (
        "Gartner 预测到 2026 年底，超过 85% 的技术服务商将以 FDE 作为交付 AI 的核心方式。这一预测的背后是巨头们已经落地的真金白银投入。",
        "微软向 Microsoft Frontier 投入 25 亿美元并新增 6000 名工程专家；AWS 投资 10 亿美元建立前沿部署工程中心；谷歌、OpenAI、Anthropic 同步扩编。交付人力正在成为 AI 竞争的第二战场。",
        "Gartner forecasts that by end-2026 more than 85% of technology service providers will use FDE as their core AI delivery model, backed by capital already committed.",
        "Microsoft put $2.5B into Microsoft Frontier and added 6,000 engineering specialists; AWS invested $1B in a forward-deployment engineering centre; Google, OpenAI and Anthropic are scaling in parallel. Delivery headcount has become the second battlefield of AI competition.",
    ),
    "FDX": (
        "MIT 材料学博士 Rick Manelius 提出了 FDX（Forward Deployed Executive，前线部署高管）这一新角色。他的类比是：如果 FDE 是 AI 项目的「外置 CTO」，FDX 就是「外置 CEO」。",
        "FDX 的职责不是写代码，而是帮企业高管识别真问题、设定优先级，并跨越组织与心理层面的门槛。这一角色的出现说明，AI 落地卡住的地方往往不在技术层，而在决策层——技术可行不等于组织愿意改。",
        "Rick Manelius, an MIT materials science PhD, proposed FDX — Forward Deployed Executive. His analogy: if FDE is an outsourced CTO for an AI project, FDX is the outsourced CEO.",
        "The FDX doesn't write code; they help executives identify the real problem, set priorities and cross organisational and psychological thresholds. The role's emergence signals that AI adoption stalls at the decision layer, not the technical one — feasible is not the same as willing to change.",
    ),
    "智能体开始构建智能体": (
        "在 2026 年产品演示中，Palantir 展示了其 AI FDE 能力：智能体可以自主编写 AIP Logic 函数、创建评估用例、调试系统，并在持续循环中把初步方案推进到生产级系统。",
        "这意味着 FDE 工作流中重复度最高的部分（搭建、调试、评估）开始被自动化。人类 FDE 的价值将进一步向前移——从「能搭出来」转向「知道该搭什么」，业务理解与问题定义成为不可替代的部分。",
        "In a 2026 product demo, Palantir showed its AI FDE autonomously writing AIP Logic functions, creating evaluations, debugging systems, and iterating an initial approach into a production-grade system.",
        "The most repetitive parts of FDE work — building, debugging, evaluating — are being automated. Human FDE value shifts further upstream, from being able to build it to knowing what to build; business understanding and problem framing become the irreplaceable part.",
    ),
    "UST": (
        "UST 与 Anthropic 达成合作，计划培训 2 万名员工使用 Claude，其中包括直接进入客户团队工作的前线部署工程师，并建立专门的 Claude 部署团队。",
        "这是 FDE 模式通过系统集成商向外扩散的典型案例。模型厂商自身的 FDE 团队规模有限，借助 SI 的既有客户关系和交付人力做规模化复制，是目前最现实的扩张路径。",
        "UST partnered with Anthropic to train 20,000 employees on Claude, including forward deployed engineers who embed directly with client teams, plus a dedicated Claude deployment practice.",
        "This is FDE spreading outward through system integrators. Model vendors' own FDE teams are inherently small; leveraging an SI's existing client relationships and delivery bench is currently the most realistic path to scale.",
    ),
    "48.5 万美元": (
        "硅谷资深 FDE 年包中位数已达 48.5 万美元。分厂商看：OpenAI 旧金山岗位年薪 16.2 万–28 万美元并附加股权，Anthropic 为 20 万–30 万美元。",
        "国内市场同样水涨船高：字节豆包 FDE 月薪 35–70K、按 15 薪计算，年薪最高可达 105 万元。薪酬曲线陡峭说明供给严重不足——既懂模型又懂业务、还愿意驻场的人，本身就是稀缺组合。",
        "Median total compensation for senior FDEs in Silicon Valley has reached $485K. By employer: OpenAI's San Francisco roles pay $162K–$280K plus equity; Anthropic pays $200K–$300K.",
        "China follows the same curve — ByteDance's Doubao FDE roles pay ¥35–70K monthly across 15 months, up to ¥1.05M a year. The steep curve reflects severe supply shortage: people who understand both models and business, and will work on site, are a rare combination.",
    ),
    "729%": (
        "Indeed 数据显示，2025 年 4 月至 2026 年 4 月，平台上的 FDE 职位数量从 643 条飙升至 5330 条，一年增幅达 729%。",
        "LinkedIn 数据同样印证了这一趋势，2023 至 2025 年全球 FDE 岗位持续增长。需要注意的是，岗位数量激增的同时职责定义仍不统一，不同公司对 FDE 的期待差异很大，求职者需要逐一确认实际工作内容。",
        "Indeed data shows FDE listings jumped from 643 to 5,330 between April 2025 and April 2026 — a 729% increase in one year.",
        "LinkedIn data corroborates sustained global growth from 2023 to 2025. Note that job definitions remain inconsistent as volume explodes; expectations differ widely between companies, so candidates should verify the actual scope role by role.",
    ),
    "博彦科技": (
        "博彦科技在金融 AI 领域采用 FDE 模式，摒弃传统按人天核算的计费方式，在国内外双市场统一实行效果付费、收益共担。",
        "公开的批量项目成效包括：信贷不良率压降 3%–10%，后台运营人力缩减 20% 以上，监管报送工作量降低 60%。计费方式的改变是 FDE 模式最硬的验证——只有真正能对结果负责，才敢按效果收费。",
        "Beyondsoft applies the FDE model in financial AI, abandoning man-day billing in favour of outcome-based pricing and shared upside across both domestic and overseas markets.",
        "Reported results across a batch of projects: 3–10% reduction in non-performing loans, over 20% cut in back-office operations headcount, and a 60% drop in regulatory reporting workload. The billing shift is the hardest proof point for FDE — only teams that can own outcomes dare to charge on them.",
    ),
    "MIT 报告": (
        "MIT Sloan Management Review 于 2026 年 7 月底发布报告，指出 95% 的企业生成式 AI 试点未产生可衡量的回报。",
        "这一数字与 FDE 起薪 40 万美元/年形成鲜明对照，也解释了 FDE 岗位为何暴涨：模型能力持续溢出，企业侧却接不住，中间缺少能把业务语言翻译成系统实现的人。FDE 正是为填补这个断层而出现的角色。",
        "MIT Sloan Management Review's late-July 2026 report found that 95% of enterprise generative AI pilots produced no measurable return.",
        "That figure sits in stark contrast with FDE starting salaries near $400K a year — and explains the hiring surge. Model capability keeps overflowing while enterprises can't absorb it, because nobody in between translates business language into working systems. FDE exists to fill that gap.",
    ),
}

# 按分类的收束段（第三段），提供本站视角的解读
CLOSING = {
    "落地案例": (
        "对正在评估 AI 落地路径的团队而言，这类案例的参考价值不在技术选型，而在组织安排：谁进场、进场多久、结束后留下什么。",
        "For teams evaluating AI adoption, the reference value here is organisational rather than technical: who embeds, for how long, and what remains afterwards.",
    ),
    "人才市场": (
        "岗位数量和薪酬区间是观察行业热度最直接的指标，但真正值得关注的是任职要求的变化——它反映了企业对这个角色的期待正在如何收敛。",
        "Headcount and pay bands are the most direct heat indicators, but the more telling signal is how job requirements evolve — they show where expectations for the role are converging.",
    ),
    "资本动向": (
        "资本的下注方向通常领先于市场共识。当交付能力开始被单独估值，说明行业已经承认「模型可用」与「模型好用」之间存在巨大的价值空间。",
        "Capital tends to lead consensus. Once delivery capability gets valued on its own, the industry has effectively conceded there is a large value gap between a usable model and a useful one.",
    ),
    "资本市场": (
        "资本的下注方向通常领先于市场共识。当交付能力开始被单独估值，说明行业已经承认「模型可用」与「模型好用」之间存在巨大的价值空间。",
        "Capital tends to lead consensus. Once delivery capability gets valued on its own, the industry has effectively conceded there is a large value gap between a usable model and a useful one.",
    ),
    "巨头动向": (
        "头部厂商的组织调整往往会在 6–12 个月内传导到整个供应链，中小服务商可以据此提前调整自身的能力结构。",
        "Organisational moves at the top usually propagate through the supply chain within 6–12 months, giving smaller providers a window to adjust their own capability mix.",
    ),
    "研究报告": (
        "研究机构的数据提供了行业基准线。对具体企业来说，更有用的做法是拿这些比例对照自身项目，判断自己处在分布的哪一段。",
        "Research data provides an industry baseline. The more useful exercise is benchmarking your own projects against these ratios to see where you sit in the distribution.",
    ),
    "行业趋势": (
        "趋势判断需要配合执行细节才有意义。关注这类预测时，值得同时追踪厂商实际投入的人力与资金规模，而不只是口径。",
        "Trend calls only matter alongside execution detail. Track the actual headcount and capital vendors commit, not just the messaging.",
    ),
    "交付模式": (
        "交付流程的重构往往比技术升级带来更直接的效率变化，也更容易被同行复制——这意味着窗口期不会太长。",
        "Restructuring the delivery process often yields more immediate efficiency gains than technical upgrades, and is easier for peers to copy — the window won't stay open long.",
    ),
    "市场分析": (
        "在概念快速扩散的阶段，区分实质与包装是采购方最需要的能力。可复用资产的有无，是一个足够简单也足够硬的判据。",
        "While a concept spreads fast, buyers most need to separate substance from packaging. Whether reusable assets exist is a simple and sufficiently hard test.",
    ),
    "新兴角色": (
        "角色的细分通常意味着市场正在成熟。当交付链条上出现更多专门化分工时，说明这套模式已经跑通并开始规模化。",
        "Role specialisation usually signals a maturing market. More division of labour along the delivery chain means the model works and is starting to scale.",
    ),
    "技术前沿": (
        "自动化正在重塑 FDE 的能力结构。可被工具替代的部分会快速贬值，而对业务问题的定义能力会持续升值。",
        "Automation is reshaping the FDE skill stack. What tooling can replace depreciates fast; the ability to frame the business problem keeps appreciating.",
    ),
    "生态合作": (
        "生态扩散是模式规模化的必经阶段。模型厂商与系统集成商的分工方式，将在很大程度上决定 FDE 未来的组织形态。",
        "Ecosystem diffusion is how a model scales. How work splits between model vendors and system integrators will largely determine the future shape of FDE organisations.",
    ),
    "薪酬观察": (
        "薪酬曲线是供需关系最诚实的反映。价格居高不下，说明合格人才的培养速度仍然远远跟不上需求扩张。",
        "Pay curves are the most honest read on supply and demand. Persistently high prices mean qualified talent still isn't being produced fast enough.",
    ),
}

DEFAULT_CLOSING = (
    "FDE 相关动态更新较快，本站每日汇总全网公开信息并沉淀存档，便于回溯对比。",
    "FDE news moves quickly; this site aggregates public reporting daily and archives it locally for later comparison.",
)


def build_body(item):
    title_zh = item["title"]["zh"]
    detail = None
    for key, val in DETAIL.items():
        if key in title_zh:
            detail = val
            break

    cat = item["category"]["zh"]
    closing = CLOSING.get(cat, DEFAULT_CLOSING)

    if detail:
        zh = "\n\n".join([detail[0], detail[1], closing[0]])
        en = "\n\n".join([detail[2], detail[3], closing[1]])
    else:
        zh = "\n\n".join([item["summary"]["zh"], closing[0]])
        en = "\n\n".join([item["summary"]["en"], closing[1]])
    return {"zh": zh, "en": en}


def main():
    with io.open(CONTENT, "r", encoding="utf-8") as f:
        data = json.load(f)
    news = data.get("aiFdeNews", [])
    if not news:
        sys.stderr.write("ERROR: content.json 中没有 aiFdeNews\n")
        sys.exit(1)

    seed = []
    for it in news:
        seed.append({
            "title": it["title"],
            "summary": it["summary"],
            "body": build_body(it),
            "category": it["category"],
            "source": it["source"],
            "date": it["date"],
            "link": it["link"],
        })

    with io.open(SEED, "w", encoding="utf-8") as f:
        json.dump(seed, f, ensure_ascii=False, indent=2)

    print("seed 已生成: %s (%d 条)" % (SEED, len(seed)))
    rc = subprocess.call([sys.executable, os.path.join(HERE, "fde_news_write.py"), SEED])
    if rc == 0:
        os.remove(SEED)
        print("seed 临时文件已清理")
    sys.exit(rc)


if __name__ == "__main__":
    main()
