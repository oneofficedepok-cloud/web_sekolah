<?php
declare(strict_types=1);

define('ROOT', dirname(__DIR__));
define('APP', ROOT . '/app');
define('UPLOAD_DIR', ROOT . '/uploads');

$configFile = ROOT . '/config.php';
$CONFIG = file_exists($configFile) ? require $configFile : require ROOT . '/config.sample.php';

date_default_timezone_set($CONFIG['timezone'] ?? 'Asia/Jakarta');
error_reporting(E_ALL);
ini_set('display_errors', !empty($CONFIG['debug']) ? '1' : '0');

if (session_status() === PHP_SESSION_NONE) {
    session_name('mtscms');
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    ]);
    session_start();
}

require APP . '/helpers.php';
require APP . '/db.php';
require APP . '/sanitize.php';
require APP . '/blocks.php';
require APP . '/auth.php';
require APP . '/upload.php';

db_migrate();
