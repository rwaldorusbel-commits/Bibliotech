<?php
class HomeController extends Controller {

    function index() {
        // Si NO está logueado → redirigir al LOGIN
        if (!Auth::check()) {
            redirect('auth/login');
        }

        // Si SÍ está logueado → redirigir según el rol
        $u = Auth::user();
        if (in_array($u['rol'], ['administrador', 'bibliotecario'])) {
            redirect('admin');
        }
        redirect('catalogo');
    }
}