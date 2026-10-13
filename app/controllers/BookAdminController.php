<?php
// RF-15, RF-33
class BookAdminController extends Controller {
    function index() { $this->staff(); redirect('catalogo'); }
    function nuevo() { $this->staff(); $this->form(null); }
    function editar($id = 0) {
        $this->staff();
        $b = Book::find((int)$id);
        if (!$b) redirect('catalogo');
        $this->form($b);
    }
    private function form($b) {
        $categories = Book::categories(); $authors = Book::authors();
        $this->render('books/form', compact('b', 'categories', 'authors'), $b ? 'Editar libro' : 'Nuevo libro');
    }
    function guardar() {
        $this->staff(); $this->post();
        $id = (int)($_POST['id'] ?? 0);
        $d = array_map(fn($v) => is_string($v) ? trim($v) : $v, $_POST);
        $d['stock_min'] = max(0, (int)($d['stock_min'] ?? 1));
        if (mb_strlen($d['titulo'] ?? '') < 2) { flash('error', 'El título es obligatorio.'); redirect($id ? "libros/editar/$id" : 'libros/nuevo'); }
        if (($d['anio'] ?? '') !== '' && !preg_match('/^\d{4}$/', $d['anio'])) { flash('error', 'Año inválido.'); redirect($id ? "libros/editar/$id" : 'libros/nuevo'); }
        $d['portada'] = $this->uploadCover() ?: ($d['portada_url'] ?? '');
        if ($d['portada'] && !preg_match('#^(https?://|uploads/)#', $d['portada'])) $d['portada'] = '';
        $id = Book::save($id, $d);
        Audit::log('libro_guardado', "Libro #$id " . $d['titulo']);
        flash('ok', 'Libro guardado.');
        redirect("catalogo/ver/$id");
    }
    private function uploadCover() {
        if (empty($_FILES['portada']['tmp_name']) || $_FILES['portada']['error'] !== UPLOAD_ERR_OK) return '';
        if ($_FILES['portada']['size'] > 2 * 1024 * 1024) return '';
        $info = @getimagesize($_FILES['portada']['tmp_name']);
        $ext = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'][$info[2] ?? 0] ?? null;
        if (!$ext) return '';
        $name = bin2hex(random_bytes(8)) . '.' . $ext;
        $dir = ROOT . '/public/assets/uploads/covers';
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        return move_uploaded_file($_FILES['portada']['tmp_name'], "$dir/$name") ? "uploads/covers/$name" : '';
    }
    function eliminar($id = 0) {
        $this->staff(); $this->post();
        $r = Book::deactivate((int)$id);
        if ($r === true) Audit::log('libro_baja', "Libro #$id");
        $this->result($r, 'Libro dado de baja del catálogo.', 'catalogo');
    }
    function ejemplares($id = 0) {
        $this->staff(); $this->post();
        $n = max(1, (int)($_POST['cantidad'] ?? 1)); $u = Auth::user()['id'];
        if (($_POST['accion'] ?? '') === 'baja') {
            $r = Book::writeOff((int)$id, $n, $u, trim($_POST['nota'] ?? '') ?: 'Baja manual');
            if ($r === true) Audit::log('stock_baja', "Libro #$id: -$n");
            $this->result($r, "Se dieron de baja $n ejemplar(es).", "catalogo/ver/$id");
        }
        Book::addCopies((int)$id, $n, $u);
        Audit::log('stock_alta', "Libro #$id: +$n");
        flash('ok', "Se agregaron $n ejemplar(es).");
        redirect("catalogo/ver/$id");
    }
}
