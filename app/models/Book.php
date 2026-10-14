<?php
class Book {
    // Consulta principal adaptada a la BD en español
    const SEL = "SELECT b.*, b.autor AS autor_nombre, c.nombre AS categoria_nombre,
        (SELECT COUNT(*) FROM reservas r WHERE r.id_libro=b.id AND r.estado IN ('pendiente','confirmada')) n_res,
        (SELECT COUNT(*) FROM prestamos l WHERE l.id_libro=b.id AND l.fecha_devolucion_real IS NULL) n_loan
        FROM libros b 
        LEFT JOIN autores a ON a.nombre = b.autor
        LEFT JOIN categorias c ON c.id = b.id_categoria";

    // RF-32: disponibilidad visual
    static function status($b) {
        if ($b['stock_disponible'] > 0) return ['disponible', 'Disponible'];
        if ($b['n_res'] > 0) return ['reservado', 'Reservado'];
        if ($b['n_loan'] > 0) return ['prestado', 'Prestado'];
        return ['nodisponible', 'No disponible'];
    }

    static function find($id) { 
        return DB::one(self::SEL . ' WHERE b.id=?', [$id]); 
    }

    // RF-11, RF-12, RF-14
    static function search($f, $page, $per) {
        $w = ['b.activo=1']; 
        $p = [];
        
        if (($f['q'] ?? '') !== '') {
            $like = '%' . $f['q'] . '%';
            $w[] = '(b.titulo LIKE ? OR b.autor LIKE ? OR c.nombre LIKE ? OR b.isbn LIKE ? OR b.sinopsis LIKE ? OR b.editorial LIKE ?)';
            array_push($p, $like, $like, $like, $like, $like, $like);
        }
        if (!empty($f['categoria'])) { $w[] = 'b.id_categoria=?'; $p[] = (int)$f['categoria']; }
        if (!empty($f['autor'])) { $w[] = 'b.autor=?'; $p[] = $f['autor']; }
        if (!empty($f['anio'])) { $w[] = 'b.anio=?'; $p[] = (int)$f['anio']; }
        if (!empty($f['disp'])) $w[] = 'b.stock_disponible>0';
        
        $where = ' WHERE ' . implode(' AND ', $w);
        
        $orders = [
            'titulo' => 'b.titulo ASC', 
            'autor' => 'b.autor ASC', 
            'anio' => 'b.anio DESC', 
            'disp' => 'b.stock_disponible DESC, b.titulo ASC'
        ];
        $order = $orders[$f['orden'] ?? 'titulo'] ?? $orders['titulo'];
        
        $total = (int)DB::val('SELECT COUNT(*) FROM libros b LEFT JOIN categorias c ON c.id=b.id_categoria' . $where, $p);
        $off = max(0, ($page - 1) * $per);
        $rows = DB::all(self::SEL . $where . " ORDER BY $order LIMIT " . (int)$per . ' OFFSET ' . $off, $p);
        
        return [$rows, $total];
    }

    static function categories() { 
        return DB::all('SELECT * FROM categorias ORDER BY nombre'); 
    }
    
    static function authors() { 
        return DB::all('SELECT * FROM autores ORDER BY nombre'); 
    }
    
    static function years() { 
        return array_column(DB::all('SELECT DISTINCT anio FROM libros WHERE anio IS NOT NULL AND activo=1 ORDER BY anio DESC'), 'anio'); 
    }

    static function save($id, $d) {
        // Adaptado a columnas de la BD en español
        $vals = [
            $d['titulo'], 
            $d['author_id'] ?? $d['autor'] ?? '', 
            $d['category_id'] ?? $d['id_categoria'] ?? null, 
            $d['editorial'] ?? '', 
            $d['anio'] ?: null, 
            $d['isbn'] ?? '', 
            $d['descripcion'] ?? $d['sinopsis'] ?? ''
        ];
        
        if ($id) {
            $vals[] = $id;
            DB::exec('UPDATE libros SET titulo=?, autor=?, id_categoria=?, editorial=?, anio=?, isbn=?, sinopsis=? WHERE id=?', $vals);
            if (!empty($d['portada'])) DB::exec('UPDATE libros SET portada_url=? WHERE id=?', [$d['portada'], $id]);
            return $id;
        }
        
        $n = max(1, (int)($d['copies'] ?? $d['stock_total'] ?? 1));
        $portada = $d['portada'] ?? $d['portada_url'] ?? null;
        $id = DB::insert(
            'INSERT INTO libros(titulo, autor, id_categoria, editorial, anio, isbn, sinopsis, portada_url, stock_total, stock_disponible, activo) VALUES(?,?,?,?,?,?,?,?,?,?,1)', 
            array_merge($vals, [$portada, $n, $n])
        );
        return $id;
    }

