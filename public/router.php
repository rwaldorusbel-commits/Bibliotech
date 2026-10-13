<?php
// Solo para el servidor embebido: php -S localhost:8000 -t public public/router.php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($path !== '/' && is_file(__DIR__ . $path)) return false;
$_GET['url'] = ltrim($path, '/');
require __DIR__ . '/index.php';
