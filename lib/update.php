<?php
/**
 * 在线更新核心：读取当前版本、获取远程最新发布、下载并覆盖（保留数据）。
 * 仅由后台 /api/update.php（已登录）调用。
 */
// 支持从临时副本执行：api/update.php 会先把 zip 里的新版 update.php 解压到临时文件再 require，
// 此时 __DIR__ 已变，需要外部传入真实 lib 目录。
$__bg_update_lib_dir = defined('BG_LIB_DIR') ? BG_LIB_DIR : __DIR__;
require_once $__bg_update_lib_dir . '/config.php';
require_once $__bg_update_lib_dir . '/helpers.php';

/** 站点根目录（lib 的上一级） */
function bg_site_root() {
  global $__bg_update_lib_dir;
  return dirname($__bg_update_lib_dir);
}

/** 读取当前部署版本（version.json） */
function bg_current_version() {
  $f = bg_site_root() . '/version.json';
  if (!file_exists($f)) return array('version' => 'unknown', 'date' => '', 'commit' => '');
  $d = json_decode(@file_get_contents($f), true);
  return is_array($d) ? $d : array('version' => 'unknown');
}

/**
 * 获取远程最新发布信息。
 * 返回 ['ok','tag','name','published_at','download_url','error']
 */
function bg_update_latest_info() {
  $source = defined('BG_UPDATE_SOURCE') ? BG_UPDATE_SOURCE : 'github';

  // 自定义信息接口（可用于 Gitee / 自建镜像，适配国内网络）
  if ($source === 'custom') {
    $infoUrl = defined('BG_UPDATE_INFO_URL') ? BG_UPDATE_INFO_URL : '';
    if (!$infoUrl) return array('ok' => false, 'error' => '未配置 BG_UPDATE_INFO_URL');
    $r = bg_http_get($infoUrl, array('timeout' => 20, 'headers' => array('Accept: application/json')));
    if (!$r['ok']) return array('ok' => false, 'error' => '获取更新信息失败：' . $r['error']);
    $d = json_decode($r['body'], true);
    if (!is_array($d) || empty($d['download_url'])) return array('ok' => false, 'error' => '更新信息格式错误（缺少 download_url）');
    return array(
      'ok' => true,
      'tag' => isset($d['tag']) ? $d['tag'] : '',
      'name' => isset($d['name']) ? $d['name'] : (isset($d['tag']) ? $d['tag'] : ''),
      'published_at' => isset($d['published_at']) ? $d['published_at'] : '',
      'download_url' => $d['download_url'],
    );
  }

  // 默认 GitHub latest release
  $repo = defined('BG_GITHUB_REPO') ? BG_GITHUB_REPO : '';
  if (!$repo) return array('ok' => false, 'error' => '未配置 BG_GITHUB_REPO（请在 lib/config.php 设置）');
  $api = 'https://api.github.com/repos/' . $repo . '/releases/latest';
  $r = bg_http_get($api, array('timeout' => 20, 'headers' => array('User-Agent: Beigang', 'Accept: application/vnd.github+json')));
  if (!$r['ok']) return array('ok' => false, 'error' => 'GitHub API 请求失败：' . $r['error']);
  $data = json_decode($r['body'], true);
  if (!is_array($data)) return array('ok' => false, 'error' => 'GitHub 返回解析失败');
  if (isset($data['message']) && !isset($data['tag_name'])) return array('ok' => false, 'error' => 'GitHub：' . $data['message']);

  $tag = isset($data['tag_name']) ? $data['tag_name'] : '';
  $download = '';
  foreach (($data['assets'] ?? array()) as $a) {
    if (($a['name'] ?? '') === 'beigang-php-deploy.zip') { $download = $a['browser_download_url'] ?? ''; break; }
  }
  if (!$download && !empty($data['zipball_url'])) $download = $data['zipball_url'];
  if (!$download) return array('ok' => false, 'error' => 'Release 中未找到 beigang-php-deploy.zip');

  return array(
    'ok' => true,
    'tag' => $tag,
    'name' => isset($data['name']) ? $data['name'] : $tag,
    'published_at' => isset($data['published_at']) ? $data['published_at'] : '',
    'download_url' => $download,
    'body' => isset($data['body']) ? $data['body'] : '',
  );
}

/**
 * 解析单个 GitHub release 数组元素，提取 tag/name/published_at/download_url。
 */
function bg_update_parse_release($data) {
  if (!is_array($data) || empty($data['tag_name'])) return null;
  $tag = $data['tag_name'];
  $download = '';
  foreach (($data['assets'] ?? array()) as $a) {
    if (($a['name'] ?? '') === 'beigang-php-deploy.zip') { $download = $a['browser_download_url'] ?? ''; break; }
  }
  if (!$download && !empty($data['zipball_url'])) $download = $data['zipball_url'];
  return array(
    'ok' => true,
    'tag' => $tag,
    'name' => isset($data['name']) ? $data['name'] : $tag,
    'published_at' => isset($data['published_at']) ? $data['published_at'] : '',
    'download_url' => $download,
    'body' => isset($data['body']) ? $data['body'] : '',
  );
}

