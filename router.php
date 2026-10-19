<?php
// Local development: php -S localhost:8000 router.php
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if (preg_match('#^/(app|storage)(/|$)|\.(sqlite|md)$|^/config#', $path)) {
    http_response_code(403);
    exit('Forbidden');
}
if (preg_match('#^/uploads/.*\.(php\d?|phtml|phar)$#i', $path)) {
    http_response_code(403);
    exit('Forbidden');
}
$file = __DIR__ . $path;
if ($path !== '/' && is_file($file)) return false;
require __DIR__ . '/index.php';
