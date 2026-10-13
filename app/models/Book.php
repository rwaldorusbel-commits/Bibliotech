<?php
class Book {
    const SEL = "SELECT b.*, a.nombre autor, c.nombre categoria,
        (SELECT COUNT(*) FROM reservations r WHERE r.book_id=b.id AND r.estado IN ('pendiente','confirmada')) n_res,
        (SELECT COUNT(*) FROM loans l WHERE l.book_id=b.id AND l.returned_at IS NULL) n_loan
        FROM books b LEFT JOIN authors a ON a.id=b.author_id LEFT JOIN categories c ON c.id=b.category_id";

    // RF-32: disponibilidad visual
    static function status($b) {
        if ($b['copies_available'] > 0) return ['disponible', 'Disponible'];
        if ($b['n_res'] > 0) return ['reservado', 'Reservado'];
        if ($b['n_loan'] > 0) return ['prestado', 'Prestado'];
        return ['nodisponible', 'No disponible'];
    }
    static function find($id) { return DB::one(self::SEL . ' WHERE b.id=?', [$id]); }

    // RF-11, RF-12, RF-14
    static function search($f, $page, $per) {
        $w = ['b.activo=1']; $p = [];
        if (($f['q'] ?? '') !== '') {
            $like = '%' . $f['q'] . '%';
            $w[] = '(b.titulo LIKE ? OR a.nombre LIKE ? OR c.nombre LIKE ? OR b.isbn LIKE ? OR b.descripcion LIKE ? OR b.editorial LIKE ?)';
            array_push($p, $like, $like, $like, $like, $like, $like);
        }
        if (!empty($f['categoria'])) { $w[] = 'b.category_id=?'; $p[] = (int)$f['categoria']; }
        if (!empty($f['autor'])) { $w[] = 'b.author_id=?'; $p[] = (int)$f['autor']; }
        if (!empty($f['anio'])) { $w[] = 'b.anio=?'; $p[] = (int)$f['anio']; }
        if (!empty($f['disp'])) $w[] = 'b.copies_available>0';
        $where = ' WHERE ' . implode(' AND ', $w);
        $orders = ['titulo' => 'b.titulo ASC', 'autor' => 'a.nombre ASC', 'anio' => 'b.anio DESC', 'disp' => 'b.copies_available DESC, b.titulo ASC'];
        $order = $orders[$f['orden'] ?? 'titulo'] ?? $orders['titulo'];
        $total = (int)DB::val('SELECT COUNT(*) FROM books b LEFT JOIN authors a ON a.id=b.author_id LEFT JOIN categories c ON c.id=b.category_id' . $where, $p);
        $off = max(0, ($page - 1) * $per);
        $rows = DB::all(self::SEL . $where . " ORDER BY $order LIMIT " . (int)$per . ' OFFSET ' . $off, $p);
        return [$rows, $total];
    }
    static function categories() { return DB::all('SELECT * FROM categories ORDER BY nombre'); }
    static function authors() { return DB::all('SELECT * FROM authors ORDER BY nombre'); }
    static function years() { return array_column(DB::all('SELECT DISTINCT anio FROM books WHERE anio IS NOT NULL AND activo=1 ORDER BY anio DESC'), 'anio'); }

    static function save($id, $d) {
        $vals = [$d['titulo'], $d['author_id'] ?: null, $d['category_id'] ?: null, $d['editorial'], $d['anio'] ?: null, $d['isbn'], $d['descripcion'], $d['stock_min']];
        if ($id) {
            $vals[] = $id;
            DB::exec('UPDATE books SET titulo=?, author_id=?, category_id=?, editorial=?, anio=?, isbn=?, descripcion=?, stock_min=? WHERE id=?', $vals);
            if (!empty($d['portada'])) DB::exec('UPDATE books SET portada=? WHERE id=?', [$d['portada'], $id]);
            return $id;
        }
        $n = max(1, (int)$d['copies']);
        $vals = array_merge($vals, [$d['portada'] ?? null, $n, $n]);
        $id = DB::insert('INSERT INTO books(titulo,author_id,category_id,editorial,anio,isbn,descripcion,stock_min,portada,copies_total,copies_available) VALUES(?,?,?,?,?,?,?,?,?,?,?)', $vals);
        self::move($id, 'alta', $n, null, 'Alta inicial');
        return $id;
    }
    static function deactivate($id) {
        $busy = DB::val("SELECT (SELECT COUNT(*) FROM loans WHERE book_id=? AND returned_at IS NULL) + (SELECT COUNT(*) FROM reservations WHERE book_id=? AND estado IN ('pendiente','confirmada'))", [$id, $id]);
        if ($busy > 0) return 'No se puede dar de baja: tiene préstamos o reservas activas.';
        DB::exec('UPDATE books SET activo=0 WHERE id=?', [$id]);
        return true;
    }

