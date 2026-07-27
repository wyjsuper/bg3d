/* 北港3D PHP 版 —— 原生客户端交互（替代 React 组件） */
(function () {
  'use strict';
  var BG_BASE = window.BG_BASE || '';

  /* ===== 1. Reveal 滚动显现 ===== */
  function initReveal() {
    var els = document.querySelectorAll('.reveal');
    if (!els.length) return;
    if (!('IntersectionObserver' in window)) {
      els.forEach(function (el) { el.classList.add('is-visible'); });
      return;
    }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });
    els.forEach(function (el) { io.observe(el); });
  }

  /* ===== 2. 数字滚动统计 ===== */
  function animateStat(el) {
    var target = parseInt(el.getAttribute('data-count-value'), 10) || 0;
    var numEl = el.querySelector('.stat-num');
    if (!numEl) return;
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduce) { numEl.textContent = String(target); return; }
    var duration = 1400, start = performance.now();
    function tick(now) {
      var p = Math.min(1, (now - start) / duration);
      var eased = 1 - Math.pow(1 - p, 3);
      numEl.textContent = String(Math.round(eased * target));
      if (p < 1) requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
  }
  function initStats() {
    var stats = document.querySelectorAll('[data-stat]');
    if (!stats.length) return;
    if (!('IntersectionObserver' in window)) {
      stats.forEach(animateStat);
      return;
    }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          animateStat(entry.target);
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.4 });
    stats.forEach(function (el) { io.observe(el); });
  }

  /* ===== 3. 视频卡片状态（骨架/错误/播放按钮） ===== */
  function initVideoCards() {
    document.querySelectorAll('[data-video]').forEach(function (card) {
      var media = card.querySelector('.video-media');
      var skeleton = card.querySelector('.video-skeleton');
      var errorBox = card.querySelector('.video-error');
      var playBtn = card.querySelector('.video-play-btn');
      if (!media) { if (skeleton) skeleton.style.display = 'none'; return; }

      function hideSkeleton() { if (skeleton) skeleton.style.display = 'none'; }
      function showError() {
        hideSkeleton();
        if (errorBox) { errorBox.classList.remove('hidden'); errorBox.style.display = 'flex'; }
        if (playBtn) playBtn.classList.add('hidden');
      }

      var tag = media.tagName.toLowerCase();
      if (tag === 'img') {
        var imgTimer = null;
        function clearImgTimer() { if (imgTimer) { clearTimeout(imgTimer); imgTimer = null; } }
        function fallbackToPosterOrError() {
          var poster = media.getAttribute('data-poster');
          if (poster && media.src !== poster) {
            media.src = poster;
            media.addEventListener('load', hideSkeleton);
          } else {
            showError();
            card.classList.add('has-error');
          }
        }
        if (media.complete && media.naturalWidth > 0) {
          hideSkeleton();
        } else {
          media.addEventListener('load', function () { clearImgTimer(); hideSkeleton(); });
          media.addEventListener('error', function () { clearImgTimer(); fallbackToPosterOrError(); });
          // 移动端大图易卡在加载中（无 error 也无 load）→ 超时降级到 poster
          imgTimer = setTimeout(function () {
            if (!media.complete || media.naturalWidth === 0) fallbackToPosterOrError();
          }, 5000);
        }
      } else if (tag === 'video') {
        media.addEventListener('loadeddata', hideSkeleton);
        media.addEventListener('canplay', hideSkeleton);
        media.addEventListener('error', showError);
        // 自动播放被浏览器阻止 → 显示播放按钮
        var tryPlay = media.play();
        if (tryPlay && typeof tryPlay.then === 'function') {
          tryPlay.catch(function () {
            hideSkeleton();
            if (playBtn) { playBtn.classList.remove('hidden'); playBtn.style.display = 'flex'; }
          });
        }
        if (playBtn) {
          playBtn.addEventListener('click', function () {
            media.play();
            playBtn.classList.add('hidden');
            playBtn.style.display = 'none';
          });
        }
        // 兜底：若 3s 内仍未触发 loadeddata 隐藏骨架
        setTimeout(hideSkeleton, 3000);
      }
    });
  }

  /* ===== 4. 移动端导航 ===== */
  function initMobileNav() {
    var openBtn = document.querySelector('.mobile-nav-open');
    var overlay = document.querySelector('.mobile-nav-overlay');
    if (!openBtn || !overlay) return;
    function open() { overlay.classList.remove('hidden'); document.body.style.overflow = 'hidden'; }
    function close() { overlay.classList.add('hidden'); document.body.style.overflow = ''; }
    openBtn.addEventListener('click', open);
    overlay.querySelectorAll('[data-mobile-nav-close]').forEach(function (el) {
      el.addEventListener('click', close);
    });
  }

  /* ===== 5. 语言切换 ===== */
  function initLangToggle() {
    document.querySelectorAll('[data-lang-toggle]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var current = /(?:^|; )lang=en/.test(document.cookie) ? 'en' : 'zh';
        var next = current === 'en' ? 'zh' : 'en';
        var oneYear = 60 * 60 * 24 * 365;
        document.cookie = 'lang=' + next + '; path=/; max-age=' + oneYear;
        window.location.reload();
      });
    });
    // 按钮文案：EN 模式显示「中」，ZH 模式显示 EN
    var isEn = /(?:^|; )lang=en/.test(document.cookie);
    document.querySelectorAll('[data-lang-toggle]').forEach(function (btn) {
      btn.textContent = isEn ? '中' : 'EN';
    });
  }

  /* ===== 6. 分类筛选（案例 / 干货） ===== */
  function initFilters() {
    document.querySelectorAll('[data-filter-root]').forEach(function (root) {
      var btns = root.querySelectorAll('[data-filter-btn]');
      var items = root.querySelectorAll('[data-filter-item]');
      var allLabel = btns.length ? btns[0].getAttribute('data-cat') : '';
      var activeCls = ['bg-brand', 'text-brand-foreground'];
      var idleCls = ['border', 'border-border', 'text-muted-foreground', 'hover:bg-accent'];
      function setActive(target) {
        btns.forEach(function (b) {
          var on = b === target;
          activeCls.forEach(function (c) { b.classList.toggle(c, on); });
          idleCls.forEach(function (c) { b.classList.toggle(c, !on); });
        });
      }
      btns.forEach(function (btn) {
        btn.addEventListener('click', function () {
          var cat = btn.getAttribute('data-cat');
          setActive(btn);
          items.forEach(function (item) {
            var show = (cat === allLabel) || (item.getAttribute('data-cat') === cat);
            item.classList.toggle('hidden', !show);
          });
        });
      });
    });
  }

  /* ===== 7. 首页案例「加载更多」 ===== */
  function initCaseMore() {
    var grid = document.querySelector('[data-case-grid]');
    if (!grid) return;
    var btn = grid.querySelector('[data-case-more]');
    if (!btn) return;
    var step = 3;
    btn.addEventListener('click', function () {
      var hidden = grid.querySelectorAll('[data-case-item].hidden');
      for (var i = 0; i < step && i < hidden.length; i++) {
        hidden[i].classList.remove('hidden');
      }
      if (grid.querySelectorAll('[data-case-item].hidden').length === 0) {
        var wrap = grid.querySelector('[data-case-more-wrap]');
        if (wrap) wrap.style.display = 'none';
      }
    });
  }

  /* ===== 8. 联系表单 AJAX 提交 ===== */
  function initContactForm() {
    var root = document.querySelector('[data-contact]');
    if (!root) return;
    var form = root.querySelector('[data-contact-form]');
    var success = root.querySelector('[data-contact-success]');
    var errorEl = root.querySelector('[data-contact-error]');
    var submitBtn = root.querySelector('[data-contact-submit]');
    var btnText = root.querySelector('[data-contact-btn-text]');
    var resetBtn = root.querySelector('[data-contact-reset]');
    if (!form) return;

    var msgSubmitting = form.getAttribute('data-msg-submitting') || 'Submitting…';
    var msgDefault = form.getAttribute('data-msg-default') || 'Submit';
    var msgError = form.getAttribute('data-msg-error') || 'Submission failed';

    function showError(text) {
      if (!errorEl) return;
      errorEl.textContent = text;
      errorEl.classList.remove('hidden');
    }
    function clearError() { if (errorEl) errorEl.classList.add('hidden'); }

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      clearError();
      submitBtn.disabled = true;
      if (btnText) btnText.textContent = msgSubmitting;

      var payload = {
        name: form.name.value.trim(),
        phone: form.phone.value.trim(),
        message: form.message.value.trim()
      };

      fetch(BG_BASE + '/api/inquiry.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      }).then(function (res) {
        if (!res.ok) return res.json().catch(function () { return {}; }).then(function (d) { throw new Error(d.error || msgError); });
        return res.json();
      }).then(function () {
        form.classList.add('hidden');
        if (success) { success.classList.remove('hidden'); success.style.display = 'flex'; }
        form.reset();
      }).catch(function (err) {
        showError(err && err.message ? err.message : msgError);
      }).finally(function () {
        submitBtn.disabled = false;
        if (btnText) btnText.textContent = msgDefault;
      });
    });

    if (resetBtn) {
      resetBtn.addEventListener('click', function () {
        if (success) { success.classList.add('hidden'); success.style.display = ''; }
        form.classList.remove('hidden');
      });
    }
  }

  /* ===== 启动 ===== */
  function init() {
    initReveal();
    initStats();
    initVideoCards();
    initMobileNav();
    initLangToggle();
    initFilters();
    initCaseMore();
    initContactForm();
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
