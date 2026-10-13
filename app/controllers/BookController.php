<?php
class BookController extends Controller {
    // RF-10 a RF-14
    function index() {
        $f = array_intersect_key($_GET, array_flip(['q', 'categoria', 'autor', 'anio', 'disp', 'orden']));
        $page = max(1, (int)($_GET['p'] ?? 1));
        $per = cfg('per_page');
        [$books, $total] = Book::search($f, $page, $per);
        $categories = Book::categories(); $authors = Book::authors(); $years = Book::years();
        $this->render('catalog/index', compact('books', 'total', 'page', 'per', 'f', 'categories', 'authors', 'years'), 'Catálogo');
    }
    function ver($id = 0) {
        $b = Book::find((int)$id);
        if (!$b || (!$b['activo'] && !Auth::isStaff())) { http_response_code(404); $title = 'No encontrado'; ob_start(); require APP . '/views/errors/404.php'; $content = ob_get_clean(); require APP . '/views/layout.php'; return; }
        $inWait = Auth::check() && Book::inWaitlist(Auth::user()['id'], $b['id']);
        $this->render('catalog/show', compact('b', 'inWait'), $b['titulo']);
    }
}
