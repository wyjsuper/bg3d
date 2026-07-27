<?php
require_once __DIR__ . '/lib/config.php';
require_once __DIR__ . '/lib/lang.php';
require_once __DIR__ . '/lib/ui-text.php';
require_once __DIR__ . '/lib/content.php';
require_once __DIR__ . '/lib/render.php';

$lang = bg_get_lang();
$siteName = ($lang === 'en') ? BG_SITE_NAME_EN : BG_SITE_NAME_ZH;

$articles = array();
foreach (bg_get_collection('articles') as $a) {
  $articles[] = array(
    'id' => isset($a['id']) ? (string) $a['id'] : '',
    'title' => bg_t($a['title'], $lang),
    'date' => isset($a['date']) ? $a['date'] : '',
    'category' => bg_t($a['category'], $lang),
  );
}

$categoryList = array(
  array('zh' => '全部', 'en' => 'All'),
  array('zh' => '品牌文化', 'en' => 'Brand Culture'),
  array('zh' => '站点运营', 'en' => 'Site Operations'),
  array('zh' => '市场营销', 'en' => 'Marketing'),
);

bg_head($lang, bg_pick($UI['pointsPage']['title'], $lang) . ' | ' . $siteName, bg_pick($UI['pointsPage']['desc'], $lang));
bg_render_nav($lang);
?>
<main class="flex-1 overflow-x-hidden">
  <?php bg_page_header('09', bg_pick($UI['pointsPage']['eyebrow'], $lang), bg_pick($UI['pointsPage']['title'], $lang), bg_pick($UI['pointsPage']['desc'], $lang)); ?>
  <section class="mx-auto max-w-4xl px-4 py-12">
    <?php bg_reveal_start(); ?>
      <div data-filter-root>
        <div class="mb-8 flex flex-wrap gap-2">
          <?php foreach ($categoryList as $i => $cat): $label = bg_pick($cat, $lang); ?>
          <button type="button" data-filter-btn data-cat="<?php echo h($label); ?>"
            class="rounded-full px-4 py-2 text-sm font-medium transition-colors <?php echo $i === 0 ? 'bg-brand text-brand-foreground' : 'border border-border text-muted-foreground hover:bg-accent'; ?>"><?php echo h($label); ?></button>
          <?php endforeach; ?>
        </div>
        <ul class="divide-y divide-border">
          <?php foreach ($articles as $article): ?>
          <li data-filter-item data-cat="<?php echo h($article['category']); ?>">
            <a href="#" class="group flex items-center justify-between gap-4 py-5 transition-colors">
              <span class="text-base text-foreground transition-colors group-hover:text-brand"><?php echo h($article['title']); ?></span>
              <span class="shrink-0 text-sm text-muted-foreground"><?php echo h($article['date']); ?></span>
            </a>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php bg_reveal_end(); ?>
  </section>
</main>
<?php bg_render_footer($lang); ?>
<?php bg_foot_js(); ?>