    // ---- Control de stock (RF-30 a RF-35) ----
    static function move($bookId, $tipo, $qty, $uid = null, $nota = '') {
        DB::exec('INSERT INTO stock_movements(book_id,tipo,cantidad,user_id,nota) VALUES(?,?,?,?,?)', [$bookId, $tipo, $qty, $uid, $nota]);
    }
    // Descuenta un ejemplar disponible (reserva o préstamo directo)
    static function take($id, $uid, $tipo, $nota = '') {
        $n = DB::exec('UPDATE books SET copies_available=copies_available-1 WHERE id=? AND copies_available>0', [$id]);
        if ($n) self::move($id, $tipo, 1, $uid, $nota);
        return $n > 0;
    }
    // Repone un ejemplar (devolución, reserva vencida/cancelada)
    static function give($id, $uid, $tipo, $nota = '') {
        $n = DB::exec('UPDATE books SET copies_available=copies_available+1 WHERE id=? AND copies_available<copies_total', [$id]);
        if ($n) { self::move($id, $tipo, 1, $uid, $nota); self::notifyWaitlist($id); }
        return $n > 0;
    }
    static function addCopies($id, $n, $uid) {
        DB::exec('UPDATE books SET copies_total=copies_total+?, copies_available=copies_available+? WHERE id=?', [$n, $n, $id]);
        self::move($id, 'alta', $n, $uid, 'Ingreso de ejemplares');
        for ($i = 0; $i < $n; $i++) self::notifyWaitlist($id);
    }
    // Baja de ejemplares. $fromShelf=true: salen del stock disponible; false: ya estaban fuera (dañado/perdido en préstamo)
    static function writeOff($id, $n, $uid, $nota, $fromShelf = true) {
        $b = DB::one('SELECT copies_total, copies_available FROM books WHERE id=?', [$id]);
        if (!$b || $b['copies_total'] < $n || ($fromShelf && $b['copies_available'] < $n)) return 'No hay suficientes ejemplares disponibles para dar de baja.';
        DB::exec('UPDATE books SET copies_total=copies_total-?, copies_available=copies_available-? WHERE id=?', [$n, $fromShelf ? $n : 0, $id]);
        self::move($id, 'baja', $n, $uid, $nota);
        return true;
    }
    // RF-22/RF-23: avisa al primero de la lista de espera
    static function notifyWaitlist($id) {
        $w = DB::one('SELECT w.id, u.email, u.nombres, b.titulo FROM waitlist w JOIN users u ON u.id=w.user_id JOIN books b ON b.id=w.book_id WHERE w.book_id=? AND w.notified=0 ORDER BY w.created_at LIMIT 1', [$id]);
        if ($w) {
            DB::exec('UPDATE waitlist SET notified=1 WHERE id=?', [$w['id']]);
            Mailer::send($w['email'], 'Libro disponible: ' . $w['titulo'], "Hola {$w['nombres']}, el libro \"{$w['titulo']}\" ya tiene un ejemplar disponible. ¡Resérvalo antes de que se agote!");
        }
    }
    static function lowStock() { return DB::all('SELECT id, titulo, copies_available, copies_total, stock_min FROM books WHERE activo=1 AND copies_available<=stock_min ORDER BY copies_available'); }
    static function movements($bookId = null, $limit = 200) {
        $w = $bookId ? 'WHERE m.book_id=' . (int)$bookId : '';
        return DB::all("SELECT m.*, b.titulo, CONCAT(u.nombres,' ',u.apellidos) usuario FROM stock_movements m JOIN books b ON b.id=m.book_id LEFT JOIN users u ON u.id=m.user_id $w ORDER BY m.id DESC LIMIT " . (int)$limit);
    }
    static function inWaitlist($uid, $bid) { return (bool)DB::val('SELECT 1 FROM waitlist WHERE user_id=? AND book_id=?', [$uid, $bid]); }
    static function joinWaitlist($uid, $bid) { DB::exec('INSERT IGNORE INTO waitlist(user_id,book_id) VALUES(?,?)', [$uid, $bid]); }
}
