<?php
/**
 * 组件渲染函数库（对应 React 组件）
 * 前台页面 require 本文件后调用各函数输出 HTML
 */
require_once __DIR__ . '/lang.php';
require_once __DIR__ . '/ui-text.php';
require_once __DIR__ . '/content.php';
require_once __DIR__ . '/helpers.php';

// ===== 内联图标（替代 lucide-react） =====
function bg_icon($name, $size = 22, $stroke = 1.8) {
  $paths = array(
    'phone' => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>',
    'mail' => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
    'map-pin' => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
    'menu' => '<line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="18" y2="18"/>',
    'x' => '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
    'arrow-up-right' => '<path d="M7 7h10v10"/><path d="M7 17 17 7"/>',
  );
  $fill = ($name === 'play') ? 'fill="currentColor" stroke="none"' : 'fill="none" stroke="currentColor" stroke-width="' . $stroke . '" stroke-linecap="round" stroke-linejoin="round"';
  if ($name === 'play') {
    $p = '<path d="M8 5v14l11-7z"/>';
  } else {
    $p = isset($paths[$name]) ? $paths[$name] : '';
  }
  return '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" ' . $fill . '>' . $p . '</svg>';
}

// ===== 布局辅助 =====
function bg_head($lang, $title, $desc = '') {
  $htmlLang = ($lang === 'en') ? 'en' : 'zh-CN';
  echo '<!DOCTYPE html>' . "\n";
  echo '<html lang="' . h($htmlLang) . '">' . "\n";
  echo '<head>' . "\n";
  echo '<meta charset="utf-8">' . "\n";
  echo '<meta name="viewport" content="width=device-width, initial-scale=1">' . "\n";
  echo '<script>window.BG_BASE = ' . json_encode(defined('BG_BASE') ? BG_BASE : '') . ';</script>' . "\n";
  echo '<title>' . h($title) . '</title>' . "\n";
  if ($desc !== '') echo '<meta name="description" content="' . h($desc) . '">' . "\n";
  echo '<link rel="stylesheet" href="' . bg_url('/assets/css/style.css') . '">' . "\n";
  echo '</head>' . "\n";
  echo '<body class="bg-aurora flex min-h-screen flex-col bg-background font-sans text-foreground antialiased">' . "\n";
}

function bg_foot_js() {
  echo '<script src="' . bg_url('/assets/js/site.js') . '"></script>' . "\n";
  echo '</body>' . "\n";
  echo '</html>' . "\n";
}

// ===== Eyebrow / PageHeader / GlowCard / Reveal =====
function bg_eyebrow($index, $text) {
  $idx = $index ? '<span class="text-tech-blue/80">' . h($index) . '</span>' : '';
  echo '<span class="inline-flex items-center gap-2.5 font-mono text-xs font-medium uppercase tracking-[0.22em] text-tech-cyan">' . $idx . '<span class="h-px w-7 bg-gradient-to-r from-tech-blue to-tech-cyan"></span>' . h($text) . '</span>';
}

function bg_page_header($index, $eyebrow, $title, $desc) {
  echo '<section class="liquid-glass-section relative overflow-hidden border-b border-[#0c1426]/10 px-4 py-20 sm:py-24">';
  echo '<div class="relative z-10 mx-auto max-w-6xl">';
  if ($eyebrow) bg_eyebrow($index, $eyebrow);
  echo '<h1 class="mt-4 font-display text-4xl font-semibold tracking-tight text-tech-ink sm:text-5xl lg:text-6xl">' . h($title) . '</h1>';
  if ($desc) echo '<p class="mt-5 max-w-2xl text-lg leading-relaxed text-tech-muted">' . h($desc) . '</p>';
  echo '</div></section>';
}

function bg_reveal_start($class = '', $delay = 0, $tag = 'div') {
  $style = $delay ? ' style="transition-delay:' . (int) $delay . 'ms"' : '';
  echo '<' . $tag . ' class="reveal ' . h($class) . '"' . $style . '>';
}
function bg_reveal_end($tag = 'div') { echo '</' . $tag . '>'; }

function bg_glow_card_start($index = null, $class = '') {
  $idx = ($index !== null && $index !== '') ? '<span class="pointer-events-none absolute right-4 top-3 font-mono text-5xl font-bold text-[#0c1426]/[0.05]">' . h((string) $index) . '</span>' : '';
  echo '<div class="glow-card relative overflow-hidden p-6 sm:p-7 ' . h($class) . '">' . $idx;
}
function bg_glow_card_end() { echo '</div>'; }

