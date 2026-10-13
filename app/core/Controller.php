<?php
abstract class Controller {
    protected function render($view, $data = [], $title = '') {
        extract($data);
        ob_start(); require APP . "/views/$view.php"; $content = ob_get_clean();
        require APP . '/views/layout.php';
    }
    protected function requireLogin() {
        if (!Auth::check()) { flash('error', 'Debes iniciar sesión para continuar.'); redirect('auth/login'); }
    }
    protected function requireRole(...$roles) {
        $this->requireLogin();
        if (!in_array(Auth::user()['rol'], $roles, true)) {
            http_response_code(403);
            $title = 'Acceso denegado';
            ob_start(); require APP . '/views/errors/403.php'; $content = ob_get_clean();
            require APP . '/views/layout.php'; exit;
        }
    }
    protected function staff() { $this->requireRole('bibliotecario', 'administrador'); }
    protected function admin() { $this->requireRole('administrador'); }
    protected function post() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_check()) { http_response_code(400); exit('Solicitud inválida.'); }
    }
    protected function result($r, $okMsg, $to) {
        if ($r === true) flash('ok', $okMsg); else flash('error', $r);
        redirect($to);
    }
}