    static function deactivate($id) {
        $busy = DB::val(
            "SELECT (SELECT COUNT(*) FROM prestamos WHERE id_libro=? AND fecha_devolucion_real IS NULL) + (SELECT COUNT(*) FROM reservas WHERE id_libro=? AND estado IN ('pendiente','confirmada'))", 
            [$id, $id]
        );
        if ($busy > 0) return 'No se puede dar de baja: tiene préstamos o reservas activas.';
        DB::exec('UPDATE libros SET activo=0 WHERE id=?', [$id]);
        return true;
    }

    // ---- Control de stock (RF-30 a RF-35) ----
    static function move($bookId, $tipo, $qty, $uid = null, $nota = '') {
        DB::exec('INSERT INTO stock_movements(book_id,tipo,cantidad,user_id,nota) VALUES(?,?,?,?,?)', [$bookId, $tipo, $qty, $uid, $nota]);
    }

    static function take($id, $uid, $tipo, $nota = '') {
        $n = DB::exec('UPDATE libros SET stock_disponible=stock_disponible-1 WHERE id=? AND stock_disponible>0', [$id]);
        if ($n) self::move($id, $tipo, 1, $uid, $nota);
        return $n > 0;
    }

    static function give($id, $uid, $tipo, $nota = '') {
        $n = DB::exec('UPDATE libros SET stock_disponible=stock_disponible+1 WHERE id=? AND stock_disponible<stock_total', [$id]);
        if ($n) { self::move($id, $tipo, 1, $uid, $nota); self::notifyWaitlist($id); }
        return $n > 0;
    }

    static function addCopies($id, $n, $uid) {
        DB::exec('UPDATE libros SET stock_total=stock_total+?, stock_disponible=stock_disponible+? WHERE id=?', [$n, $n, $id]);
        self::move($id, 'alta', $n, $uid, 'Ingreso de ejemplares');
    }

    static function writeOff($id, $n, $uid, $nota, $fromShelf = true) {
        $b = DB::one('SELECT stock_total, stock_disponible FROM libros WHERE id=?', [$id]);
        if (!$b || $b['stock_total'] < $n || ($fromShelf && $b['stock_disponible'] < $n)) 
            return 'No hay suficientes ejemplares disponibles para dar de baja.';
        DB::exec('UPDATE libros SET stock_total=stock_total-?, stock_disponible=stock_disponible-? WHERE id=?', [$n, $fromShelf ? $n : 0, $id]);
        self::move($id, 'baja', $n, $uid, $nota);
        return true;
    }

    static function notifyWaitlist($id) {
        $w = DB::one(
            'SELECT w.id, u.email, u.nombres, b.titulo FROM waitlist w 
             JOIN usuarios u ON u.id=w.user_id 
             JOIN libros b ON b.id=w.book_id 
             WHERE w.book_id=? AND w.notified=0 ORDER BY w.created_at LIMIT 1', 
            [$id]
        );
        if ($w) {
            DB::exec('UPDATE waitlist SET notified=1 WHERE id=?', [$w['id']]);
            Mailer::send($w['email'], 'Libro disponible: ' . $w['titulo'], "Hola {$w['nombres']}, el libro \"{$w['titulo']}\" ya tiene un ejemplar disponible.");
        }
    }

    static function lowStock() { 
        return DB::all('SELECT id, titulo, stock_disponible, stock_total FROM libros WHERE activo=1 AND stock_disponible<=1 ORDER BY stock_disponible'); 
    }

    static function movements($bookId = null, $limit = 200) {
        $w = $bookId ? 'WHERE m.book_id=' . (int)$bookId : '';
        return DB::all("SELECT m.*, b.titulo FROM stock_movements m JOIN libros b ON b.id=m.book_id $w ORDER BY m.id DESC LIMIT " . (int)$limit);
    }

    static function inWaitlist($uid, $bid) { 
        return (bool)DB::val('SELECT 1 FROM waitlist WHERE user_id=? AND book_id=?', [$uid, $bid]); 
    }
    
    static function joinWaitlist($uid, $bid) { 
        DB::exec('INSERT IGNORE INTO waitlist(user_id,book_id) VALUES(?,?)', [$uid, $bid]); 
    }
}