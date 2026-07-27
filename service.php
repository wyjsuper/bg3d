<?php
require_once __DIR__ . '/lib/config.php';
require_once __DIR__ . '/lib/lang.php';
require_once __DIR__ . '/lib/ui-text.php';
require_once __DIR__ . '/lib/content.php';
require_once __DIR__ . '/lib/render.php';

$lang = bg_get_lang();
$siteName = ($lang === 'en') ? BG_SITE_NAME_EN : BG_SITE_NAME_ZH;

$serviceBlocks = array();
foreach (bg_get_collection('services') as $block) {
  $serviceBlocks[] = array(
    'id' => isset($block['id']) ? $block['id'] : '',
    'title' => bg_t($block['title'], $lang),
    'items' => bg_t_list(isset($block['items']) ? $block['items'] : array(), $lang),
  );
}

bg_head($lang, bg_pick($UI['servicePage']['title'], $lang) . ' | ' . $siteName, bg_pick($UI['servicePage']['desc'], $lang));
bg_render_nav($lang);
?>
<main class="flex-1 overflow-x-hidden">
  <?php bg_page_header('05', bg_pick($UI['servicePage']['eyebrow'], $lang), bg_pick($UI['servicePage']['title'], $lang), bg_pick($UI['servicePage']['desc'], $lang)); ?>
  <section class="mx-auto max-w-6xl px-4 py-14">
    <div class="grid gap-6 md:grid-cols-3">
      <?php foreach ($serviceBlocks as $i => $block): ?>
      <?php bg_reveal_start('', $i * 90); ?>
        <?php bg_glow_card_start('0' . ($i + 1), 'h-full'); ?>
          <h3 class="text-lg font-semibold text-tech-ink"><?php echo h($block['title']); ?></h3>
          <ul class="mt-4 space-y-2.5">
            <?php foreach ($block['items'] as $item): ?>
            <li class="flex items-start gap-2.5 text-sm text-tech-muted"><span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-gradient-to-r from-tech-blue to-tech-cyan"></span><?php echo h($item); ?></li>
            <?php endforeach; ?>
          </ul>
        <?php bg_glow_card_end(); ?>
      <?php bg_reveal_end(); ?>
      <?php endforeach; ?>
    </div>

    <?php bg_reveal_start(); ?>
      <div class="glow-card relative mt-12 overflow-hidden p-10 sm:p-14">
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-tech-cyan/15 blur-3xl"></div>
        <h2 class="font-display text-2xl font-semibold text-tech-ink sm:text-3xl"><?php echo h(bg_pick($UI['servicePage']['whyTitle'], $lang)); ?></h2>
        <p class="mt-4 max-w-2xl text-tech-muted"><?php echo h(bg_pick($UI['servicePage']['whyDesc'], $lang)); ?></p>
        <div class="mt-8"><?php bg_cta_button('/contact', bg_pick($UI['servicePage']['btn'], $lang)); ?></div>
      </div>
    <?php bg_reveal_end(); ?>
  </section>
</main>
<?php bg_render_footer($lang); ?>
<?php bg_foot_js(); ?>
