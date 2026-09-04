<?php
/**
 * FDE 资讯刷新 API（需管理员登录）
 *   POST  {lang:'both'|'zh'|'en', days:3, cap:12}
 *   ->   {ok, added, updated, total, zhCount, enCount, stats:{zh,en}, new_items:[...]}
 *
 * 仅管理员可调用（bg_require_auth）。抓取 RSS 可能耗时，已 set_time_limit(0)。
 */
define('BG_IS_API', true);
require_once __DIR__ . '/../lib/config.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/auth.php';
require_once __DIR__ . '/../lib/fde_refresh.php';

bg_require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  bg_json(array('ok' => false, 'error' => 'method not allowed'), 405);
}

set_time_limit(0);

$body = bg_input_json();
$lang = isset($body['lang']) ? $body['lang'] : 'both';
if (!in_array($lang, array('zh', 'en', 'both'), true)) $lang = 'both';
$days = isset($body['days']) ? (int)$body['days'] : 3;
$cap = isset($body['cap']) ? (int)$body['cap'] : 12;
if ($days < 1) $days = 3;
if ($cap < 1) $cap = 12;

$res = bg_fde_refresh(array('lang' => $lang, 'days' => $days, 'cap' => $cap));
bg_json($res);
