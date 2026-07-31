<?php
/** 后台工作台（对应 admin/page.tsx） */
require_once __DIR__ . '/../lib/admin.php';
bg_require_auth();

bg_admin_head('工作台');
bg_admin_frame_start('工作台', '__dashboard__');
?>
<div class="mx-auto max-w-5xl">
  <div class="glass-panel p-6 md:p-8">
    <p class="text-sm font-medium uppercase tracking-widest text-tech-cyan">DASHBOARD</p>
    <h1 class="mt-2 text-2xl font-bold text-tech-ink md:text-3xl">欢迎回来 👋</h1>
    <p class="mt-2 max-w-2xl text-sm leading-relaxed text-tech-muted">这里是北港3D设计官网的统一内容管理中心。在左侧选择模块即可维护网站全部内容，修改后会实时生效于前台页面。</p>
  </div>

  <h2 class="mb-4 mt-8 flex items-center gap-2 text-sm font-semibold text-tech-ink"><?php echo bg_admin_icon('layout-dashboard', 16); ?>内容模块</h2>

  <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    <?php foreach ($BG_COLLECTIONS as $c): ?>
      <?php if (!empty($c['adminHidden'])) continue; ?>
      <a href="<?php e(bg_url('/admin/content/' . $c['type'])); ?>" class="glass-panel group flex items-center gap-4 p-5 transition-all hover:-translate-y-0.5 hover:border-tech-blue/40 hover:shadow-lg hover:shadow-tech-blue/10">
        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-tech-blue/15 text-tech-cyan transition-colors group-hover:bg-tech-blue group-hover:text-white"><?php echo bg_admin_icon(bg_admin_type_icon($c['type']), 20); ?></span>
        <div class="min-w-0 flex-1">
          <p class="truncate text-sm font-semibold text-tech-ink"><?php e($c['label']); ?></p>
          <p class="text-xs text-tech-muted"><?php echo !empty($c['singleton']) ? '单条设置' : h($c['singular']) . '管理'; ?></p>
        </div>
        <?php echo bg_admin_icon('arrow-right', 16); ?>
      </a>
    <?php endforeach; ?>
  </div>

  <div class="glass-panel mt-8 border-dashed p-5 text-xs leading-relaxed text-tech-muted">
    说明：后台登录接入服务端校验（账号密码在 <code>lib/config.php</code>，默认 admin / admin123），会话以 HttpOnly 签名 Cookie 维持，守卫 <code>/admin</code> 与 <code>/api/content</code>。所有内容存储于 <code>data/content.json</code>，由后台修改并实时反映到前台页面。
  </div>
</div>
<?php
bg_admin_frame_end();
