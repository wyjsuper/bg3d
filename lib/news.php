<?php
/**
 * FDE 资讯归档数据层
 * 读取 data/fde-archive.json —— 本站沉淀的历史资讯库（永久保留，标注原始出处）。
 * content.json 的 aiFdeNews 只保留最新 20 条用于页脚展示，历史全量在这里。
 */
require_once __DIR__ . '/config.php';

if (!defined('BG_NEWS_FILE')) {
  define('BG_NEWS_FILE', dirname(__DIR__) . '/data/fde-archive.json');
}

$GLOBALS['_bg_news'] = null;

/** 载入归档（带内存缓存），按日期倒序 */
function bg_news_load() {
  if ($GLOBALS['_bg_news'] !== null) return $GLOBALS['_bg_news'];
  $items = array();
  if (file_exists(BG_NEWS_FILE)) {
    $raw = @file_get_contents(BG_NEWS_FILE);
    if ($raw !== false) {
      $dec = json_decode($raw, true);
      if (isset($dec['items']) && is_array($dec['items'])) {
        $items = $dec['items'];
      } elseif (is_array($dec)) {
        $items = $dec;
      }
    }
  }
  // 过滤无效项 + 按日期倒序（同日按 archivedAt 再排）
  $items = array_values(array_filter($items, function ($it) {
    return is_array($it) && !empty($it['slug']) && !empty($it['title']);
  }));
  usort($items, function ($a, $b) {
    $da = isset($a['date']) ? $a['date'] : '';
    $db = isset($b['date']) ? $b['date'] : '';
    if ($da === $db) {
      $aa = isset($a['archivedAt']) ? $a['archivedAt'] : '';
      $ab = isset($b['archivedAt']) ? $b['archivedAt'] : '';
      return strcmp($ab, $aa);
    }
    return strcmp($db, $da);
  });
  $GLOBALS['_bg_news'] = $items;
  return $items;
}

/** 按语言过滤（无 lang 字段的条目视为 zh） */
function bg_news_filter_lang($items, $lang) {
  if ($lang === null || $lang === '' || $lang === 'all') return $items;
  return array_values(array_filter($items, function ($it) use ($lang) {
    $l = isset($it['lang']) ? $it['lang'] : 'zh';
    return $l === $lang;
  }));
}

/** 全部归档条目（可指定语言：zh / en / all） */
function bg_news_all($lang = null) {
  return bg_news_filter_lang(bg_news_load(), $lang);
}

/** 归档总数（可指定语言） */
function bg_news_count($lang = null) {
  return count(bg_news_all($lang));
}

/** 按 slug 查找单条 */
function bg_news_find($slug) {
  if ($slug === '' || $slug === null) return null;
  foreach (bg_news_load() as $it) {
    if ($it['slug'] === $slug) return $it;
  }
  return null;
}

/** 取某条的相邻条目 array(prev, next)：prev = 更新的一条，next = 更旧的一条（同语言内） */
function bg_news_siblings($slug, $lang = null) {
  $items = bg_news_all($lang);
  foreach ($items as $i => $it) {
    if ($it['slug'] === $slug) {
      $prev = ($i > 0) ? $items[$i - 1] : null;
      $next = ($i < count($items) - 1) ? $items[$i + 1] : null;
      return array($prev, $next);
    }
  }
  return array(null, null);
}

/** 分页：返回 array(items, page, totalPages, total) */
function bg_news_page($page = 1, $perPage = 18, $lang = null) {
  $items = bg_news_all($lang);
  $total = count($items);
  $totalPages = max(1, (int) ceil($total / $perPage));
  $page = max(1, min($totalPages, (int) $page));
  $slice = array_slice($items, ($page - 1) * $perPage, $perPage);
  return array($slice, $page, $totalPages, $total);
}

/** 按分类（按当前语言名）统计，仅统计同语言条目 */
function bg_news_categories($lang = 'zh') {
  $map = array();
  foreach (bg_news_all($lang) as $it) {
    if (empty($it['category'])) continue;
    $c = is_array($it['category']) ? (isset($it['category'][$lang]) && $it['category'][$lang] !== '' ? $it['category'][$lang] : $it['category']['zh']) : $it['category'];
    if ($c === '') continue;
    if (!isset($map[$c])) $map[$c] = 0;
    $map[$c]++;
  }
  arsort($map);
  return $map;
}

/** 正文段落数组（body 用空行分段） */
function bg_news_paragraphs($item, $lang) {
  if (empty($item['body'])) return array();
  $body = $item['body'];
  $text = '';
  if (is_array($body)) {
    $text = (isset($body[$lang]) && trim($body[$lang]) !== '') ? $body[$lang] : (isset($body['zh']) ? $body['zh'] : '');
  } else {
    $text = (string) $body;
  }
  $text = str_replace(array("\r\n", "\r"), "\n", trim($text));
  if ($text === '') return array();
  $parts = preg_split('/\n\s*\n/', $text);
  $out = array();
  foreach ($parts as $p) {
    $p = trim($p);
    if ($p !== '') $out[] = $p;
  }
  return $out;
}
