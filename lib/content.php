<?php
/**
 * 内容数据层（对应 content-store.ts）
 * 读取 / 写入 data/content.json，支持双语字段与集合 CRUD
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/schema.php';

$GLOBALS['_bg_data'] = null;

/** 加载数据（带内存缓存），必要时迁移旧格式 */
function bg_load_data() {
  if ($GLOBALS['_bg_data'] !== null) return $GLOBALS['_bg_data'];
  $data = array();
  if (file_exists(BG_DATA_FILE)) {
    $raw = @file_get_contents(BG_DATA_FILE);
    if ($raw !== false) {
      $dec = json_decode($raw, true);
      if (is_array($dec)) $data = $dec;
    }
  }
  if (bg_migrate($data)) {
    bg_persist($data);
  }
  $GLOBALS['_bg_data'] = $data;
  return $data;
}

/** 旧版纯字符串字段迁移为 {zh,en}（en 留空，前台回退中文） */
function bg_migrate(&$data) {
  global $BG_COLLECTIONS;
  $changed = false;
  foreach ($BG_COLLECTIONS as $col) {
    if (!isset($data[$col['type']]) || !is_array($data[$col['type']])) continue;
    foreach ($data[$col['type']] as &$rec) {
      if (!is_array($rec)) continue;
      foreach ($col['fields'] as $f) {
        if (empty($f['bilingual'])) continue;
        $k = $f['key'];
        if ($f['type'] === 'stringlist') {
          if (isset($rec[$k]) && is_array($rec[$k])) {
            $rec[$k] = array_map(function ($item) use (&$changed) {
              if (is_string($item)) { $changed = true; return array('zh' => $item, 'en' => ''); }
              return $item;
            }, $rec[$k]);
          } elseif (!array_key_exists($k, $rec)) {
            $rec[$k] = array();
          }
        } else {
          if (isset($rec[$k]) && is_string($rec[$k])) { $changed = true; $rec[$k] = array('zh' => $rec[$k], 'en' => ''); }
          elseif (!array_key_exists($k, $rec)) { $rec[$k] = array('zh' => '', 'en' => ''); }
        }
      }
      if ($col['type'] === 'nav' && isset($rec['children']) && is_array($rec['children'])) {
        foreach ($rec['children'] as &$child) {
          if (is_array($child) && isset($child['label']) && is_string($child['label'])) {
            $changed = true;
            $child['label'] = array('zh' => $child['label'], 'en' => '');
          }
        }
        unset($child);
      }
    }
    unset($rec);
  }
  return $changed;
}

/** 写回数据文件（原子写 + 文件锁） */
function bg_persist($data) {
  $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
  if ($json === false) return false;
  $dir = dirname(BG_DATA_FILE);
  if (!is_dir($dir)) @mkdir($dir, 0755, true);
  $tmp = $dir . '/.content.' . getmypid() . '.tmp';
  $ok = @file_put_contents($tmp, $json, LOCK_EX);
  if ($ok === false) return false;
  if (!@rename($tmp, BG_DATA_FILE)) {
    @unlink($tmp);
    return false;
  }
  $GLOBALS['_bg_data'] = $data;
  return true;
}

function bg_get_collection($type) {
  $data = bg_load_data();
  return (isset($data[$type]) && is_array($data[$type])) ? $data[$type] : array();
}

function bg_get_singleton($type) {
  $arr = bg_get_collection($type);
  return count($arr) ? $arr[0] : null;
}

function bg_create_item($type, $input) {
  $data = bg_load_data();
  if (!isset($data[$type]) || !is_array($data[$type])) $data[$type] = array();
  $rec = array_merge($input, array('id' => bg_new_id($type)));
  $data[$type][] = $rec;
  bg_persist($data);
  return $rec;
}

function bg_update_item($type, $id, $input) {
  $data = bg_load_data();
  if (!isset($data[$type]) || !is_array($data[$type])) return null;
  foreach ($data[$type] as &$rec) {
    if (isset($rec['id']) && $rec['id'] === $id) {
      $rec = array_merge($rec, $input);
      $rec['id'] = $id;
      bg_persist($data);
      return $rec;
    }
  }
  unset($rec);
  return null;
}

function bg_update_singleton($type, $input) {
  $data = bg_load_data();
  if (!isset($data[$type]) || !is_array($data[$type])) $data[$type] = array();
  if (count($data[$type]) === 0) {
    $rec = array_merge($input, array('id' => $type . '-main'));
    $data[$type][] = $rec;
    bg_persist($data);
    return $rec;
  }
  $data[$type][0] = array_merge($data[$type][0], $input);
  $data[$type][0]['id'] = $data[$type][0]['id'];
  bg_persist($data);
  return $data[$type][0];
}

function bg_delete_item($type, $id) {
  $data = bg_load_data();
  if (!isset($data[$type]) || !is_array($data[$type])) return;
  $data[$type] = array_values(array_filter($data[$type], function ($r) use ($id) {
    return !(isset($r['id']) && $r['id'] === $id);
  }));
  bg_persist($data);
}
