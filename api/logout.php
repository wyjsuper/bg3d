<?php
/** POST /api/admin/logout */
define('BG_IS_API', true);
require_once __DIR__ . '/../lib/config.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/auth.php';

bg_logout();
bg_json(array('ok' => true));
