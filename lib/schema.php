<?php
/**
 * 集合字段定义（对应 Next 版 content-schema.ts）
 * bilingual=true 的字段以 {zh,en} 存储
 */

$BG_COLLECTIONS = array(
  array(
    'type' => 'nav', 'label' => '导航菜单', 'singular' => '菜单项', 'singleton' => false,
    'fields' => array(
      array('key' => 'label', 'label' => '名称', 'type' => 'text', 'bilingual' => true),
      array('key' => 'href', 'label' => '链接', 'type' => 'text'),
      array('key' => 'children', 'label' => '子菜单（JSON，label 为 {zh,en}）', 'type' => 'json'),
    ),
  ),
  array(
    'type' => 'cases', 'label' => '精选案例', 'singular' => '案例', 'singleton' => false,
    'fields' => array(
      array('key' => 'title', 'label' => '标题', 'type' => 'text', 'bilingual' => true),
      array('key' => 'category', 'label' => '分类', 'type' => 'text', 'bilingual' => true),
      array('key' => 'description', 'label' => '简介', 'type' => 'textarea', 'bilingual' => true),
    ),
  ),
  array(
    'type' => 'threeds', 'label' => '三维动画作品', 'singular' => '作品', 'singleton' => false,
    'fields' => array(
      array('key' => 'title', 'label' => '标题', 'type' => 'text', 'bilingual' => true),
      array('key' => 'category', 'label' => '分类', 'type' => 'text', 'bilingual' => true),
      array('key' => 'description', 'label' => '简介', 'type' => 'textarea', 'bilingual' => true),
      array('key' => 'videoUrl', 'label' => '视频文件 URL（/videos/xxx.mp4）', 'type' => 'text'),
      array('key' => 'poster', 'label' => '封面图 URL（/posters/xxx.jpg）', 'type' => 'text'),
      array('key' => 'introSkip', 'label' => '片头 logo 跳过秒数（0 表示不跳过）', 'type' => 'text'),
    ),
  ),
  array(
    'type' => 'stats', 'label' => '公司数据指标', 'singular' => '指标', 'singleton' => false,
    'fields' => array(
      array('key' => 'value', 'label' => '数值', 'type' => 'text'),
      array('key' => 'label', 'label' => '说明', 'type' => 'text', 'bilingual' => true),
    ),
  ),
  array(
    'type' => 'plans', 'label' => '营销推广方案', 'singular' => '方案分组', 'singleton' => false,
    'fields' => array(
      array('key' => 'title', 'label' => '分组标题', 'type' => 'text', 'bilingual' => true),
      array('key' => 'items', 'label' => '项目（每行一项）', 'type' => 'stringlist', 'bilingual' => true),
    ),
  ),
  array(
    'type' => 'services', 'label' => '包年服务模块', 'singular' => '服务模块', 'singleton' => false,
    'fields' => array(
      array('key' => 'title', 'label' => '模块标题', 'type' => 'text', 'bilingual' => true),
      array('key' => 'items', 'label' => '项目（每行一项）', 'type' => 'stringlist', 'bilingual' => true),
    ),
  ),
  array(
    'type' => 'articles', 'label' => '干货文章', 'singular' => '文章', 'singleton' => false,
    'fields' => array(
      array('key' => 'title', 'label' => '标题', 'type' => 'text', 'bilingual' => true),
      array('key' => 'date', 'label' => '日期', 'type' => 'text'),
      array('key' => 'category', 'label' => '分类', 'type' => 'text', 'bilingual' => true),
    ),
  ),
  array(
    'type' => 'cities', 'label' => '服务城市', 'singular' => '城市', 'singleton' => false,
    'fields' => array(
      array('key' => 'name', 'label' => '城市名', 'type' => 'text', 'bilingual' => true),
    ),
  ),
  array(
    'type' => 'footerColumns', 'label' => '页脚栏目', 'singular' => '栏目', 'singleton' => false,
    'fields' => array(
      array('key' => 'title', 'label' => '栏目标题', 'type' => 'text', 'bilingual' => true),
      array('key' => 'links', 'label' => '链接（每行一项）', 'type' => 'stringlist', 'bilingual' => true),
    ),
  ),
  array(
    'type' => 'aiFdeNews', 'label' => 'AI FDE 最新信息', 'singular' => '资讯', 'singleton' => false,
    'fields' => array(
      array('key' => 'title', 'label' => '标题', 'type' => 'text', 'bilingual' => true),
      array('key' => 'summary', 'label' => '摘要', 'type' => 'textarea', 'bilingual' => true),
      array('key' => 'category', 'label' => '分类', 'type' => 'text', 'bilingual' => true),
      array('key' => 'source', 'label' => '来源媒体', 'type' => 'text', 'bilingual' => true),
      array('key' => 'date', 'label' => '日期', 'type' => 'text'),
      array('key' => 'link', 'label' => '原文链接', 'type' => 'text'),
    ),
  ),
  array(
    'type' => 'aiFdeNewsEn', 'label' => 'AI FDE 最新信息（英文·国际）', 'singular' => '资讯', 'singleton' => false,
    'fields' => array(
      array('key' => 'title', 'label' => '标题', 'type' => 'text', 'bilingual' => true),
      array('key' => 'summary', 'label' => '摘要', 'type' => 'textarea', 'bilingual' => true),
      array('key' => 'category', 'label' => '分类', 'type' => 'text', 'bilingual' => true),
      array('key' => 'source', 'label' => '来源媒体', 'type' => 'text', 'bilingual' => true),
      array('key' => 'date', 'label' => '日期', 'type' => 'text'),
      array('key' => 'link', 'label' => '原文链接', 'type' => 'text'),
    ),
  ),
  array(
    'type' => 'contact', 'label' => '联系信息', 'singular' => '联系信息', 'singleton' => true,
    'fields' => array(
      array('key' => 'address', 'label' => '地址', 'type' => 'text', 'bilingual' => true),
      array('key' => 'phoneWechat', 'label' => '电话 / 微信', 'type' => 'text'),
      array('key' => 'qqEmail', 'label' => 'QQ / Email', 'type' => 'text'),
    ),
  ),
  array(
    'type' => 'companyOverview', 'label' => '公司综述', 'singular' => '综述', 'singleton' => true,
    'fields' => array(
      array('key' => 'text', 'label' => '综述正文', 'type' => 'textarea', 'bilingual' => true),
    ),
  ),
  array(
    'type' => 'heroSlogans', 'label' => '首页标语', 'singular' => '标语', 'singleton' => false,
    'fields' => array(
      array('key' => 'value', 'label' => '标语内容', 'type' => 'text', 'bilingual' => true),
    ),
  ),
  array(
    'type' => 'inquiries', 'label' => '客户咨询', 'singular' => '咨询', 'singleton' => false,
    'fields' => array(
      array('key' => 'name', 'label' => '姓名', 'type' => 'text'),
      array('key' => 'phone', 'label' => '电话', 'type' => 'text'),
      array('key' => 'message', 'label' => '需求说明', 'type' => 'textarea'),
      array('key' => 'createdAt', 'label' => '提交时间', 'type' => 'text'),
    ),
  ),
  array(
    'type' => 'site', 'label' => '站点品牌', 'singular' => '站点设置', 'singleton' => true,
    'fields' => array(
      array('key' => 'brandZh', 'label' => '中文品牌名', 'type' => 'text'),
      array('key' => 'brandEn', 'label' => '英文品牌名', 'type' => 'text'),
      array('key' => 'logo', 'label' => 'Logo 图片 URL', 'type' => 'text'),
      array('key' => 'copyrightYearStart', 'label' => '版权起始年份', 'type' => 'text'),
      array('key' => 'copyrightOwner', 'label' => '版权所有方', 'type' => 'text', 'bilingual' => true),
      array('key' => 'icpRecord', 'label' => 'ICP 备案号', 'type' => 'text', 'bilingual' => true),
    ),
  ),
);

/** 取集合定义 */
function bg_get_collection_def($type) {
  global $BG_COLLECTIONS;
  foreach ($BG_COLLECTIONS as $c) {
    if ($c['type'] === $type) return $c;
  }
  return null;
}
