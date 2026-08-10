<?php
require_once __DIR__ . '/lib/config.php';
require_once __DIR__ . '/lib/lang.php';
require_once __DIR__ . '/lib/ui-text.php';
require_once __DIR__ . '/lib/content.php';
require_once __DIR__ . '/lib/render.php';

$lang = bg_get_lang();
$siteName = ($lang === 'en') ? BG_SITE_NAME_EN : BG_SITE_NAME_ZH;

$title = ($lang === 'en') ? 'FDE Map' : 'FDE地图';
$eyebrow = ($lang === 'en') ? 'PROJECT ECOSYSTEM' : '项目生态';
$desc = ($lang === 'en')
  ? 'Explore the floating ecosystem of projects under FDE Map. Click any node to dive in.'
  : '探索 FDE 地图旗下项目生态，点击任意飘图即可进入对应项目。';

// 飘图项目从 CMS 的 nav 集合「FDE地图」子项自动读取，dropdown 里新增/删除项目后页面自动更新
$projectMeta = array(
  'https://wiki.eupgrading.com/' => array('icon' => '📚', 'desc' => array('zh' => '团队文档、知识与经验沉淀', 'en' => 'Team docs, knowledge and experience repository')),
  'https://quant.eupgrading.com/' => array('icon' => '📈', 'desc' => array('zh' => '量化策略研究、回测与交易', 'en' => 'Quantitative strategy research, backtesting and trading')),
  'https://onward.eupgrading.com/' => array('icon' => '✈️', 'desc' => array('zh' => '海外移民、留学规划与资讯', 'en' => 'Overseas migration, study planning and information')),
  'https://fortune.eupgrading.com/' => array('icon' => '🔮', 'desc' => array('zh' => '数据洞察、预测与智能分析', 'en' => 'Data insights, forecasting and intelligent analytics')),
  'https://oral.eupgrading.com/' => array('icon' => '🗣️', 'desc' => array('zh' => 'AI 驱动的英语口语练习', 'en' => 'AI-powered spoken English practice')),
  'https://kidbbc.eupgrading.com/' => array('icon' => '🎬', 'desc' => array('zh' => '儿童教育视频内容库', 'en' => 'Children educational video content library')),
);
$fallbackMeta = array('icon' => '🚀', 'desc' => array('zh' => '点击探索该项目', 'en' => 'Click to explore this project'));

$navFde = null;
foreach (bg_get_collection('nav') as $n) {
  if (isset($n['id']) && $n['id'] === 'nav-fde') { $navFde = $n; break; }
}
$satellites = array();
if ($navFde && !empty($navFde['children']) && is_array($navFde['children'])) {
  foreach ($navFde['children'] as $c) {
    $href = isset($c['href']) ? $c['href'] : '#';
    $label = isset($c['label']) ? $c['label'] : array('zh' => '', 'en' => '');
    $zh = is_array($label) && isset($label['zh']) ? $label['zh'] : (is_string($label) ? $label : '');
    $en = is_array($label) && isset($label['en']) ? $label['en'] : (is_string($label) ? $label : '');
    $meta = isset($projectMeta[$href]) ? $projectMeta[$href] : $fallbackMeta;
    $satellites[] = array(
      'id' => 'fde-' . substr(md5($href), 0, 8),
      'icon' => $meta['icon'],
      'title' => $zh,
      'en' => $en,
      'desc' => $meta['desc'],
      'href' => $href,
    );
  }
}

$projects = array_merge(
  array(array(
    'id' => 'fde',
    'icon' => '🗺️',
    'title' => 'FDE地图',
    'en' => 'FDE Map',
    'desc' => array('zh' => '项目总览与地理分布可视化', 'en' => 'Project overview and geographic distribution visualization'),
    'href' => '/fde-map'
  )),
  $satellites
);

// 根据卫星数量动态生成初始位置（无 JS 降级时仍可用）
$positions = array(array('left' => '50%', 'top' => '50%'));
$n = count($satellites);
if ($n > 0) {
  for ($i = 0; $i < $n; $i++) {
    $rad = deg2rad(-90 + $i * (360 / $n));
    $positions[] = array(
      'left' => round(50 + 37 * cos($rad), 1) . '%',
      'top'  => round(50 + 44 * sin($rad), 1) . '%',
    );
  }
}

