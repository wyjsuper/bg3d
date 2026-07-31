<?php
/** 后台登录页（对应 admin/login/page.tsx） */
require_once __DIR__ . '/../lib/config.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/auth.php';

// 已登录则直接进工作台
if (bg_current_user()) {
  header('Location: ' . bg_url('/admin'));
  exit;
}
?><!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>后台登录 · 北港3D</title>
<script>window.BG_BASE = <?php echo json_encode(defined('BG_BASE') ? BG_BASE : ''); ?>;</script>
<link rel="stylesheet" href="<?php echo bg_url('/assets/css/style.css'); ?>">
</head>
<body class="font-sans text-foreground antialiased">
<div class="flex min-h-screen">
  <!-- 品牌区 -->
  <div class="relative hidden w-1/2 flex-col justify-between overflow-hidden bg-gradient-to-br from-tech-blue via-[#2f6bff] to-tech-cyan p-12 text-white lg:flex">
    <span class="inline-flex w-fit items-center rounded-md bg-white/95 px-2.5 py-1.5"><img src="<?php echo bg_url('/logo-removebg.png'); ?>" alt="Logo" class="h-12 w-auto"></span>
    <div>
      <p class="text-sm font-medium uppercase tracking-widest text-white/80">ADMIN CONSOLE</p>
      <h1 class="mt-3 text-4xl font-bold leading-tight">北港3D数字营销设计<br>管理后台</h1>
      <p class="mt-4 max-w-md text-white/70">品牌内容、案例、方案与营销数据的统一管理中心。请使用管理员账号登录。</p>
    </div>
    <p class="text-xs text-white/60">Copyright © 2003-2026 北港3D数字营销设计 · 苏ICP备10011650号-5</p>
  </div>

  <!-- 登录表单区 -->
  <div class="flex w-full flex-col items-center justify-center bg-background px-6 lg:w-1/2">
    <div class="glass relative z-10 w-full max-w-sm p-8">
      <div class="mb-8 lg:hidden"><span class="inline-flex items-center rounded-md bg-white px-2.5 py-1.5 shadow"><img src="<?php echo bg_url('/logo-removebg.png'); ?>" alt="Logo" class="h-12 w-auto"></span></div>

      <h2 class="text-2xl font-bold text-tech-ink">后台登录</h2>
      <p class="mt-2 text-sm text-tech-muted">请输入您的管理员凭据以继续</p>

      <form data-login-form class="mt-8 space-y-5">
        <div>
          <label class="text-sm font-medium text-tech-ink">账号</label>
          <input data-username autocomplete="username" class="liquid-input mt-1.5" placeholder="管理员账号">
        </div>
        <div>
          <label class="text-sm font-medium text-tech-ink">密码</label>
          <input type="password" data-password autocomplete="current-password" class="liquid-input mt-1.5" placeholder="登录密码">
        </div>
        <div>
          <label class="text-sm font-medium text-tech-ink">验证码</label>
          <div class="mt-1.5 flex gap-3">
            <input data-code class="liquid-input uppercase tracking-widest" placeholder="请输入右侧验证码">
            <button type="button" data-captcha title="换一张" class="select-none rounded-md border border-[#0c1426]/15 bg-white/60 px-4 text-lg font-bold tracking-[0.3em] text-tech-cyan transition-colors hover:bg-white/80"><?php echo ''; ?></button>
          </div>
        </div>

        <div class="flex items-center justify-between text-sm">
          <label class="flex items-center gap-2 text-tech-muted"><input type="checkbox" class="h-4 w-4 accent-[var(--brand)]">记住我</label>
          <a href="#" class="text-tech-cyan hover:underline">忘记密码？</a>
        </div>

        <p data-error class="hidden rounded-md bg-red-500/10 px-3 py-2 text-sm text-red-600"></p>

        <button type="submit" data-submit class="liquid-btn w-full px-6 py-3 text-sm">登录后台</button>
      </form>

      <p class="mt-6 text-center text-xs text-tech-muted">返回 <a href="<?php echo bg_url('/'); ?>" class="text-tech-cyan hover:underline">网站首页</a></p>
    </div>
  </div>
</div>

<script>
(function () {
  var BG_BASE = window.BG_BASE || '';
  var CHARS = "ABCDEFGHJKLMNPQRSTUVWXYZ23456789";
  function makeCode() { var s = ""; for (var i = 0; i < 4; i++) s += CHARS[Math.floor(Math.random() * CHARS.length)]; return s; }
  var captcha = makeCode();
  var btn = document.querySelector("[data-captcha]");
  var codeInput = document.querySelector("[data-code]");
  var errEl = document.querySelector("[data-error]");
  var submitBtn = document.querySelector("[data-submit]");
  function renderCaptcha() { btn.textContent = captcha; }
  renderCaptcha();
  btn.addEventListener("click", function () { captcha = makeCode(); renderCaptcha(); });

  function showError(msg) { errEl.textContent = msg; errEl.classList.remove("hidden"); }
  function clearError() { errEl.textContent = ""; errEl.classList.add("hidden"); }

  document.querySelector("[data-login-form]").addEventListener("submit", function (e) {
    e.preventDefault();
    clearError();
    var username = document.querySelector("[data-username]").value.trim();
    var password = document.querySelector("[data-password]").value.trim();
    var code = codeInput.value.trim().toUpperCase();
    if (!username || !password) { showError("请输入账号和密码"); return; }
    if (code !== captcha) { showError("验证码不正确"); captcha = makeCode(); renderCaptcha(); codeInput.value = ""; return; }

    submitBtn.disabled = true;
    submitBtn.textContent = "登录中…";
    fetch(BG_BASE + "/api/login.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ username: username, password: password })
    }).then(function (res) {
      if (!res.ok) {
        return res.json().catch(function () { return {}; }).then(function (data) {
          showError(data.error || "登录失败");
          submitBtn.disabled = false;
          submitBtn.textContent = "登录后台";
        });
      }
      window.location.href = BG_BASE + "/admin/index.php";
    }).catch(function () {
      showError("网络异常，请重试");
      submitBtn.disabled = false;
      submitBtn.textContent = "登录后台";
    });
  });
})();
</script>
</body>
</html>