/**
 * 按 tag 获取指定版本的发布信息（用于「更新到该版本」/「回滚」）。
 * 返回结构同 bg_update_latest_info。
 */
function bg_update_release_by_tag($tag) {
  $source = defined('BG_UPDATE_SOURCE') ? BG_UPDATE_SOURCE : 'github';

  if ($source === 'custom') {
    $infoUrl = defined('BG_UPDATE_INFO_URL') ? BG_UPDATE_INFO_URL : '';
    if (!$infoUrl) return array('ok' => false, 'error' => '未配置 BG_UPDATE_INFO_URL');
    $r = bg_http_get($infoUrl, array('timeout' => 20, 'headers' => array('Accept: application/json')));
    if (!$r['ok']) return array('ok' => false, 'error' => '获取更新信息失败：' . $r['error']);
    $list = json_decode($r['body'], true);
    if (!is_array($list)) return array('ok' => false, 'error' => '更新信息格式错误');
    foreach ($list as $item) {
      if (isset($item['tag']) && $item['tag'] === $tag) {
        return bg_update_parse_release($item);
      }
    }
    return array('ok' => false, 'error' => '未找到版本 ' . $tag);
  }

  $repo = defined('BG_GITHUB_REPO') ? BG_GITHUB_REPO : '';
  if (!$repo) return array('ok' => false, 'error' => '未配置 BG_GITHUB_REPO');
  $api = 'https://api.github.com/repos/' . $repo . '/releases/tags/' . rawurlencode($tag);
  $r = bg_http_get($api, array('timeout' => 20, 'headers' => array('User-Agent: Beigang', 'Accept: application/vnd.github+json')));
  if (!$r['ok']) return array('ok' => false, 'error' => 'GitHub API 请求失败：' . $r['error']);
  $data = json_decode($r['body'], true);
  if (!is_array($data) || empty($data['tag_name'])) return array('ok' => false, 'error' => '未找到版本 ' . $tag);
  if (isset($data['message'])) return array('ok' => false, 'error' => 'GitHub：' . $data['message']);
  $parsed = bg_update_parse_release($data);
  if (!$parsed || empty($parsed['download_url'])) return array('ok' => false, 'error' => '该版本缺少 beigang-php-deploy.zip');
  return $parsed;
}

/**
 * 获取远程所有 Release 列表（按发布从新到旧）。
 * 返回 ['ok','releases'=>[...],'error']，每个 release 同 bg_update_parse_release。
 */
function bg_update_all_releases() {
  $source = defined('BG_UPDATE_SOURCE') ? BG_UPDATE_SOURCE : 'github';

  // 自定义信息接口：返回数组 [{tag, name, published_at, download_url}, ...]
  if ($source === 'custom') {
    $infoUrl = defined('BG_UPDATE_INFO_URL') ? BG_UPDATE_INFO_URL : '';
    if (!$infoUrl) return array('ok' => false, 'error' => '未配置 BG_UPDATE_INFO_URL');
    $r = bg_http_get($infoUrl, array('timeout' => 20, 'headers' => array('Accept: application/json')));
    if (!$r['ok']) return array('ok' => false, 'error' => '获取更新信息失败：' . $r['error']);
    $list = json_decode($r['body'], true);
    if (!is_array($list)) return array('ok' => false, 'error' => '更新信息格式错误');
    return array('ok' => true, 'releases' => $list);
  }

  $repo = defined('BG_GITHUB_REPO') ? BG_GITHUB_REPO : '';
  if (!$repo) return array('ok' => false, 'error' => '未配置 BG_GITHUB_REPO');
  $api = 'https://api.github.com/repos/' . $repo . '/releases?per_page=100';
  $r = bg_http_get($api, array('timeout' => 20, 'headers' => array('User-Agent: Beigang', 'Accept: application/vnd.github+json')));
  if (!$r['ok']) return array('ok' => false, 'error' => 'GitHub API 请求失败：' . $r['error']);
  $data = json_decode($r['body'], true);
  if (!is_array($data)) return array('ok' => false, 'error' => 'GitHub 返回解析失败');
  if (isset($data['message'])) return array('ok' => false, 'error' => 'GitHub：' . $data['message']);

  $releases = array();
  foreach ($data as $item) {
    $parsed = bg_update_parse_release($item);
    if ($parsed && $parsed['download_url']) $releases[] = $parsed;
  }
  return array('ok' => true, 'releases' => $releases);
}