bg_head($lang, $title . ' | ' . $siteName, $desc);
bg_render_nav($lang);
?>
<style>
/* ───── 页头（沿用全站浅色极光风） ───── */
.fde-page-header {
  position: relative;
  text-align: center;
  padding: 4rem 1rem 2rem;
  background: transparent;
  overflow: hidden;
  border-bottom: 1px solid rgba(12, 20, 38, 0.08);
}
.fde-page-header::before {
  content: '';
  position: absolute;
  inset: 0;
  background-image:
    linear-gradient(90deg, rgba(12, 20, 38, 0.045) 1px, transparent 1px),
    linear-gradient(rgba(12, 20, 38, 0.045) 1px, transparent 1px);
  background-size: 60px 60px;
  mask-image: linear-gradient(180deg, rgba(0,0,0,0.6), transparent 80%);
  -webkit-mask-image: linear-gradient(180deg, rgba(0,0,0,0.6), transparent 80%);
  pointer-events: none;
}
.fde-page-header .eyebrow {
  display: inline-block;
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.32em;
  color: #2f6bff;
  text-transform: uppercase;
  margin-bottom: 0.9rem;
  padding: 0.4rem 1.1rem;
  border: 1px solid rgba(47, 107, 255, 0.30);
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.70);
  box-shadow: 0 6px 20px -10px rgba(47, 107, 255, 0.35);
  backdrop-filter: blur(6px);
  position: relative;
  z-index: 1;
}
.fde-page-header h1 {
  font-size: clamp(2.2rem, 6vw, 3.8rem);
  font-weight: 800;
  background: linear-gradient(100deg, #2f6bff 0%, #3b82f6 45%, #0ea5b7 100%);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
  margin-bottom: 0.8rem;
  position: relative;
  z-index: 1;
}
.fde-page-header .lead {
  max-width: 640px;
  margin: 0 auto;
  font-size: 1.05rem;
  color: rgba(12, 20, 38, 0.70);
  line-height: 1.7;
  position: relative;
  z-index: 1;
}

/* ───── 主视觉区（透明，露出全站极光背景） ───── */
.fde-hero {
  position: relative;
  min-height: calc(100vh - 80px);
  overflow: hidden;
  padding: 2.5rem 1rem 5rem;
  background: transparent;
}
/* 透视网格（浅色） */
.fde-hero::before {
  content: '';
  position: absolute;
  inset: -50%;
  background-image:
    linear-gradient(rgba(47, 107, 255, 0.05) 1px, transparent 1px),
    linear-gradient(90deg, rgba(47, 107, 255, 0.05) 1px, transparent 1px);
  background-size: 90px 90px;
  transform: perspective(800px) rotateX(58deg) translateY(-10%);
  transform-origin: 50% 100%;
  animation: gridMove 28s linear infinite;
  pointer-events: none;
  opacity: 0.8;
}
@keyframes gridMove {
  0% { transform: perspective(800px) rotateX(58deg) translateY(0); }
  100% { transform: perspective(800px) rotateX(58deg) translateY(90px); }
}

/* ───── 星座容器 ───── */
.fde-constellation {
  position: relative;
  width: 100%;
  max-width: 1480px;
  min-height: 980px;
  margin: 0 auto;
}
.fde-anchor {
  position: absolute;
  transform: translate(calc(-50% + var(--parallax-x, 0px)), calc(-50% + var(--parallax-y, 0px)));
  will-change: transform, left, top;
}

/* ───── 节点卡片（浅色玻璃，与全站一致） ───── */
.fde-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  width: 172px;
  padding: 1.15rem 0.95rem;
  border-radius: 1.35rem;
  background: rgba(255, 255, 255, 0.78);
  border: 1px solid rgba(12, 20, 38, 0.10);
  box-shadow:
    0 18px 44px -16px rgba(15, 23, 42, 0.20),
    inset 0 1px 0 rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(18px);
  -webkit-backdrop-filter: blur(18px);
  text-decoration: none;
  color: #0c1426;
  position: relative;
  z-index: 2;
  will-change: transform;
  transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.4s ease, background 0.4s ease, border-color 0.4s ease;
  cursor: pointer;
}
/* 卡片底部微光 */
.fde-card .glow-floor {
  position: absolute;
  left: 10%; right: 10%; bottom: -14px;
  height: 22px;
  border-radius: 50%;
  background: radial-gradient(ellipse at center, rgba(47,107,255,0.22), transparent 70%);
  filter: blur(10px);
  opacity: 0.6;
  transition: opacity 0.35s ease;
  pointer-events: none;
  z-index: -1;
}
.fde-card:hover .glow-floor { opacity: 1; }
/* 霓虹边框光环 */
.fde-card::before {
  content: '';
  position: absolute;
  inset: -2px;
  border-radius: inherit;
  padding: 2px;
  background: conic-gradient(from 0deg,
    rgba(47,107,255,0),
    rgba(47,107,255,0.75),
    rgba(14,165,183,0.65),
    rgba(99,102,241,0.35),
    rgba(47,107,255,0));
  -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
  -webkit-mask-composite: xor;
  mask-composite: exclude;
  animation: ringSpin 12s linear infinite;
  pointer-events: none;
  opacity: 0.40;
  transition: opacity 0.35s ease;
}
.fde-card::after {
  content: '';
  position: absolute;
  inset: 0;
  border-radius: inherit;
  box-shadow: 0 0 36px rgba(47,107,255,0.14);
  opacity: 0;
  transition: opacity 0.35s ease;
  pointer-events: none;
}
.fde-card:hover {
  transform: scale(1.10) translateY(-7px);
  background: rgba(255, 255, 255, 0.92);
  border-color: rgba(47, 107, 255, 0.40);
  box-shadow:
    0 28px 60px -20px rgba(47, 107, 255, 0.35),
    inset 0 1px 0 rgba(255, 255, 255, 0.9);
  z-index: 10;
}
.fde-card:hover::before { opacity: 0.8; animation-duration: 4.5s; }
.fde-card:hover::after { opacity: 1; }
@keyframes ringSpin { to { transform: rotate(360deg); } }

