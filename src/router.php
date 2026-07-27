<?php
/**
 * 仅供本地 php -S 内置服务器测试用的路由（模拟 .htaccess 重写）
 * 生产环境使用 .htaccess，不需要本文件。
 * 启动：php -S 127.0.0.1:8090 src/router.php  （工作目录须为项目根）
 */
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$root = __DIR__ . '/..';

// 真实静态文件直接返回
$path = $root . $uri;
if ($uri !== '/' && file_exists($path) && !is_dir($path)) {
  return false;
}

// API
if (preg_match('#^/api/admin/login/?$#', $uri)) { require $root . '/api/login.php'; return true; }
if (preg_match('#^/api/admin/logout/?$#', $uri)) { require $root . '/api/logout.php'; return true; }
if (preg_match('#^/api/admin/change-password/?$#', $uri)) { require $root . '/api/change-password.php'; return true; }
if (preg_match('#^/api/content/([a-zA-Z0-9_]+)/([^/]+)/?$#', $uri, $m)) { $_GET['type'] = $m[1]; $_GET['id'] = $m[2]; require $root . '/api/content.php'; return true; }
if (preg_match('#^/api/content/([a-zA-Z0-9_]+)/?$#', $uri, $m)) { $_GET['type'] = $m[1]; require $root . '/api/content.php'; return true; }
if (preg_match('#^/api/inquiry/?$#', $uri)) { require $root . '/api/inquiry.php'; return true; }
if (preg_match('#^/api/upload/?$#', $uri)) { require $root . '/api/upload.php'; return true; }

// 后台
if (preg_match('#^/admin/login/?$#', $uri)) { require $root . '/admin/login.php'; return true; }
if (preg_match('#^/admin/content/([a-zA-Z0-9_]+)/?$#', $uri, $m)) { $_GET['type'] = $m[1]; require $root . '/admin/content.php'; return true; }
if (preg_match('#^/admin/?$#', $uri)) { require $root . '/admin/index.php'; return true; }

// 前台
$map = array('/3d' => '3d.php', '/service' => 'service.php', '/plan' => 'plan.php', '/profile' => 'profile.php', '/case' => 'case.php', '/points' => 'points.php', '/contact' => 'contact.php');
$clean = rtrim($uri, '/');
if ($clean === '') { require $root . '/index.php'; return true; }
if (isset($map[$clean])) { require $root . '/' . $map[$clean]; return true; }

require $root . '/index.php';
return true;
