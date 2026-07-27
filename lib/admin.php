<?php
/**
 * 后台通用框架（对应 React 版 admin-frame.tsx）
 * 提供：admin 头部、侧栏、顶栏、修改密码弹窗、页脚脚本
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/schema.php';
require_once __DIR__ . '/auth.php';

// 侧栏「站点设置」分组
$BG_SETTING_TYPES = array('contact', 'companyOverview', 'heroSlogans', 'site');

// ===== 后台图标（内联 SVG，替代 lucide-react） =====
function bg_admin_icon($name, $size = 18) {
  $paths = array(
    'layout-dashboard' => '<rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/>',
    'navigation' => '<polygon points="3 11 22 2 13 21 11 13 3 11"/>',
    'images' => '<path d="M18 22H4a2 2 0 0 1-2-2V6"/><path d="m22 13-1.296-1.296a2.41 2.41 0 0 0-3.408 0L11 18"/><circle cx="12" cy="8" r="2"/><rect width="16" height="16" x="6" y="2" rx="2"/>',
    'film' => '<rect width="18" height="18" x="3" y="3" rx="2"/><path d="M7 3v18"/><path d="M3 7.5h4"/><path d="M3 12h18"/><path d="M3 16.5h4"/><path d="M17 3v18"/><path d="M17 7.5h4"/><path d="M17 16.5h4"/>',
    'bar-chart-3' => '<path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/>',
    'megaphone' => '<path d="m3 11 18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/>',
    'package' => '<path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>',
    'file-text' => '<path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/>',
    'map-pin' => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
    'columns-3' => '<rect width="18" height="18" x="3" y="3" rx="2"/><path d="M9 3v18"/><path d="M15 3v18"/>',
    'phone' => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>',
    'building-2' => '<path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/>',
    'quote' => '<path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V20c0 1 0 1 1 1z"/><path d="M15 21c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2h.75c0 2.25.25 4-2.75 4v3c0 1 0 1 1 1z"/>',
    'settings' => '<path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/>',
    'log-out' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/>',
    'menu' => '<line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="18" y2="18"/>',
    'x' => '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
    'external-link' => '<path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>',
    'key-round' => '<path d="M2.586 17.414A2 2 0 0 0 2 18.828V21a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1v-1a1 1 0 0 1 1-1h1a1 1 0 0 0 1-1v-1a1 1 0 0 1 1-1h.172a2 2 0 0 0 1.414-.586l.814-.814a6.5 6.5 0 1 0-4-4z"/><circle cx="16.5" cy="7.5" r=".5" fill="currentColor"/>',
    'eye' => '<path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0z"/><circle cx="12" cy="12" r="3"/>',
    'eye-off' => '<path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49"/><path d="M14.084 14.158a3 3 0 0 1-4.242-4.242"/><path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-5.143"/><path d="m2 2 20 20"/>',
    'arrow-right' => '<path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>',
    'refresh' => '<path d="M21 12a9 9 0 1 1-2.64-6.36"/><path d="M21 3v6h-6"/>',
  );
  $p = isset($paths[$name]) ? $paths[$name] : '';
  return '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $p . '</svg>';
}

/** type → 图标名 映射 */
function bg_admin_type_icon($type) {
  $map = array(
    'nav' => 'navigation', 'cases' => 'images', 'threeds' => 'film', 'stats' => 'bar-chart-3',
    'plans' => 'megaphone', 'services' => 'package', 'articles' => 'file-text', 'cities' => 'map-pin',
    'footerColumns' => 'columns-3', 'contact' => 'phone', 'companyOverview' => 'building-2',
    'heroSlogans' => 'quote', 'site' => 'settings',
  );
  return isset($map[$type]) ? $map[$type] : 'file-text';
}

/** 后台 HTML 头 */
function bg_admin_head($title) {
  echo '<!DOCTYPE html>' . "\n<html lang=\"zh-CN\">\n<head>\n";
  echo '<meta charset="utf-8">' . "\n";
  echo '<meta name="viewport" content="width=device-width, initial-scale=1">' . "\n";
  echo '<meta name="robots" content="noindex, nofollow">' . "\n";
  echo '<title>' . h($title) . ' · 北港3D 后台</title>' . "\n";
  echo '<script>window.BG_BASE = ' . json_encode(defined('BG_BASE') ? BG_BASE : '') . ';</script>' . "\n";
  echo '<link rel="stylesheet" href="' . bg_url('/assets/css/style.css') . '">' . "\n";
  echo '</head>' . "\n";
  echo '<body class="bg-[#f4f7fc] font-sans text-foreground antialiased">' . "\n";
}

