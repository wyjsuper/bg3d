<?php
/**
 * FDE 资讯归档 —— 列表页 + 详情页
 *   /news              归档列表（分页）
 *   /news?p=2          第 2 页
 *   /news?cat=行业趋势  按分类筛选
 *   /news?slug=xxx     单条详情（本站沉淀内容 + 原始出处标注）
 */
require_once __DIR__ . '/lib/config.php';
require_once __DIR__ . '/lib/lang.php';
require_once __DIR__ . '/lib/ui-text.php';
require_once __DIR__ . '/lib/content.php';
require_once __DIR__ . '/lib/render.php';
require_once __DIR__ . '/lib/news.php';

$lang = bg_get_lang();
$siteName = ($lang === 'en') ? BG_SITE_NAME_EN : BG_SITE_NAME_ZH;
$T = $UI['newsPage'];

$slug = isset($_GET['slug']) ? trim((string) $_GET['slug']) : '';
$cat = isset($_GET['cat']) ? trim((string) $_GET['cat']) : '';
$page = isset($_GET['p']) ? (int) $_GET['p'] : 1;

/** 语言链接保持（本地预览用 ?lang=en） */
function news_qs($params) {
  if (isset($_GET['lang']) && $_GET['lang'] !== '') $params['lang'] = $_GET['lang'];
  $parts = array();
  foreach ($params as $k => $v) {
    if ($v === '' || $v === null) continue;
    $parts[] = rawurlencode($k) . '=' . rawurlencode((string) $v);
  }
  return count($parts) ? ('/news?' . implode('&', $parts)) : '/news';
}

/** 单条卡片 */
function news_card($item, $lang, $delay = 0) {
  bg_reveal_start('', $delay);
  echo '<a href="' . h(bg_url(news_qs(array('slug' => $item['slug'])))) . '" class="group flex h-full flex-col rounded-2xl border border-[#0c1426]/10 bg-white/70 p-5 backdrop-blur-sm transition-all duration-300 hover:-translate-y-1 hover:border-tech-blue/40 hover:bg-white/90 hover:shadow-lg">';
  echo '<div class="flex items-center justify-between gap-3">';
  echo '<span class="rounded-md bg-tech-blue/10 px-2 py-0.5 text-[10px] font-semibold tracking-wider text-tech-blue">' . h(bg_t($item['category'], $lang)) . '</span>';
  echo '<span class="shrink-0 font-mono text-[11px] text-tech-muted/70">' . h($item['date']) . '</span>';
  echo '</div>';
  echo '<h3 class="mt-3 text-[15px] font-semibold leading-snug text-tech-ink line-clamp-2 group-hover:text-tech-blue">' . h(bg_t($item['title'], $lang)) . '</h3>';
  echo '<p class="mt-2 flex-1 text-xs leading-relaxed text-tech-muted line-clamp-3">' . h(bg_t($item['summary'], $lang)) . '</p>';
  echo '<div class="mt-4 flex items-center justify-between gap-2 border-t border-[#0c1426]/[0.08] pt-3">';
  echo '<span class="inline-flex min-w-0 items-center gap-1 text-[11px] text-tech-muted/80">' . bg_icon('globe', 11, 2) . '<span class="truncate">' . h(bg_t($item['source'], $lang)) . '</span></span>';
  echo '<span class="shrink-0 text-tech-cyan transition-transform group-hover:translate-x-0.5">' . bg_icon('arrow-right', 13, 2) . '</span>';
  echo '</div>';
  echo '</a>';
  bg_reveal_end();
}

