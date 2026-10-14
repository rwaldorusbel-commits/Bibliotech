<?php
class Reservation {
    const ACTIVE = "('pendiente','confirmada')";

    static function find($id) {
        return DB::one('SELECT * FROM reservas WHERE id=?', [$id]);
    }

    // RF-17, RF-18, RF-41, RF-58. Devuelve true o un mensaje de error.
    static function create($uid, $bid) {
        $u = User::find($uid);
        if (!$u || $u['estado'] !== 'activo') return 'Tu cuenta no está activa: no puedes reservar.';
        if (Fine::totalPending($uid) > 0) return 'Tienes multas pendientes. Págalas para volver a reservar.';

        $b = Book::find($bid);
        if (!$b || !$b['activo']) return 'El libro no existe.';

        if (DB::val('SELECT COUNT(*) FROM reservas WHERE id_usuario=? AND id_libro=? AND estado IN ' . self::ACTIVE, [$uid, $bid]) > 0)
            return 'Ya tienes una reserva activa de este libro.';

        if (self::activeCount($uid) >= (int)Setting::get('max_prestamos', 3))
            return 'Alcanzaste el límite de reservas y préstamos activos permitido.';

        $pdo = DB::pdo();
        $pdo->beginTransaction();
        try {
            if (!Book::take($bid, $uid, 'reserva', 'Reserva en línea')) {
                $pdo->rollBack();
                return 'No hay ejemplares disponibles.';
            }

            $horas = (int)Setting::get('plazo_recojo_horas', 48);
            DB::insert(
                'INSERT INTO reservas (id_usuario, id_libro, estado, fecha_reserva, fecha_expiracion) 
                 VALUES (?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL ? HOUR))',
                [$uid, $bid, 'pendiente', $horas]
            );

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            return 'No se pudo crear la reserva. Inténtalo de nuevo.';
        }

