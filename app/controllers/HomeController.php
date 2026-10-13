<?php
class HomeController extends Controller {
    function index() {
        $stats = ['libros' => (int)DB::val('SELECT COALESCE(SUM(copies_total),0) FROM books WHERE activo=1'), 'titulos' => (int)DB::val('SELECT COUNT(*) FROM books WHERE activo=1')];
        $this->render('home/index', compact('stats'), 'Inicio');
    }
}
