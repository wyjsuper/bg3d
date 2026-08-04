<?php
/**
 * 双语工具函数
 * 数据字段可能两种形态：
 *  - 普通字符串（遗留 / 非双语）
 *  - 关联数组 {zh, en}
 */

/** 取展示字符串：普通字符串 或 {zh,en} 数组 */
function bg_t($field, $lang) {
  if (is_string($field)) return $field;
  if (is_array($field) && array_key_exists('zh', $field) && array_key_exists('en', $field)) {
    return ($lang === 'en' && $field['en'] !== '' && $field['en'] !== null) ? $field['en'] : $field['zh'];
  }
  if (is_array($field) && array_key_exists('zh', $field)) return $field['zh'];
  return is_scalar($field) ? (string) $field : '';
}

/** 取字符串数组：元素可能是普通字符串 或 {zh,en} */
function bg_t_list($field, $lang) {
  if (!is_array($field)) return array();
  return array_map(function ($item) use ($lang) {
    if (is_string($item)) return $item;
    if (is_array($item) && array_key_exists('zh', $item) && array_key_exists('en', $item)) {
      return ($lang === 'en' && $item['en'] !== '' && $item['en'] !== null) ? $item['en'] : $item['zh'];
    }
    if (is_array($item) && array_key_exists('zh', $item)) return $item['zh'];
    return is_scalar($item) ? (string) $item : '';
  }, $field);
}

/** 判断是否为双语数组 */
function bg_is_bi($v) {
  return is_array($v) && array_key_exists('zh', $v) && array_key_exists('en', $v);
}

/** 从双语数组按语言取字符串 */
function bg_pick($bi, $lang) {
  if (is_array($bi) && array_key_exists('zh', $bi) && array_key_exists('en', $bi)) {
    return ($lang === 'en' && $bi['en'] !== '' && $bi['en'] !== null) ? $bi['en'] : $bi['zh'];
  }
  if (is_array($bi) && array_key_exists('zh', $bi)) return $bi['zh'];
  return is_scalar($bi) ? (string) $bi : '';
}

/** 读取当前语言：cookie lang=en 则英文，否则中文。
 *  额外支持 ?lang=en GET 参数（GET 优先），便于本地 php-wasm 预览验证英文版
 *  （php-wasm 的 PHPRequestHandler 不转发 Cookie 头，真实虚拟主机仍以 cookie 为准）。 */
function bg_get_lang() {
  if (isset($_GET['lang']) && $_GET['lang'] === 'en') return 'en';
  if (isset($_COOKIE['lang']) && $_COOKIE['lang'] === 'en') return 'en';
  return 'zh';
}
