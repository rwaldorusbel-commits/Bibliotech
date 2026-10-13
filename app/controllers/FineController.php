<?php
class FineController extends Controller {
    // RF-60 a RF-62: "Mis multas"
    function index() {
        $this->requireLogin();
        $fines = Fine::byUser(Auth::user()['id']);
        $total = Fine::totalPending(Auth::user()['id']);
        $this->render('fines/index', compact('fines', 'total'), 'Mis multas');
    }
    function gestionar() {
        $this->staff();
        $estado = in_array($_GET['estado'] ?? 'pendiente', ['pendiente', 'pagada', 'condonada', 'anulada', ''], true) ? ($_GET['estado'] ?? 'pendiente') : 'pendiente';
        $fines = Fine::all($estado);
        $this->render('fines/manage', compact('fines', 'estado'), 'Gestión de multas');
    }
    function pagar($id = 0) {
        $this->staff(); $this->post();
        if (Fine::pay((int)$id, Auth::user()['id'])) { flash('ok', 'Pago registrado.'); redirect('multas/comprobante/' . (int)$id); }
        flash('error', 'La multa no está pendiente.'); redirect('multas/gestionar');
    }
    function comprobante($id = 0) {
        $this->requireLogin();
        $f = Fine::find((int)$id);
        if (!$f || $f['estado'] !== 'pagada' || (!Auth::isStaff() && $f['user_id'] != Auth::user()['id'])) redirect('multas');
        $this->render('fines/receipt', compact('f'), 'Comprobante');
    }
    function anular($id = 0) {
        $this->admin(); $this->post();
        $this->result(Fine::waive((int)$id, $_POST['tipo'] ?? '', $_POST['justificacion'] ?? ''), 'Multa actualizada.', 'multas/gestionar');
    }
}
