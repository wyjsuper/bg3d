<?php
require_once __DIR__ . '/lib/config.php';
require_once __DIR__ . '/lib/lang.php';
require_once __DIR__ . '/lib/ui-text.php';
require_once __DIR__ . '/lib/content.php';
require_once __DIR__ . '/lib/render.php';

$lang = bg_get_lang();
$siteName = ($lang === 'en') ? BG_SITE_NAME_EN : BG_SITE_NAME_ZH;

$cases = array();
foreach (bg_get_collection('cases') as $c) {
  $cases[] = array(
    'id' => isset($c['id']) ? (string) $c['id'] : '',
    'title' => bg_t($c['title'], $lang),
    'category' => bg_t($c['category'], $lang),
    'description' => bg_t($c['description'], $lang),
    'image' => (isset($c['image']) && is_string($c['image'])) ? $c['image'] : '',
  );
}

$categoryList = array(
  array('zh' => '全部', 'en' => 'All'),
  array('zh' => '品牌文化', 'en' => 'Brand Culture'),
  array('zh' => '网站建设', 'en' => 'Website'),
  array('zh' => '图片 / 视频', 'en' => 'Image / Video'),
);

bg_head($lang, bg_pick($UI['casePage']['title'], $lang) . ' | ' . $siteName, bg_pick($UI['casePage']['desc'], $lang));
bg_render_nav($lang);
?>
<main class="flex-1 overflow-x-hidden">
  <?php bg_page_header('08', bg_pick($UI['casePage']['eyebrow'], $lang), bg_pick($UI['casePage']['title'], $lang), bg_pick($UI['casePage']['desc'], $lang)); ?>
  <section class="mx-auto max-w-6xl px-4 py-12">
    <?php bg_reveal_start(); ?>
      <div data-filter-root>
        <div class="mb-8 flex flex-wrap gap-2">
          <?php foreach ($categoryList as $i => $cat): $label = bg_pick($cat, $lang); ?>
          <button type="button" data-filter-btn data-cat="<?php echo h($label); ?>"
            class="rounded-full px-4 py-2 text-sm font-medium transition-colors <?php echo $i === 0 ? 'bg-brand text-brand-foreground' : 'border border-border text-muted-foreground hover:bg-accent'; ?>"><?php echo h($label); ?></button>
          <?php endforeach; ?>
        </div>
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          <?php foreach ($cases as $item): ?>
          <div data-filter-item data-cat="<?php echo h($item['category']); ?>"><?php bg_case_card($item, $lang); ?></div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php bg_reveal_end(); ?>
  </section>
</main>
<?php bg_render_footer($lang); ?>
<?php bg_foot_js(); ?>
