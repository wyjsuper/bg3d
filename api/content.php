<?php
/**
 * 内容 CRUD API（需登录）
 *  GET    /api/content/{type}           读取集合 / 单例
 *  POST   /api/content/{type}           新建列表项
 *  PUT    /api/content/{type}           更新单例
 *  PUT    /api/content/{type}/{id}      更新列表项
 *  DELETE /api/content/{type}/{id}      删除列表项
 * 路由参数由 .htaccess 重写为 ?type=&id=
 */
define('BG_IS_API', true);
require_once __DIR__ . '/../lib/config.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/schema.php';
require_once __DIR__ . '/../lib/content.php';
require_once __DIR__ . '/../lib/auth.php';

bg_require_auth();

$type = isset($_GET['type']) ? preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['type']) : '';
$id = isset($_GET['id']) ? (string) $_GET['id'] : '';
$method = $_SERVER['REQUEST_METHOD'];

$def = bg_get_collection_def($type);
if (!$def) bg_json(array('error' => 'unknown collection'), 404);
$isSingleton = !empty($def['singleton']);

switch ($method) {
  case 'GET':
    $data = $isSingleton ? bg_get_singleton($type) : bg_get_collection($type);
    bg_json(array('type' => $type, 'singleton' => $isSingleton, 'data' => $data));
    break;

  case 'POST':
    if ($isSingleton) bg_json(array('error' => 'singleton 请使用 PUT'), 400);
    $created = bg_create_item($type, bg_input_json());
    bg_json(array('data' => $created));
    break;

  case 'PUT':
    if ($id === '') {
      if (!$isSingleton) bg_json(array('error' => '列表集合请使用 PUT /{id}'), 400);
      $updated = bg_update_singleton($type, bg_input_json());
      bg_json(array('data' => $updated));
    } else {
      if ($isSingleton) bg_json(array('error' => 'invalid collection'), 400);
      $updated = bg_update_item($type, $id, bg_input_json());
      if (!$updated) bg_json(array('error' => 'not found'), 404);
      bg_json(array('data' => $updated));
    }
    break;

  case 'DELETE':
    if ($isSingleton || $id === '') bg_json(array('error' => 'invalid collection'), 400);
    bg_delete_item($type, $id);
    bg_json(array('ok' => true));
    break;

  default:
    bg_json(array('error' => 'method not allowed'), 405);
}