/** 侧栏内容（桌面 / 移动共用） */
function bg_admin_sidebar($activeType) {
  global $BG_COLLECTIONS, $BG_SETTING_TYPES;
  $isDash = ($activeType === '__dashboard__');
  $isActive = function ($type) use ($activeType) { return $activeType === $type; };

  echo '<div class="flex h-full flex-col bg-[#0c1426] text-slate-100">';
  // 品牌
  echo '<div class="flex items-center gap-2.5 border-b border-white/10 px-5 py-4">';
  echo '<span class="inline-flex items-center rounded-md bg-white/95 px-2 py-1"><img src="' . bg_url('/logo-removebg.png') . '" alt="Logo" class="h-9 w-auto"></span>';
  echo '<div class="leading-tight"><p class="text-sm font-semibold text-white">北港3D</p><p class="text-[11px] text-slate-400">内容管理后台</p></div>';
  echo '</div>';

  echo '<nav class="flex-1 space-y-6 overflow-y-auto px-3 py-5">';
  // 工作台
  $dashCls = $isDash ? 'bg-white/10 text-white' : 'text-slate-300 hover:bg-white/5 hover:text-white';
  echo '<a href="' . bg_url('/admin') . '" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors ' . $dashCls . '">' . bg_admin_icon('layout-dashboard') . '工作台</a>';

  // 内容管理
  echo '<div><p class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-500">内容管理</p><div class="space-y-1">';
  foreach ($BG_COLLECTIONS as $c) {
    if (!empty($c['adminHidden'])) continue;
    if (in_array($c['type'], $BG_SETTING_TYPES, true)) continue;
    bg_admin_side_link($c, $isActive($c['type']));
  }
  echo '</div></div>';

  // 站点设置
  echo '<div><p class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-500">站点设置</p><div class="space-y-1">';
  foreach ($BG_COLLECTIONS as $c) {
    if (!in_array($c['type'], $BG_SETTING_TYPES, true)) continue;
    bg_admin_side_link($c, $isActive($c['type']));
  }
  echo '</div></div>';

  // 系统
  echo '<div><p class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-500">系统</p><div class="space-y-1">';
  $updCls = $activeType === '__update__' ? 'bg-gradient-to-r from-tech-blue to-tech-cyan font-medium text-white shadow-lg shadow-tech-blue/20' : 'text-slate-300 hover:bg-white/5 hover:text-white';
  echo '<a href="' . bg_url('/admin/update') . '" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-colors ' . $updCls . '">' . bg_admin_icon('refresh', 18) . '<span class="truncate">在线更新</span></a>';
  echo '</div></div>';

  echo '</nav>';

  // 底部操作
  echo '<div class="border-t border-white/10 p-3">';
  echo '<button type="button" data-pw-open class="mb-1 flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-300 transition-colors hover:bg-white/5 hover:text-white">' . bg_admin_icon('key-round') . '修改密码</button>';
  echo '<button type="button" data-logout class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-300 transition-colors hover:bg-red-500/15 hover:text-red-300">' . bg_admin_icon('log-out') . '退出登录</button>';
  echo '</div>';
  echo '</div>';
}

function bg_admin_side_link($c, $active) {
  $cls = $active
    ? 'bg-gradient-to-r from-tech-blue to-tech-cyan font-medium text-white shadow-lg shadow-tech-blue/20'
    : 'text-slate-300 hover:bg-white/5 hover:text-white';
  echo '<a href="' . bg_url('/admin/content/' . h($c['type'])) . '" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-colors ' . $cls . '">'
    . bg_admin_icon(bg_admin_type_icon($c['type'])) . '<span class="truncate">' . h($c['label']) . '</span></a>';
}

/** 框架开始：侧栏 + 顶栏，返回后在 main 内输出页面内容 */
function bg_admin_frame_start($title, $activeType) {
  echo '<div class="min-h-screen bg-[#f4f7fc]">';
  // 桌面侧栏
  echo '<aside class="fixed inset-y-0 left-0 z-40 hidden w-64 md:block">';
  bg_admin_sidebar($activeType);
  echo '</aside>';
  // 移动抽屉
  echo '<div class="mobile-drawer fixed inset-0 z-50 hidden md:hidden">';
  echo '<div class="absolute inset-0 bg-black/40 backdrop-blur-sm" data-drawer-close></div>';
  echo '<div class="absolute left-0 top-0 h-full w-[80%] max-w-xs">';
  bg_admin_sidebar($activeType);
  echo '</div>';
  echo '<button type="button" data-drawer-close aria-label="关闭菜单" class="absolute right-3 top-3 inline-flex h-9 w-9 items-center justify-center rounded-lg bg-white/90 text-tech-ink">' . bg_admin_icon('x', 20) . '</button>';
  echo '</div>';
  // 右侧
  echo '<div class="flex min-h-screen flex-col md:pl-64">';
  echo '<header class="sticky top-0 z-30 flex items-center justify-between border-b border-[#0c1426]/10 bg-white/85 px-4 py-3 backdrop-blur md:px-8">';
  echo '<div class="flex items-center gap-3">';
  echo '<button type="button" data-drawer-open aria-label="打开菜单" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-[#0c1426]/10 text-tech-ink md:hidden">' . bg_admin_icon('menu', 20) . '</button>';
  echo '<h1 class="text-base font-semibold text-tech-ink md:text-lg">' . h($title) . '</h1>';
  echo '</div>';
  echo '<a href="' . bg_url('/') . '" target="_blank" class="inline-flex items-center gap-1.5 rounded-lg border border-[#0c1426]/10 px-3 py-1.5 text-sm text-tech-muted transition-colors hover:bg-[#0c1426]/[0.04] hover:text-tech-ink">查看前台 ' . bg_admin_icon('external-link', 14) . '</a>';
  echo '</header>';
  echo '<main class="flex-1 p-4 md:p-8">';
}