        Audit::log('reserva_creada', "Libro #$bid", $uid);
        Mailer::send(
            $u['email'],
            'Reserva registrada: ' . $b['titulo'],
            "Hola {$u['nombres']}, reservaste \"{$b['titulo']}\". Tienes " . Setting::get('plazo_recojo_horas', 48) . ' horas para recogerlo en la biblioteca.'
        );
        return true;
    }

    static function activeCount($uid, $excludeReservation = 0) {
        return (int)DB::val(
            'SELECT (SELECT COUNT(*) FROM reservas WHERE id_usuario=? AND id<>? AND estado IN ' . self::ACTIVE . ') 
                    + (SELECT COUNT(*) FROM prestamos WHERE id_usuario=? AND fecha_devolucion_real IS NULL)',
            [$uid, $excludeReservation, $uid]
        );
    }

    // RF-20, RF-48, RF-49, RF-54
    static function byUser($uid, $estado = '', $orden = 'desc') {
        $w = ['r.id_usuario=?'];
        $p = [$uid];
        if ($estado) { $w[] = 'r.estado=?'; $p[] = $estado; }
        $dir = $orden === 'asc' ? 'ASC' : 'DESC';
        return DB::all(
            "SELECT r.*, l.titulo, l.portada_url AS portada, l.autor,
                    TIMESTAMPDIFF(SECOND, NOW(), r.fecha_expiracion) AS secs_left
             FROM reservas r
             JOIN libros l ON l.id = r.id_libro
             WHERE " . implode(' AND ', $w) . "
             ORDER BY r.fecha_reserva $dir, r.id $dir",
            $p
        );
    }

    // RF-21, RF-53: solo reservas pendientes
    static function cancel($id, $uid) {
        $r = self::find($id);
        if (!$r || $r['id_usuario'] != $uid) return 'Reserva no encontrada.';
        if ($r['estado'] !== 'pendiente') return 'Solo se pueden cancelar reservas pendientes.';

        $n = DB::exec("UPDATE reservas SET estado='cancelada' WHERE id=? AND estado='pendiente'", [$id]);
        if ($n) {
            Book::give($r['id_libro'], $uid, 'liberacion', 'Reserva cancelada');
            Audit::log('reserva_cancelada', "Reserva #$id");
        }
        return true;
    }

    static function confirm($id) {
        $r = DB::one(
            'SELECT r.*, u.email, u.nombres, l.titulo 
             FROM reservas r 
             JOIN usuarios u ON u.id = r.id_usuario 
             JOIN libros l ON l.id = r.id_libro 
             WHERE r.id=?',
            [$id]
        );
        if (!$r || $r['estado'] !== 'pendiente') return 'La reserva no está pendiente.';

        DB::exec("UPDATE reservas SET estado='confirmada' WHERE id=?", [$id]);
        Audit::log('reserva_confirmada', "Reserva #$id");
        Mailer::send(
            $r['email'],
            'Reserva confirmada: ' . $r['titulo'],
            "Hola {$r['nombres']}, tu reserva de \"{$r['titulo']}\" fue confirmada. Pasa a recogerla antes del " . fdt($r['fecha_expiracion']) . '.'
        );
        return true;
    }

    // RF-19, RF-59: vence reservas y devuelve el ejemplar al stock
    static function expireDue() {
        $rows = DB::all(
            "SELECT id, id_libro, id_usuario FROM reservas 
             WHERE estado IN " . self::ACTIVE . " AND fecha_expiracion <= NOW()"
        );
        foreach ($rows as $r) {
            if (DB::exec("UPDATE reservas SET estado='vencida' WHERE id=? AND estado IN " . self::ACTIVE, [$r['id']])) {
                Book::give($r['id_libro'], null, 'liberacion', 'Reserva vencida #' . $r['id']);
            }
        }
        return count($rows);
    }

    // RF-23: aviso de reservas por vencer
    static function sendExpiryWarnings() {
        $rows = DB::all(
            "SELECT r.id, r.fecha_expiracion AS expires_at, u.email, u.nombres, l.titulo 
             FROM reservas r 
             JOIN usuarios u ON u.id = r.id_usuario 
             JOIN libros l ON l.id = r.id_libro 
             WHERE r.estado IN " . self::ACTIVE . " 
             AND r.fecha_expiracion <= DATE_ADD(NOW(), INTERVAL 12 HOUR)"
        );
        foreach ($rows as $r) {
            Mailer::send(
                $r['email'],
                'Tu reserva está por vencer',
                "Hola {$r['nombres']}, tu reserva de \"{$r['titulo']}\" vence el " . fdt($r['expires_at']) . '.'
            );
        }
        return count($rows);
    }

    static function allActive() {
        return DB::all(
            "SELECT r.*, l.titulo, CONCAT(u.nombres,' ',u.apellidos) AS usuario, u.documento,
                    TIMESTAMPDIFF(SECOND, NOW(), r.fecha_expiracion) AS secs_left
             FROM reservas r
             JOIN libros l ON l.id = r.id_libro
             JOIN usuarios u ON u.id = r.id_usuario
             WHERE r.estado IN " . self::ACTIVE . "
             ORDER BY r.fecha_expiracion"
        );
    }

    // ✅ NUEVO: Para el admin, TODAS las reservas con filtro de estado opcional
    static function all($estado = '') {
        $w = [];
        $p = [];
        if ($estado) { $w[] = 'r.estado = ?'; $p[] = $estado; }
        $where = $w ? ' WHERE ' . implode(' AND ', $w) : '';

        return DB::all(
            "SELECT r.*, l.titulo, l.portada_url AS portada,
                    CONCAT(u.nombres,' ',u.apellidos) AS usuario, u.documento, u.email,
                    TIMESTAMPDIFF(SECOND, NOW(), r.fecha_expiracion) AS secs_left
             FROM reservas r
             JOIN libros l ON l.id = r.id_libro
             JOIN usuarios u ON u.id = r.id_usuario
             $where
             ORDER BY r.fecha_reserva DESC",
            $p
        );
    }
}