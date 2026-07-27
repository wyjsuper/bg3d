<?php
require_once __DIR__ . '/lib/config.php';
require_once __DIR__ . '/lib/lang.php';
require_once __DIR__ . '/lib/ui-text.php';
require_once __DIR__ . '/lib/content.php';
require_once __DIR__ . '/lib/render.php';

$lang = bg_get_lang();
$siteName = ($lang === 'en') ? BG_SITE_NAME_EN : BG_SITE_NAME_ZH;

$planGroups = array();
foreach (bg_get_collection('plans') as $group) {
  $planGroups[] = array(
    'id' => isset($group['id']) ? $group['id'] : '',
    'title' => bg_t($group['title'], $lang),
    'items' => bg_t_list(isset($group['items']) ? $group['items'] : array(), $lang),
  );
}

bg_head($lang, bg_pick($UI['planPage']['title'], $lang) . ' | ' . $siteName, bg_pick($UI['planPage']['desc'], $lang));
bg_render_nav($lang);
?>
<main class="flex-1 overflow-x-hidden">
  <?php bg_page_header('04', bg_pick($UI['planPage']['eyebrow'], $lang), bg_pick($UI['planPage']['title'], $lang), bg_pick($UI['planPage']['desc'], $lang)); ?>
  <section class="mx-auto max-w-6xl px-4 py-14">
    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
      <?php foreach ($planGroups as $i => $group): ?>
      <?php bg_reveal_start('', ($i % 3) * 90); ?>
        <div id="<?php echo h($group['title']); ?>" class="glow-card relative overflow-hidden p-6 sm:p-7 h-full">
          <span class="pointer-events-none absolute right-4 top-3 font-mono text-5xl font-bold text-[#0c1426]/[0.05]">0<?php echo ($i % 6) + 1; ?></span>
          <h3 class="text-lg font-semibold text-tech-ink"><?php echo h($group['title']); ?></h3>
          <ul class="mt-4 space-y-2.5">
            <?php foreach ($group['items'] as $item): ?>
            <li class="flex items-start gap-2.5 text-sm text-tech-muted"><span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-gradient-to-r from-tech-blue to-tech-cyan"></span><?php echo h($item); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php bg_reveal_end(); ?>
      <?php endforeach; ?>
    </div>
  </section>
</main>
<?php bg_render_footer($lang); ?>
<?php bg_foot_js(); ?>
