<?php
/** POST /api/admin/login */
define('BG_IS_API', true);
require_once __DIR__ . '/../lib/config.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') bg_json(array('ok' => false, 'error' => 'method not allowed'), 405);

$body = bg_input_json();
$username = isset($body['username']) ? (string) $body['username'] : '';
$password = isset($body['password']) ? (string) $body['password'] : '';

if ($username === '' || $password === '' || !bg_login($username, $password)) {
  bg_json(array('ok' => false, 'error' => '账号或密码错误'), 401);
}

bg_json(array('ok' => true, 'username' => $username));