// ===== BrandLogo =====
function bg_brand_logo($src, $class = 'h-9 w-auto', $withBg = false) {
  $final = ($src && $src !== '') ? $src : '/logo-removebg.png';
  $img = '<img src="' . h(bg_url($final)) . '" alt="品牌 Logo" class="' . h($class) . '">';
  if (!$withBg) { echo $img; return; }
  echo '<div class="inline-flex items-center rounded-md px-2.5 py-1.5" style="background-color:rgba(255,255,255,0.95);box-shadow:0 0 18px -4px rgba(255,255,255,0.3);">' . $img . '</div>';
}

// ===== VideoCard =====
function bg_video_card($item) {
  $videoUrl = isset($item['videoUrl']) ? (string) $item['videoUrl'] : '';
  $hasVideo = $videoUrl !== '';
  $poster = isset($item['poster']) ? (string) $item['poster'] : '';
  $hasPoster = $poster !== '';
  $title = isset($item['title']) ? $item['title'] : '';
  $category = isset($item['category']) ? $item['category'] : '';
  $desc = isset($item['description']) ? $item['description'] : '';
  $playUrl = '';
  if ($hasVideo && $videoUrl !== '') {
    $q = 'src=' . rawurlencode($videoUrl);
    if ($title !== '') $q .= '&title=' . rawurlencode($title);
    $playUrl = bg_url('/play.php?' . $q);
  }
  echo '<article class="video-card group flex flex-col' . ($playUrl ? ' cursor-pointer' : '') . '" data-video' . ($playUrl ? (' data-play-url="' . h($playUrl) . '"') : '') . '>';
  echo '<div class="relative aspect-[4/3] overflow-hidden rounded-xl border-[#0c1426]/10 bg-[#e9eef7]">';
    if ($hasVideo) {
      // 网格用短循环 GIF 预览（4s/320px，原生循环，100% 不受微信/iOS 自动播放限制）；
      // 点击卡片在 play.php 打开原始高清 mp4 全屏播放
      $gifUrl = preg_replace('#/videos/([^/]+)\.mp4$#i', '/videos/g/$1.gif', $videoUrl);
      echo '<img class="video-media relative z-10 h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" loading="lazy" src="' . h(bg_url($gifUrl)) . '" alt="' . h($title) . '">';
    } elseif ($hasPoster) {
    echo '<img class="video-media relative z-10 h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" src="' . h(bg_url($poster)) . '" alt="' . h($title) . '">';
  }
  echo '<div class="video-skeleton absolute inset-0 z-0 bg-[#e9eef7]"><div class="absolute inset-0 bg-grid opacity-40"></div></div>';
  echo '<div class="video-error absolute inset-0 z-0 hidden items-center justify-center bg-[#e9eef7]"><div class="text-center">' . bg_icon('play', 32) . '<p class="mt-1 text-xs text-tech-muted/70">Video unavailable</p></div></div>';
  if ($hasVideo) {
    echo '<span class="video-play-indicator pointer-events-none absolute right-3 top-3 z-20 flex h-8 w-8 items-center justify-center rounded-full bg-black/55 text-white opacity-100 shadow-sm transition-transform duration-200 group-hover:scale-110">' . bg_icon('play', 14) . '</span>';
  }
  echo '<div class="pointer-events-none absolute inset-x-0 bottom-0 z-20 h-px bg-gradient-to-r from-transparent via-tech-cyan/70 to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-100"></div>';
  echo '</div>';
  echo '<div class="mt-3"><h3 class="text-sm font-semibold text-tech-ink transition-colors group-hover:text-tech-blue">' . h($title) . '</h3>';
  if ($desc) echo '<p class="mt-1 line-clamp-2 text-xs leading-relaxed text-tech-muted">' . h($desc) . '</p>';
  echo '</div></article>';
}