/** 框架结束：关闭 main/右侧/容器，输出修改密码弹窗 + 后台 JS */
function bg_admin_frame_end() {
  echo '</main></div>'; // main + 右侧
  // 修改密码弹窗（默认隐藏）
  echo '<div class="pw-modal fixed inset-0 z-[60] hidden items-center justify-center p-4">';
  echo '<div class="absolute inset-0 bg-black/40 backdrop-blur-sm" data-pw-close></div>';
  echo '<div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">';
  // 成功态
  echo '<div class="pw-success hidden text-center">';
  echo '<div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-green-100"><svg class="h-7 w-7 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></div>';
  echo '<h3 class="text-lg font-bold text-tech-ink">密码修改成功</h3><p class="mt-2 text-sm text-tech-muted">下次登录请使用新密码</p>';
  echo '<button type="button" data-pw-close class="mt-6 w-full rounded-lg bg-brand px-4 py-2.5 text-sm font-medium text-brand-foreground transition-opacity hover:opacity-90">知道了</button>';
  echo '</div>';
  // 表单态
  echo '<div class="pw-form-wrap">';
  echo '<div class="mb-5 flex items-center gap-3"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-tech-blue/10 text-tech-blue">' . bg_admin_icon('key-round', 20) . '</span><div><h3 class="text-lg font-bold text-tech-ink">修改密码</h3><p class="text-xs text-tech-muted">修改后下次登录使用新密码</p></div></div>';
  echo '<p class="pw-error mb-4 hidden rounded-md bg-red-50 px-3 py-2 text-sm text-red-600"></p>';
  echo '<form data-pw-form class="space-y-4">';
  echo '<div><label class="text-sm font-medium text-tech-ink">当前密码</label><div class="relative mt-1.5"><input type="password" data-pw-cur autocomplete="current-password" class="w-full rounded-lg border border-input bg-background px-3 py-2.5 pr-10 text-sm outline-none focus:border-brand" placeholder="输入当前密码"><button type="button" data-pw-toggle="cur" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-tech-muted hover:text-tech-ink">' . bg_admin_icon('eye', 16) . '</button></div></div>';
  echo '<div><label class="text-sm font-medium text-tech-ink">新密码</label><div class="relative mt-1.5"><input type="password" data-pw-new autocomplete="new-password" class="w-full rounded-lg border border-input bg-background px-3 py-2.5 pr-10 text-sm outline-none focus:border-brand" placeholder="至少 6 位"><button type="button" data-pw-toggle="new" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-tech-muted hover:text-tech-ink">' . bg_admin_icon('eye', 16) . '</button></div></div>';
  echo '<div><label class="text-sm font-medium text-tech-ink">确认新密码</label><input type="password" data-pw-confirm autocomplete="new-password" class="mt-1.5 w-full rounded-lg border border-input bg-background px-3 py-2.5 text-sm outline-none focus:border-brand" placeholder="再次输入新密码"></div>';
  echo '<div class="flex gap-3 pt-2"><button type="button" data-pw-close class="flex-1 rounded-lg border border-input px-4 py-2.5 text-sm font-medium text-tech-muted transition-colors hover:bg-gray-50">取消</button><button type="submit" data-pw-submit class="flex-1 rounded-lg bg-brand px-4 py-2.5 text-sm font-medium text-brand-foreground transition-opacity hover:opacity-90 disabled:opacity-60">确认修改</button></div>';
  echo '</form></div>';
  echo '</div></div>';
  echo '</div>'; // 容器
  echo '<script src="' . bg_url('/assets/js/admin.js') . '"></script>' . "\n";
  echo '</body></html>' . "\n";
}