/* ───── 中心节点 ───── */
.fde-card.center {
  width: 234px;
  padding: 1.8rem 1.4rem;
  background: rgba(255, 255, 255, 0.86);
  border-color: rgba(47, 107, 255, 0.30);
  box-shadow:
    0 0 70px rgba(47, 107, 255, 0.18),
    0 24px 60px -16px rgba(15, 23, 42, 0.18),
    inset 0 1px 0 rgba(255, 255, 255, 0.9);
  animation: floatCenter 7s ease-in-out infinite;
}
/* 中心旋转光环 */
.fde-card.center::before {
  background: conic-gradient(from 0deg,
    rgba(47,107,255,0),
    rgba(47,107,255,0.9),
    rgba(14,165,183,0.85),
    rgba(47,107,255,0.5),
    rgba(47,107,255,0));
  opacity: 0.85;
  animation-duration: 12s;
}
/* 中心脉冲波纹 */
.fde-card.center .pulse-ring {
  position: absolute;
  inset: -22%;
  border-radius: 50%;
  border: 1px solid rgba(47, 107, 255, 0.22);
  box-shadow: 0 0 30px rgba(47, 107, 255, 0.12);
  animation: pulseRing 3.6s ease-out infinite;
  pointer-events: none;
  z-index: -2;
}
.fde-card.center .pulse-ring:nth-child(2) { animation-delay: 1.2s; }
.fde-card.center .pulse-ring:nth-child(3) { animation-delay: 2.4s; }
@keyframes pulseRing {
  0% { transform: scale(0.68); opacity: 0.6; }
  100% { transform: scale(1.45); opacity: 0; }
}
.fde-card.center .fde-icon {
  width: 84px;
  height: 84px;
  font-size: 2.4rem;
  background: linear-gradient(135deg, rgba(47,107,255,0.16), rgba(14,165,183,0.16));
  box-shadow: 0 0 30px rgba(47,107,255,0.15);
}
.fde-card.center .fde-title { font-size: 1.3rem; }
.fde-card.center .fde-desc { color: rgba(12, 20, 38, 0.62); }
@keyframes floatCenter { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-12px); } }