// ============ 详情页 ============
if ($slug !== '') {
  $item = bg_news_find($slug);

  if (!$item) {
    bg_head($lang, bg_pick($T['notFound'], $lang) . ' | ' . $siteName);
    bg_render_nav($lang);
    echo '<main class="flex-1 overflow-x-hidden">';
    bg_page_header('', bg_pick($T['eyebrow'], $lang), bg_pick($T['notFound'], $lang), bg_pick($T['notFoundDesc'], $lang));
    echo '<section class="mx-auto max-w-6xl px-4 py-14">';
    bg_cta_button(news_qs(array()), bg_pick($T['backToList'], $lang), 'ghost');
    echo '</section></main>';
    bg_render_footer($lang);
    bg_foot_js();
    exit;
  }

  $title = bg_t($item['title'], $lang);
  $summary = bg_t($item['summary'], $lang);
  $source = bg_t($item['source'], $lang);
  $category = bg_t($item['category'], $lang);
  $paragraphs = bg_news_paragraphs($item, $lang);
  list($prev, $next) = bg_news_siblings($slug, $lang);

  bg_head($lang, $title . ' | ' . $siteName, $summary);
  bg_render_nav($lang);
  ?>
  <main class="flex-1 overflow-x-hidden">
    <section class="liquid-glass-section relative overflow-hidden border-b border-[#0c1426]/10 px-4 py-16 sm:py-20">
      <div class="relative z-10 mx-auto max-w-3xl">
        <a href="<?php echo h(bg_url(news_qs(array()))); ?>" class="inline-flex items-center gap-1.5 font-mono text-xs uppercase tracking-[0.18em] text-tech-cyan transition-opacity hover:opacity-70">
          <span class="inline-block rotate-180"><?php echo bg_icon('arrow-right', 12, 2); ?></span><?php echo h(bg_pick($T['backToList'], $lang)); ?>
        </a>
        <div class="mt-5 flex flex-wrap items-center gap-2.5">
          <span class="rounded-md bg-tech-blue/10 px-2.5 py-1 text-[11px] font-semibold tracking-wider text-tech-blue"><?php echo h($category); ?></span>
          <span class="font-mono text-xs text-tech-muted/80"><?php echo h(bg_pick($T['publishedOn'], $lang)); ?> <?php echo h($item['date']); ?></span>
          <span class="inline-flex items-center gap-1 text-xs text-tech-muted/80"><?php echo bg_icon('globe', 12, 2); ?><?php echo h($source); ?></span>
        </div>
        <h1 class="mt-4 font-display text-3xl font-semibold leading-tight tracking-tight text-tech-ink sm:text-4xl"><?php echo h($title); ?></h1>
        <p class="mt-5 border-l-2 border-tech-cyan/50 pl-4 text-base leading-relaxed text-tech-muted"><?php echo h($summary); ?></p>
      </div>
    </section>

    <section class="mx-auto max-w-3xl px-4 py-12 sm:py-14">
      <?php bg_reveal_start(); ?>
        <article class="glow-card relative overflow-hidden p-7 sm:p-9">
          <?php if ($paragraphs): ?>
            <div class="space-y-5">
              <?php foreach ($paragraphs as $p): ?>
                <p class="text-[15px] leading-[1.9] text-tech-ink/85"><?php echo h($p); ?></p>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <p class="text-[15px] leading-[1.9] text-tech-ink/85"><?php echo h($summary); ?></p>
          <?php endif; ?>
        </article>
      <?php bg_reveal_end(); ?>

      <?php bg_reveal_start('', 80); ?>
        <div class="mt-7 rounded-2xl border border-tech-blue/20 bg-tech-blue/[0.04] p-6">
          <h2 class="flex items-center gap-2 font-mono text-xs uppercase tracking-[0.2em] text-tech-blue">
            <?php echo bg_icon('globe', 14, 2); ?><?php echo h(bg_pick($T['sourceTitle'], $lang)); ?>
          </h2>
          <dl class="mt-4 space-y-2 text-sm">
            <div class="flex flex-wrap gap-x-2">
              <dt class="shrink-0 text-tech-muted/80"><?php echo h(bg_pick($T['sourceFrom'], $lang)); ?>：</dt>
              <dd class="font-medium text-tech-ink"><?php echo h($source); ?></dd>
            </div>
            <div class="flex flex-wrap gap-x-2">
              <dt class="shrink-0 text-tech-muted/80"><?php echo h(bg_pick($T['publishedOn'], $lang)); ?>：</dt>
              <dd class="font-mono text-tech-ink"><?php echo h($item['date']); ?></dd>
            </div>
            <?php if (!empty($item['archivedAt'])): ?>
            <div class="flex flex-wrap gap-x-2">
              <dt class="shrink-0 text-tech-muted/80"><?php echo h(bg_pick($T['archivedOn'], $lang)); ?>：</dt>
              <dd class="font-mono text-tech-ink"><?php echo h($item['archivedAt']); ?></dd>
            </div>
            <?php endif; ?>
            <div class="flex flex-wrap gap-x-2">
              <dt class="shrink-0 text-tech-muted/80"><?php echo h(bg_pick($T['originalLink'], $lang)); ?>：</dt>
              <dd class="min-w-0 flex-1 break-all"><a href="<?php echo h($item['link']); ?>" target="_blank" rel="noopener nofollow" class="text-tech-cyan underline-offset-2 hover:underline"><?php echo h($item['link']); ?></a></dd>
            </div>
          </dl>
          <p class="mt-4 border-t border-tech-blue/15 pt-3 text-xs leading-relaxed text-tech-muted/85"><?php echo h(bg_pick($T['sourceNotice'], $lang)); ?></p>
          <a href="<?php echo h($item['link']); ?>" target="_blank" rel="noopener nofollow" class="group mt-4 inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-tech-blue to-tech-cyan px-5 py-2.5 text-sm font-semibold text-white transition-transform hover:-translate-y-0.5">
            <?php echo h(bg_pick($T['viewOriginal'], $lang)); ?><?php echo bg_icon('arrow-up-right', 14, 2); ?>
          </a>
        </div>
      <?php bg_reveal_end(); ?>

      <?php if ($prev || $next): ?>
      <nav class="mt-8 grid gap-3 sm:grid-cols-2">
        <?php if ($prev): ?>
          <a href="<?php echo h(bg_url(news_qs(array('slug' => $prev['slug'])))); ?>" class="group rounded-xl border border-[#0c1426]/10 bg-white/60 p-4 transition-all hover:border-tech-blue/40 hover:bg-white/90">
            <span class="font-mono text-[10px] uppercase tracking-[0.18em] text-tech-cyan"><?php echo h(bg_pick($T['prevArticle'], $lang)); ?></span>
            <p class="mt-1.5 text-sm font-medium leading-snug text-tech-ink line-clamp-2 group-hover:text-tech-blue"><?php echo h(bg_t($prev['title'], $lang)); ?></p>
          </a>
        <?php else: ?><span></span><?php endif; ?>
        <?php if ($next): ?>
          <a href="<?php echo h(bg_url(news_qs(array('slug' => $next['slug'])))); ?>" class="group rounded-xl border border-[#0c1426]/10 bg-white/60 p-4 text-right transition-all hover:border-tech-blue/40 hover:bg-white/90">
            <span class="font-mono text-[10px] uppercase tracking-[0.18em] text-tech-cyan"><?php echo h(bg_pick($T['nextArticle'], $lang)); ?></span>
            <p class="mt-1.5 text-sm font-medium leading-snug text-tech-ink line-clamp-2 group-hover:text-tech-blue"><?php echo h(bg_t($next['title'], $lang)); ?></p>
          </a>
        <?php endif; ?>
      </nav>
      <?php endif; ?>
    </section>
  </main>
  <?php
  bg_render_footer($lang);
  bg_foot_js();
  exit;
}

