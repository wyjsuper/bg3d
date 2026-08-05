<?php
/**
 * 视频放大播放页（点击首页/3D 页的视频卡片在新窗口打开）。
 * 用法：/play.php?src=/videos/xxx.mp4
 */
require_once __DIR__ . '/lib/config.php';
require_once __DIR__ . '/lib/lang.php';
require_once __DIR__ . '/lib/ui-text.php';
require_once __DIR__ . '/lib/render.php';

$lang = bg_get_lang();
$siteName = ($lang === 'en') ? BG_SITE_NAME_EN : BG_SITE_NAME_ZH;

// 安全：只允许站内路径（/ 开头），外部 URL 一律拒绝
$raw = isset($_GET['src']) ? (string) $_GET['src'] : '';
$src = '';
if (bg_starts_with($raw, '/')) {
  $src = bg_url($raw);
}
$title = isset($_GET['title']) ? (string) $_GET['title'] : '';
$start = isset($_GET['start']) ? (float) $_GET['start'] : 0;
$isGif = (bool) preg_match('/\.gif(\?.*)?$/i', $src);
?>
<!DOCTYPE html>
<html lang="<?php echo h($lang); ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo h(bg_pick(array('zh' => '视频播放', 'en' => 'Video'), $lang)); ?> · <?php echo h($siteName); ?></title>
  <link rel="stylesheet" href="<?php echo bg_url('/assets/css/style.css'); ?>">
  <style>
    html, body { height: 100%; }
    body {
      margin: 0;
      background: radial-gradient(120% 120% at 50% 0%, #142042 0%, #0a0f1f 60%, #070b16 100%);
      color: #fff;
      font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "PingFang SC", "Microsoft YaHei", sans-serif;
    }
    .player-wrap {
      min-height: 100%;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 24px;
      box-sizing: border-box;
      gap: 18px;
    }
    .player-frame {
      width: 100%;
      max-width: 960px;
      border-radius: 18px;
      overflow: hidden;
      box-shadow: 0 30px 80px -20px rgba(0,0,0,.7), 0 0 0 1px rgba(255,255,255,.08);
      background: #000;
    }
    .player-frame video {
      display: block;
      width: 100%;
      height: auto;
      max-height: 80vh;
      background: #000;
    }
    .player-bar {
      display: flex;
      align-items: center;
      gap: 14px;
      width: 100%;
      max-width: 960px;
    }
    .player-title {
      flex: 1;
      min-width: 0;
      font-size: 15px;
      font-weight: 600;
      color: rgba(255,255,255,.92);
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
    .player-close {
      flex: none;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 8px 16px;
      border-radius: 999px;
      border: 1px solid rgba(255,255,255,.18);
      background: rgba(255,255,255,.06);
      color: #fff;
      font-size: 14px;
      text-decoration: none;
      cursor: pointer;
      transition: background .2s;
    }
    .player-close:hover { background: rgba(255,255,255,.14); }
    .player-empty {
      color: rgba(255,255,255,.6);
      font-size: 15px;
    }
  </style>
</head>
<body>
  <div class="player-wrap">
    <div class="player-bar">
      <div class="player-title"><?php echo h($title); ?></div>
      <a class="player-close" href="<?php echo bg_url('/'); ?>">&larr; <?php echo h(bg_pick(array('zh' => '返回网站', 'en' => 'Back'), $lang)); ?></a>
    </div>
    <div class="player-frame">
      <?php if ($src): ?>
        <?php if ($isGif): ?>
          <img src="<?php echo h($src); ?>" alt="<?php echo h($title); ?>" style="display:block;width:100%;height:auto;max-height:80vh;background:#000;">
        <?php else: ?>
          <video id="bg-player" autoplay controls playsinline webkit-playsinline x5-playsinline x5-video-player-type="h5" x5-video-player-fullscreen="true" preload="metadata">
            <source src="<?php echo h($src); ?>" type="video/mp4">
          </video>
          <?php if ($start > 0): ?>
          <script>
            (function () {
              var v = document.getElementById('bg-player');
              var start = <?php echo json_encode($start, JSON_NUMERIC_CHECK); ?>;
              function seek() {
                if (v.duration && start < v.duration) {
                  v.currentTime = start;
                }
                v.play && v.play();
              }
              v.addEventListener('loadedmetadata', seek, { once: true });
              if (v.readyState >= 1) seek();
            })();
          </script>
          <?php endif; ?>
        <?php endif; ?>
      <?php else: ?>
        <div class="player-empty" style="padding:48px;text-align:center;"><?php echo h(bg_pick(array('zh' => '视频不存在或链接无效', 'en' => 'Video not found or invalid link'), $lang)); ?></div>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
