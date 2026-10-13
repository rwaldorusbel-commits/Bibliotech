<?php
define('ROOT', dirname(__DIR__));
define('APP', __DIR__);
$GLOBALS['config'] = require APP . '/config/config.php';
date_default_timezone_set('America/Lima');

$base = $GLOBALS['config']['base_url'];
if ($base === null) {
    $base = (PHP_SAPI === 'cli-server' || PHP_SAPI === 'cli') ? '' : rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
}
define('BASE_URL', $base);

spl_autoload_register(function ($c) {
    foreach (['core', 'models', 'controllers'] as $d) {
        $f = APP . "/$d/$c.php";
        if (is_file($f)) { require $f; return; }
    }
});
require APP . '/core/helpers.php';

if (PHP_SAPI !== 'cli') {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}