// ============ 列表页 ============
$all = bg_news_all($lang);
if ($cat !== '') {
  $all = array_values(array_filter($all, function ($it) use ($cat, $lang) {
    return bg_t($it['category'], $lang) === $cat || (isset($it['category']['zh']) && $it['category']['zh'] === $cat);
  }));
}
$perPage = 18;
$total = count($all);
$totalPages = max(1, (int) ceil($total / $perPage));
$page = max(1, min($totalPages, $page));
$items = array_slice($all, ($page - 1) * $perPage, $perPage);
$cats = bg_news_categories($lang);

bg_head($lang, bg_pick($T['title'], $lang) . ' | ' . $siteName, bg_pick($T['desc'], $lang));
bg_render_nav($lang);
?>
<main class="flex-1 overflow-x-hidden">
  <?php bg_page_header('', bg_pick($T['eyebrow'], $lang), bg_pick($T['title'], $lang), bg_pick($T['desc'], $lang)); ?>

  <section class="mx-auto max-w-6xl px-4 py-12">
    <div class="flex flex-wrap items-center justify-between gap-4">
      <span class="inline-flex items-center gap-1.5 rounded-full bg-tech-cyan/10 px-3 py-1.5 text-xs font-medium text-tech-cyan">
        <span class="relative flex h-1.5 w-1.5"><span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-tech-cyan opacity-75"></span><span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-tech-cyan"></span></span>
        <?php echo h(bg_pick($T['totalLabel'], $lang)); ?> <?php echo (int) bg_news_count($lang); ?> <?php echo h(bg_pick($T['unit'], $lang)); ?>
      </span>
      <?php if ($cats): ?>
      <div class="flex flex-wrap items-center gap-1.5">
        <a href="<?php echo h(bg_url(news_qs(array()))); ?>" class="rounded-full px-3 py-1 text-xs transition-colors <?php echo $cat === '' ? 'bg-tech-blue text-white' : 'border border-[#0c1426]/12 text-tech-muted hover:border-tech-blue/40 hover:text-tech-blue'; ?>">All</a>
        <?php foreach ($cats as $cname => $ccount): ?>
        <a href="<?php echo h(bg_url(news_qs(array('cat' => $cname)))); ?>" class="rounded-full px-3 py-1 text-xs transition-colors <?php echo $cat === $cname ? 'bg-tech-blue text-white' : 'border border-[#0c1426]/12 text-tech-muted hover:border-tech-blue/40 hover:text-tech-blue'; ?>"><?php echo h($cname); ?> <span class="opacity-60"><?php echo (int) $ccount; ?></span></a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <?php if (!$items): ?>
      <p class="mt-12 text-center text-sm text-tech-muted"><?php echo h(bg_pick($T['empty'], $lang)); ?></p>
    <?php else: ?>
      <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($items as $i => $item) { news_card($item, $lang, min($i, 8) * 60); } ?>
      </div>

      <?php if ($totalPages > 1): ?>
      <nav class="mt-12 flex flex-wrap items-center justify-center gap-2">
        <?php if ($page > 1): ?>
          <a href="<?php echo h(bg_url(news_qs(array('cat' => $cat, 'p' => $page - 1)))); ?>" class="rounded-full border border-[#0c1426]/12 px-4 py-2 text-sm text-tech-muted transition-colors hover:border-tech-blue/40 hover:text-tech-blue"><?php echo h(bg_pick($T['pagePrev'], $lang)); ?></a>
        <?php endif; ?>
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
          <?php if ($p === $page): ?>
            <span class="rounded-full bg-tech-blue px-4 py-2 text-sm font-medium text-white"><?php echo $p; ?></span>
          <?php else: ?>
            <a href="<?php echo h(bg_url(news_qs(array('cat' => $cat, 'p' => $p)))); ?>" class="rounded-full border border-[#0c1426]/12 px-4 py-2 text-sm text-tech-muted transition-colors hover:border-tech-blue/40 hover:text-tech-blue"><?php echo $p; ?></a>
          <?php endif; ?>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
          <a href="<?php echo h(bg_url(news_qs(array('cat' => $cat, 'p' => $page + 1)))); ?>" class="rounded-full border border-[#0c1426]/12 px-4 py-2 text-sm text-tech-muted transition-colors hover:border-tech-blue/40 hover:text-tech-blue"><?php echo h(bg_pick($T['pageNext'], $lang)); ?></a>
        <?php endif; ?>
      </nav>
      <?php endif; ?>
    <?php endif; ?>
  </section>
</main>
<?php bg_render_footer($lang); ?>
<?php bg_foot_js(); ?>