/* ───── 图标 ───── */
.fde-icon {
  width: 62px;
  height: 62px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.8rem;
  color: #0c1426;
  background: linear-gradient(135deg, rgba(47,107,255,0.12), rgba(14,165,183,0.12));
  border-radius: 1.2rem;
  margin-bottom: 0.9rem;
  transition: transform 0.45s ease, box-shadow 0.45s ease;
  position: relative;
  box-shadow: inset 0 1px 0 rgba(255,255,255,0.6);
}
.fde-card:hover .fde-icon {
  transform: scale(1.14) rotate(-8deg);
  box-shadow: 0 0 26px rgba(47,107,255,0.22);
}
.fde-title {
  font-size: 1.06rem;
  font-weight: 700;
  line-height: 1.3;
  margin-bottom: 0.35rem;
  color: #0c1426;
}
.fde-en {
  font-size: 0.7rem;
  color: rgba(12, 20, 38, 0.55);
  letter-spacing: 0.03em;
  margin-bottom: 0.5rem;
}
.fde-desc {
  font-size: 0.8rem;
  color: rgba(12, 20, 38, 0.62);
  line-height: 1.5;
}
.fde-arrow {
  margin-top: 0.75rem;
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 999px;
  background: linear-gradient(135deg, rgba(47,107,255,0.14), rgba(14,165,183,0.14));
  color: #2f6bff;
  font-size: 0.9rem;
  opacity: 0;
  transform: translateY(8px) scale(0.85);
  transition: opacity 0.3s, transform 0.3s, box-shadow 0.3s;
}
.fde-card:hover .fde-arrow {
  opacity: 1;
  transform: translateY(0) scale(1);
  box-shadow: 0 0 16px rgba(47,107,255,0.30);
}

