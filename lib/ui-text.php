<?php
/**
 * 页面硬编码文案（对应 ui-text.ts），双语常量
 * 使用：bg_pick($UI['threeds']['title'], $lang)
 */
$UI = array(
  'common' => array(
    'contactBtn' => array('zh' => '立即咨询', 'en' => 'Contact Us'),
    'viewCase'   => array('zh' => '查看案例', 'en' => 'View Case'),
    'home'      => array('zh' => '首页', 'en' => 'Home'),
    'navMenu'   => array('zh' => '导航菜单', 'en' => 'Navigation'),
  ),

  'home' => array(
    'badge' => array('zh' => 'BEIGANG DESIGN® · 数字化品牌增长伙伴', 'en' => 'BEIGANG DESIGN® · Digital Brand Growth Partner'),
    'capMatrix' => array('zh' => '品牌 × 网站 × 营销 × 三维，一体化数字增长引擎', 'en' => 'Brand × Web × Marketing × 3D — Integrated Digital Growth Engine'),
    'capItems' => array(
      array('zh' => '品牌识别', 'en' => 'Brand Identity'),
      array('zh' => '官网搭建', 'en' => 'Website Build'),
      array('zh' => '营销推广', 'en' => 'Marketing'),
      array('zh' => '三维动画', 'en' => '3D Animation'),
    ),
    'sysStatus' => array(
      array('zh' => '品牌实战', 'en' => 'Brand Practice'),
      array('zh' => '合作客户', 'en' => 'Clients'),
      array('zh' => '技术团队', 'en' => 'Tech Team'),
      array('zh' => '在线状态', 'en' => 'Status'),
    ),
    'capabilities' => array(
      array(
        'title' => array('zh' => '品牌识别系统 VIS & LOGO', 'en' => 'Brand Identity System VIS & LOGO'),
        'desc'  => array('zh' => '从命名、LOGO 到 VI 规范，建立可延展、可一致的视觉语言，让每次对外露出都成为资产积累。', 'en' => 'From naming and LOGO to VI standards, build a scalable, consistent visual language that turns every touchpoint into brand equity.'),
        'tags'  => array(array('zh' => '品牌命名', 'en' => 'Brand Naming'), array('zh' => 'LOGO 设计', 'en' => 'LOGO Design'), array('zh' => 'VI 规范', 'en' => 'VI Standards'), array('zh' => '宣传画册', 'en' => 'Brochures')),
      ),
      array(
        'title' => array('zh' => '品牌官网 & 数字搭建', 'en' => 'Brand Website & Digital Build'),
        'desc'  => array('zh' => '策划—设计—上线运营一体化交付，覆盖官网、小程序、公众号到 B2B/B2C 全渠道阵地。', 'en' => 'Strategy—design—launch integrated delivery, covering websites, mini-programs, official accounts and B2B/B2C channels.'),
        'tags'  => array(array('zh' => '官网建设', 'en' => 'Website Dev'), array('zh' => '小程序', 'en' => 'Mini-program'), array('zh' => '公众号', 'en' => 'Official Account'), array('zh' => '独立站', 'en' => 'Independent Site')),
      ),
      array(
        'title' => array('zh' => '数字营销推广', 'en' => 'Digital Marketing Promotion'),
        'desc'  => array('zh' => '站内站外 SEO 与广告组合拳，把流量真正转化为成交，用数据驱动增长闭环。', 'en' => 'On-site and off-site SEO plus advertising, turning traffic into conversions with data-driven growth loops.'),
        'tags'  => array(array('zh' => 'SEO 优化', 'en' => 'SEO'), array('zh' => '广告投放', 'en' => 'Ad Placement'), array('zh' => '内容营销', 'en' => 'Content Marketing'), array('zh' => '转化分析', 'en' => 'Conversion Analysis')),
      ),
      array(
        'title' => array('zh' => '营销 & 设计 包年服务', 'en' => 'Annual Marketing & Design Service'),
        'desc'  => array('zh' => '一支专属年度团队持续输出能力，把外包变成可预期、可积淀的长期增长伙伴。', 'en' => 'A dedicated annual team delivering continuous brand design and digital marketing capabilities, turning outsourcing into a predictable, compounding growth partner.'),
        'tags'  => array(array('zh' => '专属团队', 'en' => 'Dedicated Team'), array('zh' => '月度输出', 'en' => 'Monthly Output'), array('zh' => '长期陪跑', 'en' => 'Long-term Partner'), array('zh' => '效果可测', 'en' => 'Measurable Results')),
      ),
    ),
    'capSectionTitle' => array('zh' => '一体化数字增长能力矩阵', 'en' => 'Integrated Digital Growth Capability Matrix'),
    'capSectionDesc' => array('zh' => '从品牌识别到网站搭建，从营销推广到三维虚拟——以结构化能力支撑企业全链路增长。', 'en' => 'From brand identity to website build, from marketing to 3D visualization — structured capabilities supporting full-chain enterprise growth.'),
    'synergyTitle' => array('zh' => '网站设计 & 运营推广，1 + 1 + 1 > 3', 'en' => 'Web Design & Marketing, 1 + 1 + 1 > 3'),
    'synergyDesc' => array('zh' => '品牌官网、B2B/B2C 搭建运营与数字营销推广三者协同，让流量真正转化为成交。', 'en' => 'Brand websites, B2B/B2C operations and digital marketing work in synergy, turning traffic into real conversions.'),
    'synergyItems' => array(
      array('t' => array('zh' => '品牌官网', 'en' => 'Brand Website'), 'd' => array('zh' => '策划—设计—上线运营一体化交付', 'en' => 'Strategy—design—launch integrated delivery')),
      array('t' => array('zh' => 'B2B / B2C 搭建运营', 'en' => 'B2B / B2C Build & Ops'), 'd' => array('zh' => '官网、小程序、公众号全渠道', 'en' => 'Website, mini-program, official account omnichannel')),
      array('t' => array('zh' => '数字营销推广', 'en' => 'Digital Marketing'), 'd' => array('zh' => '站内站外 SEO / 广告组合拳', 'en' => 'On/off-site SEO / advertising combo')),
    ),
    'annualTitle' => array('zh' => '营销 & 设计 包年服务', 'en' => 'Annual Marketing & Design Service'),
    'annualDesc' => array('zh' => '一支专属的年度专业团队，持续输出品牌设计与数字营销能力，把外包变成可预期、可积淀的长期增长伙伴。', 'en' => 'A dedicated annual team delivering continuous brand design and digital marketing capabilities, turning outsourcing into a predictable, compounding growth partner.'),
    'annualBtn' => array('zh' => '了解包年服务', 'en' => 'Learn More'),
    'casesTitle' => array('zh' => '精选案例', 'en' => 'Featured Cases'),
    'casesDesc' => array('zh' => '从品牌 VI 到外贸独立站，从宣传片到三维动画——23 年来我们与 600+ 企业共同生长。', 'en' => 'From brand VI to global independent sites, from promo videos to 3D animation — 23 years growing with 600+ enterprises.'),
    'casesBtn' => array('zh' => '全部案例 →', 'en' => 'All Cases →'),
    'ctaTitle' => array('zh' => '让品牌更科技，让营销快一步', 'en' => 'Make Your Brand Tech-Forward, Marketing One Step Ahead'),
    'ctaDesc' => array('zh' => '告诉我们您的项目需求，北港3D团队将在 1 个工作日内与您联系。', 'en' => 'Tell us your project needs, the BEIGANG 3D team will contact you within 1 business day.'),
    'ctaBtn' => array('zh' => '立即咨询方案', 'en' => 'Contact Us for a Proposal'),
    'freeConsult' => array('zh' => '免费咨询方案', 'en' => 'Free Consultation'),
    'viewCases' => array('zh' => '查看案例', 'en' => 'View Cases'),
  ),

  'threeds' => array(
    'eyebrow' => array('zh' => '3DS · 三维动画', 'en' => '3DS · 3D Animation'),
    'title' => array('zh' => '工业产品三维虚拟动画 3D 建模和渲染', 'en' => '3D Modeling & Rendering for Industrial Product Virtual Animation'),
    'desc' => array('zh' => '利用产品 3D 图和三维动画视频，让品牌更科技，让营销快一步。', 'en' => 'Use product 3D visuals and animation videos to make your brand more tech-forward and your marketing one step ahead.'),
    'benefitsTitle' => array('zh' => '工业三维建模和渲染的好处', 'en' => 'Benefits of Industrial 3D Modeling & Rendering'),
    'benefits' => array(
      array('title' => array('zh' => '产品展示', 'en' => 'Product Showcase'), 'desc' => array('zh' => '能在现场拍摄不到的细节处进行完整细节展现。在产品设备没有打造出来前，进行虚拟整体演示，帮助工程团队完成工作、检测项目准确值，方便沟通。', 'en' => 'Reveal full detail where on-site shooting falls short. Demo the entire product virtually before it is built, helping engineering teams execute, verify accurate specs, and communicate with ease.')),
      array('title' => array('zh' => '实力展现', 'en' => 'Capability Showcase'), 'desc' => array('zh' => '一般能做出三维虚拟动画的企业，代表具有完善的工程师团队的工作。方案中为用户进行现场三维虚拟，能让客户看到合作的可能性与未来方案的更新度。', 'en' => 'A firm that can produce 3D virtual animation demonstrates a complete engineering team. On-site 3D virtualization lets clients see the potential for collaboration and the freshness of future solutions.')),
      array('title' => array('zh' => '营销推广', 'en' => 'Marketing Promotion'), 'desc' => array('zh' => '可用在营销推广资料的制作上、网站及新媒体平台上的发布推广。这样能在产品线设备没有被打造出来前就拿出来在竞标里打响，以便后期调整设计方案。', 'en' => 'Use it for marketing materials and publish across websites and new-media platforms. Launch it in bids before the product line is built, then refine the design later.')),
    ),
    'worksTitle' => array('zh' => '工业三维图、产品动画建模渲染作品', 'en' => 'Industrial 3D Graphics & Product Animation Works'),
    'worksDesc' => array('zh' => '北港3D设计团队经过多年和企业工程师对接，协助完成产品设备的三维图和三维动画渲染。只要提供 .stp 模型文件，我们就可以开始工作；如果只有 CAD 和照片也没关系，我们可以协助你们建模，一样把工作搞定。', 'en' => 'BEIGANG 3D\'s team has worked with enterprise engineers for years, delivering 3D graphics and animation rendering for products and equipment. Provide the .stp model and we can start; even with only CAD and photos, we can model it for you and get it done.'),
    'platformsTitle' => array('zh' => '平台应用与合作', 'en' => 'Platforms & Partnerships'),
    'contactRows' => array(
      array('t' => array('zh' => '电话 / 微信', 'en' => 'Tel / WeChat')),
      array('t' => array('zh' => 'QQ / Email', 'en' => 'QQ / Email')),
      array('t' => array('zh' => '地址', 'en' => 'Address')),
    ),
  ),

  'casePage' => array(
    'eyebrow' => 'CASE STUDIES',
    'title' => array('zh' => '精选案例', 'en' => 'Featured Cases'),
    'desc' => array('zh' => '从品牌 VI 到外贸独立站，从宣传片到三维动画——23 年来我们与 600+ 企业共同生长。', 'en' => 'From brand VI to global independent sites, from promo videos to 3D animation — 23 years growing with 600+ enterprises.'),
  ),

  'planPage' => array(
    'eyebrow' => array('zh' => 'PLAN · 方案', 'en' => 'PLAN'),
    'title' => array('zh' => '营销推广与运维方案', 'en' => 'Marketing & Operations Plans'),
    'desc' => array('zh' => '策划—设计—营销全链路，覆盖内贸、外贸、平台搭建与会展品宣的一站式数字营销方案。', 'en' => 'Full-chain strategy—design—marketing, covering domestic, global, platform build and exhibition branding in one-stop digital marketing solutions.'),
  ),

  'servicePage' => array(
    'eyebrow' => array('zh' => 'SERVICE · 服务', 'en' => 'SERVICE'),
    'title' => array('zh' => '营销 & 设计 包年服务', 'en' => 'Annual Marketing & Design Service'),
    'desc' => array('zh' => '一支专属的年度专业团队，持续输出品牌设计与数字营销能力，把外包变成可预期的长期增长伙伴。', 'en' => 'A dedicated annual team delivering continuous brand design and digital marketing capabilities, turning outsourcing into a predictable long-term growth partner.'),
    'whyTitle' => array('zh' => '为什么选择包年服务？', 'en' => 'Why Choose Annual Service?'),
    'whyDesc' => array('zh' => '把分散的设计与营销需求交给一支固定团队，省去反复比价与磨合成本，让品牌视觉与推广动作保持长期一致，效果可累积、可预期。', 'en' => 'Entrust scattered design and marketing needs to a fixed team, eliminating repeated price comparisons and onboarding costs, keeping brand visuals and marketing actions consistently aligned, with compounding and predictable results.'),
    'btn' => array('zh' => '咨询包年方案', 'en' => 'Consult Annual Plans'),
  ),

  'profilePage' => array(
    'eyebrow' => array('zh' => 'PROFILE · 北港3D', 'en' => 'PROFILE'),
    'title' => array('zh' => '企业全链路品牌共生伙伴', 'en' => 'Full-Chain Brand Symbiosis Partner'),
    'desc' => array('zh' => '北港3D不仅是品牌视觉的缔造者，更是品牌数字营销的推手。', 'en' => 'BEIGANG 3D is not just a brand visual creator, but a driver of brand digital marketing.'),
    'citiesTitle' => array('zh' => '服务城市', 'en' => 'Service Cities'),
    'citiesDesc' => array('zh' => '北港3D设计正活跃于长三角腹地，并辐射至全国多地。', 'en' => 'BEIGANG 3D Design is active in the Yangtze River Delta and radiates to multiple cities nationwide.'),
  ),

  'pointsPage' => array(
    'eyebrow' => array('zh' => 'POINTS · 发现', 'en' => 'POINTS'),
    'title' => array('zh' => '品牌设计与数字营销干货', 'en' => 'Brand Design & Digital Marketing Insights'),
    'desc' => array('zh' => '我们持续输出关于品牌 VI、网站运营与市场营销的思考与实践。', 'en' => 'We continuously share our thinking and practice on brand VI, website operations and marketing.'),
  ),

  'contactPage' => array(
    'eyebrow' => array('zh' => 'CONTACT · 联系', 'en' => 'CONTACT'),
    'title' => array('zh' => '立即咨询方案', 'en' => 'Contact Us for a Proposal'),
    'desc' => array('zh' => '告诉我们您的项目需求，北港3D团队将在 1 个工作日内与您联系。', 'en' => 'Tell us your project needs, the BEIGANG 3D team will contact you within 1 business day.'),
  ),

  'newsPage' => array(
    'eyebrow' => array('zh' => 'FDE ARCHIVE · 资讯归档', 'en' => 'FDE ARCHIVE'),
    'title' => array('zh' => 'FDE 资讯归档', 'en' => 'FDE News Archive'),
    'desc' => array('zh' => '本站每日汇总并沉淀 FDE（前沿部署工程师）行业资讯，全部条目标注原始出处，历史内容永久可查。', 'en' => 'A daily-curated, permanently archived library of FDE (Forward Deployed Engineer) industry news. Every entry credits its original source.'),
    'totalLabel' => array('zh' => '已沉淀', 'en' => 'Archived'),
    'unit' => array('zh' => '条资讯', 'en' => 'entries'),
    'empty' => array('zh' => '暂无归档内容，明早 8 点将自动更新。', 'en' => 'No entries yet. The archive updates automatically at 8am.'),
    'pagePrev' => array('zh' => '上一页', 'en' => 'Previous'),
    'pageNext' => array('zh' => '下一页', 'en' => 'Next'),
    'pageOf' => array('zh' => '页', 'en' => ''),
    'backToList' => array('zh' => '返回资讯归档', 'en' => 'Back to archive'),
    'sourceTitle' => array('zh' => '内容来源声明', 'en' => 'Source Attribution'),
    'sourceNotice' => array('zh' => '本文由北港3D根据公开报道编译整理，仅作行业信息参考，版权归原作者与原发布平台所有。如需完整内容请访问原文。', 'en' => 'This entry was compiled by BEIGANG 3D from publicly available reporting for industry reference only. Copyright remains with the original author and publisher. Please visit the source for the full article.'),
    'sourceFrom' => array('zh' => '信息来源', 'en' => 'Source'),
    'originalLink' => array('zh' => '原文链接', 'en' => 'Original link'),
    'viewOriginal' => array('zh' => '访问原文', 'en' => 'View original'),
    'publishedOn' => array('zh' => '发布日期', 'en' => 'Published'),
    'archivedOn' => array('zh' => '本站收录', 'en' => 'Archived on'),
    'prevArticle' => array('zh' => '较新一篇', 'en' => 'Newer'),
    'nextArticle' => array('zh' => '较早一篇', 'en' => 'Older'),
    'notFound' => array('zh' => '未找到该条资讯', 'en' => 'Entry not found'),
    'notFoundDesc' => array('zh' => '该内容可能已被移除，或链接有误。你可以返回归档列表继续浏览。', 'en' => 'This entry may have been removed or the link is incorrect. Return to the archive to keep browsing.'),
    'relatedTitle' => array('zh' => '同类资讯', 'en' => 'Related entries'),
  ),

  'footer' => array(
    'contactTitle' => array('zh' => '联系我们 / CONTACT', 'en' => 'Contact Us'),
    'addrLabel' => array('zh' => '地址：', 'en' => 'Address: '),
    'phoneLabel' => array('zh' => '电话 / 微信：', 'en' => 'Tel / WeChat: '),
    'qqLabel' => array('zh' => 'QQ / Email：', 'en' => 'QQ / Email: '),
    'citiesTitle' => array('zh' => '服务城市 / NETWORK', 'en' => 'Service Cities'),
    'etc' => array('zh' => '等', 'en' => 'etc.'),
    'linksTitle' => array('zh' => '快捷入口 / LINKS', 'en' => 'Quick Links'),
    'adminLogin' => array('zh' => '管理后台登录 →', 'en' => 'Admin Login →'),
    'threedsLink' => array('zh' => '三维动画作品 3D →', 'en' => '3D Animation Works →'),
    'tagline' => array('zh' => '三维动画 · 工业可视化 · 数字展厅', 'en' => '3D Animation · Industrial Visualization · Digital Showroom'),
    'articlesTitle' => array('zh' => '设计 & 营销干货', 'en' => 'Design & Marketing Insights'),
    'aiFdeEyebrow' => array('zh' => 'AI FDE NEWS', 'en' => 'AI FDE NEWS'),
    'aiFdeTitle' => array('zh' => 'AI FDE 最新信息', 'en' => 'AI FDE Latest'),
    'aiFdeDesc' => array('zh' => '每日精选全网 FDE（前沿部署工程师）行业资讯：巨头动向、人才市场、薪酬行情与技术前沿。', 'en' => 'A daily digest of FDE (Forward Deployed Engineer) industry news from across the web: big-tech moves, talent market, compensation and tech frontier.'),
    'aiFdeBadge' => array('zh' => '每日更新', 'en' => 'Updated Daily'),
    'aiFdeReadMore' => array('zh' => '阅读原文', 'en' => 'Read source'),
    'aiFdeMore' => array('zh' => '查看全部归档', 'en' => 'Browse full archive'),
  ),
);
