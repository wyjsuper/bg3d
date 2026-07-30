<?php
require_once __DIR__ . '/lib/config.php';
require_once __DIR__ . '/lib/lang.php';
require_once __DIR__ . '/lib/ui-text.php';
require_once __DIR__ . '/lib/content.php';
require_once __DIR__ . '/lib/render.php';

$lang = bg_get_lang();
$siteName = ($lang === 'en') ? BG_SITE_NAME_EN : BG_SITE_NAME_ZH;

// 数据
$threeDSCases = bg_get_collection('threeds');
$videos = array();
foreach ($threeDSCases as $c) {
  $videos[] = array(
    'id' => isset($c['id']) ? $c['id'] : '',
    'title' => bg_t($c['title'], $lang),
    'category' => bg_t($c['category'], $lang),
    'description' => bg_t($c['description'], $lang),
    'videoUrl' => (isset($c['videoUrl']) && is_string($c['videoUrl'])) ? $c['videoUrl'] : '',
    'poster' => (isset($c['poster']) && is_string($c['poster'])) ? $c['poster'] : '',
  );
}
$contact = bg_get_singleton('contact') ?: array('address' => '', 'phoneWechat' => '', 'qqEmail' => '');

$benefits = array();
foreach ($UI['threeds']['benefits'] as $i => $b) {
  $benefits[] = array(
    'no' => str_pad($i + 1, 2, '0', STR_PAD_LEFT),
    'title' => bg_pick($b['title'], $lang),
    'desc' => bg_pick($b['desc'], $lang),
  );
}

bg_head($lang, bg_pick($UI['threeds']['title'], $lang) . ' | ' . $siteName, bg_pick($UI['threeds']['desc'], $lang));
bg_render_nav($lang);
?>
<main class="flex-1 overflow-x-hidden">
  <?php bg_page_header('07', bg_pick($UI['threeds']['eyebrow'], $lang), bg_pick($UI['threeds']['title'], $lang), bg_pick($UI['threeds']['desc'], $lang)); ?>

  <!-- 好处 -->
  <section class="mx-auto max-w-6xl px-4 py-16">
    <?php bg_reveal_start(); bg_eyebrow('', bg_pick($UI['threeds']['benefitsTitle'], $lang)); bg_reveal_end(); ?>
    <div class="mt-10 grid gap-6 md:grid-cols-3">
      <?php foreach ($benefits as $i => $b): ?>
      <?php bg_reveal_start('', $i * 90); ?>
        <?php bg_glow_card_start($b['no'], 'h-full glass'); ?>
          <h3 class="text-lg font-semibold text-gradient"><?php echo h($b['title']); ?></h3>
          <p class="mt-3 text-sm leading-relaxed text-tech-muted"><?php echo h($b['desc']); ?></p>
        <?php bg_glow_card_end(); ?>
      <?php bg_reveal_end(); ?>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- 作品 -->
  <section class="bg-[#0c1426]/[0.025] px-4 py-16">
    <div class="mx-auto max-w-6xl">
      <?php bg_reveal_start(); ?>
        <?php bg_eyebrow('', bg_pick($UI['threeds']['worksTitle'], $lang)); ?>
        <h2 class="mt-4 font-display text-2xl font-semibold text-tech-ink sm:text-3xl"><?php echo h(bg_pick($UI['threeds']['worksTitle'], $lang)); ?></h2>
        <p class="mt-3 max-w-3xl text-sm leading-relaxed text-tech-muted"><?php echo h(bg_pick($UI['threeds']['worksDesc'], $lang)); ?></p>
      <?php bg_reveal_end(); ?>
      <?php bg_reveal_start(); ?>
        <div class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
          <?php foreach ($videos as $item): ?>
            <?php bg_video_card($item); ?>
          <?php endforeach; ?>
        </div>
      <?php bg_reveal_end(); ?>
    </div>
  </section>

  <!-- 平台应用与合作 -->
  <section class="mx-auto max-w-6xl px-4 py-16">
    <?php bg_reveal_start(); ?>
      <?php bg_eyebrow('', bg_pick($UI['threeds']['platformsTitle'], $lang)); ?>
      <h2 class="mt-4 font-display text-2xl font-semibold text-tech-ink sm:text-3xl"><?php echo h(bg_pick($UI['threeds']['platformsTitle'], $lang)); ?></h2>
    <?php bg_reveal_end(); ?>
    <div class="mt-8 grid gap-5 sm:grid-cols-3">
      <?php foreach ($UI['threeds']['contactRows'] as $i => $row):
        $value = $i === 0 ? $contact['phoneWechat'] : ($i === 1 ? $contact['qqEmail'] : bg_t($contact['address'], $lang));
        $icon = $i === 0 ? 'phone' : ($i === 1 ? 'mail' : 'map-pin');
        $label = bg_pick($row['t'], $lang);
      ?>
      <?php bg_reveal_start('', $i * 90); ?>
        <?php bg_glow_card_start('0' . ($i + 1), 'group h-full'); ?>
          <div class="flex items-start gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-tech-cyan/15 to-tech-cyan/5 text-tech-cyan shadow-sm transition-transform duration-300 group-hover:scale-105">
              <?php echo bg_icon($icon, 22, 1.8); ?>
            </div>
            <div class="min-w-0 flex-1">
              <p class="text-xs font-medium uppercase tracking-wider text-tech-muted"><?php echo h($label); ?></p>
              <p class="mt-1.5 break-words text-base font-semibold leading-snug text-tech-ink"><?php echo h($value ?: '—'); ?></p>
            </div>
          </div>
        <?php bg_glow_card_end(); ?>
      <?php bg_reveal_end(); ?>
      <?php endforeach; ?>
    </div>
  </section>
</main>
<?php bg_render_footer($lang); ?>
<?php bg_foot_js(); ?>
