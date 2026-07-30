<?php
/** 后台在线更新页面 */
require_once __DIR__ . '/../lib/admin.php';
bg_require_auth();
require_once __DIR__ . '/../lib/update.php';

$allowFull = defined('BG_UPDATE_ALLOW_FULL') ? BG_UPDATE_ALLOW_FULL : false;
$cur = bg_current_version();
$repo = defined('BG_GITHUB_REPO') ? BG_GITHUB_REPO : '';

bg_admin_head('在线更新');
bg_admin_frame_start('在线更新', '__update__');
?>
<div class="mx-auto max-w-3xl space-y-6" data-update-page>
  <div class="rounded-2xl border border-[#0c1426]/10 bg-gradient-to-br from-white to-[#eef3fc] p-6">
    <p class="text-sm font-medium uppercase tracking-widest text-tech-blue">ONLINE UPDATE</p>
    <h1 class="mt-2 text-2xl font-bold text-tech-ink">在线更新</h1>
    <p class="mt-2 text-sm leading-relaxed text-tech-muted">从 GitHub / 镜像源拉取最新发布包，一键更新站点代码。默认保留你的运营数据（data/、uploads/），更新前自动备份将被覆盖的文件。</p>
  </div>

  <div class="grid gap-4 sm:grid-cols-2">
    <div class="rounded-2xl border border-[#0c1426]/10 bg-white p-5">
      <p class="text-xs font-semibold uppercase tracking-wider text-tech-muted">当前版本</p>
      <p class="mt-2 text-lg font-bold text-tech-ink"><?php e($cur['version'] ?? 'unknown'); ?></p>
      <?php if (!empty($cur['date'])): ?><p class="text-xs text-tech-muted">发布于 <?php e($cur['date']); ?></p><?php endif; ?>
      <p class="mt-1 text-xs text-tech-muted">Git: <?php e($cur['commit'] ?? '-'); ?></p>
    </div>
    <div class="rounded-2xl border border-[#0c1426]/10 bg-white p-5">
      <p class="text-xs font-semibold uppercase tracking-wider text-tech-muted">远程最新</p>
      <div id="update-remote" class="mt-2 text-sm text-tech-muted">点击「检查更新」获取最新版本。</div>
    </div>
  </div>

  <div class="rounded-2xl border border-[#0c1426]/10 bg-white p-5">
    <p class="text-sm font-semibold text-tech-ink">更新方式</p>
    <div class="mt-3 space-y-2 text-sm">
      <label class="flex items-center gap-2"><input type="radio" name="upd-mode" value="preserve" checked> <span>保留数据（推荐）—— 仅更新代码，不动 data/ 与 uploads/</span></label>
      <?php if ($allowFull): ?><label class="flex items-center gap-2"><input type="radio" name="upd-mode" value="full"> <span>全量覆盖 —— 同时覆盖 data/content.json 与 uploads/（会丢失线上编辑）</span></label><?php endif; ?>
    </div>
    <p class="mt-3 text-xs text-tech-muted">未配置仓库（BG_GITHUB_REPO 为空）时，「检查更新」会提示不可用。配置方法见部署包内 PUBLISH.md。</p>
    <div class="mt-4 flex flex-wrap gap-3">
      <button type="button" data-check class="rounded-lg bg-tech-blue px-4 py-2 text-sm font-medium text-white transition-opacity hover:opacity-90">检查更新</button>
      <button type="button" data-do class="rounded-lg bg-brand px-4 py-2 text-sm font-medium text-brand-foreground transition-opacity hover:opacity-90">立即更新</button>
    </div>
    <div id="update-log" class="mt-4 hidden rounded-md border px-3 py-2 text-sm"></div>
  </div>

  <div class="rounded-2xl border border-dashed border-[#0c1426]/15 bg-white/60 p-5 text-xs leading-relaxed text-tech-muted">
    说明：本功能调用 <code>api.github.com/repos/&lt;repo&gt;/releases/latest</code> 获取最新发布包 <code>beigang-php-deploy.zip</code> 并解压覆盖。需要服务器开启 <code>ZipArchive</code> 扩展、PHP 进程对站点目录可写、且主机能访问 GitHub（或已配置自定义镜像接口）。所有更新操作均记录于 <code>data/backups/</code>，可随时回滚。
  </div>

  <div class="rounded-2xl border border-[#0c1426]/10 bg-white p-5">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-sm font-semibold text-tech-ink">历史版本与修改说明</p>
        <p class="mt-1 text-xs text-tech-muted">点击「检查更新」加载全部发布版本及其修改信息，可「更新到该版本」或「回滚」到旧版本。</p>
      </div>
      <button type="button" data-check class="shrink-0 rounded-lg border border-[#0c1426]/15 px-3 py-1.5 text-sm text-tech-ink transition-colors hover:bg-[#0c1426]/[0.04]">刷新</button>
    </div>
    <div id="update-releases" class="mt-4 space-y-3 text-sm text-tech-muted">尚未加载，点击「检查更新」。</div>
  </div>
</div>
<?php
bg_admin_frame_end();
