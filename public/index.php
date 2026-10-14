<?php
// Ocultar warnings y errores para que no se vean feas las páginas
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);

// Punto de entrada del sistema BiblioTech
require_once __DIR__ . '/../app/bootstrap.php';

// Arrancar el enrutador
$router = new Router();
$router->dispatch();