// ===== FooterSlogan =====
function bg_footer_slogan($main, $sub = '') {
  echo '<div class="relative text-center">';
  echo '<div class="absolute inset-0 -z-10 mx-auto max-w-md rounded-2xl bg-gradient-to-r from-tech-blue/10 via-tech-cyan/10 to-tech-blue/10 blur-2xl slogan-bg-glow"></div>';
  echo '<div class="mx-auto mb-3 h-px w-16 slogan-line-grow bg-gradient-to-r from-transparent via-tech-blue/60 to-transparent"></div>';
  echo '<p class="slogan-title text-lg font-bold tracking-wide sm:text-xl"><span class="slogan-grad">' . h($main) . '</span></p>';
  if ($sub) echo '<p class="mt-2 slogan-sub"><span class="slogan-sub-text">' . h($sub) . '</span></p>';
  echo '<div class="mx-auto mt-3 h-px w-16 slogan-line-grow bg-gradient-to-r from-transparent via-tech-cyan/60 to-transparent"></div>';
  echo '</div>';
}

// ===== CtaButton（链接形态辉光按钮） =====
function bg_cta_button($href, $label, $variant = 'primary', $class = '') {
  if ($variant === 'ghost') {
    echo '<a href="' . bg_url($href) . '" class="group inline-flex items-center gap-2 rounded-full border border-[#0c1426]/15 px-6 py-3 text-sm font-medium text-tech-ink transition-all hover:border-tech-cyan/50 hover:bg-[#0c1426]/[0.04] ' . h($class) . '">' . h($label) . '<span class="transition-transform group-hover:translate-x-1">&rarr;</span></a>';
    return;
  }
  echo '<a href="' . bg_url($href) . '" class="group relative inline-flex items-center gap-2 overflow-hidden rounded-full px-7 py-3 text-sm font-semibold text-white transition-transform hover:-translate-y-0.5 ' . h($class) . '">';
  echo '<span class="absolute inset-0 bg-gradient-to-r from-tech-blue to-tech-cyan"></span>';
  echo '<span class="absolute inset-0 translate-x-[-120%] bg-white/25 blur-md transition-transform duration-700 group-hover:translate-x-[120%]"></span>';
  echo '<span class="relative flex items-center gap-2">' . h($label) . '<span class="transition-transform group-hover:translate-x-1">&rarr;</span></span></a>';
}

// ===== SectionHeading（$titleHtml 允许安全 HTML；$eyebrow/$desc 为纯文本） =====
function bg_section_heading($index, $eyebrow, $titleHtml, $desc = '', $align = 'left', $class = '') {
  $center = ($align === 'center');
  echo '<div class="' . ($center ? 'text-center ' : '') . h($class) . '">';
  bg_eyebrow($index, $eyebrow);
  echo '<h2 class="mt-4 font-display text-3xl font-semibold leading-tight tracking-tight text-tech-ink sm:text-4xl">' . $titleHtml . '</h2>';
  if ($desc !== '') {
    echo '<p class="mt-4 max-w-2xl text-base leading-relaxed text-tech-muted' . ($center ? ' mx-auto' : '') . '">' . h($desc) . '</p>';
  }
  echo '</div>';
}

// ===== Stat（数字滚动，值由 JS 动画；解析 "23年" => 23 + 年） =====
function bg_stat($rawValue, $label) {
  $value = 0; $suffix = '';
  if (preg_match('/^(\d+)(.*)$/s', (string) $rawValue, $m)) {
    $value = (int) $m[1];
    $suffix = isset($m[2]) ? $m[2] : '';
  }
  echo '<div class="text-center sm:text-left" data-stat data-count-value="' . $value . '">';
  echo '<div class="font-mono text-4xl font-bold tracking-tight text-gradient sm:text-5xl"><span class="stat-num">0</span>' . h($suffix) . '</div>';
  echo '<div class="mt-2 text-sm text-tech-muted">' . h($label) . '</div>';
  echo '</div>';
}

