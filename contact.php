<?php
require_once __DIR__ . '/lib/config.php';
require_once __DIR__ . '/lib/lang.php';
require_once __DIR__ . '/lib/ui-text.php';
require_once __DIR__ . '/lib/content.php';
require_once __DIR__ . '/lib/render.php';

$lang = bg_get_lang();
$siteName = ($lang === 'en') ? BG_SITE_NAME_EN : BG_SITE_NAME_ZH;

$contact = bg_get_singleton('contact') ?: array('address' => '', 'phoneWechat' => '', 'qqEmail' => '');
$address = bg_t($contact['address'], $lang);
$phoneWechat = isset($contact['phoneWechat']) ? $contact['phoneWechat'] : '';
$qqEmail = isset($contact['qqEmail']) ? $contact['qqEmail'] : '';
$cities = array();
foreach (bg_get_collection('cities') as $c) { $cities[] = bg_t($c['name'], $lang); }

$inputBase = 'glass-input mt-1.5 w-full rounded-lg px-3.5 py-2.5 text-sm text-[#0c1426] placeholder:text-[#5a6b8c]/60 outline-none transition-all';

$rows = array(
  array('dt' => bg_pick(array('zh' => '地址', 'en' => 'Address'), $lang), 'dd' => $address),
  array('dt' => bg_pick(array('zh' => '电话 / 微信', 'en' => 'Tel / WeChat'), $lang), 'dd' => $phoneWechat),
  array('dt' => 'QQ / Email', 'dd' => $qqEmail),
);

