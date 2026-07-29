<?php
/** 后台内容编辑器（对应 admin/content/[type]/page.tsx + content-editor.tsx） */
require_once __DIR__ . '/../lib/admin.php';
require_once __DIR__ . '/../lib/content.php';
bg_require_auth();

$type = isset($_GET['type']) ? preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['type']) : '';
$def = bg_get_collection_def($type);
if (!$def) {
  bg_admin_head('未找到');
  bg_admin_frame_start('未找到', '');
  echo '<div class="glass-card mx-auto max-w-3xl p-8 text-center"><p class="text-sm text-tech-muted">未找到该内容模块：' . h($type) . '</p><a href="' . bg_url('/admin') . '" class="mt-4 inline-block text-sm font-medium text-tech-blue hover:underline">返回工作台</a></div>';
  bg_admin_frame_end();
  exit;
}

$isSingleton = !empty($def['singleton']);
if ($isSingleton) {
  $single = bg_get_singleton($type);
  $items = $single ? array($single) : array();
} else {
  $items = bg_get_collection($type);
}

// 传给前端 JS 的编辑器配置
$editorData = array(
  'type' => $def['type'],
  'label' => $def['label'],
  'singular' => $def['singular'],
  'singleton' => $isSingleton,
  'fields' => array_map(function ($f) {
    return array(
      'key' => $f['key'],
      'label' => $f['label'],
      'type' => $f['type'],
      'bilingual' => !empty($f['bilingual']),
    );
  }, $def['fields']),
  'items' => $items,
);

bg_admin_head($def['label']);
bg_admin_frame_start($def['label'], $type);
?>
<div data-content-editor></div>
<script type="application/json" id="bg-editor-data"><?php echo json_encode($editorData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?></script>
<?php
bg_admin_frame_end();
