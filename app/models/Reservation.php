<?php
class Reservation {
    const ACTIVE = "('pendiente','confirmada')";

    static function find($id) { return DB::one('SELECT * FROM reservations WHERE id=?', [$id]); }

    // RF-17, RF-18, RF-41, RF-58. Devuelve true o un mensaje de error.
    static function create($uid, $bid) {
        $u = User::find($uid);
        if (!$u || $u['estado'] !== 'activo') return 'Tu cuenta no está activa: no puedes reservar.';
        if (Fine::totalPending($uid) > 0) return 'Tienes multas pendientes. Págalas para volver a reservar.';
        $b = Book::find($bid);
        if (!$b || !$b['activo']) return 'El libro no existe.';
        if (DB::val('SELECT COUNT(*) FROM reservations WHERE user_id=? AND book_id=? AND estado IN ' . self::ACTIVE, [$uid, $bid]) > 0)
            return 'Ya tienes una reserva activa de este libro.';
        if (self::activeCount($uid) >= (int)Setting::get('max_prestamos', 3))
            return 'Alcanzaste el límite de reservas y préstamos activos permitido.';
        $pdo = DB::pdo();
        $pdo->beginTransaction();
        try {
            if (!Book::take($bid, $uid, 'reserva', 'Reserva en línea')) { $pdo->rollBack(); return 'No hay ejemplares disponibles.'; }
            DB::insert('INSERT INTO reservations(user_id,book_id,estado,created_at,expires_at) VALUES(?,?,?,NOW(),DATE_ADD(NOW(),INTERVAL ? HOUR))',
                [$uid, $bid, 'pendiente', (int)Setting::get('plazo_recojo_horas', 48)]);
            DB::exec('DELETE FROM waitlist WHERE user_id=? AND book_id=?', [$uid, $bid]);
            $pdo->commit();
        } catch (Throwable $e) { $pdo->rollBack(); return 'No se pudo crear la reserva. Inténtalo de nuevo.'; }
        Audit::log('reserva_creada', "Libro #$bid", $uid);
        Mailer::send($u['email'], 'Reserva registrada: ' . $b['titulo'],
            "Hola {$u['nombres']}, reservaste \"{$b['titulo']}\". Tienes " . Setting::get('plazo_recojo_horas', 48) . ' horas para recogerlo en la biblioteca.');
        return true;
    }
    static function activeCount($uid, $excludeReservation = 0) {
        return (int)DB::val('SELECT (SELECT COUNT(*) FROM reservations WHERE user_id=? AND id<>? AND estado IN ' . self::ACTIVE . ') + (SELECT COUNT(*) FROM loans WHERE user_id=? AND returned_at IS NULL)', [$uid, $excludeReservation, $uid]);
    }
    // RF-20, RF-48, RF-49, RF-54
    static function byUser($uid, $estado = '', $orden = 'desc') {
        $w = ['r.user_id=?']; $p = [$uid];
        if ($estado) { $w[] = 'r.estado=?'; $p[] = $estado; }
        $dir = $orden === 'asc' ? 'ASC' : 'DESC';
        return DB::all("SELECT r.*, b.titulo, b.portada, a.nombre autor, TIMESTAMPDIFF(SECOND, NOW(), r.expires_at) secs_left
            FROM reservations r JOIN books b ON b.id=r.book_id LEFT JOIN authors a ON a.id=b.author_id
            WHERE " . implode(' AND ', $w) . " ORDER BY r.created_at $dir, r.id $dir", $p);
    }
    // RF-21, RF-53: solo reservas pendientes
    static function cancel($id, $uid) {
        $r = self::find($id);
        if (!$r || $r['user_id'] != $uid) return 'Reserva no encontrada.';
        if ($r['estado'] !== 'pendiente') return 'Solo se pueden cancelar reservas pendientes.';
        $n = DB::exec("UPDATE reservations SET estado='cancelada' WHERE id=? AND estado='pendiente'", [$id]);
        if ($n) { Book::give($r['book_id'], $uid, 'liberacion', 'Reserva cancelada'); Audit::log('reserva_cancelada', "Reserva #$id"); }
        return true;
    }
    static function confirm($id) {
        $r = DB::one('SELECT r.*, u.email, u.nombres, b.titulo FROM reservations r JOIN users u ON u.id=r.user_id JOIN books b ON b.id=r.book_id WHERE r.id=?', [$id]);
        if (!$r || $r['estado'] !== 'pendiente') return 'La reserva no está pendiente.';
        DB::exec("UPDATE reservations SET estado='confirmada' WHERE id=?", [$id]);
        Audit::log('reserva_confirmada', "Reserva #$id");
        Mailer::send($r['email'], 'Reserva confirmada: ' . $r['titulo'], "Hola {$r['nombres']}, tu reserva de \"{$r['titulo']}\" fue confirmada. Pasa a recogerla antes del " . fdt($r['expires_at']) . '.');
        return true;
    }
    // RF-19, RF-59: vence reservas y devuelve el ejemplar al stock
    static function expireDue() {
        $rows = DB::all("SELECT id, book_id, user_id FROM reservations WHERE estado IN " . self::ACTIVE . " AND expires_at<=NOW()");
        foreach ($rows as $r) {
            if (DB::exec("UPDATE reservations SET estado='vencida' WHERE id=? AND estado IN " . self::ACTIVE, [$r['id']])) {
                Book::give($r['book_id'], null, 'liberacion', 'Reserva vencida #' . $r['id']);
            }
        }
        return count($rows);
    }
    // RF-23: aviso de reservas por vencer (se ejecuta desde cli/cron.php)
    static function sendExpiryWarnings() {
        $rows = DB::all("SELECT r.id, r.expires_at, u.email, u.nombres, b.titulo FROM reservations r JOIN users u ON u.id=r.user_id JOIN books b ON b.id=r.book_id WHERE r.estado IN " . self::ACTIVE . " AND r.aviso=0 AND r.expires_at<=DATE_ADD(NOW(), INTERVAL 12 HOUR)");
        foreach ($rows as $r) {
            Mailer::send($r['email'], 'Tu reserva está por vencer', "Hola {$r['nombres']}, tu reserva de \"{$r['titulo']}\" vence el " . fdt($r['expires_at']) . '.');
            DB::exec('UPDATE reservations SET aviso=1 WHERE id=?', [$r['id']]);
        }
        return count($rows);
    }
    static function allActive() {
        return DB::all("SELECT r.*, b.titulo, CONCAT(u.nombres,' ',u.apellidos) usuario, u.documento, TIMESTAMPDIFF(SECOND, NOW(), r.expires_at) secs_left
            FROM reservations r JOIN books b ON b.id=r.book_id JOIN users u ON u.id=r.user_id WHERE r.estado IN " . self::ACTIVE . " ORDER BY r.expires_at");
    }
}