bg_head($lang, bg_pick($UI['contactPage']['title'], $lang) . ' | ' . $siteName, bg_pick($UI['contactPage']['desc'], $lang));
bg_render_nav($lang);
?>
<main class="flex-1 overflow-x-hidden">
  <?php bg_page_header('10', bg_pick($UI['contactPage']['eyebrow'], $lang), bg_pick($UI['contactPage']['title'], $lang), bg_pick($UI['contactPage']['desc'], $lang)); ?>
  <?php bg_reveal_start(); ?>
  <section class="mx-auto grid max-w-6xl gap-10 px-4 py-14 lg:grid-cols-2">
    <div>
      <h2 class="font-display text-xl font-semibold text-tech-ink"><?php echo h(bg_pick(array('zh' => '联系方式', 'en' => 'Contact Info'), $lang)); ?></h2>
      <dl class="mt-6 space-y-5">
        <?php foreach ($rows as $i => $row): ?>
        <div class="glass-card flex items-start gap-4 p-4">
          <span class="font-mono text-xs text-tech-blue/80">0<?php echo $i + 1; ?></span>
          <div>
            <dt class="text-sm font-medium text-tech-ink"><?php echo h($row['dt']); ?></dt>
            <dd class="mt-1 text-sm text-tech-muted"><?php echo h($row['dd']); ?></dd>
          </div>
        </div>
        <?php endforeach; ?>
      </dl>
      <div class="mt-8">
        <h3 class="font-mono text-xs uppercase tracking-[0.2em] text-tech-cyan"><?php echo h(bg_pick($UI['footer']['citiesTitle'], $lang)); ?></h3>
        <p class="mt-3 text-sm text-tech-muted"><?php echo h(implode('、', $cities)); ?> <?php echo h(bg_pick($UI['footer']['etc'], $lang)); ?></p>
      </div>
    </div>

    <div class="glow-card relative overflow-hidden p-6 sm:p-8" data-contact>
      <div class="pointer-events-none absolute -right-12 -top-12 h-48 w-48 rounded-full bg-tech-blue/15 blur-3xl"></div>
      <!-- 成功态 -->
      <div class="hidden h-full flex-col items-center justify-center text-center" data-contact-success>
        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-br from-tech-blue to-tech-cyan text-2xl text-white">&#10003;</div>
        <p class="mt-4 text-lg font-semibold text-tech-ink"><?php echo h(bg_pick(array('zh' => '提交成功', 'en' => 'Submitted'), $lang)); ?></p>
        <p class="mt-2 text-sm text-tech-muted"><?php echo h(bg_pick(array('zh' => '感谢您的留言，我们会尽快与您联系。', 'en' => "Thank you for your message. We'll be in touch soon."), $lang)); ?></p>
        <button type="button" data-contact-reset class="mt-6 rounded-full border border-[#0c1426]/15 px-5 py-2 text-sm font-medium text-tech-ink transition-colors hover:border-tech-cyan/50 hover:bg-[#0c1426]/[0.04]"><?php echo h(bg_pick(array('zh' => '再填一份', 'en' => 'Submit Another'), $lang)); ?></button>
      </div>
      <!-- 表单态 -->
      <form class="relative space-y-4" data-contact-form
        data-msg-submitting="<?php echo h(bg_pick(array('zh' => '提交中…', 'en' => 'Submitting…'), $lang)); ?>"
        data-msg-default="<?php echo h(bg_pick(array('zh' => '提交咨询', 'en' => 'Submit'), $lang)); ?>"
        data-msg-error="<?php echo h(bg_pick(array('zh' => '提交失败，请稍后重试', 'en' => 'Submission failed, please retry'), $lang)); ?>">
        <div>
          <label for="name" class="text-sm font-medium text-tech-ink"><?php echo h(bg_pick(array('zh' => '姓名', 'en' => 'Name'), $lang)); ?> <span class="text-red-500">*</span></label>
          <input id="name" name="name" type="text" required autocomplete="name" class="<?php echo $inputBase; ?>" placeholder="<?php echo h(bg_pick(array('zh' => '您的称呼', 'en' => 'Your name'), $lang)); ?>">
        </div>
        <div>
          <label for="phone" class="text-sm font-medium text-tech-ink"><?php echo h(bg_pick(array('zh' => '电话', 'en' => 'Phone'), $lang)); ?> <span class="text-red-500">*</span></label>
          <input id="phone" name="phone" type="tel" required autocomplete="tel" class="<?php echo $inputBase; ?>" placeholder="<?php echo h(bg_pick(array('zh' => '便于回拨的号码', 'en' => 'Phone number for callback'), $lang)); ?>">
        </div>
        <div>
          <label for="message" class="text-sm font-medium text-tech-ink"><?php echo h(bg_pick(array('zh' => '需求说明', 'en' => 'Project Details'), $lang)); ?></label>
          <textarea id="message" name="message" rows="4" class="<?php echo $inputBase; ?> resize-y" placeholder="<?php echo h(bg_pick(array('zh' => '简单描述您的项目（品牌 / 网站 / 推广 / 会展…）', 'en' => 'Briefly describe your project (brand / website / marketing / exhibition…)'), $lang)); ?>"></textarea>
        </div>
        <p class="hidden rounded-lg bg-red-50 px-3 py-2 text-sm text-red-600" data-contact-error></p>
        <button type="submit" data-contact-submit class="liquid-sheen group relative w-full overflow-hidden rounded-lg px-6 py-3 text-sm font-semibold text-white transition-transform hover:-translate-y-0.5 disabled:cursor-not-allowed disabled:opacity-70 disabled:hover:translate-y-0">
          <span class="absolute inset-0 bg-gradient-to-r from-tech-blue to-tech-cyan"></span>
          <span class="absolute inset-0 border border-white/30 rounded-lg"></span>
          <span class="relative flex items-center justify-center gap-2"><span data-contact-btn-text><?php echo h(bg_pick(array('zh' => '提交咨询', 'en' => 'Submit'), $lang)); ?></span></span>
        </button>
      </form>
    </div>
  </section>
  <?php bg_reveal_end(); ?>
</main>
<?php bg_render_footer($lang); ?>
<?php bg_foot_js(); ?>
