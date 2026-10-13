<?php
class Fine {
    static function create($uid, $loanId, $motivo, $dias, $monto) {
        $id = DB::insert('INSERT INTO fines(user_id,loan_id,motivo,dias_retraso,monto) VALUES(?,?,?,?,?)', [$uid, $loanId, $motivo, $dias, $monto]);
        Audit::log('multa_generada', "Multa #$id ($motivo) por " . money($monto), $uid);
        return $id;
    }
    // RF-39, RF-61
    static function byUser($uid) {
        return DB::all('SELECT f.*, b.titulo FROM fines f LEFT JOIN loans l ON l.id=f.loan_id LEFT JOIN books b ON b.id=l.book_id WHERE f.user_id=? ORDER BY f.id DESC', [$uid]);
    }
    // RF-62
    static function totalPending($uid) { return (float)DB::val("SELECT COALESCE(SUM(monto),0) FROM fines WHERE user_id=? AND estado='pendiente'", [$uid]); }
    static function all($estado = '') {
        $w = $estado ? 'WHERE f.estado=?' : '';
        return DB::all("SELECT f.*, b.titulo, CONCAT(u.nombres,' ',u.apellidos) usuario, u.documento FROM fines f JOIN users u ON u.id=f.user_id LEFT JOIN loans l ON l.id=f.loan_id LEFT JOIN books b ON b.id=l.book_id $w ORDER BY f.id DESC LIMIT 300", $estado ? [$estado] : []);
    }
    static function find($id) {
        return DB::one("SELECT f.*, b.titulo, CONCAT(u.nombres,' ',u.apellidos) usuario, u.documento, CONCAT(p.nombres,' ',p.apellidos) cobrador FROM fines f JOIN users u ON u.id=f.user_id LEFT JOIN users p ON p.id=f.paid_by LEFT JOIN loans l ON l.id=f.loan_id LEFT JOIN books b ON b.id=l.book_id WHERE f.id=?", [$id]);
    }
    // RF-40: registrar pago y emitir comprobante
    static function pay($id, $staffId) {
        $comp = 'C-' . date('Ymd') . '-' . str_pad($id, 5, '0', STR_PAD_LEFT);
        $n = DB::exec("UPDATE fines SET estado='pagada', paid_at=NOW(), paid_by=?, comprobante=? WHERE id=? AND estado='pendiente'", [$staffId, $comp, $id]);
        if ($n) Audit::log('multa_pagada', "Multa #$id, comprobante $comp");
        return $n > 0;
    }
    // RF-42: condonar o anular con justificación
    static function waive($id, $estado, $just) {
        if (!in_array($estado, ['condonada', 'anulada'], true) || mb_strlen(trim($just)) < 5) return 'Debes indicar una justificación.';
        $n = DB::exec("UPDATE fines SET estado=?, justificacion=? WHERE id=? AND estado='pendiente'", [$estado, trim($just), $id]);
        if ($n) Audit::log('multa_' . $estado, "Multa #$id: " . $just);
        return $n > 0 ? true : 'La multa ya no está pendiente.';
    }
}
