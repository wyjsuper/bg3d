<?php
/**
 * 在线更新应用逻辑（可被 api/update.php 从目标 zip 里解压到临时位置执行）。
 * 这样可避免 lib/update.php 自身正在执行时无法被覆盖的问题。
 */
$__bg_update_lib_dir = defined('BG_LIB_DIR') ? BG_LIB_DIR : __DIR__;
require_once $__bg_update_lib_dir . '/config.php';
require_once $__bg_update_lib_dir . '/helpers.php';

if (!function_exists('bg_site_root')) {
  function bg_site_root() {
    global $__bg_update_lib_dir;
    return dirname($__bg_update_lib_dir);
  }
}

/**
 * 下载并应用更新。
 * @param string $download_url 发布包(zip)下载地址
 * @param bool   $preserve_data 保留运营数据（data/、uploads/ 不覆盖）
 * @param bool   $allow_full    是否允许全量覆盖（由 BG_UPDATE_ALLOW_FULL 与用户勾选共同决定）
 * @return array ['ok','covered','backed','skipped','backup','error']
 */
function bg_update_apply($download_url, $preserve_data = true, $allow_full = false) {
  $root = bg_site_root();
  $backupBase = $root . '/data/backups';
  if (!is_dir($backupBase)) @mkdir($backupBase, 0755, true);
  $ts = date('Ymd-His');
  $tmpZip = $backupBase . '/update-' . $ts . '.zip';

  // 下载
  $dl = bg_http_download_file($download_url, $tmpZip, array('timeout' => 600));
  if (!$dl['ok']) { @unlink($tmpZip); return array('ok' => false, 'error' => '下载失败：' . $dl['error']); }

  if (!class_exists('ZipArchive')) { @unlink($tmpZip); return array('ok' => false, 'error' => '服务器未启用 ZipArchive 扩展，无法解压'); }

  $za = new ZipArchive();
  if ($za->open($tmpZip) !== TRUE) { @unlink($tmpZip); return array('ok' => false, 'error' => '压缩包无法打开'); }

  // 决定哪些条目需要覆盖（保留数据模式下跳过真正的运营数据）
  $doFull = (!$preserve_data && $allow_full);
  $entries = array();
  $skipped = 0;
  $backed = 0;
  $backupDir = $backupBase . '/site-' . $ts;

  for ($i = 0; $i < $za->numFiles; $i++) {
    $name = $za->getNameIndex($i);
    if ($name === false) continue;
    $name = ltrim($name, '/');
    // 保留数据模式下，仅保留真正的运营数据与用户备份；归档类文件（如 fde-archive.json）应随版本更新
    if (!$doFull) {
      if (strpos($name, 'uploads/') === 0) { $skipped++; continue; }
      if (strpos($name, 'data/backups/') === 0) { $skipped++; continue; }
      if ($name === 'data/content.json' || $name === 'data/auth.json') { $skipped++; continue; }
      if (preg_match('#^data/.+\.bak(\d+)?$#', $name)) { $skipped++; continue; }
    }
    // 备份将被覆盖的现有文件
    $target = $root . '/' . $name;
    if (is_file($target)) {
      $bd = $backupDir . '/' . dirname($name);
      if (!is_dir($bd)) @mkdir($bd, 0755, true);
      if (@copy($target, $backupDir . '/' . $name)) $backed++;
    }
    $entries[] = $name;
  }

  $extractOk = $za->extractTo($root, $entries);
  $za->close();
  @unlink($tmpZip);

  if (!$extractOk) {
    return array('ok' => false, 'error' => '解压失败', 'backup' => $backupDir, 'backed' => $backed, 'skipped' => $skipped);
  }

  return array(
    'ok' => true,
    'covered' => count($entries),
    'backed' => $backed,
    'skipped' => $skipped,
    'backup' => $backupDir,
  );
}
