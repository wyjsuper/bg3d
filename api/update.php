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
require_once __DIR__ . '/../lib/update.php';

bg_require_auth();

$action = isset($_GET['action']) ? preg_replace('/[^a-z]/', '', $_GET['action']) : '';

if ($action === 'check') {
  $cur = bg_current_version();
  $info = bg_update_latest_info();
  $all = bg_update_all_releases();
  $releases = $all['ok'] ? $all['releases'] : array();
  // 远程最新：优先取 latest，失败则取列表第一条
  $remote = $info['ok'] ? $info : (isset($releases[0]) ? $releases[0] : array('ok' => false, 'error' => isset($all['error']) ? $all['error'] : '未知错误'));
  bg_json(array('ok' => true, 'current' => $cur, 'remote' => $remote, 'releases' => $releases));
}

if ($action === 'do') {
  set_time_limit(0);
  $body = bg_input_json();
  $full = !empty($body['full']);
  if ($full && !BG_UPDATE_ALLOW_FULL) {
    // 未开启全量覆盖则强制保留数据
    $full = false;
  }
  $target = isset($body['tag']) ? preg_replace('/[^a-zA-Z0-9._-]/', '', (string) $body['tag']) : '';
  $info = $target ? bg_update_release_by_tag($target) : bg_update_latest_info();
  if (empty($info['ok'])) {
    bg_json(array('ok' => false, 'error' => isset($info['error']) ? $info['error'] : '未找到目标版本'));
  }

  $res = bg_update_apply($info['download_url'], !$full, $full);
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

bg_json(array('error' => 'unknown action'), 400);
