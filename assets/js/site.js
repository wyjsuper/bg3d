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

  /* ===== 3. 视频卡片自动播放（懒加载视频源） =====
     说明：网格直接渲染 <video>（poster=轻量封面占位），进入视口才赋值 src 并自动循环播放，
     复用原始 mp4；点击卡片才在 play.php 打开高清 mp4 全屏播放。 */
  function initVideoCards() {
    // 并发受限 + 优先当前最可见视频的加载队列（解决移动端滚动时多视频同时下载抢占带宽）
    var isMobileV = window.matchMedia('(max-width: 768px)').matches;
    var MAX_CONCURRENT = isMobileV ? 2 : 4;
    var registry = [];          // 每个视频卡片的运行时句柄
    var visibleMap = new Map(); // card -> 可见比例（用于排序优先级）
    var activeLoads = 0;

    document.querySelectorAll('[data-video]').forEach(function (card) {
      var media = card.querySelector('.video-media');
      var skeleton = card.querySelector('.video-skeleton');
      var errorBox = card.querySelector('.video-error');

      // 点击卡片 → 新窗口放大播放（data-play-url 由后端仅在视频项上输出）
      if (card.hasAttribute('data-play-url')) {
        card.addEventListener('click', function () {
          var url = card.getAttribute('data-play-url');
          if (url) window.open(url, '_blank', 'noopener');
        });
      }

      // 视频卡片（<video>）与纯图片卡片分开处理
      if (!media || media.tagName.toLowerCase() !== 'video') {
        if (skeleton) skeleton.style.display = 'none';
        return;
      }

      function hideSkeleton() { if (skeleton) skeleton.style.display = 'none'; }
      function showError() {
        hideSkeleton();
        if (errorBox) { errorBox.classList.remove('hidden'); errorBox.style.display = 'flex'; }
        card.classList.add('has-error');
      }

      // poster 已显示封面占位，骨架无必要，直接隐藏
      hideSkeleton();

      var previewUrl = media.getAttribute('data-src');
      if (!previewUrl) return;

      var item = {
        card: card, media: media, url: previewUrl,
        hideSkeleton: hideSkeleton, showError: showError,
        started: false, busy: false
      };
      registry.push(item);

      // 进入视口（提前 200px 预载）：登记可见度并请求加载；滚出视口暂停（省电省流量）。
      if ('IntersectionObserver' in window) {
        var io = new IntersectionObserver(function (entries) {
          entries.forEach(function (entry) {
            if (entry.isIntersecting) {
              visibleMap.set(card, entry.intersectionRatio || 1);
              if (item.started) {
                var rp = media.play && media.play();
                if (rp && rp.catch) rp.catch(function () {});
              } else {
                pump();
              }
            } else {
              visibleMap.delete(card);
              if (media.dataset.loaded) media.pause();
            }
          });
        }, { rootMargin: '200px 0px' });
        io.observe(media);
      } else {
        visibleMap.set(card, 1);
        pump();
      }
    });

    // 启动单个视频加载（受 MAX_CONCURRENT 限制）
    function startLoad(it) {
      if (it.started) return;
      it.started = true;
      var m = it.media;
      m.dataset.loaded = '1';
      m.muted = true; m.defaultMuted = true; m.playsInline = true;
      m.setAttribute('muted', '');
      m.setAttribute('playsinline', '');
      m.src = it.url;
      m.load();
      var p = m.play && m.play();
      if (p && p.catch) p.catch(function () {});
      var done = function () { activeLoads--; it.busy = false; pump(); };
      m.addEventListener('loadeddata', function () { it.hideSkeleton(); done(); }, { once: true });
      m.addEventListener('error', function () { m.style.display = 'none'; it.showError(); done(); }, { once: true });
    }

    // 从可见列表中挑「可见度最高且未开始」的视频加载，最多同时 MAX_CONCURRENT 个
    function pump() {
      if (activeLoads >= MAX_CONCURRENT) return;
      var best = null, bestRatio = -1;
      registry.forEach(function (it) {
        if (it.started || it.busy) return;
        var r = visibleMap.has(it.card) ? visibleMap.get(it.card) : -1;
        if (r > bestRatio) { bestRatio = r; best = it; }
      });
      if (best) { activeLoads++; best.busy = true; startLoad(best); pump(); }
    }

    // 移动端兜底：iOS / 微信等 WebView 会拦截「无用户手势」的自动播放（即便 muted）。
    // 首次交互（触摸 / 点击 / 滚动）后补播所有已加载却仍暂停的视频。
    if (!window.__bgVideoFallbackBound) {
      window.__bgVideoFallbackBound = true;
      var kick = function () {
        registry.forEach(function (it) {
          if (it.media.dataset.loaded && it.media.paused) {
            it.media.muted = true; it.media.defaultMuted = true; it.media.playsInline = true;
            var pp = it.media.play && it.media.play();
            if (pp && pp.catch) pp.catch(function () {});
          }
        });
      };
      window.addEventListener('scroll', kick, { once: true, passive: true });
      document.addEventListener('touchstart', kick, { once: true, passive: true });
      document.addEventListener('click', kick, { once: true });
    }
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

  /* ===== 8. 移动端玻璃卡片光泽扫光（滚动进入视口 / 点击触发） ===== */
  function initGlassShine() {
    var cards = document.querySelectorAll('.glow-card.glass');
    if (!cards.length) return;

    function trigger(el) {
      if (el.classList.contains('shine')) return;
      el.classList.add('shine');
      el.addEventListener('animationend', function onEnd() {
        el.classList.remove('shine');
        el.removeEventListener('animationend', onEnd);
      });
    }

    // 点击/触摸时扫一次
    cards.forEach(function (card) {
      card.addEventListener('click', function () { trigger(card); });
    });

    // 滚动进入视口时扫一次（只在支持 IntersectionObserver 且非 hover 设备上自动触发）
    if (!('IntersectionObserver' in window)) return;
    var hoverFine = window.matchMedia && window.matchMedia('(hover: hover) and (pointer: fine)').matches;
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          trigger(entry.target);
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.35, rootMargin: '0px 0px -10% 0px' });
    cards.forEach(function (card) {
      if (!hoverFine) io.observe(card);
    });
  }

  /* ===== 9. 联系表单 AJAX 提交 ===== */
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
    initGlassShine();
    initContactForm();
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
