<?php
class Loan {

    static function find($id) {
        return DB::one('SELECT * FROM prestamos WHERE id=?', [$id]);
    }

    // RF-24, RF-25: préstamo desde una reserva o directo.
    static function create($uid, $bid, $staffId, $reservationId = 0) {
        $u = User::find($uid);
        if (!$u || $u['estado'] !== 'activo') return 'El usuario no está activo.';
        if (Fine::totalPending($uid) > 0) return 'El usuario tiene multas pendientes.';
        if (Reservation::activeCount($uid, (int)$reservationId) >= (int)Setting::get('max_prestamos', 3)) {
            return 'El usuario alcanzó el límite de préstamos.';
        }

        $pdo = DB::pdo();
        $pdo->beginTransaction();
        try {
            if ($reservationId) {
                $r = Reservation::find($reservationId);
                if (!$r || $r['id_usuario'] != $uid || $r['id_libro'] != $bid || !in_array($r['estado'], ['pendiente', 'confirmada'], true)) {
                    $pdo->rollBack();
                    return 'La reserva no es válida.';
                }
                DB::exec("UPDATE reservas SET estado='recogida' WHERE id=?", [$reservationId]);
            } elseif (!Book::take($bid, $uid, 'prestamo', 'Préstamo directo')) {
                $pdo->rollBack();
                return 'No hay ejemplares disponibles.';
            }

            $dias = (int)Setting::get('dias_prestamo', 7);
            $id = DB::insert(
                "INSERT INTO prestamos (id_usuario, id_libro, id_reserva, fecha_prestamo, fecha_devolucion_esperada, estado)
                 VALUES (?, ?, ?, NOW(), DATE_ADD(CURDATE(), INTERVAL ? DAY), 'activo')",
                [$uid, $bid, $reservationId ?: null, $dias]
            );

            if ($reservationId) {
                Book::move($bid, 'prestamo', 1, $uid, 'Préstamo desde reserva #' . $reservationId);
            }
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            return 'No se pudo registrar el préstamo.';
        }

        Audit::log('prestamo_registrado', "Préstamo #$id libro #$bid usuario #$uid");
        return true;
    }

    // RF-26, RF-36, RF-37, RF-38
    static function giveBack($id, $cond, $staffId) {
        $l = self::find($id);
        if (!$l || $l['fecha_devolucion_real']) return 'El préstamo no existe o ya fue devuelto.';
        if (!in_array($cond, ['bueno', 'danado', 'perdido'], true)) return 'Estado de devolución inválido.';

        $pdo = DB::pdo();
        $pdo->beginTransaction();
        try {
            DB::exec("UPDATE prestamos SET fecha_devolucion_real=NOW(), estado='devuelto' WHERE id=?", [$id]);

            if ($cond === 'bueno') {
                Book::give($l['id_libro'], $staffId, 'devolucion', "Devolución préstamo #$id");
            } else {
                Book::writeOff($l['id_libro'], 1, $staffId, "Baja por ejemplar $cond (préstamo #$id)", false);
            }

            $dias = (int)DB::val('SELECT GREATEST(DATEDIFF(CURDATE(), fecha_devolucion_esperada), 0) FROM prestamos WHERE id=?', [$id]);
            if ($dias > 0) Fine::create($l['id_usuario'], $id, 'retraso', $dias, $dias * (float)Setting::get('tarifa_diaria', 1.5));
            if ($cond === 'danado')  Fine::create($l['id_usuario'], $id, 'dano', 0, (float)Setting::get('multa_dano', 20));
            if ($cond === 'perdido') Fine::create($l['id_usuario'], $id, 'perdida', 0, (float)Setting::get('multa_perdida', 60));

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            return 'No se pudo registrar la devolución.';
        }

        Audit::log('devolucion_registrada', "Préstamo #$id ($cond)");
        return true;
    }

    // RF-27
    static function renew($id, $uid) {
        $l = self::find($id);
        if (!$l || $l['id_usuario'] != $uid || $l['fecha_devolucion_real']) return 'Préstamo no encontrado.';
        if (Fine::totalPending($uid) > 0) return 'No puedes renovar con multas pendientes.';
        if (DB::val('SELECT CURDATE() > ?', [$l['fecha_devolucion_esperada']])) return 'El préstamo está vencido: debes devolverlo.';

        $pend = DB::val(
            "SELECT COUNT(*) FROM reservas 
             WHERE id_libro=? AND id_usuario<>? AND estado IN ('pendiente','confirmada')",
            [$l['id_libro'], $uid]
        );
        if ($pend > 0) return 'No se puede renovar: otros usuarios esperan este libro.';

        DB::exec(
            'UPDATE prestamos SET fecha_devolucion_esperada=DATE_ADD(fecha_devolucion_esperada, INTERVAL ? DAY) WHERE id=?',
            [(int)Setting::get('dias_prestamo', 7), $id]
        );
        Audit::log('prestamo_renovado', "Préstamo #$id");
        return true;
    }

    // RF-28
    static function byUser($uid, $onlyActive = false) {
        $w = $onlyActive ? 'AND p.fecha_devolucion_real IS NULL' : '';
        return DB::all(
            "SELECT p.*, l.titulo, l.portada_url AS portada, l.autor,
                    (p.fecha_devolucion_real IS NULL AND p.fecha_devolucion_esperada < CURDATE()) AS vencido
             FROM prestamos p
             JOIN libros l ON l.id = p.id_libro
             WHERE p.id_usuario = ? $w
             ORDER BY p.id DESC",
            [$uid]
        );
    }

