<?php
/**
 * 通用输出 / 辅助函数
 */

/** HTML 转义（正文 / 属性通用） */
function h($s) {
  return htmlspecialchars((string) $s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/** 转义并输出 */
function e($s) {
  echo h($s);
}

/**
 * 路由映射：把「美化路径」翻译成真实 PHP 文件，避免依赖 Apache mod_rewrite。
 * 这样即使虚拟主机未开启重写，直接访问 .php 文件也能跑通所有页面与 API。
 * 不在映射表中的路径（如 /assets/...、/logo-removebg.png、已带 .php 的路径）原样返回。
 */
function bg_route($path) {
  static $map = array(
    '/admin'          => '/admin/index.php',
    '/admin/login'    => '/admin/login.php',
    '/3d'             => '/3d.php',
    '/service'        => '/service.php',
    '/case'           => '/case.php',
    '/plan'           => '/plan.php',
    '/profile'        => '/profile.php',
    '/points'         => '/points.php',
    '/contact'        => '/contact.php',
    '/api/inquiry'    => '/api/inquiry.php',
    '/api/admin/login'       => '/api/login.php',
    '/api/admin/logout'      => '/api/logout.php',
    '/api/admin/change-password' => '/api/change-password.php',
    '/api/upload'     => '/api/upload.php',
    '/api/update'     => '/api/update.php',
  );
  if (isset($map[$path])) {
    return $map[$path];
  }
  // 带参数的友好路径
  if (preg_match('#^/admin/content/([a-zA-Z0-9_]+)$#', $path, $m)) {
    return '/admin/content.php?type=' . $m[1];
  }
  if (preg_match('#^/case(\?.*)?$#', $path, $m)) {
    return '/case.php' . (isset($m[1]) ? $m[1] : '');
  }
  if (preg_match('#^/api/content/([a-zA-Z0-9_]+)/([^/]+)$#', $path, $m)) {
    return '/api/content.php?type=' . $m[1] . '&id=' . $m[2];
  }
  if (preg_match('#^/api/content/([a-zA-Z0-9_]+)$#', $path, $m)) {
    return '/api/content.php?type=' . $m[1];
  }
  return $path;
}

/** 部署基础路径前缀：把以 / 开头的站内路径加上部署子目录前缀（如 /3d） */
function bg_url($path) {
  if ($path === null || $path === '') return '';
  if (bg_starts_with($path, 'http://') || bg_starts_with($path, 'https://') || bg_starts_with($path, '//')) return (string) $path;
  if (!bg_starts_with($path, '/')) return (string) $path;
  $base = defined('BG_BASE') ? BG_BASE : '';
  return $base . bg_route($path);
}

/** 资源 URL 修正：以 / 开头视为站点根；自动加上部署基础路径前缀 */
function asset_url($path) {
  return bg_url($path);
}

/** 安全 JSON 输出（API 响应） */
function bg_json($data, $code = 200) {
  http_response_code($code);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  exit;
}

/** 读取请求体 JSON（兼容多字节 / 多运行环境的容错处理） */
function bg_input_json() {
  $raw = file_get_contents('php://input');
  if ($raw === false || $raw === '') return array();
  // 正常环境：严格 UTF-8 解码即可。
  // 个别 Windows 环境（mod_fcgid + php-cgi）会把多字节请求体错误编码成
  // 非法 UTF-8 导致 json_decode 失败；此处放宽解码并做归一化兜底，
  // 在合法主机（Linux Apache/Nginx/LiteSpeed）上此分支为 no-op。
  $dec = json_decode($raw, true, 512, JSON_INVALID_UTF8_IGNORE);
  if (is_array($dec)) return $dec;
  if (function_exists('mb_convert_encoding')) {
    $norm = mb_convert_encoding($raw, 'UTF-8', 'UTF-8');
    $dec2 = json_decode($norm, true, 512, JSON_INVALID_UTF8_IGNORE);
    if (is_array($dec2)) return $dec2;
  }
  return array();
}

/** 生成短 ID */
function bg_new_id($type) {
  return $type . '-' . base_convert(time(), 10, 36) . '-' . substr(bin2hex(random_bytes(3)), 0, 5);
}

/** 字符串是否以某前缀开头（兼容 PHP 7.4，不依赖 str_starts_with） */
function bg_starts_with($haystack, $needle) {
  return substr((string) $haystack, 0, strlen((string) $needle)) === (string) $needle;
}

/** HTTP GET（优先 curl，降级 file_get_contents）。返回 ['ok','code','body','error'] */
function bg_http_get($url, $opts = array()) {
  $result = array('ok' => false, 'code' => 0, 'body' => '', 'error' => '');
  $headers = isset($opts['headers']) ? $opts['headers'] : array();
  $timeout = isset($opts['timeout']) ? (int) $opts['timeout'] : 30;
  if (function_exists('curl_init')) {
    $ch = curl_init($url);
    curl_setopt_array($ch, array(
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_TIMEOUT => $timeout,
      CURLOPT_CONNECTTIMEOUT => 10,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_MAXREDIRS => 5,
      CURLOPT_SSL_VERIFYPEER => true,
      CURLOPT_HTTPHEADER => $headers,
      CURLOPT_USERAGENT => 'Beigang-Updater',
    ));
    $body = curl_exec($ch);
    if ($body === false) { $result['error'] = curl_error($ch); curl_close($ch); return $result; }
    $result['code'] = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $result['body'] = $body;
    curl_close($ch);
    $result['ok'] = ($result['code'] >= 200 && $result['code'] < 300);
    return $result;
  }
  $ctx = stream_context_create(array('http' => array('timeout' => $timeout, 'header' => implode("\r\n", $headers) . "\r\n")));
  $body = @file_get_contents($url, false, $ctx);
  if ($body === false) { $result['error'] = 'file_get_contents 失败'; return $result; }
  $result['ok'] = true; $result['code'] = 200; $result['body'] = $body;
  return $result;
}

/** 流式下载文件到本地。返回 ['ok','error'] */
function bg_http_download_file($url, $dest, $opts = array()) {
  $headers = isset($opts['headers']) ? $opts['headers'] : array();
  $timeout = isset($opts['timeout']) ? (int) $opts['timeout'] : 600;
  $fp = @fopen($dest, 'wb');
  if (!$fp) return array('ok' => false, 'error' => '无法写入临时文件：' . $dest);
  if (function_exists('curl_init')) {
    $ch = curl_init($url);
    curl_setopt_array($ch, array(
      CURLOPT_FILE => $fp,
      CURLOPT_TIMEOUT => $timeout,
      CURLOPT_CONNECTTIMEOUT => 15,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_MAXREDIRS => 5,
      CURLOPT_SSL_VERIFYPEER => true,
      CURLOPT_HTTPHEADER => $headers,
      CURLOPT_USERAGENT => 'Beigang-Updater',
    ));
    $ok = curl_exec($ch);
    if ($ok === false) { $err = curl_error($ch); curl_close($ch); fclose($fp); @unlink($dest); return array('ok' => false, 'error' => $err); }
    curl_close($ch); fclose($fp);
    return array('ok' => true);
  }
  $ctx = stream_context_create(array('http' => array('timeout' => $timeout, 'header' => implode("\r\n", $headers) . "\r\n")));
  $body = @file_get_contents($url, false, $ctx);
  fclose($fp);
  if ($body === false) { @unlink($dest); return array('ok' => false, 'error' => '下载失败'); }
  if (@file_put_contents($dest, $body) === false) { @unlink($dest); return array('ok' => false, 'error' => '写入失败'); }
  return array('ok' => true);
}