// ===== CaseCard =====
function bg_case_card($item, $lang) {
  global $UI;
  $image = (isset($item['image']) && is_string($item['image']) && $item['image'] !== '') ? $item['image'] : '';
  echo '<article class="group glow-card flex flex-col overflow-hidden">';
  echo '<div class="relative aspect-[4/3] overflow-hidden bg-[#e9eef7]">';
  if ($image !== '') {
    echo '<img src="' . h(bg_url($image)) . '" alt="' . h($item['title']) . '" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" loading="lazy">';
  } else {
    echo '<div class="absolute inset-0 bg-grid opacity-60"></div><div class="absolute inset-0 bg-gradient-to-br from-[#e9eef7] to-[#dbe4f3]"></div>';
  }
  echo '<span class="absolute left-3 top-3 rounded-full border border-[#0c1426]/10 bg-[#0c1426]/[0.04] px-2.5 py-1 text-xs text-tech-muted backdrop-blur">' . h($item['category']) . '</span>';
  echo '<div class="pointer-events-none absolute inset-x-0 bottom-0 h-px bg-gradient-to-r from-transparent via-tech-blue/60 to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-100"></div>';
  echo '</div>';
  echo '<div class="flex flex-1 flex-col p-5">';
  echo '<h3 class="text-base font-semibold text-tech-ink transition-colors group-hover:text-tech-blue">' . h($item['title']) . '</h3>';
  echo '<p class="mt-2 flex-1 text-sm leading-relaxed text-tech-muted">' . h($item['description']) . '</p>';
  echo '<span class="mt-4 inline-flex items-center text-sm font-medium text-tech-blue">' . h(bg_pick($UI['common']['viewCase'], $lang)) . '<span class="ml-1 transition-transform group-hover:translate-x-1">&rarr;</span></span>';
  echo '</div></article>';
}

// ===== SiteNav =====
function bg_render_nav($lang) {
  global $UI;
  $nav = bg_get_collection('nav');
  $site = bg_get_singleton('site');
  $logo = !empty($site['logo']) ? $site['logo'] : '/logo-removebg.png';
  echo '<header class="sticky top-0 z-50 glass border-b border-[#0c1426]/10">';
  echo '<div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 md:h-20">';
  echo '<a href="' . bg_url('/') . '" class="group flex items-center gap-2.5" aria-label="北港3D设计 BEIGANG DESIGN">';
  bg_brand_logo($logo, 'h-12 w-auto md:h-[72px]');
  echo '</a>';
  echo '<nav class="hidden items-center gap-1 md:flex">';
  foreach ($nav as $item) {
    $label = bg_t($item['label'], $lang);
    $href = isset($item['href']) ? $item['href'] : '#';
    $children = (isset($item['children']) && is_array($item['children'])) ? $item['children'] : array();
    echo '<div class="group relative">';
    echo '<a href="' . bg_url($href) . '" class="inline-flex items-center rounded-md px-3 py-2 text-base font-medium text-tech-muted transition-colors hover:text-tech-ink">' . h($label) . '</a>';
    if (count($children)) {
      echo '<div class="invisible absolute left-0 top-full w-56 translate-y-2 rounded-xl border border-[#0c1426]/10 bg-tech-panel/95 p-1.5 opacity-0 shadow-2xl backdrop-blur-xl transition-all duration-200 group-hover:visible group-hover:translate-y-1 group-hover:opacity-100">';
      foreach ($children as $child) {
        $clabel = bg_t($child['label'], $lang);
        $chref = isset($child['href']) ? $child['href'] : '#';
        echo '<a href="' . bg_url($chref) . '" class="block rounded-md px-3 py-2 text-sm text-tech-muted transition-colors hover:bg-[#0c1426]/[0.04] hover:text-tech-ink">' . h($clabel) . '</a>';
      }
      echo '</div>';
    }
    echo '</div>';
  }
  echo '</nav>';
  echo '<div class="flex items-center gap-2">';
  echo '<button type="button" class="lang-toggle-btn inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-tech-blue text-xs font-bold text-white shadow-sm transition-colors hover:bg-tech-blue/90" data-lang-toggle aria-label="切换语言">EN</button>';
  echo '<a href="' . bg_url('/contact') . '" class="group relative hidden items-center gap-2 overflow-hidden rounded-full px-5 py-2 text-base font-semibold text-white transition-transform hover:-translate-y-0.5 sm:inline-flex"><span class="absolute inset-0 bg-gradient-to-r from-tech-blue to-tech-cyan"></span><span class="relative">' . h(bg_pick($UI['common']['contactBtn'], $lang)) . '</span></a>';
  echo '<button type="button" class="mobile-nav-open inline-flex h-10 w-10 items-center justify-center rounded-lg border border-[#0c1426]/10 bg-white/70 text-tech-ink transition-colors hover:bg-white md:hidden" aria-label="打开菜单">' . bg_icon('menu', 20) . '</button>';
  echo '</div></div></header>';
  bg_render_mobile_nav($nav, $lang);
}