/* ───── 连线层 ───── */
.fde-lines {
  position: absolute;
  inset: 0;
  pointer-events: none;
  z-index: 1;
  overflow: visible;
}
.fde-lines .fde-line {
  stroke: url(#fdeLineGrad);
  stroke-width: 1.2;
  stroke-dasharray: 4 12;
  animation: dashFlow 3.2s linear infinite;
  filter: drop-shadow(0 0 3px rgba(47, 107, 255, 0.25));
  opacity: 0.38;
}
.fde-lines .fde-dot {
  fill: #2f6bff;
  filter: drop-shadow(0 0 6px rgba(47, 107, 255, 0.60));
  opacity: 0.90;
}
@keyframes dashFlow { to { stroke-dashoffset: -26; } }

/* ───── 背景装饰粒子（浅蓝） ───── */
.fde-particles {
  position: absolute;
  inset: 0;
  pointer-events: none;
  z-index: 0;
  overflow: hidden;
}
.fde-particle {
  position: absolute;
  width: 3px;
  height: 3px;
  border-radius: 50%;
  background: rgba(47, 107, 255, 0.45);
  box-shadow: 0 0 8px rgba(47, 107, 255, 0.50);
  animation: floatParticle 12s ease-in-out infinite;
}
@keyframes floatParticle {
    0%, 100% { transform: translateY(0) scale(1); opacity: 0.25; }
    50% { transform: translateY(-30px) scale(1.5); opacity: 0.75; }
}

/* ───── 响应式 ───── */
@media (max-width: 1100px) {
  .fde-constellation { min-height: 860px; }
  .fde-card { width: 158px; padding: 1rem 0.8rem; }
  .fde-card.center { width: 200px; padding: 1.4rem 1rem; }
  .fde-icon { width: 52px; height: 52px; font-size: 1.6rem; }
  .fde-card.center .fde-icon { width: 72px; height: 72px; font-size: 2.1rem; }
}
@media (max-width: 900px) {
  .fde-page-header { padding: 3rem 1rem 1.5rem; }
  .fde-page-header h1 { font-size: clamp(1.8rem, 7vw, 2.8rem); }
  .fde-constellation { min-height: 800px; }
  .fde-card { width: 152px; padding: 0.95rem 0.75rem; }
  .fde-card.center { width: 190px; padding: 1.3rem 0.95rem; }
}
@media (max-width: 768px) {
  .fde-page-header { padding: 2.5rem 1rem 1.2rem; }
  .fde-hero::before { transform: none; animation: none; opacity: 0.25; background-size: 50px 50px; }
  .fde-constellation { min-height: auto; display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; padding: 0 0.5rem; }
  .fde-anchor {
    position: relative !important;
    left: auto !important; top: auto !important;
    transform: none !important;
    display: flex; justify-content: center;
  }
  .fde-card { width: 100%; animation: none !important; }
  .fde-card::before { animation: ringSpin 16s linear infinite; opacity: 0.5; }
  .fde-card .glow-floor { display: none; }
  .fde-card.center { grid-column: 1 / -1; animation: none !important; }
  .fde-card.center .pulse-ring { display: none; }
  .fde-lines { display: none; }
  .fde-particles { display: none; }
}

@media (prefers-reduced-motion: reduce) {
  .fde-hero::before,
  .fde-card, .fde-card::before, .fde-card.center, .fde-card.center .pulse-ring,
  .fde-lines .fde-line, .fde-particle { animation: none !important; }
  .fde-lines .fde-dot { opacity: 0.5; }
  .fde-card .glow-floor { opacity: 0.4; }
}
</style>

<main class="flex-1 overflow-x-hidden">
  <header class="fde-page-header">
    <span class="eyebrow"><?php echo h($eyebrow); ?></span>
    <h1><?php echo h($title); ?></h1>
    <p class="lead"><?php echo h($desc); ?></p>
  </header>

  <section class="fde-hero">
    <div class="fde-particles" id="fdeParticles"></div>

    <div class="fde-constellation" id="fdeConstellation">
      <!-- 连线层（SVG，含渐变定义） -->
      <svg class="fde-lines" id="fdeLines" xmlns="http://www.w3.org/2000/svg">
        <defs>
          <linearGradient id="fdeLineGrad" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%"  stop-color="#2f6bff" stop-opacity="0.95"/>
            <stop offset="55%" stop-color="#3b82f6" stop-opacity="0.85"/>
            <stop offset="100%" stop-color="#0ea5b7" stop-opacity="0.75"/>
          </linearGradient>
        </defs>
      </svg>

      <?php
      // 初始布局（无 JS / 降级时仍可见）；JS 会接管为轨道运动
      $positions = array(
        array('left' => '50%', 'top' => '50%'),
        array('left' => '50%', 'top' => '6%'),
        array('left' => '87%', 'top' => '26%'),
        array('left' => '87%', 'top' => '74%'),
        array('left' => '50%', 'top' => '94%'),
        array('left' => '13%', 'top' => '74%'),
        array('left' => '13%', 'top' => '26%'),
      );
      foreach ($projects as $i => $p):
        $isCenter = ($i === 0);
        $isExternal = (strpos($p['href'], 'http') === 0);
        $pos = $positions[$i];
        $displayTitle = ($lang === 'en' && !empty($p['en'])) ? $p['en'] : $p['title'];
        $displayDesc = bg_t($p['desc'], $lang);
      ?>
      <div class="fde-anchor" style="left: <?php echo $pos['left']; ?>; top: <?php echo $pos['top']; ?>;">
        <a class="fde-card <?php echo $isCenter ? 'center' : ''; ?>"
           href="<?php echo h(bg_url($p['href'])); ?>"
           data-id="<?php echo h($p['id']); ?>"
           <?php if ($isExternal): ?>target="_blank" rel="noopener"<?php endif; ?>>
          <?php if ($isCenter): ?>
          <span class="pulse-ring"></span>
          <span class="pulse-ring"></span>
          <span class="pulse-ring"></span>
          <?php endif; ?>
          <span class="glow-floor"></span>
          <div class="fde-icon"><?php echo $p['icon']; ?></div>
          <div class="fde-title"><?php echo h($displayTitle); ?></div>
          <?php if ($lang === 'zh' && !empty($p['en'])): ?><div class="fde-en"><?php echo h($p['en']); ?></div><?php endif; ?>
          <p class="fde-desc"><?php echo h($displayDesc); ?></p>
          <span class="fde-arrow">→</span>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </section>
</main>

<script>
(function() {
  const container = document.getElementById('fdeConstellation');
  const anchors = [...container.querySelectorAll('.fde-anchor')];
  const centerAnchor = container.querySelector('.fde-card.center')?.closest('.fde-anchor');
  const peripheral = anchors.filter(a => !a.querySelector('.fde-card.center'));
  const svg = document.getElementById('fdeLines');
  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  const N = peripheral.length;
  const baseAngles = peripheral.map((_, i) => (-90 + i * (360 / N)) * Math.PI / 180);
  const lines = peripheral.map(() => {
    const ln = document.createElementNS('http://www.w3.org/2000/svg', 'line');
    ln.setAttribute('class', 'fde-line');
    svg.appendChild(ln);
    return ln;
  });
  const dots = peripheral.map(() => {
    const d = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
    d.setAttribute('class', 'fde-dot');
    d.setAttribute('r', '3');
    svg.appendChild(d);
    return d;
  });

  // 生成背景装饰粒子
  const particles = document.getElementById('fdeParticles');
  for (let i = 0; i < 30; i++) {
    const p = document.createElement('span');
    p.className = 'fde-particle';
    p.style.left = (Math.random() * 100) + '%';
    p.style.top = (Math.random() * 100) + '%';
    p.style.animationDelay = (Math.random() * 10) + 's';
    p.style.animationDuration = (12 + Math.random() * 10) + 's';
    particles.appendChild(p);
  }

  let W = 0, H = 0, cx = 0, cy = 0, R = 0;

  function layout() {
    const rect = container.getBoundingClientRect();
    W = rect.width; H = rect.height;
    cx = W / 2; cy = H / 2;
    // 更大半径，让外圈卡片更舒展
    R = Math.max(215, Math.min(W, H) / 2 - 208);
    svg.setAttribute('width', W);
    svg.setAttribute('height', H);
    svg.setAttribute('viewBox', `0 0 ${W} ${H}`);
    const mobile = window.innerWidth <= 768;
    svg.style.display = mobile ? 'none' : '';
    if (mobile) {
      peripheral.forEach(a => { a.style.left = ''; a.style.top = ''; });
    }
  }

  function frame(ts) {
    if (!reduced && window.innerWidth > 768) {
      const rot = (ts / 60000) * Math.PI * 2; // 60s 公转一圈，更舒缓大气
      peripheral.forEach((anchor, i) => {
        const ang = baseAngles[i] + rot;
        const x = cx + R * Math.cos(ang);
        const y = cy + R * Math.sin(ang);
        anchor.style.left = x + 'px';
        anchor.style.top = y + 'px';

        // 连线起点：从中心节点边缘出发，避免 6 条线汇聚成一团
        const centerOffset = 100;
        const x1 = cx + centerOffset * Math.cos(ang);
        const y1 = cy + centerOffset * Math.sin(ang);
        lines[i].setAttribute('x1', x1);
        lines[i].setAttribute('y1', y1);
        lines[i].setAttribute('x2', x);
        lines[i].setAttribute('y2', y);

        // 流动光点：沿连线从中心向外移动
        const progress = (ts / 3200 + i / N) % 1;
        dots[i].setAttribute('cx', x1 + (x - x1) * progress);
        dots[i].setAttribute('cy', y1 + (y - y1) * progress);
      });
    }
    requestAnimationFrame(frame);
  }

  layout();
  if (reduced) {
    peripheral.forEach((anchor, i) => {
      const r = anchor.getBoundingClientRect();
      const c = container.getBoundingClientRect();
      const ang = baseAngles[i];
      const centerOffset = 100;
      lines[i].setAttribute('x1', cx + centerOffset * Math.cos(ang));
      lines[i].setAttribute('y1', cy + centerOffset * Math.sin(ang));
      lines[i].setAttribute('x2', r.left + r.width / 2 - c.left);
      lines[i].setAttribute('y2', r.top + r.height / 2 - c.top);
    });
  } else {
    requestAnimationFrame(frame);
  }
  window.addEventListener('resize', layout);

  // 鼠标视差（外圈更强，中心较弱）
  document.querySelector('.fde-hero').addEventListener('mousemove', (e) => {
    if (window.innerWidth <= 900) return;
    const rect = container.getBoundingClientRect();
    const dx = (e.clientX - rect.left - rect.width / 2) / rect.width;
    const dy = (e.clientY - rect.top - rect.height / 2) / rect.height;
    anchors.forEach((a, i) => {
      const depth = i === 0 ? 5 : 12;
      a.style.setProperty('--parallax-x', (dx * depth) + 'px');
      a.style.setProperty('--parallax-y', (dy * depth) + 'px');
    });
  });
})();
</script>

<?php bg_render_footer($lang, true); ?>
<?php bg_foot_js(); ?>
