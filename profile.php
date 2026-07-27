<?php
require_once __DIR__ . '/lib/config.php';
require_once __DIR__ . '/lib/lang.php';
require_once __DIR__ . '/lib/ui-text.php';
require_once __DIR__ . '/lib/content.php';
require_once __DIR__ . '/lib/render.php';

$lang = bg_get_lang();
$siteName = ($lang === 'en') ? BG_SITE_NAME_EN : BG_SITE_NAME_ZH;

$overview = bg_get_singleton('companyOverview');
$overviewText = ($overview && isset($overview['text'])) ? bg_t($overview['text'], $lang) : '';
$companyStats = bg_get_collection('stats');
$cities = bg_get_collection('cities');

bg_head($lang, bg_pick($UI['profilePage']['title'], $lang) . ' | ' . $siteName, bg_pick($UI['profilePage']['desc'], $lang));
bg_render_nav($lang);
?>
<main class="flex-1 overflow-x-hidden">
  <?php bg_page_header('06', bg_pick($UI['profilePage']['eyebrow'], $lang), bg_pick($UI['profilePage']['title'], $lang), bg_pick($UI['profilePage']['desc'], $lang)); ?>
  <section class="mx-auto max-w-6xl px-4 py-14">
    <div class="grid gap-10 lg:grid-cols-3">
      <?php bg_reveal_start('lg:col-span-2'); ?>
        <p class="text-lg leading-relaxed text-tech-muted"><?php echo h($overviewText); ?></p>
      <?php bg_reveal_end(); ?>
      <?php bg_reveal_start('grid grid-cols-3 gap-4 lg:grid-cols-1', 120); ?>
        <?php foreach ($companyStats as $i => $stat): ?>
        <?php bg_glow_card_start('0' . ($i + 1), 'p-5'); ?>
          <p class="font-mono text-3xl font-bold text-gradient"><?php echo h(isset($stat['value']) ? $stat['value'] : ''); ?></p>
          <p class="mt-1 text-sm text-tech-muted"><?php echo h(bg_t($stat['label'], $lang)); ?></p>
        <?php bg_glow_card_end(); ?>
        <?php endforeach; ?>
      <?php bg_reveal_end(); ?>
    </div>

    <?php bg_reveal_start(); ?>
      <div class="glow-card mt-12 p-8">
        <h2 class="text-xl font-semibold text-tech-ink"><?php echo h(bg_pick($UI['profilePage']['citiesTitle'], $lang)); ?></h2>
        <p class="mt-4 text-tech-muted"><?php echo h(bg_pick($UI['profilePage']['citiesDesc'], $lang)); ?></p>
        <div class="mt-4 flex flex-wrap gap-2">
          <?php foreach ($cities as $city): ?>
          <span class="rounded-full border border-[#0c1426]/10 bg-[#0c1426]/[0.03] px-3 py-1 text-sm text-tech-cyan/90"><?php echo h(bg_t($city['name'], $lang)); ?></span>
          <?php endforeach; ?>
        </div>
      </div>
    <?php bg_reveal_end(); ?>
  </section>
</main>
<?php bg_render_footer($lang); ?>
<?php bg_foot_js(); ?>
