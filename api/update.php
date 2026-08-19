<?php
/**
 * 在线更新 API（需登录）
 *  GET  ?action=check   获取当前版本 + 远程最新版本信息
 *  POST ?action=do      下载并应用最新发布包（默认保留 data/uploads，可传 full=1 全量覆盖）
 */
define('BG_IS_API', true);
require_once __DIR__ . '/../lib/config.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/auth.php';

bg_require_auth();

$action = isset($_GET['action']) ? preg_replace('/[^a-z]/', '', $_GET['action']) : '';

// 关键修复：do 动作时，先从目标 zip 里提取最新 lib/update-apply.php 到临时文件再执行。
// 这避免了"正在执行的 lib/update.php 无法被自身覆盖"导致永远用旧版逻辑的问题。
$__bg_update_apply_tmp = null;
if ($action === 'do') {
  set_time_limit(0);
  $body = bg_input_json();
  $target = isset($body['tag']) ? preg_replace('/[^a-zA-Z0-9._-]/', '', (string)$body['tag']) : '';

  // 先用线上 update.php 里的查询函数拿到 download_url（lib/update.php 不再加载 apply 函数）
  require_once __DIR__ . '/../lib/update.php';

  $info = $target ? bg_update_release_by_tag($target) : bg_update_latest_info();
  if (empty($info['ok']) || empty($info['download_url'])) {
    bg_json(array('ok' => false, 'error' => isset($info['error']) ? $info['error'] : '未找到目标版本'));
  }

  // 下载 zip 并提取其中的 lib/update-apply.php 到临时 runner
  $tmpZip = sys_get_temp_dir() . '/bg-update-' . time() . '.zip';
  $dl = bg_http_download_file($info['download_url'], $tmpZip, array('timeout' => 600));
  if ($dl['ok'] && class_exists('ZipArchive')) {
    $za = new ZipArchive();
    if ($za->open($tmpZip) === TRUE) {
      $runnerDir = __DIR__ . '/../data/backups';
      if (!is_dir($runnerDir)) @mkdir($runnerDir, 0755, true);
      $runnerPath = $runnerDir . '/update-apply-' . time() . '.php';
      if ($za->extractTo($runnerDir, array('lib/update-apply.php'))) {
        @rename($runnerDir . '/lib/update-apply.php', $runnerPath);
        @rmdir($runnerDir . '/lib');
        if (is_file($runnerPath)) $__bg_update_apply_tmp = $runnerPath;
      }
      $za->close();
    }
  }
  @unlink($tmpZip);

  if ($__bg_update_apply_tmp && is_file($__bg_update_apply_tmp)) {
    define('BG_LIB_DIR', realpath(__DIR__ . '/../lib'));
    require_once $__bg_update_apply_tmp;
  } else {
    // 兜底：从本地加载 apply 函数
    require_once __DIR__ . '/../lib/update-apply.php';
  }

  $full = !empty($body['full']);
  if ($full && !BG_UPDATE_ALLOW_FULL) {
    $full = false;
  }

  $res = bg_update_apply($info['download_url'], !$full, $full);

  // 清理临时 runner
  if ($__bg_update_apply_tmp && is_file($__bg_update_apply_tmp)) {
    @unlink($__bg_update_apply_tmp);
  }

  if (!$res['ok']) {
    bg_json(array(
      'ok' => false,
      'error' => $res['error'],
      'backup' => isset($res['backup']) ? $res['backup'] : null,
      'backed' => isset($res['backed']) ? $res['backed'] : 0,
    ));
  }
  bg_json(array('ok' => true, 'remote' => $info, 'result' => $res));
}

// check 动作直接加载线上 update.php
require_once __DIR__ . '/../lib/update.php';

if ($action === 'check') {
  $cur = bg_current_version();
  $info = bg_update_latest_info();
  $all = bg_update_all_releases();
  $releases = $all['ok'] ? $all['releases'] : array();
  // 远程最新：优先取 latest，失败则取列表第一条
  $remote = $info['ok'] ? $info : (isset($releases[0]) ? $releases[0] : array('ok' => false, 'error' => isset($all['error']) ? $all['error'] : '未知错误'));
  bg_json(array('ok' => true, 'current' => $cur, 'remote' => $remote, 'releases' => $releases));
}

bg_json(array('error' => 'unknown action'), 400);
