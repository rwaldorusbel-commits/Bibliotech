<?php
class AdminController extends Controller {
    // RF-43: panel de control (bibliotecario y administrador)
    function index() {
        $this->staff();
        $stats = Loan::stats(); $low = Book::lowStock(); $overdue = Loan::overdueUsers();
        $this->render('admin/dashboard', compact('stats', 'low', 'overdue'), 'Panel de control');
    }
    // RF-07, RF-09
    function usuarios() {
        $this->admin();
        $users = User::search(trim($_GET['q'] ?? ''));
        $this->render('admin/users', compact('users'), 'Usuarios');
    }
    function usuarioEstado($id = 0) {
        $this->admin(); $this->post();
        $id = (int)$id;
        if ($id === Auth::user()['id']) { flash('error', 'No puedes modificar tu propia cuenta.'); redirect('admin/usuarios'); }
        if (in_array($_POST['estado'] ?? '', ['activo', 'inactivo', 'suspendido'], true)) { User::setStatus($id, $_POST['estado']); Audit::log('usuario_estado', "Usuario #$id -> " . $_POST['estado']); }
        if (in_array($_POST['rol'] ?? '', ['estudiante', 'bibliotecario', 'administrador'], true)) { User::setRole($id, $_POST['rol']); Audit::log('usuario_rol', "Usuario #$id -> " . $_POST['rol']); }
        flash('ok', 'Usuario actualizado.');
        redirect('admin/usuarios');
    }
    // RF-47, RF-42 (tarifas)
    function configuracion() {
        $this->admin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->post();
            foreach (Setting::all() as $s) {
                $v = trim($_POST[$s['clave']] ?? '');
                if ($v !== '' && is_numeric($v) && $v >= 0) Setting::set($s['clave'], $v);
            }
            Audit::log('configuracion', 'Parámetros actualizados');
            flash('ok', 'Configuración guardada.');
            redirect('admin/configuracion');
        }
        $this->render('admin/settings', ['settings' => Setting::all()], 'Configuración');
    }
    // RF-16
    function categorias() { $this->taxonomy('categories', 'categorías', 'categorias'); }
    function autores() { $this->taxonomy('authors', 'autores', 'autores'); }
    private function taxonomy($table, $label, $route) {
        $this->admin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->post();
            $name = trim($_POST['nombre'] ?? '');
            if (isset($_POST['borrar'])) { DB::exec("DELETE FROM $table WHERE id=?", [(int)$_POST['borrar']]); flash('ok', 'Eliminado.'); }
            elseif (!empty($_POST['editar_id']) && $name !== '') { DB::exec("UPDATE $table SET nombre=? WHERE id=?", [$name, (int)$_POST['editar_id']]); flash('ok', 'Actualizado.'); }
            elseif ($name !== '') { DB::exec("INSERT IGNORE INTO $table(nombre) VALUES(?)", [$name]); flash('ok', 'Agregado.'); }
            Audit::log("gestion_$table", $name);
            redirect("admin/$route");
        }
        $items = DB::all("SELECT t.*, (SELECT COUNT(*) FROM books b WHERE b." . ($table === 'authors' ? 'author_id' : 'category_id') . "=t.id) usos FROM $table t ORDER BY nombre");
        $this->render('admin/taxonomy', compact('items', 'label', 'route'), ucfirst($label));
    }
    // RF-44, RF-45
    function reportes() {
        $this->admin();
        [$from, $to] = $this->range();
        $data = $this->reportData($from, $to);
        $this->render('admin/reports', compact('data', 'from', 'to'), 'Reportes');
    }
    private function range() {
        $from = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['desde'] ?? '') ? $_GET['desde'] : date('Y-m-01');
        $to = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['hasta'] ?? '') ? $_GET['hasta'] : date('Y-m-d');
        return [$from, $to];
    }
    private function reportData($from, $to) {
        return [
            'top' => ['Libros más solicitados', ['Título', 'Autor', 'Préstamos'], array_map('array_values', Loan::topBooks($from, $to))],
            'periodo' => ['Préstamos por periodo', ['Periodo', 'Préstamos'], array_map('array_values', Loan::perPeriod($from, $to))],
            'morosos' => ['Usuarios morosos', ['Usuario', 'Documento', 'Correo', 'Libro', 'Vencía', 'Días de retraso'], array_map('array_values', Loan::overdueUsers())],
            'recaudado' => ['Recaudado por multas', ['Comprobante', 'Fecha', 'Usuario', 'Motivo', 'Monto (S/)'], array_map('array_values', Loan::collected($from, $to))],
        ];
    }
    // Exportación: CSV (abre en Excel). Para PDF, la vista de reportes usa "Imprimir / Guardar como PDF".
    function exportar($tipo = '') {
        $this->admin();
        [$from, $to] = $this->range();
        $data = $this->reportData($from, $to);
        if (!isset($data[$tipo])) redirect('admin/reportes');
        [$title, $head, $rows] = $data[$tipo];
        header('Content-Type: text/csv; charset=UTF-8');
        header("Content-Disposition: attachment; filename=\"reporte_{$tipo}_{$from}_{$to}.csv\"");
        echo "\xEF\xBB\xBF";
        $o = fopen('php://output', 'w');
        fputcsv($o, [$title . " ($from a $to)"], ';');
        fputcsv($o, $head, ';');
        foreach ($rows as $r) fputcsv($o, $r, ';');
        Audit::log('reporte_exportado', $tipo);
        exit;
    }
    // RF-46, RF-35
    function bitacora() {
        $this->admin();
        $this->render('admin/audit', ['rows' => Audit::recent()], 'Bitácora');
    }
    function stock() {
        $this->staff();
        $this->render('admin/stock', ['rows' => Book::movements(), 'low' => Book::lowStock()], 'Movimientos de stock');
    }
}
