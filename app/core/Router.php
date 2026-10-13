<?php
class Router {
    const MAP = [
        '' => 'HomeController', 'auth' => 'AuthController', 'catalogo' => 'BookController',
        'libros' => 'BookAdminController', 'reservas' => 'ReservationController',
        'prestamos' => 'LoanController', 'multas' => 'FineController', 'admin' => 'AdminController',
    ];
    function dispatch() {
        $this->maintenance();
        $url = trim($_GET['url'] ?? '', '/');
        $p = $url === '' ? [] : explode('/', $url);
        $class = self::MAP[$p[0] ?? ''] ?? null;
        $action = isset($p[1]) ? lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $p[1])))) : 'index';
        $args = array_slice($p, 2);
        if ($class && method_exists($class, $action) && (new ReflectionMethod($class, $action))->isPublic()) {
            $ctrl = new $class();
            try { $ctrl->$action(...$args); }
            catch (ArgumentCountError $e) { $this->notFound(); }
            return;
        }
        $this->notFound();
    }
    private function notFound() {
        http_response_code(404);
        $title = 'No encontrado';
        ob_start(); require APP . '/views/errors/404.php'; $content = ob_get_clean();
        require APP . '/views/layout.php';
    }
    // Respaldo del proceso programado (RF-59): se ejecuta como máximo una vez por minuto
    private function maintenance() {
        $f = ROOT . '/storage/last_maintenance';
        if (!is_file($f) || time() - filemtime($f) > 60) {
            @touch($f);
            try { Reservation::expireDue(); } catch (Throwable $e) {}
        }
    }
}
