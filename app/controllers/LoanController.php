<?php
class LoanController extends Controller {
    // RF-28
    function index() {
        $this->requireLogin();
        $loans = Loan::byUser(Auth::user()['id']);
        $this->render('loans/index', compact('loans'), 'Mis préstamos');
    }
    function renovar($id = 0) {
        $this->requireLogin(); $this->post();
        $this->result(Loan::renew((int)$id, Auth::user()['id']), 'Préstamo renovado.', 'prestamos');
    }
    function gestionar() {
        $this->staff();
        $loans = Loan::allActive();
        $this->render('loans/manage', compact('loans'), 'Préstamos activos');
    }
    function nuevo() {
        $this->staff();
        $res = isset($_GET['reserva']) ? DB::one("SELECT r.*, u.documento, b.titulo, b.isbn FROM reservations r JOIN users u ON u.id=r.user_id JOIN books b ON b.id=r.book_id WHERE r.id=?", [(int)$_GET['reserva']]) : null;
        $books = DB::all('SELECT id, titulo, copies_available FROM books WHERE activo=1 ORDER BY titulo');
        $this->render('loans/new', compact('res', 'books'), 'Registrar préstamo');
    }
    function registrar() {
        $this->staff(); $this->post();
        $u = DB::one('SELECT id FROM users WHERE documento=?', [trim($_POST['documento'] ?? '')]);
        if (!$u) { flash('error', 'No existe un usuario con ese documento.'); redirect('prestamos/nuevo'); }
        $r = Loan::create($u['id'], (int)($_POST['book_id'] ?? 0), Auth::user()['id'], (int)($_POST['reservation_id'] ?? 0));
        $this->result($r, 'Préstamo registrado.', $r === true ? 'prestamos/gestionar' : 'prestamos/nuevo');
    }
    function devolver($id = 0) {
        $this->staff(); $this->post();
        $this->result(Loan::giveBack((int)$id, $_POST['estado'] ?? 'bueno', Auth::user()['id']), 'Devolución registrada.', 'prestamos/gestionar');
    }
}
