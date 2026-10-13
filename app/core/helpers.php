<?php
function cfg($k) { return $GLOBALS['config'][$k]; }
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function url($p = '') { return BASE_URL . '/' . ltrim($p, '/'); }
function asset($p) { return BASE_URL . '/assets/' . $p; }
function redirect($p) { header('Location: ' . (preg_match('#^https?://#', $p) ? $p : url($p))); exit; }
function flash($t = null, $m = null) {
    if ($t !== null) { $_SESSION['flash'][] = [$t, $m]; return null; }
    $f = $_SESSION['flash'] ?? []; unset($_SESSION['flash']); return $f;
}
function csrf_token() { return $_SESSION['csrf'] ??= bin2hex(random_bytes(16)); }
function csrf_field() { return '<input type="hidden" name="_csrf" value="' . csrf_token() . '">'; }
function csrf_check() { return hash_equals($_SESSION['csrf'] ?? '', $_POST['_csrf'] ?? ''); }
function old($k, $d = '') { return e($_POST[$k] ?? $d); }
function money($n) { return 'S/ ' . number_format((float)$n, 2); }
function fdt($d) { return $d ? date('d/m/Y H:i', strtotime($d)) : '—'; }
function fd($d) { return $d ? date('d/m/Y', strtotime($d)) : '—'; }
function badge($estado) { return '<span class="badge b-' . e($estado) . '">' . e(ucfirst($estado)) . '</span>'; }
function cover_url($b) {
    $p = $b['portada'] ?? '';
    if (!$p) return asset('img/no-cover.svg');
    return preg_match('#^https?://#', $p) ? $p : asset($p);
}
function pager($total, $page, $per, $path, $q = []) {
    $pages = (int)ceil($total / $per);
    if ($pages <= 1) return '';
    $q = array_filter($q, fn($v) => $v !== '' && $v !== null);
    $h = '<nav class="pager">';
    for ($i = 1; $i <= $pages; $i++) {
        $q['p'] = $i;
        $h .= '<a class="' . ($i == $page ? 'active' : '') . '" href="' . e(url($path) . '?' . http_build_query($q)) . '">' . $i . '</a>';
    }
    return $h . '</nav>';
}
