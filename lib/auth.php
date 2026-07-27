<?php
/**
 * 后台鉴权：PBKDF2 密码校验 + HMAC-SHA256 无状态签名会话
 * 兼容 Next 版生成的 data/auth.json（{salt, hash, iterations}）
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

// ===== base64url =====
function bg_b64u_encode($bytes) {
  return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
}
function bg_b64u_decode($s) {
  $s = strtr((string) $s, '-_', '+/');
  $pad = strlen($s) % 4;
  if ($pad) $s .= str_repeat('=', 4 - $pad);
  return base64_decode($s);
}

// ===== PBKDF2 密码 =====
function bg_get_stored_auth() {
  if (!file_exists(BG_AUTH_FILE)) return null;
  $raw = @file_get_contents(BG_AUTH_FILE);
  if ($raw === false) return null;
  $data = json_decode($raw, true);
  if (is_array($data) && !empty($data['salt']) && !empty($data['hash']) && !empty($data['iterations'])) {
    return $data;
  }
  return null;
}

function bg_pbkdf2_hash($password, $salt_b64u, $iterations) {
  $salt = bg_b64u_decode($salt_b64u);
  $derived = hash_pbkdf2('sha256', (string) $password, $salt, (int) $iterations, 32, true);
  return bg_b64u_encode($derived);
}

/** 验证密码：优先 auth.json 的 PBKDF2，否则回退明文常量 */
function bg_verify_password($password) {
  $stored = bg_get_stored_auth();
  if ($stored) {
    $computed = bg_pbkdf2_hash($password, $stored['salt'], $stored['iterations']);
    return hash_equals($computed, $stored['hash']);
  }
  return (string) $password === (string) BG_ADMIN_PASS;
}

/** 生成新的 PBKDF2 存储对象（用于修改密码） */
function bg_hash_password($password) {
  $salt = random_bytes(16);
  $hash = hash_pbkdf2('sha256', (string) $password, $salt, 100000, 32, true);
  return array(
    'salt' => bg_b64u_encode($salt),
    'hash' => bg_b64u_encode($hash),
    'iterations' => 100000,
  );
}

function bg_save_auth($stored) {
  $dir = dirname(BG_AUTH_FILE);
  if (!is_dir($dir)) @mkdir($dir, 0755, true);
  return @file_put_contents(BG_AUTH_FILE, json_encode($stored, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX) !== false;
}

// ===== 会话（HMAC-SHA256 签名，无状态） =====
function bg_sign_session($user) {
  $payload = array('u' => $user, 'exp' => (time() + BG_SESSION_TTL) * 1000);
  $p = bg_b64u_encode(json_encode($payload));
  $sig = bg_b64u_encode(hash_hmac('sha256', $p, BG_SESSION_SECRET, true));
  return $p . '.' . $sig;
}

function bg_verify_session($token) {
  if (!$token || !is_string($token)) return null;
  $parts = explode('.', $token, 2);
  if (count($parts) !== 2) return null;
  list($p, $s) = $parts;
  $expected = bg_b64u_encode(hash_hmac('sha256', $p, BG_SESSION_SECRET, true));
  if (!hash_equals($expected, $s)) return null;
  $json = json_decode(bg_b64u_decode($p), true);
  if (!is_array($json) || empty($json['exp']) || (time() * 1000) > $json['exp']) return null;
  return isset($json['u']) ? $json['u'] : null;
}

function bg_login($user, $pass) {
  if ((string) $user !== (string) BG_ADMIN_USER) return false;
  if (!bg_verify_password($pass)) return false;
  $token = bg_sign_session($user);
  setcookie('bg_session', $token, array(
    'expires' => time() + BG_SESSION_TTL,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax',
    // 若有 HTTPS，建议追加 'secure' => true
  ));
  return true;
}

function bg_logout() {
  setcookie('bg_session', '', array(
    'expires' => time() - 3600,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax',
  ));
}

function bg_current_user() {
  if (empty($_COOKIE['bg_session'])) return null;
  return bg_verify_session($_COOKIE['bg_session']);
}

/** 要求登录：未登录时 API 返回 401、页面跳登录 */
function bg_require_auth() {
  $u = bg_current_user();
  if (!$u) {
    if (defined('BG_IS_API') && BG_IS_API) {
      bg_json(array('ok' => false, 'error' => 'unauthorized'), 401);
    } else {
      if (!headers_sent()) header('Location: ' . bg_url('/admin/login'));
      exit;
    }
  }
  return $u;
}
