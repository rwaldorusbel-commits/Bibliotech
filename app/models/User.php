<?php
class User {
    static function find($id) { return DB::one('SELECT * FROM users WHERE id=?', [$id]); }
    static function byEmail($e) { return DB::one('SELECT * FROM users WHERE email=?', [$e]); }
    static function create($d) {
        return DB::insert('INSERT INTO users(nombres,apellidos,documento,email,telefono,password_hash) VALUES(?,?,?,?,?,?)',
            [$d['nombres'], $d['apellidos'], $d['documento'], $d['email'], $d['telefono'], password_hash($d['password'], PASSWORD_DEFAULT)]);
    }
    // RF-01/RF-02: valida el formulario y la unicidad de correo y documento
    static function validateRegistration($d) {
        $err = [];
        foreach (['nombres', 'apellidos'] as $k) if (mb_strlen(trim($d[$k] ?? '')) < 2) $err[$k] = 'Campo obligatorio.';
        if (!preg_match('/^[A-Za-z0-9]{8,15}$/', $d['documento'] ?? '')) $err['documento'] = 'Documento inválido (8 a 15 caracteres).';
        if (!filter_var($d['email'] ?? '', FILTER_VALIDATE_EMAIL)) $err['email'] = 'Correo inválido.';
        if (!preg_match('/^[0-9+ ]{6,20}$/', $d['telefono'] ?? '')) $err['telefono'] = 'Teléfono inválido.';
        $p = $d['password'] ?? '';
        if (strlen($p) < 8 || !preg_match('/[A-Za-z]/', $p) || !preg_match('/\d/', $p)) $err['password'] = 'Mínimo 8 caracteres con letras y números.';
        if ($p !== ($d['password2'] ?? '')) $err['password2'] = 'Las contraseñas no coinciden.';
        if (!isset($err['email']) && self::byEmail($d['email'])) $err['email'] = 'Este correo ya está registrado.';
        if (!isset($err['documento']) && DB::val('SELECT id FROM users WHERE documento=?', [$d['documento']])) $err['documento'] = 'Este documento ya está registrado.';
        return $err;
    }
    static function updateProfile($id, $nombres, $apellidos, $tel) {
        DB::exec('UPDATE users SET nombres=?, apellidos=?, telefono=? WHERE id=?', [$nombres, $apellidos, $tel, $id]);
    }
    static function setPassword($id, $p) { DB::exec('UPDATE users SET password_hash=? WHERE id=?', [password_hash($p, PASSWORD_DEFAULT), $id]); }
    static function setStatus($id, $estado) { DB::exec('UPDATE users SET estado=? WHERE id=?', [$estado, $id]); }
    static function setRole($id, $rol) { DB::exec('UPDATE users SET rol=? WHERE id=?', [$rol, $id]); }
    static function search($q = '') {
        $like = '%' . $q . '%';
        return DB::all('SELECT * FROM users WHERE nombres LIKE ? OR apellidos LIKE ? OR email LIKE ? OR documento LIKE ? ORDER BY id DESC LIMIT 200', [$like, $like, $like, $like]);
    }
    // RF-06: restablecimiento de contraseña
    static function createReset($uid) {
        $token = bin2hex(random_bytes(32));
        DB::exec('INSERT INTO password_resets(user_id,token_hash,expires_at) VALUES(?,?,DATE_ADD(NOW(),INTERVAL 1 HOUR))', [$uid, hash('sha256', $token)]);
        return $token;
    }
    static function findReset($token) {
        return DB::one('SELECT * FROM password_resets WHERE token_hash=? AND used=0 AND expires_at>NOW()', [hash('sha256', $token)]);
    }
    static function useReset($id) { DB::exec('UPDATE password_resets SET used=1 WHERE id=?', [$id]); }
}
