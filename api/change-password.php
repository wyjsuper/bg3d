<?php
/** POST /api/admin/change-password（需登录） */
define('BG_IS_API', true);
require_once __DIR__ . '/../lib/config.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') bg_json(array('ok' => false, 'error' => 'method not allowed'), 405);

bg_require_auth();

$body = bg_input_json();
$currentPassword = isset($body['currentPassword']) ? (string) $body['currentPassword'] : '';
$newPassword = isset($body['newPassword']) ? (string) $body['newPassword'] : '';

if ($currentPassword === '' || $newPassword === '') {
  bg_json(array('ok' => false, 'error' => '请输入当前密码和新密码'), 400);
}
if (mb_strlen($newPassword) < 6) {
  bg_json(array('ok' => false, 'error' => '新密码至少 6 位'), 400);
}
if (!bg_verify_password($currentPassword)) {
  bg_json(array('ok' => false, 'error' => '当前密码不正确'), 403);
}

$stored = bg_hash_password($newPassword);
if (!bg_save_auth($stored)) {
  bg_json(array('ok' => false, 'error' => '保存失败，请检查 data 目录写权限'), 500);
}

bg_json(array('ok' => true, 'message' => '密码修改成功，下次登录请使用新密码'));
