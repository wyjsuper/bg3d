<?php
/** POST /api/upload（需登录，multipart/form-data: file, dir） */
define('BG_IS_API', true);
require_once __DIR__ . '/../lib/config.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/auth.php';

bg_require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') bg_json(array('error' => 'method not allowed'), 405);

$allowedDirs = array('videos', 'posters', 'images', 'logos');
$maxSize = 500 * 1024 * 1024; // 500 MB

$dir = 'videos';
if (isset($_POST['dir']) && in_array($_POST['dir'], $allowedDirs, true)) {
  $dir = $_POST['dir'];
}

if (!isset($_FILES['file']) || !is_array($_FILES['file']) || $_FILES['file']['error'] === UPLOAD_ERR_NO_FILE) {
  bg_json(array('error' => 'No file provided'), 400);
}

$file = $_FILES['file'];
if ($file['error'] !== UPLOAD_ERR_OK) {
  $msg = ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE)
    ? 'File too large (超出服务器 upload_max_filesize)'
    : ('Upload error code ' . $file['error']);
  bg_json(array('error' => $msg), 400);
}
if ($file['size'] <= 0) bg_json(array('error' => 'No file provided'), 400);
if ($file['size'] > $maxSize) bg_json(array('error' => 'File too large (max 500 MB)'), 413);

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if ($ext === '') $ext = 'bin';
$base = base_convert((string) time(), 10, 36) . '-' . substr(bin2hex(random_bytes(4)), 0, 6);
$fileName = $base . '.' . $ext;

$outDir = __DIR__ . '/../' . $dir;
if (!is_dir($outDir)) @mkdir($outDir, 0755, true);
$outPath = $outDir . '/' . $fileName;

if (!@move_uploaded_file($file['tmp_name'], $outPath)) {
  bg_json(array('error' => '写入失败，请检查 ' . $dir . ' 目录写权限'), 500);
}

bg_json(array('url' => bg_url('/' . $dir . '/' . $fileName)));