function bg_render_mobile_nav($nav, $lang) {
  global $UI;
  echo '<div class="mobile-nav-overlay fixed inset-0 z-[60] hidden md:hidden">';
  echo '<div class="absolute inset-0 bg-[#0c1426]/40 backdrop-blur-sm" data-mobile-nav-close></div>';
  echo '<div class="mobile-nav-panel absolute right-0 top-0 flex h-full w-[82%] max-w-xs flex-col bg-white shadow-2xl">';
  echo '<div class="flex items-center justify-between border-b border-[#0c1426]/10 px-5 py-4"><span class="text-sm font-semibold text-tech-ink">' . h(bg_pick($UI['common']['navMenu'], $lang)) . '</span><button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-tech-muted transition-colors hover:bg-[#0c1426]/[0.05]" data-mobile-nav-close aria-label="关闭菜单">' . bg_icon('x', 20) . '</button></div>';
  echo '<nav class="flex-1 overflow-y-auto px-3 py-4">';
  echo '<a href="' . bg_url('/') . '" data-mobile-nav-close class="block rounded-lg px-3 py-3 text-base font-medium text-tech-ink transition-colors hover:bg-[#0c1426]/[0.04]">' . h(bg_pick($UI['common']['home'], $lang)) . '</a>';
  foreach ($nav as $item) {
    $label = bg_t($item['label'], $lang);
    $href = isset($item['href']) ? $item['href'] : '#';
    $children = (isset($item['children']) && is_array($item['children'])) ? $item['children'] : array();
    echo '<div class="py-0.5">';
    echo '<a href="' . bg_url($href) . '" data-mobile-nav-close class="block rounded-lg px-3 py-3 text-base font-medium text-tech-ink transition-colors hover:bg-[#0c1426]/[0.04]">' . h($label) . '</a>';
    if (count($children)) {
      echo '<div class="ml-3 border-l border-[#0c1426]/10 pl-2">';
      foreach ($children as $child) {
        $clabel = bg_t($child['label'], $lang);
        $chref = isset($child['href']) ? $child['href'] : '#';
        echo '<a href="' . bg_url($chref) . '" data-mobile-nav-close class="block rounded-lg px-3 py-2 text-sm text-tech-muted transition-colors hover:bg-[#0c1426]/[0.04] hover:text-tech-ink">' . h($clabel) . '</a>';
      }
      echo '</div>';
    }
    echo '</div>';
  }
  echo '</nav>';
  echo '<div class="border-t border-[#0c1426]/10 p-4"><a href="' . bg_url('/contact') . '" data-mobile-nav-close class="flex items-center justify-center gap-1.5 rounded-full bg-gradient-to-r from-tech-blue to-tech-cyan px-5 py-3 text-base font-semibold text-white">' . h(bg_pick($UI['common']['contactBtn'], $lang)) . ' ' . bg_icon('arrow-up-right', 16) . '</a></div>';
  echo '</div></div>';
}

