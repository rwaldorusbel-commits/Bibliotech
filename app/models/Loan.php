<?php
class Loan {
    static function find($id) { return DB::one('SELECT * FROM loans WHERE id=?', [$id]); }

    // RF-24, RF-25: préstamo desde una reserva o directo. Devuelve true o mensaje de error.
    static function create($uid, $bid, $staffId, $reservationId = 0) {
        $u = User::find($uid);
        if (!$u || $u['estado'] !== 'activo') return 'El usuario no está activo.';
        if (Fine::totalPending($uid) > 0) return 'El usuario tiene multas pendientes.';
        if (Reservation::activeCount($uid, (int)$reservationId) >= (int)Setting::get('max_prestamos', 3)) return 'El usuario alcanzó el límite de préstamos.';
        $pdo = DB::pdo();
        $pdo->beginTransaction();
        try {
            if ($reservationId) {
                $r = Reservation::find($reservationId);
                if (!$r || $r['user_id'] != $uid || $r['book_id'] != $bid || !in_array($r['estado'], ['pendiente', 'confirmada'], true)) { $pdo->rollBack(); return 'La reserva no es válida.'; }
                DB::exec("UPDATE reservations SET estado='recogida' WHERE id=?", [$reservationId]); // el stock ya se descontó al reservar
            } elseif (!Book::take($bid, $uid, 'prestamo', 'Préstamo directo')) { $pdo->rollBack(); return 'No hay ejemplares disponibles.'; }
            $id = DB::insert('INSERT INTO loans(user_id,book_id,reservation_id,librarian_id,loaned_at,due_date) VALUES(?,?,?,?,NOW(),DATE_ADD(CURDATE(),INTERVAL ? DAY))',
                [$uid, $bid, $reservationId ?: null, $staffId, (int)Setting::get('dias_prestamo', 7)]);
            if ($reservationId) Book::move($bid, 'prestamo', 1, $uid, 'Préstamo desde reserva #' . $reservationId);
            $pdo->commit();
        } catch (Throwable $e) { $pdo->rollBack(); return 'No se pudo registrar el préstamo.'; }
        Audit::log('prestamo_registrado', "Préstamo #$id libro #$bid usuario #$uid");
        return true;
    }
    // RF-26, RF-36, RF-37, RF-38
    static function giveBack($id, $cond, $staffId) {
        $l = self::find($id);
        if (!$l || $l['returned_at']) return 'El préstamo no existe o ya fue devuelto.';
        if (!in_array($cond, ['bueno', 'danado', 'perdido'], true)) return 'Estado de devolución inválido.';
        $pdo = DB::pdo();
        $pdo->beginTransaction();
        try {
            DB::exec('UPDATE loans SET returned_at=NOW(), estado_devolucion=? WHERE id=?', [$cond, $id]);
            if ($cond === 'bueno') Book::give($l['book_id'], $staffId, 'devolucion', "Devolución préstamo #$id");
            else Book::writeOff($l['book_id'], 1, $staffId, "Baja por ejemplar $cond (préstamo #$id)", false);
            $dias = (int)DB::val('SELECT GREATEST(DATEDIFF(CURDATE(), due_date),0) FROM loans WHERE id=?', [$id]);
            if ($dias > 0) Fine::create($l['user_id'], $id, 'retraso', $dias, $dias * (float)Setting::get('tarifa_diaria', 1.5));
            if ($cond === 'danado') Fine::create($l['user_id'], $id, 'dano', 0, (float)Setting::get('multa_dano', 20));
            if ($cond === 'perdido') Fine::create($l['user_id'], $id, 'perdida', 0, (float)Setting::get('multa_perdida', 60));
            $pdo->commit();
        } catch (Throwable $e) { $pdo->rollBack(); return 'No se pudo registrar la devolución.'; }
        Audit::log('devolucion_registrada', "Préstamo #$id ($cond)");
        return true;
    }
    // RF-27
    static function renew($id, $uid) {
        $l = self::find($id);
        if (!$l || $l['user_id'] != $uid || $l['returned_at']) return 'Préstamo no encontrado.';
        if (Fine::totalPending($uid) > 0) return 'No puedes renovar con multas pendientes.';
        if ($l['renovaciones'] >= (int)Setting::get('max_renovaciones', 2)) return 'Alcanzaste el máximo de renovaciones.';
        if (DB::val('SELECT CURDATE() > ?', [$l['due_date']])) return 'El préstamo está vencido: debes devolverlo.';
        $pend = DB::val("SELECT (SELECT COUNT(*) FROM reservations WHERE book_id=? AND user_id<>? AND estado IN ('pendiente','confirmada')) + (SELECT COUNT(*) FROM waitlist WHERE book_id=? AND user_id<>?)", [$l['book_id'], $uid, $l['book_id'], $uid]);
        if ($pend > 0) return 'No se puede renovar: otros usuarios esperan este libro.';
        DB::exec('UPDATE loans SET due_date=DATE_ADD(due_date, INTERVAL ? DAY), renovaciones=renovaciones+1, recordado=0 WHERE id=?', [(int)Setting::get('dias_prestamo', 7), $id]);
        Audit::log('prestamo_renovado', "Préstamo #$id");
        return true;
    }
    // RF-28
    static function byUser($uid, $onlyActive = false) {
        $w = $onlyActive ? 'AND l.returned_at IS NULL' : '';
        return DB::all("SELECT l.*, b.titulo, b.portada, a.nombre autor, (l.returned_at IS NULL AND l.due_date < CURDATE()) vencido FROM loans l JOIN books b ON b.id=l.book_id LEFT JOIN authors a ON a.id=b.author_id WHERE l.user_id=? $w ORDER BY l.id DESC", [$uid]);
    }
    static function allActive() {
        return DB::all("SELECT l.*, b.titulo, CONCAT(u.nombres,' ',u.apellidos) usuario, u.documento, (l.due_date < CURDATE()) vencido FROM loans l JOIN books b ON b.id=l.book_id JOIN users u ON u.id=l.user_id WHERE l.returned_at IS NULL ORDER BY l.due_date");
    }
    // RF-29: recordatorios (se ejecuta desde cli/cron.php)
    static function sendReminders() {
        $d = (int)Setting::get('dias_recordatorio', 2);
        $rows = DB::all('SELECT l.id, l.due_date, u.email, u.nombres, b.titulo FROM loans l JOIN users u ON u.id=l.user_id JOIN books b ON b.id=l.book_id WHERE l.returned_at IS NULL AND l.recordado=0 AND l.due_date<=DATE_ADD(CURDATE(), INTERVAL ? DAY)', [$d]);
        foreach ($rows as $r) {
            Mailer::send($r['email'], 'Recordatorio de devolución', "Hola {$r['nombres']}, el libro \"{$r['titulo']}\" debe devolverse el " . fd($r['due_date']) . '. Evita multas devolviéndolo a tiempo o renovándolo.');
            DB::exec('UPDATE loans SET recordado=1 WHERE id=?', [$r['id']]);
        }
        return count($rows);
    }
    // RF-43, RF-44
    static function stats() {
        return [
            'prestados' => (int)DB::val('SELECT COUNT(*) FROM loans WHERE returned_at IS NULL'),
            'reservas' => (int)DB::val("SELECT COUNT(*) FROM reservations WHERE estado IN ('pendiente','confirmada')"),
            'usuarios' => (int)DB::val('SELECT COUNT(*) FROM users'),
            'multas' => (float)DB::val("SELECT COALESCE(SUM(monto),0) FROM fines WHERE estado='pendiente'"),
        ];
    }
    static function topBooks($from, $to) {
        return DB::all('SELECT b.titulo, a.nombre autor, COUNT(*) total FROM loans l JOIN books b ON b.id=l.book_id LEFT JOIN authors a ON a.id=b.author_id WHERE DATE(l.loaned_at) BETWEEN ? AND ? GROUP BY b.id ORDER BY total DESC LIMIT 15', [$from, $to]);
    }
    static function perPeriod($from, $to) {
        return DB::all('SELECT DATE_FORMAT(loaned_at,"%Y-%m") periodo, COUNT(*) total FROM loans WHERE DATE(loaned_at) BETWEEN ? AND ? GROUP BY periodo ORDER BY periodo', [$from, $to]);
    }
    static function overdueUsers() {
        return DB::all("SELECT CONCAT(u.nombres,' ',u.apellidos) usuario, u.documento, u.email, b.titulo, l.due_date, DATEDIFF(CURDATE(), l.due_date) dias FROM loans l JOIN users u ON u.id=l.user_id JOIN books b ON b.id=l.book_id WHERE l.returned_at IS NULL AND l.due_date<CURDATE() ORDER BY dias DESC");
    }
    static function collected($from, $to) {
        return DB::all("SELECT f.comprobante, DATE(f.paid_at) fecha, CONCAT(u.nombres,' ',u.apellidos) usuario, f.motivo, f.monto FROM fines f JOIN users u ON u.id=f.user_id WHERE f.estado='pagada' AND DATE(f.paid_at) BETWEEN ? AND ? ORDER BY f.paid_at", [$from, $to]);
    }
}