    static function allActive() {
        return DB::all(
            "SELECT p.*, l.titulo, CONCAT(u.nombres,' ',u.apellidos) AS usuario, u.documento,
                    (p.fecha_devolucion_esperada < CURDATE()) AS vencido
             FROM prestamos p
             JOIN libros l ON l.id = p.id_libro
             JOIN usuarios u ON u.id = p.id_usuario
             WHERE p.fecha_devolucion_real IS NULL
             ORDER BY p.fecha_devolucion_esperada"
        );
    }

    // RF-29: recordatorios (se ejecuta desde cli/cron.php)
    static function sendReminders() {
        $d = (int)Setting::get('dias_recordatorio', 2);
        $rows = DB::all(
            "SELECT p.id, p.fecha_devolucion_esperada AS due_date, u.email, u.nombres, l.titulo
             FROM prestamos p
             JOIN usuarios u ON u.id = p.id_usuario
             JOIN libros l ON l.id = p.id_libro
             WHERE p.fecha_devolucion_real IS NULL
               AND p.fecha_devolucion_esperada <= DATE_ADD(CURDATE(), INTERVAL ? DAY)",
            [$d]
        );
        foreach ($rows as $r) {
            Mailer::send(
                $r['email'],
                'Recordatorio de devolución',
                "Hola {$r['nombres']}, el libro \"{$r['titulo']}\" debe devolverse el " . fd($r['due_date']) . '.'
            );
        }
        return count($rows);
    }

    // RF-43, RF-44: estadísticas GLOBALES del sistema
    static function stats() {
        return [
            // Préstamos
            'prestados'       => (int)DB::val('SELECT COUNT(*) FROM prestamos WHERE fecha_devolucion_real IS NULL'),
            'devueltos'       => (int)DB::val('SELECT COUNT(*) FROM prestamos WHERE fecha_devolucion_real IS NOT NULL'),
            'vencidos'        => (int)DB::val('SELECT COUNT(*) FROM prestamos WHERE fecha_devolucion_real IS NULL AND fecha_devolucion_esperada < CURDATE()'),
            'total_prestamos' => (int)DB::val('SELECT COUNT(*) FROM prestamos'),

            // Reservas
            'reservas'        => (int)DB::val("SELECT COUNT(*) FROM reservas WHERE estado IN ('pendiente','confirmada')"),
            'reservas_pend'   => (int)DB::val("SELECT COUNT(*) FROM reservas WHERE estado='pendiente'"),
            'reservas_conf'   => (int)DB::val("SELECT COUNT(*) FROM reservas WHERE estado='confirmada'"),
            'reservas_venc'   => (int)DB::val("SELECT COUNT(*) FROM reservas WHERE estado='vencida'"),
            'total_reservas'  => (int)DB::val('SELECT COUNT(*) FROM reservas'),

            // Usuarios
            'usuarios'        => (int)DB::val('SELECT COUNT(*) FROM usuarios'),
            'estudiantes'     => (int)DB::val("SELECT COUNT(*) FROM usuarios WHERE rol='estudiante'"),
            'bibliotecarios'  => (int)DB::val("SELECT COUNT(*) FROM usuarios WHERE rol='bibliotecario'"),

            // Multas
            'multas'          => (float)DB::val("SELECT COALESCE(SUM(monto),0) FROM multas WHERE estado='pendiente'"),
            'multas_pagadas'  => (float)DB::val("SELECT COALESCE(SUM(monto),0) FROM multas WHERE estado='pagada'"),
            'multas_count'    => (int)DB::val("SELECT COUNT(*) FROM multas WHERE estado='pendiente'"),

            // Libros
            'libros'          => (int)DB::val('SELECT COUNT(*) FROM libros WHERE activo=1'),
            'ejemplares'      => (int)DB::val('SELECT COALESCE(SUM(stock_total),0) FROM libros WHERE activo=1'),
        ];
    }

    static function topBooks($from, $to) {
        return DB::all(
            "SELECT l.titulo, l.autor, COUNT(*) AS total
             FROM prestamos p
             JOIN libros l ON l.id = p.id_libro
             WHERE DATE(p.fecha_prestamo) BETWEEN ? AND ?
             GROUP BY l.id
             ORDER BY total DESC LIMIT 15",
            [$from, $to]
        );
    }

    static function perPeriod($from, $to) {
        return DB::all(
            "SELECT DATE_FORMAT(fecha_prestamo, '%Y-%m') AS periodo, COUNT(*) AS total
             FROM prestamos
             WHERE DATE(fecha_prestamo) BETWEEN ? AND ?
             GROUP BY periodo
             ORDER BY periodo",
            [$from, $to]
        );
    }

    static function overdueUsers() {
        return DB::all(
            "SELECT CONCAT(u.nombres,' ',u.apellidos) AS usuario, u.documento, u.email, l.titulo,
                    p.fecha_devolucion_esperada AS due_date,
                    DATEDIFF(CURDATE(), p.fecha_devolucion_esperada) AS dias
             FROM prestamos p
             JOIN usuarios u ON u.id = p.id_usuario
             JOIN libros l ON l.id = p.id_libro
             WHERE p.fecha_devolucion_real IS NULL
               AND p.fecha_devolucion_esperada < CURDATE()
             ORDER BY dias DESC"
        );
    }

    static function collected($from, $to) {
        return DB::all(
            "SELECT m.comprobante, DATE(m.paid_at) AS fecha, 
                    CONCAT(u.nombres,' ',u.apellidos) AS usuario,
                    m.motivo, m.monto
             FROM multas m
             JOIN usuarios u ON u.id = m.user_id
             WHERE m.estado = 'pagada' 
               AND DATE(m.paid_at) BETWEEN ? AND ?
             ORDER BY m.paid_at",
            [$from, $to]
        );
    }
}