// ===== SiteFooter =====
function bg_render_footer($lang) {
  global $UI;
  $footerCols = bg_get_collection('footerColumns');
  $articles = bg_get_collection('articles');
  $contact = bg_get_singleton('contact');
  $cities = bg_get_collection('cities');
  $site = bg_get_singleton('site');
  $heroSlogans = bg_get_collection('heroSlogans');
  $brandZh = !empty($site['brandZh']) ? $site['brandZh'] : BG_SITE_NAME_ZH;
  $brandEn = !empty($site['brandEn']) ? $site['brandEn'] : BG_SITE_NAME_EN;
  $logo = !empty($site['logo']) ? $site['logo'] : '/logo-removebg.png';
  $copyrightYearStart = !empty($site['copyrightYearStart']) ? $site['copyrightYearStart'] : '2003';

  echo '<footer class="liquid-glass-section relative mt-24 border-t border-[#0c1426]/10">';
  echo '<div class="relative z-10 mx-auto grid max-w-6xl gap-10 px-4 py-16 sm:grid-cols-2 lg:grid-cols-4">';
  foreach ($footerCols as $i => $col) {
    echo '<div>';
    echo '<div class="mb-5 flex items-center gap-2"><span class="font-mono text-xs text-tech-blue/80">0' . ($i + 1) . '</span><h3 class="text-sm font-semibold tracking-wide text-tech-ink">' . h(bg_t($col['title'], $lang)) . '</h3></div>';
    echo '<ul class="space-y-2.5">';
    foreach (bg_t_list($col['links'], $lang) as $link) {
      echo '<li class="text-sm text-tech-muted transition-colors hover:text-tech-cyan">' . h($link) . '</li>';
    }
    echo '</ul></div>';
  }
  echo '<div><div class="mb-5 flex items-center gap-2"><span class="font-mono text-xs text-tech-blue/80">0' . (count($footerCols) + 1) . '</span><h3 class="text-sm font-semibold tracking-wide text-tech-ink">' . h(bg_pick($UI['footer']['articlesTitle'], $lang)) . '</h3></div><ul class="space-y-2.5">';
  foreach (array_slice($articles, 0, 6) as $article) {
    echo '<li><a href="' . bg_url('/points') . '" class="text-sm text-tech-muted transition-colors hover:text-tech-cyan">' . h(bg_t($article['title'], $lang)) . '</a></li>';
  }
  echo '</ul></div></div>';

  echo '<div class="relative z-10 border-t border-[#0c1426]/10"><div class="mx-auto grid gap-8 px-4 py-10 sm:grid-cols-2 lg:grid-cols-3">';
  echo '<div class="glass p-6"><h4 class="mb-3 font-mono text-xs uppercase tracking-[0.2em] text-tech-cyan">' . h(bg_pick($UI['footer']['contactTitle'], $lang)) . '</h4>';
  echo '<p class="text-sm text-tech-muted">' . h(bg_pick($UI['footer']['addrLabel'], $lang)) . h(bg_t($contact['address'], $lang)) . '</p>';
  echo '<p class="mt-1 text-sm text-tech-muted">' . h(bg_pick($UI['footer']['phoneLabel'], $lang)) . h($contact['phoneWechat']) . '</p>';
  echo '<p class="mt-1 text-sm text-tech-muted">' . h(bg_pick($UI['footer']['qqLabel'], $lang)) . h($contact['qqEmail']) . '</p></div>';
  echo '<div class="glass p-6"><h4 class="mb-3 font-mono text-xs uppercase tracking-[0.2em] text-tech-cyan">' . h(bg_pick($UI['footer']['citiesTitle'], $lang)) . '</h4>';
  echo '<p class="text-sm leading-relaxed text-tech-muted">' . h(implode('、', array_map(function ($c) use ($lang) { return bg_t($c['name'], $lang); }, $cities))) . ' ' . h(bg_pick($UI['footer']['etc'], $lang)) . '</p></div>';
  echo '<div class="glass flex flex-col justify-between p-6"><h4 class="font-mono text-xs uppercase tracking-[0.2em] text-tech-cyan">' . h(bg_pick($UI['footer']['linksTitle'], $lang)) . '</h4><div class="mt-4 flex flex-col gap-2">';
  echo '<a href="' . bg_url('/admin/login') . '" class="text-sm font-medium text-tech-cyan transition-opacity hover:opacity-80">' . h(bg_pick($UI['footer']['adminLogin'], $lang)) . '</a>';
  echo '<a href="' . bg_url('/3d') . '" class="text-sm font-medium text-tech-cyan transition-opacity hover:opacity-80">' . h(bg_pick($UI['footer']['threedsLink'], $lang)) . '</a>';
  echo '</div></div></div></div>';

  echo '<div class="relative z-10 border-t border-[#0c1426]/10"><div class="mx-auto grid grid-cols-1 items-center gap-8 px-4 py-10 sm:grid-cols-3">';
  echo '<div class="flex items-center justify-center gap-5 sm:justify-start">';
  bg_brand_logo($logo, 'h-[110px] w-auto drop-shadow-[0_4px_24px_rgba(12,142,255,0.35)]');
  echo '<div class="hidden sm:block"><p class="text-xl font-bold tracking-wide text-tech-ink">' . h($lang === 'en' ? $brandEn : $brandZh) . '</p><p class="mt-1 text-sm text-tech-muted">' . h(bg_pick($UI['footer']['tagline'], $lang)) . '</p></div>';
  echo '</div>';
  bg_footer_slogan(bg_t($heroSlogans[0]['value'], $lang) ?: bg_pick($UI['home']['ctaTitle'], $lang), bg_t($heroSlogans[1]['value'], $lang));
  echo '<div class="text-center sm:text-right"><p class="text-sm text-tech-muted">Copyright © ' . h($copyrightYearStart) . '-2026 ' . h(bg_t($site['copyrightOwner'], $lang)) . '</p>';
  if (!empty($site['icpRecord'])) echo '<p class="mt-1 text-sm text-tech-muted">' . h(bg_t($site['icpRecord'], $lang)) . '</p>';
  echo '</div></div></div>';
  echo '</footer>';
}
