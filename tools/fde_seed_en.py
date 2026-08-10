# -*- coding: utf-8 -*-
"""
一次性种子脚本：生成国际（英文）FDE 资讯 20 条，写入 data/_en_seed.json，
随后由 fde_news_write.py 合并进 fde-archive.json（lang=en）并刷新 aiFdeNewsEn。

所有内容均基于公开报道（OpenAI / Anthropic / AWS / Microsoft / Palantir / Sierra 等官方或行业媒体），
不编造数据；每条标注真实原文链接与来源。
"""
import json
import os

HERE = os.path.dirname(os.path.abspath(__file__))
OUT = os.path.normpath(os.path.join(HERE, "..", "data", "_en_seed.json"))


def item(title, summary, body, category, source, date, link):
    return {
        "lang": "en",
        "title": {"zh": "", "en": title},
        "summary": {"zh": "", "en": summary},
        "body": {"zh": "", "en": body},
        "category": {"zh": "", "en": category},
        "source": {"zh": "", "en": source},
        "date": date,
        "link": link,
    }


ITEMS = [
    item(
        "OpenAI launches the Deployment Company with $4B to embed FDEs in enterprises",
        "OpenAI stood up a majority-owned subsidiary seeded with more than $4 billion from 19 investors led by TPG, and acquired applied-AI firm Tomoro to bring roughly 150 Forward Deployed Engineers from day one.",
        "On 11 May 2026 OpenAI launched the OpenAI Deployment Company (DeployCo), a standalone, majority-owned business built to help organizations deploy AI across their core workflows. The venture was seeded with more than $4 billion from OpenAI and 19 investors, led by TPG with Advent International, Bain Capital and Brookfield as co-lead founding partners; Axios put the valuation near $14 billion including the raise.\n\nTo staff it, OpenAI agreed to acquire Tomoro, a UK applied-AI consultancy whose work includes an in-game support agent for Supercell that reached 110 million users within 12 weeks, plus deployments at Tesco and Virgin Atlantic. DeployCo embeds Forward Deployed Engineers directly inside client organizations to design, build, test and ship production systems rather than hand over slide decks.",
        "Capital & M&A", "OpenAI", "2026-05-11",
        "https://openai.com/index/openai-launches-the-deployment-company/",
    ),
    item(
        "Anthropic forms $1.5B enterprise deployment venture with Blackstone, H&F and Goldman Sachs",
        "Anthropic launched a joint venture with private-equity and Wall Street partners capitalized around $1.5 billion to deliver AI implementation services directly to enterprises, mirroring Palantir's forward-deployed model.",
        "On 4 May 2026 Anthropic announced an enterprise deployment joint venture with Blackstone, Hellman & Friedman and Goldman Sachs, capitalized around $1.5 billion, with each of Anthropic, Blackstone and H&F committing roughly $300 million, per reporting cited by TechCrunch and industry analyses.\n\nThe move followed OpenAI's own deployment unit by hours and was widely read as both labs betting that the services layer around enterprise AI - not the models themselves - will be the durable, billable business as model quality converges.",
        "Capital & M&A", "AINews.tech", "2026-05-04",
        "https://www.ainews.tech/blog/forward-deployed-engineer-race-what-it-means-for-builders",
    ),
    item(
        "AWS commits $1B to a Forward Deployed Engineering organization",
        "Amazon stood up a $1 billion forward-deployed engineering arm with no outside investors, organizing small senior squads into roughly 45-day customer engagements.",
        "On 30 June 2026 AWS created a $1 billion Forward Deployed Engineering organization funded entirely from internal Amazon resources. Rather than a consulting retainer, teams of five to six engineers embed inside a customer's environment for about 45-day cycles and build production agentic systems alongside the client's own staff.\n\nNamed early customers include the Allen Institute, Cox Automotive, the NBA, the NFL, Ricoh and Southwest Airlines. AWS describes the model as agentic-first and time-compressed, measured in days rather than the months a classic system-integration project runs.",
        "Enterprise Adoption", "Ecorpit", "2026-06-30",
        "https://ecorpit.com/microsoft-frontier-aws-ai-deployment-enterprise-2026",
    ),
    item(
        "Microsoft backs Frontier Company with $2.5B and 6,000 engineers",
        "Microsoft committed $2.5 billion and roughly 6,000 industry and engineering specialists to a standalone unit, Microsoft Frontier Company, to co-design and deploy AI on its stack for customers including Unilever and Novo Nordisk.",
        "On 2 July 2026 Microsoft announced Microsoft Frontier Company, a standalone operating business backed by $2.5 billion and about 6,000 specialists, led by Rodrigo Kede Lima, formerly president of Microsoft Asia. Initial clients named at launch were Unilever and Novo Nordisk.\n\nJudson Althoff, CEO of Microsoft's Commercial Business, framed the effort as going beyond 'Forward-Deployed Engineering' to become 'the largest, most capable, outcome-driven engineering organization in the industry,' aimed at closing the delivery gap that keeps Copilot and Azure AI seats from converting into results.",
        "Capital & M&A", "Ecorpit", "2026-07-02",
        "https://ecorpit.com/microsoft-frontier-aws-ai-deployment-enterprise-2026",
    ),
    item(
        "Four AI labs spent $9 billion in eight weeks building deployment arms",
        "Between 4 May and 2 July 2026, Anthropic, OpenAI, AWS and Microsoft each launched a billion-dollar unit embedding engineers inside enterprise customers - a combined roughly $9 billion bet on the deployment layer.",
        "Analysis of the four launches found the commitments add up to almost exactly $9 billion across about eight weeks: Anthropic's ~$1.5B joint venture, OpenAI's $4B DeployCo, AWS's $1B organization and Microsoft's $2.5B Frontier Company.\n\nTwo of the four are outside-capital joint ventures (Anthropic, OpenAI) betting deployment is a recurring, billable services line; the other two are internal commitments (AWS, Microsoft) that function more as distribution strategy to drive workload onto their clouds. All four concluded that whoever owns the 'last mile' of enterprise AI owns the renewal and the larger share of the AI budget.",
        "Capital & M&A", "AINews.tech", "2026-07-06",
        "https://www.ainews.tech/blog/forward-deployed-engineer-race-what-it-means-for-builders",
    ),
    item(
        "Forward Deployed Engineer called the fastest-growing AI role of 2026",
        "A May 2026 tally counted 224 open FDE roles across 118 companies, led by Palantir (51), OpenAI (31), Databricks (12), Mistral (11) and Cohere (10).",
        "Jobs-by-culture's May 2026 survey found 224 active Forward Deployed Engineer postings across 118 companies, with Palantir (51), OpenAI (31), Databricks (12), Mistral (11), Cohere (10), Cresta (10) and Scale AI (8) among the most active hirers.\n\nMid-level FDEs were reported at $300K-$450K total comp, senior at $450K-$550K and staff or principal above $600K - a premium the analysis attributes to the hybrid skill set of strong coding plus customer-facing communication plus AI product judgment.",
        "Talent Market", "JobsByCulture", "2026-05-20",
        "https://jobsbyculture.com/blog/forward-deployed-engineer-boom-2026",
    ),
    item(
        "FDE pay benchmarks 2026: $135K-$390K, and the travel that decides who lasts",
        "A compilation of nine real FDE postings shows base salaries from Palantir's $135K to Sierra Infrastructure's $390K, with travel demands of 25%-75% separating the roles people actually keep.",
        "Match.dev's August 2026 roundup of nine real FDE job posts shows base bands including Palantir $135K-$200K, OpenAI $162K-$280K, Ramp $189K-$330K, Baseten $165K-$330K, Scale AI $179K-$224K and Sierra Infrastructure $230K-$390K, with equity on top at most.\n\nThe piece stresses that travel is the column people skip: Palantir asks up to 25%, OpenAI up to 50% and Ramp up to 75% - three weeks in four on the road. It also notes the Wikipedia article explaining the term, created on 5 May 2026, was read 23,524 times in July, outpacing the DevOps article.",
        "Compensation", "Match.dev", "2026-08-06",
        "https://www.match.dev/post/what-is-a-forward-deployed-engineer",
    ),
    item(
        "What OpenAI's $4B DeployCo actually is - and what it means for buyers",
        "DeployCo is majority-owned by OpenAI, seeded at a ~$10B pre-money valuation, and staffed by acquiring Tomoro; analysts say it puts a model maker into the systems-integration business.",
        "Ecorpit's teardown reports DeployCo launched 11 May 2026 as a standalone, majority-owned OpenAI company seeded with $4 billion at roughly a $10 billion pre-money valuation (about $14 billion including the raise), with a reported guaranteed minimum 17.5% investor return and capped profits.\n\nThe core unit is the Forward Deployed Engineer who embeds in the client and writes production code, distinct from advisory consulting. Tomoro's track record - a Supercell support agent serving 110 million users in 12 weeks, plus Tesco and Virgin Atlantic - is the production outcome DeployCo is selling, and why FDE hiring now spans OpenAI, Anthropic and Google.",
        "Enterprise Adoption", "Ecorpit", "2026-05-15",
        "https://ecorpit.com/openai-deployco-enterprise-ai-deployment-2026",
    ),
    item(
        "Why 2026's deployment race started: 95% of enterprise GenAI pilots return nothing",
        "MIT's NANDA initiative found about 95% of enterprise generative-AI pilots show no measurable return, reframing the contest from model quality to deployment capability.",
        "Coverage of Microsoft's and AWS's launches ties the timing to a hard number from MIT's NANDA initiative: roughly 95% of enterprise generative-AI pilots deliver no measurable return. The bottleneck is integration - connecting a model to messy internal data, enforcing policy and adding audit trails - not raw intelligence.\n\nThat realization pushed the four largest AI vendors to place the same bet inside ten weeks: embed engineers inside customers to make AI work against real data, real compliance constraints and real legacy software, the part no API call fixes.",
        "Enterprise Adoption", "Ecorpit", "2026-07-08",
        "https://ecorpit.com/microsoft-frontier-aws-ai-deployment-enterprise-2026",
    ),
    item(
        "AI deployment, not model quality, is becoming the real enterprise moat",
        "With models like Claude Opus 5 holding price (about $5/$25 per million tokens), analysts argue the labs are quietly becoming consulting firms because pilots keep stalling.",
        "Momo Advisors argues OpenAI's Deployment Company 'looks a lot like a consulting firm,' with partners reading like a management-consulting roster - McKinsey, Bain & Company and Capgemini alongside TPG and Bain Capital. The trigger: the models got good but deployments did not.\n\nThe piece cites reporting that about 80% of customer pilots stall, and warns of a second-order effect - every deployment a lab runs is also field research that flows back to the vendor, not the customer. It advises enterprises to use the labs' deployment help but keep ownership of which workflows are worth automating.",
        "Industry Trend", "Momo Advisors", "2026-07-12",
        "https://blog.momoadvisors.com/ai-deployment-labs-become-consultants",
    ),
    item(
        "Palantir opens Forward Deployed Software Engineer, New Grad roles",
        "Palantir's new-grad FDSE posting emphasizes end-to-end ownership with customers from day one, 25-50% travel, and a $135K-$145K base salary.",
        "Palantir's posting for Forward Deployed Software Engineer, New Grad describes the role as a 'radical commitment to the outcome' - embedding engineers directly with customers to tackle pressing challenges, owning projects end-to-end from first customer conversation to shipped product.\n\nThe listing notes 25-50% travel depending on team and location, requires an engineering degree, and estimates base salary at $135,000-$145,000 plus restricted stock and sign-on bonus. New grads are promised real ownership on day one rather than a ticket queue.",
        "Talent Market", "Palantir Careers", "2026-08-01",
        "https://jobs.lever.co/palantir/e500bcf3-19d8-4d3c-b340-4d76e4a55b40",
    ),
    item(
        "Sierra's $950M raise values the agent builder at ~$15.8B and signals an FDE-style workforce",
        "Bret Taylor and Clay Bavor's Sierra raised $950M at a ~$15.8B valuation and is hiring forward-deployed infrastructure engineers; median engineer total comp is reported near $460K.",
        "Zero G Talent reports Sierra closed a $950M round at roughly a $15.8B valuation, with a job board showing 120+ open roles and a stated median engineer total comp around $460K (senior San Francisco above $520K). The hiring targets people who build and run agent systems inside Fortune 50 environments.\n\nThe presence of a 'Forward Deployed Infrastructure Engineer' role - owning end-to-end customer deployments of Sierra's AI platform across security and compliance - shows Sierra adopting the same embed-with-the-customer model that defines FDEs elsewhere, alongside a FedRAMP High pathway that will grow compliance hiring.",
        "Capital & M&A", "Zero G Talent", "2026-06-18",
        "https://zerogtalent.com/blog/sierra-ai-s-950m-series-at-15b-and-fedramp-high-certification-are-quietly-building-enterprise-ai-s-first-sovereign-agent-operations-workforce-and-the-bret-taylor-signal-changes-everything",
    ),
    item(
        "Sierra splits engineering into 20 agent specialties; APX feeds the pipeline",
        "Sierra's 120+ open roles reveal agent engineering, platform and forward-deployed infrastructure tracks, plus an APX early-career program whose members 'work directly with customers to build AI agents for leading global brands.'",
        "Zero G Talent's read of Sierra's careers page finds the single largest category is Agent Engineering - about 20 variations of 'Software Engineer, Agent' across global offices, many language-specific (Arabic, Cantonese, Dutch, French, German, Italian, Korean, Spanish, Thai). The platform layer adds Agent Architecture, Builder, Data Platform, Intelligence and SDK roles.\n\nA Forward Deployed Infrastructure Engineer role spans San Francisco, New York and London, suggesting Sierra embeds platform staff alongside customer-facing teams. The APX early-career program places members directly with customers building agents for major brands - a compressed path into enterprise-agent engineering.",
        "Talent Market", "Zero G Talent", "2026-06-20",
        "https://zerogtalent.com/blog/sierra-ai-s-10b-rise-and-the-ghostwriter-platform-are-fueling-a-hidden-hiring-blitz-in-enterprise-agent-engineering-and-san-francisco-is-quietly-becoming-the-new-capital-of-agentic-software",
    ),
    item(
        "Sierra posts Forward Deployed Infrastructure Engineer in Tokyo",
        "Sierra's Tokyo FDE role owns end-to-end customer deployments of its AI platform - VPC, permissioning, networking - and requires English and Japanese; the company was founded by Bret Taylor and Clay Bavor.",
        "Built In's listing for Sierra's Forward Deployed Infrastructure Engineer in Tokyo describes owning the full lifecycle of customer deployments: designing deployment architecture, provisioning cloud infrastructure, managing upgrades and incidents, and building runbooks and automation.\n\nThe role requires 4+ years in infrastructure, DevOps or solutions architecture with customer-facing deployment work, deep AWS/Terraform/container-orchestration experience, and English and Japanese communication. Sierra, founded by Bret Taylor (OpenAI board chair, ex-Salesforce co-CEO) and Clay Bavor (ex-Google), is primarily in-person with offices across nine cities.",
        "Talent Market", "Built In", "2026-06-25",
        "https://builtin.com/job/forward-deployed-infrastructure-engineer/9973277",
    ),
    item(
        "Databricks lists an AI Engineer - FDE role in Tokyo",
        "Databricks is hiring an AI Engineer (Forward Deployed Engineer) in Tokyo, joining a wave of data/AI platforms staffing field-deployment roles in Asia.",
        "Underprompt's Tokyo AI jobs board lists a Databricks 'AI Engineer - FDE (Forward Deployed Engineer)' opening in Tokyo, alongside regional postings from OpenAI, Anthropic, Cohere, ElevenLabs and Sierra. The FDE framing on a data-platform vendor shows the embed-with-customer model spreading beyond pure model labs.\n\nThe listing reflects broader regional demand: the same board shows OpenAI 'AI Deployment Manager (Builder)' and Anthropic channel roles in Tokyo, indicating that forward-deployed and deployment-management titles are now standard across the major AI platforms' Asia go-to-market.",
        "Talent Market", "Underprompt", "2026-08-09",
        "https://underprompt.com/jobs/location/tokyo",
    ),
    item(
        "Ramp's forward-deployed AI role: up to $330K and 75% travel",
        "Match.dev lists Ramp's FD AI position at $189K-$330K base with up to 75% travel - the highest travel ask in its FDE salary sample.",
        "Within Match.dev's August 2026 salary compilation, Ramp's forward-deployed AI engineering role carries a base band of $189,000-$330,000 plus equity, with experience of unspecified years and the steepest travel expectation in the sample at up to 75%.\n\nThat 75% figure - three weeks in four away from base - is exactly the column the analysis says decides whether someone lasts in the role. Ramp's inclusion alongside Palantir, OpenAI, Sierra, Baseten and Scale AI shows the title is now standard across high-growth fintech and AI infrastructure companies.",
        "Compensation", "Match.dev", "2026-08-06",
        "https://www.match.dev/post/what-is-a-forward-deployed-engineer",
    ),
    item(
        "Baseten FDE compensation lands at $165K-$330K",
        "Match.dev places Baseten's Forward Deployed Engineer band at $165K-$330K base with equity, reflecting demand for engineers who ship inference infrastructure inside customer environments.",
        "Baseten appears in Match.dev's FDE salary table at a $165,000-$330,000 base band plus equity, with two or more years of experience. Baseten, which sells infrastructure for running AI models in production, uses the FDE title for engineers who embed with customers to deploy and operate inference systems.\n\nThe band sits in the middle of the sample and reinforces the report's thesis that FDE pay is driven less by the tech stack (ordinary Python, TypeScript, cloud) and more by the willingness to sit inside a client's organization and still ship.",
        "Compensation", "Match.dev", "2026-08-06",
        "https://www.match.dev/post/what-is-a-forward-deployed-engineer",
    ),
    item(
        "Scale AI FDE band: $179K-$224K, hybrid three days",
        "Match.dev lists Scale AI's Forward Deployed Engineer at $179K-$224K with a hybrid (three days) schedule in SF or NYC - one of the few hybrid entries in the sample.",
        "Scale AI's FDE role in Match.dev's compilation carries a $179,000-$224,000 base band with two or more years of experience and a hybrid schedule of three days in San Francisco or New York. It is one of the few partially remote entries, contrasting with the heavy travel mandates elsewhere.\n\nScale AI's presence in the FDE hiring wave (Jobs-by-culture counted it among active hirers) shows data-labeling and model-evaluation leaders also adopting the embed-with-customer model to take their platforms into enterprise production.",
        "Compensation", "Match.dev", "2026-08-06",
        "https://www.match.dev/post/what-is-a-forward-deployed-engineer",
    ),
    item(
        "FDE earns a Wikipedia article, read 23,524 times in July",
        "The 'Forward Deployed Engineer' Wikipedia article, created 5 May 2026, was viewed 23,524 times in July - surpassing the DevOps article's 17,760, signaling the term has gone mainstream.",
        "Match.dev notes the Wikipedia article explaining 'Forward Deployed Engineer' was created on 5 May 2026 and read 23,524 times in July 2026, ahead of the DevOps article's 17,760 in the same month - a sign the once-niche Palantir-coined title has entered the mainstream technical lexicon.\n\nTwo years earlier, almost no company outside Palantir used the phrase; by 2026 OpenAI runs a forward-deployed engineering department with its own hiring manager and recruiter, and the term now appears across job boards from Palantir to Sierra to Databricks.",
        "Industry Trend", "Match.dev", "2026-08-06",
        "https://www.match.dev/post/what-is-a-forward-deployed-engineer",
    ),
    item(
        "Cohere and Mistral join the FDE hiring wave",
        "Alongside Palantir and OpenAI, Jobs-by-culture counted Cohere (10) and Mistral (11) among the 118 companies actively posting Forward Deployed Engineer roles in May 2026.",
        "Jobs-by-culture's May 2026 tally placed Cohere and Mistral among the most active FDE hirers, with about 10 and 11 open postings respectively, part of a field of 118 companies actively recruiting for the role.\n\nThe inclusion of European model labs (Mistral) and enterprise-AI firms (Cohere) shows forward-deployed engineering is no longer a US-big-tech or Palantir-only phenomenon but a global hiring category spanning model makers, data platforms and vertical system integrators.",
        "Talent Market", "JobsByCulture", "2026-05-22",
        "https://jobsbyculture.com/blog/forward-deployed-engineer-boom-2026",
    ),
]


def main():
    with open(OUT, "w", encoding="utf-8") as f:
        json.dump(ITEMS, f, ensure_ascii=False, indent=2)
    print("wrote %d EN items -> %s" % (len(ITEMS), OUT))


if __name__ == "__main__":
    main()
