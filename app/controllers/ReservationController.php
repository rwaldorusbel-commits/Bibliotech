<?php
class ReservationController extends Controller {

    // RF-48 a RF-56: "Mis reservas"
    function index() {
        $this->requireLogin();
        $estado = in_array($_GET['estado'] ?? '', ['pendiente', 'confirmada', 'recogida', 'vencida', 'cancelada'], true) ? $_GET['estado'] : '';
        $orden = ($_GET['orden'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $reservations = Reservation::byUser(Auth::user()['id'], $estado, $orden);
        $this->render('reservations/index', compact('reservations', 'estado', 'orden'), 'Mis reservas');
    }

    // RF-17, RF-57
    function crear($bid = 0) {
        $this->requireLogin(); $this->post();
        $r = Reservation::create(Auth::user()['id'], (int)$bid);
        $this->result(
            $r,
            'Reserva creada. Tienes ' . Setting::get('plazo_recojo_horas', 48) . ' horas para recogerla.',
            $r === true ? 'reservas' : "catalogo/ver/" . (int)$bid
        );
    }

    function cancelar($id = 0) {
        $this->requireLogin(); $this->post();
        $this->result(Reservation::cancel((int)$id, Auth::user()['id']), 'Reserva cancelada.', 'reservas');
    }

    // RF-22
    function espera($bid = 0) {
        $this->requireLogin(); $this->post();
        $b = Book::find((int)$bid);
        if (!$b || $b['stock_disponible'] > 0) {
            flash('error', 'Este libro tiene ejemplares disponibles: puedes reservarlo.');
            redirect("catalogo/ver/" . (int)$bid);
        }
        Book::joinWaitlist(Auth::user()['id'], $b['id']);
        flash('ok', 'Te avisaremos por correo cuando haya un ejemplar disponible.');
        redirect("catalogo/ver/" . $b['id']);
    }

    // Gestión del bibliotecario (solo reservas activas)
    function gestionar() {
        $this->staff();
        $reservations = Reservation::allActive();
        $this->render('reservations/manage', compact('reservations'), 'Reservas activas');
    }

    // Vista de administración: TODAS las reservas (pendientes, confirmadas, vencidas, etc.)
    function admin() {
        $this->staff();
        $estado = in_array($_GET['estado'] ?? '', ['pendiente', 'confirmada', 'recogida', 'vencida', 'cancelada'], true) ? $_GET['estado'] : '';
        $reservations = Reservation::all($estado);
        $this->render('reservations/admin', compact('reservations', 'estado'), 'Gestión de reservas');
    }

    function confirmar($id = 0) {
        $this->staff(); $this->post();
        $this->result(Reservation::confirm((int)$id), 'Reserva confirmada y usuario notificado.', 'reservas/gestionar');
    }
}