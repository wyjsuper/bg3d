<?php
/**
 * FDE 资讯刷新引擎（纯 PHP 镜像版，供网页按钮调用）
 *
 * 与 tools/fde_daily_update.py 逻辑对齐，但用 PHP 原生实现，
 * 因此无需主机上安装 Python / exec 权限，只要有 PHP + 出站网络即可。
 *
 * 入口：bg_fde_refresh(['lang'=>'both','days'=>3,'cap'=>12])
 *   - 抓取中/英文科技·AI·企业类 RSS 源，解析最近 N 天条目
 *   - 按 FDE 相关性打分过滤、按归一化链接去重（跨天重复不再入库）
 *   - 合并沉淀到 data/fde-archive.json，并刷新 data/content.json 的
 *     aiFdeNews（中文）/ aiFdeNewsEn（英文）各最新 20 条
 *   - slug 生成规则与 Python 版完全一致，二者互不产生重复
 *
 * 注意：前台 FDE 版块（lib/render.php）直接读 fde-archive.json，
 * 所以写好归档即可即时生效；content.json 的副本同步刷新以保持一致。
 */

if (!function_exists('bg_fde_feeds')) {

  /** RSS/Atom 源（公开、无需密钥）。失效源逐个容错跳过。 */
  function bg_fde_feeds() {
    return array(
      'en' => array(
        'https://hnrss.org/frontpage',
        'https://techcrunch.com/category/artificial-intelligence/feed/',
        'https://venturebeat.com/feed/',
        'https://www.theverge.com/rss/ai-artificial-intelligence/index.xml',
        'https://feeds.arstechnica.com/arstechnica/index',
        'https://www.zdnet.com/news/rss.xml',
        'https://blog.google/technology/ai/rss/',
        'https://openai.com/blog/rss.xml',
        'https://techcrunch.com/feed/',
      ),
      'zh' => array(
        'https://www.36kr.com/feed',
        'https://www.qbitai.com/feed',
        'https://www.infoq.cn/feed',
        'https://www.leiphone.com/rss',
        'https://www.thepaper.cn/rss.jsp',
        'https://www.jiqizhixin.com/rss',
      ),
    );
  }

  /** 强相关词：命中即视为 FDE 相关 */
  function bg_fde_strong() {
    return array(
      'en' => array('forward deployed', 'forward-deployed', ' fde', 'ai agent', 'agentic',
        'enterprise ai', 'ai deployment', 'palantir', 'openai deployment',
        'anthropic', 'claude', 'chatgpt', 'gemini', 'copilot', ' rag', 'llm',
        'large language model', 'generative ai', 'ai startup', 'ai funding',
        'machine learning', 'deploy '),
      'zh' => array('前沿部署', 'fde', '智能体', 'agent', '大模型', '企业 ai', '企业ai',
        'ai 部署', '部署', 'palantir', 'openai', 'anthropic', 'copilot', 'rag',
        '生成式', '机器学习', '招聘', '薪酬', '薪资',
        '阿里', '腾讯', '百度', '字节', '华为'),
    );
  }

  /** 弱相关词：仅用于打分，单靠它们不足以入选 */
  function bg_fde_weak() {
    return array(
      'en' => array('ai', 'artificial intelligence', 'model', 'startup', 'funding',
        'valuation', 'cloud', 'data', 'software', 'enterprise', 'robot', 'chip', 'gpu'),
      'zh' => array('人工智能', '模型', '创业', '融资', '估值', '云', '数据', '软件', '企业', '科技', '智能'),
    );
  }

  /** (中文分类, 英文分类): 触发关键词 */
  function bg_fde_cat_rules() {
    return array(
      array(array('薪酬观察', 'Compensation'), array('薪酬', '薪资', 'salary', 'pay', 'compensation')),
      array(array('资本动向', 'Capital & M&A'), array('融资', '估值', 'ipo', 'funding', 'raise', 'valuation', '并购', 'billion', 'million')),
      array(array('人才市场', 'Talent Market'), array('招聘', 'hire', 'role', '岗位', 'jobs', 'career', 'talent', '人才')),
      array(array('落地案例', 'Enterprise Adoption'), array('部署', '落地', '交付', 'deployment', 'embed', 'enterprise', '客户', 'customer')),
      array(array('政策合规', 'Regulation'), array('法规', '政策', 'policy', 'regulation', 'law', '法案')),
      array(array('安全', 'AI Security'), array('安全', 'security', 'safety', '攻击', 'breach', '漏洞')),
    );
  }

  function bg_fde_default_cat() {
    return array('zh' => '行业趋势', 'en' => 'Industry Trend');
  }

  /** 跳过「每日早讯/晚报」类大杂烩聚合稿 */
  function bg_fde_digest_re() {
    return '#早讯|午讯|早报|午报|晚报|日报|周报|要闻|速览|3分钟|盘点|汇总|晨报|1氪|开门红|热点导览|今日热点|晚间消息|财讯|早间#u';
  }

  function bg_fde_zero_stat() {
    return array('fetched' => 0, 'digest' => 0, 'dup' => 0, 'old' => 0, 'irrelevant' => 0, 'added' => 0);
  }

  function bg_fde_strip_html($s) {
    if ($s === null || $s === '') return '';
    $s = preg_replace('/<[^>]+>/', ' ', (string)$s);
    $s = html_entity_decode($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $s = preg_replace('/\s+/', ' ', $s);
    return trim($s);
  }

  function bg_fde_norm_link($u) {
    $u = trim((string)($u ?? ''));
    $u = preg_replace('/[?&]utm_[^=]*=[^&]*/', '', $u);
    $u = preg_replace('/[?&]spm=[^&]*/', '', $u);
    $u = preg_replace('/#.*$/', '', $u);
    $u = rtrim($u, '/');
    return strtolower($u);
  }

  function bg_fde_local_name($el) {
    $name = $el->getName();
    $pos = strrpos($name, ':');
    return $pos === false ? $name : substr($name, $pos + 1);
  }

  function bg_fde_child_text($node, $name) {
    foreach ($node->children() as $child) {
      if (bg_fde_local_name($child) === $name && (string)$child !== '') {
        return trim((string)$child);
      }
    }
    return '';
  }

  function bg_fde_link_of($node) {
    $links = array();
    foreach ($node->children() as $child) {
      if (bg_fde_local_name($child) !== 'link') continue;
      $attrs = $child->attributes();
      $href = isset($attrs['href']) ? trim((string)$attrs['href']) : '';
      $text = trim((string)$child);
      if ($href !== '') { $links[] = $href; }
      elseif ($text !== '') { $links[] = $text; }
    }
    // 优先返回 rel="alternate" 的链接（Atom 下才是文章地址），否则返回第一个
    foreach ($node->children() as $child) {
      if (bg_fde_local_name($child) !== 'link') continue;
      $attrs = $child->attributes();
      if (isset($attrs['rel']) && (string)$attrs['rel'] === 'alternate') {
        $href = trim((string)$attrs['href']);
        if ($href !== '') return $href;
      }
    }
    return count($links) ? $links[0] : '';
  }

  function bg_fde_collect_nodes($el, &$out) {
    foreach ($el->children() as $child) {
      $local = bg_fde_local_name($child);
      if ($local === 'item' || $local === 'entry') {
        $out[] = $child;
      }
      bg_fde_collect_nodes($child, $out);
    }
  }

  function bg_fde_extract($xml, $lang) {
    $out = array();
    $root = @simplexml_load_string($xml);
    if ($root === false) return $out;
    $nodes = array();
    bg_fde_collect_nodes($root, $nodes);
    foreach ($nodes as $node) {
      $title = bg_fde_child_text($node, 'title');
      $link = bg_fde_link_of($node);
      $summary = bg_fde_child_text($node, 'description');
      if ($summary === '') $summary = bg_fde_child_text($node, 'summary');
      if ($summary === '') $summary = bg_fde_child_text($node, 'content');
      $date = bg_fde_child_text($node, 'pubDate');
      if ($date === '') $date = bg_fde_child_text($node, 'published');
      if ($date === '') $date = bg_fde_child_text($node, 'updated');
      if ($date === '') $date = bg_fde_child_text($node, 'date');
      $out[] = array('title' => $title, 'link' => $link, 'summary' => $summary, 'date' => $date);
    }
    return $out;
  }

  function bg_fde_parse_date($s) {
    if ($s === null || $s === '') return null;
    $s = trim((string)$s);
    $ts = @strtotime($s);
    if ($ts === false) {
      if (preg_match('/(\d{4}-\d{2}-\d{2})/', $s, $m)) {
        $ts = @strtotime($m[1]);
      }
      if ($ts === false) return null;
    }
    $dt = new DateTime();
    $dt->setTimestamp($ts);
    return $dt;
  }

  function bg_fde_score($text, $lang) {
    $strong = bg_fde_strong();
    $weak = bg_fde_weak();
    $strong_hits = array();
    foreach ($strong[$lang] as $k) { if (strpos($text, $k) !== false) $strong_hits[] = $k; }
    $weak_hits = array();
    foreach ($weak[$lang] as $k) { if (strpos($text, $k) !== false) $weak_hits[] = $k; }
    $has_strong = count($strong_hits) > 0;
    $title_strong = false;
    foreach ($strong_hits as $k) { if (strpos(substr($text, 0, 120), $k) !== false) { $title_strong = true; break; } }
    $score = (($title_strong ? 4 : 2) * ($has_strong ? 1 : 0)) + count($weak_hits);
    return array($score, $has_strong);
  }

  function bg_fde_relevant($text, $lang) {
    list($score, $has_strong) = bg_fde_score($text, $lang);
    if ($has_strong) return true;
    return $score >= 2;
  }

  function bg_fde_category($text, $lang) {
    $def = bg_fde_default_cat();
    foreach (bg_fde_cat_rules() as $rule) {
      list($zc, $ec) = $rule[0];
      foreach ($rule[1] as $kw) {
        if (strpos($text, $kw) !== false) {
          return $lang === 'zh' ? $zc : $ec;
        }
      }
    }
    return $def[$lang];
  }

  function bg_fde_domain_of($url) {
    if (!preg_match('#https?://([^/]+)/?#', (string)$url, $m)) return 'RSS';
    return str_replace('www.', '', $m[1]);
  }

  function bg_fde_make_slug($item) {
    $lang = isset($item['lang']) ? $item['lang'] : 'zh';
    $title = isset($item['title'][$lang]) ? $item['title'][$lang] : '';
    $base = ($item['link'] ?? '') . '|' . $title . '|' . $lang;
    $digest = substr(sha1($base), 0, 8);
    $d = preg_replace('/[^0-9]/', '', $item['date'] ?? '');
    $d = substr($d, 0, 8);
    if ($d === '') $d = date('Ymd');
    return 'fde-' . $d . '-' . $digest;
  }

  function bg_fde_fetch($url) {
    if (!function_exists('bg_http_get')) return '';
    $r = bg_http_get($url, array('timeout' => 12));
    return !empty($r['ok']) ? $r['body'] : '';
  }

  function bg_fde_load_archive() {
    $path = defined('BG_NEWS_FILE') ? BG_NEWS_FILE : dirname(__FILE__) . '/../data/fde-archive.json';
    if (!file_exists($path)) return array();
    $raw = @file_get_contents($path);
    if ($raw === false) return array();
    $dec = json_decode($raw, true);
    if (isset($dec['items']) && is_array($dec['items'])) return $dec['items'];
    if (is_array($dec)) return $dec;
    return array();
  }

  function bg_fde_backup($path) {
    if (file_exists($path)) {
      @copy($path, $path . '.bak');
    }
  }

  function bg_fde_build_footer($items) {
    $out = array();
    $n = min(20, count($items));
    for ($i = 0; $i < $n; $i++) {
      $it = $items[$i];
      $out[] = array(
        'id' => 'ai-' . $i,
        'slug' => isset($it['slug']) ? $it['slug'] : '',
        'title' => isset($it['title']) ? $it['title'] : array('zh' => '', 'en' => ''),
        'summary' => isset($it['summary']) ? $it['summary'] : array('zh' => '', 'en' => ''),
        'category' => isset($it['category']) ? $it['category'] : array('zh' => '', 'en' => ''),
        'source' => isset($it['source']) ? $it['source'] : array('zh' => '', 'en' => ''),
        'date' => isset($it['date']) ? $it['date'] : '',
        'link' => isset($it['link']) ? $it['link'] : '',
      );
    }
    return $out;
  }

  /**
   * 执行一次 FDE 资讯刷新。
   * @param array $opts ['lang'=>both|zh|en, 'days'=>int, 'cap'=>int]
   * @return array 统计与新增条目
   */
  function bg_fde_refresh($opts = array()) {
    $langArg = isset($opts['lang']) ? $opts['lang'] : 'both';
    $days = isset($opts['days']) ? (int)$opts['days'] : 3;
    $cap = isset($opts['cap']) ? (int)$opts['cap'] : 12;

    $today = new DateTime();
    $cutoff = clone $today;
    $cutoff->modify('-' . $days . ' days');

    $archive = bg_fde_load_archive();
    $existingLinks = array('zh' => array(), 'en' => array());
    foreach ($archive as $it) {
      if (!is_array($it)) continue;
      $l = isset($it['lang']) ? $it['lang'] : 'zh';
      if (!empty($it['link'])) $existingLinks[$l][bg_fde_norm_link($it['link'])] = true;
    }

    $langs = ($langArg === 'both') ? array('zh', 'en') : array($langArg);
    $stats = array('zh' => bg_fde_zero_stat(), 'en' => bg_fde_zero_stat());
    $allNew = array();
    $feeds = bg_fde_feeds();

    foreach ($langs as $lang) {
      $stat = &$stats[$lang];
      $seen = array();
      $results = array();

      foreach ($feeds[$lang] as $feed) {
        $xml = bg_fde_fetch($feed);
        if ($xml === '') continue;
        $src = bg_fde_domain_of($feed);

        foreach (bg_fde_extract($xml, $lang) as $e) {
          $link = isset($e['link']) ? $e['link'] : '';
          if (!$link || !preg_match('#^https?://#i', $link)) continue;
          $nl = bg_fde_norm_link($link);
          if (isset($existingLinks[$lang][$nl]) || isset($seen[$nl])) { $stat['dup']++; continue; }

          $d = bg_fde_parse_date(isset($e['date']) ? $e['date'] : '');
          if ($d === null) $d = clone $today;
          if ($d->format('Y-m-d') > $today->format('Y-m-d')) { $stat['old']++; continue; }
          if ($d->format('Y-m-d') < $cutoff->format('Y-m-d')) { $stat['old']++; continue; }

          $title = bg_fde_strip_html(isset($e['title']) ? $e['title'] : '');
          if ($title === '') continue;
          if (preg_match(bg_fde_digest_re(), $title)) { $stat['digest']++; continue; }

          $summaryRaw = bg_fde_strip_html(isset($e['summary']) ? $e['summary'] : '');
          $summary = $summaryRaw !== ''
            ? mb_substr($summaryRaw, 0, 220, 'UTF-8') . (mb_strlen($summaryRaw, 'UTF-8') > 220 ? '…' : '')
            : $title;

          $stat['fetched']++;
          $text = mb_strtolower($title . ' ' . $summary, 'UTF-8');
          if (!bg_fde_relevant($text, $lang)) { $stat['irrelevant']++; continue; }

          list($score, $has_strong) = bg_fde_score($text, $lang);
          $cat = bg_fde_category($text, $lang);

          if ($lang === 'zh') {
            $titleD = array('zh' => $title, 'en' => '');
            $sumD = array('zh' => $summary, 'en' => '');
            $catD = array('zh' => $cat, 'en' => '');
            $srcD = array('zh' => $src, 'en' => '');
          } else {
            $titleD = array('zh' => '', 'en' => $title);
            $sumD = array('zh' => '', 'en' => $summary);
            $catD = array('zh' => '', 'en' => $cat);
            $srcD = array('zh' => '', 'en' => $src);
          }

          $results[] = array(
            'lang' => $lang,
            'title' => $titleD,
            'summary' => $sumD,
            'category' => $catD,
            'source' => $srcD,
            'date' => $d->format('Y-m-d'),
            'link' => $link,
            '_score' => array($score, $has_strong),
          );
          $seen[$nl] = true;
        }
      }

      usort($results, function ($a, $b) {
        $sa = $a['_score']; $sb = $b['_score'];
        if ($sb[0] != $sa[0]) return ($sb[0] > $sa[0]) ? 1 : -1;
        $hb = $sb[1] ? 1 : 0; $ha = $sa[1] ? 1 : 0;
        if ($hb != $ha) return $hb - $ha;
        return strcmp($b['date'], $a['date']);
      });

      if ($cap && count($results) > $cap) $results = array_slice($results, 0, $cap);
      foreach ($results as &$it) { unset($it['_score']); $stat['added']++; }
      unset($it);

      $allNew = array_merge($allNew, $results);
    }

    // 合并进归档（按 slug 去重，已存在则更新内容、保留最早 archivedAt）
    $bySlug = array();
    $order = array();
    foreach ($archive as $it) {
      if (!is_array($it) || empty($it['slug'])) continue;
      $bySlug[$it['slug']] = $it;
      $order[] = $it['slug'];
    }
    $added = 0; $updated = 0;
    foreach ($allNew as $it) {
      $it['archivedAt'] = date('Y-m-d');
      $slug = bg_fde_make_slug($it);
      $it['slug'] = $slug;
      if (isset($bySlug[$slug])) {
        $old = $bySlug[$slug];
        $it['archivedAt'] = isset($old['archivedAt']) ? $old['archivedAt'] : $it['archivedAt'];
        $bySlug[$slug] = $it;
        $updated++;
      } else {
        $bySlug[$slug] = $it;
        $order[] = $slug;
        $added++;
      }
    }
    $merged = array();
    foreach ($order as $slug) { $merged[] = $bySlug[$slug]; }
    usort($merged, function ($a, $b) {
      $da = isset($a['date']) ? $a['date'] : '';
      $db = isset($b['date']) ? $b['date'] : '';
      if ($da === $db) return strcmp(isset($b['archivedAt']) ? $b['archivedAt'] : '', isset($a['archivedAt']) ? $a['archivedAt'] : '');
      return strcmp($db, $da);
    });

    // 写归档
    $archivePath = defined('BG_NEWS_FILE') ? BG_NEWS_FILE : dirname(__FILE__) . '/../data/fde-archive.json';
    bg_fde_backup($archivePath);
    $archiveOut = array(
      'updatedAt' => date('Y-m-d'),
      'total' => count($merged),
      'items' => $merged,
    );
    @file_put_contents($archivePath, json_encode($archiveOut, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n");

    // 刷新 content.json 的 aiFdeNews / aiFdeNewsEn（与 Python 写入器保持一致）
    $contentPath = defined('BG_DATA_FILE') ? BG_DATA_FILE : dirname(__FILE__) . '/../data/content.json';
    if (file_exists($contentPath)) {
      $cj = json_decode(@file_get_contents($contentPath), true);
      if (is_array($cj)) {
        $zhItems = array(); $enItems = array();
        foreach ($merged as $it) {
          $l = isset($it['lang']) ? $it['lang'] : 'zh';
          if ($l === 'zh') $zhItems[] = $it; else $enItems[] = $it;
        }
        $cj['aiFdeNews'] = bg_fde_build_footer($zhItems);
        $cj['aiFdeNewsEn'] = bg_fde_build_footer($enItems);
        bg_fde_backup($contentPath);
        @file_put_contents($contentPath, json_encode($cj, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n");
      }
    }

    // 组装响应
    $newItems = array();
    foreach (array_slice($allNew, 0, 12) as $it) {
      $l = $it['lang'];
      $newItems[] = array(
        'lang' => $l,
        'date' => $it['date'],
        'title' => $it['title'][$l],
        'source' => $it['source'][$l],
      );
    }
    $zhCount = 0; $enCount = 0;
    foreach ($merged as $it) {
      if ((isset($it['lang']) ? $it['lang'] : 'zh') === 'zh') $zhCount++; else $enCount++;
    }

    return array(
      'ok' => true,
      'added' => $added,
      'updated' => $updated,
      'total' => count($merged),
      'zhCount' => $zhCount,
      'enCount' => $enCount,
      'stats' => $stats,
      'new_items' => $newItems,
    );
  }
}
