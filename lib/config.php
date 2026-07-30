<?php
/**
 * 站点全局配置 —— 虚拟主机部署时按需修改本文件
 * 注意：data/ 与 uploads/ 目录需 PHP 进程可写（chmod 755 或 775）
 */

// 数据文件路径（相对于本文件 ../data）
define('BG_DATA_FILE', __DIR__ . '/../data/content.json');
define('BG_AUTH_FILE', __DIR__ . '/../data/auth.json');

// 上传目录（CMS 上传文件存放处，需可写）
define('BG_UPLOAD_DIR', __DIR__ . '/../uploads');

// 默认站点品牌名（也可在后台「站点品牌」中覆盖）
define('BG_SITE_NAME_ZH', '北港3D数字营销设计');
define('BG_SITE_NAME_EN', 'BEIGANG 3D Digital Marketing Design');

// ===== 后台登录凭据 =====
// 默认：admin / admin123
// 修改方式：在 data/auth.json 中写入 PBKDF2 密码哈希（后台「修改密码」会自动生成），
// 或在此直接改下面的常量（回退凭据）。
if (!defined('BG_ADMIN_USER')) define('BG_ADMIN_USER', 'admin');
if (!defined('BG_ADMIN_PASS')) define('BG_ADMIN_PASS', 'admin123');

// 会话签名密钥：上线前务必改成一段随机长字符串（可用 php -r 'echo bin2hex(random_bytes(32));' 生成）
if (!defined('BG_SESSION_SECRET')) define('BG_SESSION_SECRET', 'change-this-session-secret-before-deploy-2026');

// 会话有效期（秒）：8 小时
if (!defined('BG_SESSION_TTL')) define('BG_SESSION_TTL', 8 * 3600);

// 时区
date_default_timezone_set('Asia/Shanghai');

// ===== 部署基础路径（站点相对网站根目录的目录） =====
// 例：网站根目录为 /web，本程序解压到 /web/3d，则自动识别为 '/3d'；
//     若直接解压到网站根目录，则识别为 ''（空）。
// 一般无需修改。个别环境自动识别异常时，可在引入本文件前 define('BG_BASE_MANUAL', '/3d') 或 '' 强制指定。
if (defined('BG_BASE_MANUAL')) {
  define('BG_BASE', BG_BASE_MANUAL);
} else {
  $bg_base = '';
  $bg_deploy_dir = dirname(__DIR__); // lib/ 的上一级 = 站点根目录
  $bg_deploy_basename = basename($bg_deploy_dir); // 站点根目录名，如 3d

  // 主检测：从请求的真实 URL 路径定位部署目录名。
  // 例：SCRIPT_NAME 或 REQUEST_URI 包含 /3d、部署目录名=3d → 基准路径=/3d
  // 不依赖 DOCUMENT_ROOT，规避虚拟主机的符号链接 / 路径大小写差异。
  if ($bg_deploy_basename !== '') {
    $marker = '/' . $bg_deploy_basename;
    foreach (array($_SERVER['SCRIPT_NAME'] ?? '', $_SERVER['REQUEST_URI'] ?? '') as $bg_path) {
      if ($bg_path === '') continue;
      $pos = strpos($bg_path, $marker);
      if ($pos !== false) {
        $bg_base = rtrim(str_replace('\\', '/', substr($bg_path, 0, $pos + strlen($marker))), '/');
        break;
      }
    }
  }

  // 兜底层：用 DOCUMENT_ROOT 与部署目录的差值（realpath + 统一斜杠，兼容 Windows 反斜杠）
  if ($bg_base === '') {
    $bg_doc_real = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
    $bg_dep_real = realpath($bg_deploy_dir);
    if ($bg_doc_real && $bg_dep_real) {
      $bg_doc_real = str_replace('\\', '/', $bg_doc_real);
      $bg_dep_real = str_replace('\\', '/', $bg_dep_real);
      if (strpos($bg_dep_real, $bg_doc_real) === 0) {
        $bg_base = rtrim(substr($bg_dep_real, strlen($bg_doc_real)), '/');
      }
    }
  }

  define('BG_BASE', $bg_base);
}

// ===== 在线更新（后台拉取 GitHub / 镜像最新发布包）=====
// 发布脚本会把 beigang-php-deploy.zip 作为 GitHub Release 附件上传。
// GitHub 仓库（owner/repo），例：'yourname/beigang-php'。留空则后台「在线更新」不可用。
if (!defined('BG_GITHUB_REPO')) define('BG_GITHUB_REPO', 'wyjsuper/bg3d');
// 内置 CA 证书（让任何环境含沙箱都能验证 GitHub HTTPS，真实主机有系统 CA 时此文件可忽略）
if (!defined('BG_CACERT')) define('BG_CACERT', __DIR__ . '/cacert.pem');
// 更新信息源：'github'（默认，用 GitHub API 查 latest release）或 'custom'（用下面的信息接口）。
if (!defined('BG_UPDATE_SOURCE')) define('BG_UPDATE_SOURCE', 'github');
// custom 模式下返回 JSON {tag,name,published_at,download_url} 的接口地址（可用 Gitee/自建以适配国内网络）。
if (!defined('BG_UPDATE_INFO_URL')) define('BG_UPDATE_INFO_URL', '');
// 是否允许「全量覆盖」（含 data/content.json、uploads、auth.json）。默认 false，更新时仅覆盖代码并保留运营数据。
if (!defined('BG_UPDATE_ALLOW_FULL')) define('BG_UPDATE_ALLOW_FULL', false);
