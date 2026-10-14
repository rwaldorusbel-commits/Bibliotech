<?php
define('ROOT', dirname(__DIR__));
define('APP', __DIR__);
$GLOBALS['config'] = require APP . '/config/config.php';
date_default_timezone_set('America/Lima');

// =====================================================
// DETECCIÓN AUTOMÁTICA DE BASE_URL
// Funciona tanto en:
//   - http://192.168.0.113/          (raíz)
//   - http://localhost/BiblioTech/public/  (subcarpeta)
// =====================================================
$base = $GLOBALS['config']['base_url'];

if ($base === null || $base === '') {
    if (PHP_SAPI === 'cli-server' || PHP_SAPI === 'cli') {
        $base = '';
    } else {
        $script = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        $script = str_replace('\\', '/', $script);
        // Si el script está en la raíz, no añadir nada
        $base = ($script === '/' || $script === '.' || $script === '') ? '' : rtrim($script, '/');
    }
}

define('BASE_URL', $base);

// =====================================================
// AUTOLOADER: carga clases desde core, models y controllers
// =====================================================
spl_autoload_register(function ($c) {
    foreach (['core', 'models', 'controllers'] as $d) {
        $f = APP . "/$d/$c.php";
        if (is_file($f)) { require $f; return; }
    }
});

// Cargar helpers globales
require APP . '/core/helpers.php';

// =====================================================
// SESIÓN
// =====================================================
if (PHP_SAPI !== 'cli') {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}