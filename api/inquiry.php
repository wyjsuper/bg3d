<?php
/** POST /api/inquiry —— 前台联系表单提交（无需登录） */
define('BG_IS_API', true);
require_once __DIR__ . '/../lib/config.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/content.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  bg_json(array('error' => 'method not allowed'), 405);
}

$body = bg_input_json();
$name = isset($body['name']) ? trim((string) $body['name']) : '';
$phone = isset($body['phone']) ? trim((string) $body['phone']) : '';
$message = isset($body['message']) ? trim((string) $body['message']) : '';

if ($name === '' || $phone === '') {
  bg_json(array('error' => '姓名和电话为必填项'), 400);
}

$record = bg_create_item('inquiries', array(
  'name' => $name,
  'phone' => $phone,
  'message' => $message,
  'createdAt' => date('c'),
));

bg_json(array('success' => true, 'data' => $record));
