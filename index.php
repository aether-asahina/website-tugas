<?php
// =============================================
// MANAJEMEN TUGAS KULIAH
// Simpan di: C:/xampp/htdocs/tugas_kuliah/index.php
// =============================================

session_start();

// ── CONFIG DATABASE ──────────────────────────
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'tugas_kuliah');

function getDB() {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $conn->set_charset('utf8mb4');
        if ($conn->connect_error) {
            die('<div style="padding:20px;color:red;font-family:sans-serif;">
                ❌ Koneksi database gagal: ' . $conn->connect_error . '<br>
                Pastikan XAMPP sudah jalan dan database <b>tugas_kuliah</b> sudah dibuat.
            </div>');
        }
    }
    return $conn;
}

function sanitize($val) {
    return htmlspecialchars(trim($val), ENT_QUOTES, 'UTF-8');
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ?page=login');
        exit;
    }
}

// ── CSRF ─────────────────────────────────────
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
function csrfField() {
    return '<input type="hidden" name="csrf" value="' . $_SESSION['csrf'] . '">';
}
function checkCsrf() {
    if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf']) {
        die('CSRF token tidak valid.');
    }
}

// ── ROUTING & ACTIONS ────────────────────────
$page   = $_GET['page']   ?? 'dashboard';
$action = $_POST['action'] ?? '';
$msg    = '';
$msgType = 'success';

// LOGIN
if ($action === 'login') {
    checkCsrf();
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $db = getDB();
    $stmt = $db->prepare('SELECT id, nama, password FROM users WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if ($row && password_verify($password, $row['password'])) {
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['user_nama'] = $row['nama'];
        header('Location: ?page=dashboard');
        exit;
    } else {
        $msg = 'Email atau password salah.';
        $msgType = 'error';
        $page = 'login';
    }
}

// REGISTER
if ($action === 'register') {
    checkCsrf();
    $nama     = trim($_POST['nama'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';
    if ($password !== $confirm) {
        $msg = 'Password tidak cocok.'; $msgType = 'error'; $page = 'register';
    } elseif (strlen($password) < 6) {
        $msg = 'Password minimal 6 karakter.'; $msgType = 'error'; $page = 'register';
    } else {
        $db = getDB();
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $db->prepare('INSERT INTO users (nama, email, password) VALUES (?, ?, ?)');
        $stmt->bind_param('sss', $nama, $email, $hash);
        if ($stmt->execute()) {
            $msg = 'Registrasi berhasil! Silakan login.'; $msgType = 'success'; $page = 'login';
        } else {
            $msg = 'Email sudah terdaftar.'; $msgType = 'error'; $page = 'register';
        }
    }
}

// LOGOUT
if ($page === 'logout') {
    session_destroy();
    header('Location: ?page=login');
    exit;
}

// TAMBAH TUGAS
if ($action === 'tambah_tugas') {
    requireLogin(); checkCsrf();
    $judul       = trim($_POST['judul'] ?? '');
    $matkul      = trim($_POST['mata_kuliah'] ?? '');
    $deskripsi   = trim($_POST['deskripsi'] ?? '');
    $deadline    = $_POST['deadline'] ?? '';
    $prioritas   = $_POST['prioritas'] ?? 'sedang';
    $db = getDB();
    $uid = $_SESSION['user_id'];
    $stmt = $db->prepare('INSERT INTO tugas (user_id,judul,mata_kuliah,deskripsi,deadline,prioritas) VALUES (?,?,?,?,?,?)');
    $stmt->bind_param('isssss', $uid, $judul, $matkul, $deskripsi, $deadline, $prioritas);
    $stmt->execute();
    $msg = 'Tugas berhasil ditambahkan!';
    $page = 'dashboard';
}

// UPDATE STATUS
if ($action === 'update_status') {
    requireLogin(); checkCsrf();
    $tid    = (int)($_POST['tugas_id'] ?? 0);
    $status = $_POST['status'] ?? 'belum';
    $db = getDB();
    $uid = $_SESSION['user_id'];
    $stmt = $db->prepare('UPDATE tugas SET status=? WHERE id=? AND user_id=?');
    $stmt->bind_param('sii', $status, $tid, $uid);
    $stmt->execute();
    header('Location: ?page=dashboard&updated=1');
    exit;
}

// HAPUS TUGAS
if ($action === 'hapus_tugas') {
    requireLogin(); checkCsrf();
    $tid = (int)($_POST['tugas_id'] ?? 0);
    $uid = $_SESSION['user_id'];
    $db  = getDB();
    $stmt = $db->prepare('DELETE FROM tugas WHERE id=? AND user_id=?');
    $stmt->bind_param('ii', $tid, $uid);
    $stmt->execute();
    header('Location: ?page=dashboard&deleted=1');
    exit;
}

// EDIT TUGAS
if ($action === 'edit_tugas') {
    requireLogin(); checkCsrf();
    $tid       = (int)($_POST['tugas_id'] ?? 0);
    $judul     = trim($_POST['judul'] ?? '');
    $matkul    = trim($_POST['mata_kuliah'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $deadline  = $_POST['deadline'] ?? '';
    $prioritas = $_POST['prioritas'] ?? 'sedang';
    $status    = $_POST['status'] ?? 'belum';
    $uid = $_SESSION['user_id'];
    $db  = getDB();
    $stmt = $db->prepare('UPDATE tugas SET judul=?,mata_kuliah=?,deskripsi=?,deadline=?,prioritas=?,status=? WHERE id=? AND user_id=?');
    $stmt->bind_param('ssssssii', $judul, $matkul, $deskripsi, $deadline, $prioritas, $status, $tid, $uid);
    $stmt->execute();
    $msg = 'Tugas berhasil diupdate!';
    $page = 'dashboard';
}

// ── DATA UNTUK VIEW ───────────────────────────
$tugas_list  = [];
$stats       = [];
$notif_list  = [];
$edit_tugas  = null;

if (isLoggedIn()) {
    $db  = getDB();
    $uid = $_SESSION['user_id'];

    // Filter
    $filter_status = $_GET['status'] ?? '';
    $filter_matkul = $_GET['matkul'] ?? '';
    $search        = $_GET['q'] ?? '';

    $where = 'WHERE t.user_id = ?';
    $params = [$uid];
    $types  = 'i';

    if ($filter_status) { $where .= ' AND t.status = ?'; $params[] = $filter_status; $types .= 's'; }
    if ($filter_matkul) { $where .= ' AND t.mata_kuliah = ?'; $params[] = $filter_matkul; $types .= 's'; }
    if ($search)        { $where .= ' AND (t.judul LIKE ? OR t.mata_kuliah LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; $types .= 'ss'; }

    $stmt = $db->prepare("SELECT * FROM tugas t $where ORDER BY FIELD(t.prioritas,'tinggi','sedang','rendah'), t.deadline ASC");
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $tugas_list = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Stats
    $r = $db->prepare("SELECT
        COUNT(*) as total,
        SUM(status='selesai') as selesai,
        SUM(status='proses') as proses,
        SUM(status='belum') as belum,
        SUM(deadline < NOW() AND status != 'selesai') as terlambat
        FROM tugas WHERE user_id=?");
    $r->bind_param('i', $uid);
    $r->execute();
    $stats = $r->get_result()->fetch_assoc();

    // Notifikasi: deadline dalam 3 hari & belum selesai
    $n = $db->prepare("SELECT * FROM tugas WHERE user_id=? AND status != 'selesai' AND deadline BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 3 DAY) ORDER BY deadline ASC");
    $n->bind_param('i', $uid);
    $n->execute();
    $notif_list = $n->get_result()->fetch_all(MYSQLI_ASSOC);

    // Edit form data
    if ($page === 'edit' && isset($_GET['id'])) {
        $eid = (int)$_GET['id'];
        $e = $db->prepare('SELECT * FROM tugas WHERE id=? AND user_id=?');
        $e->bind_param('ii', $eid, $uid);
        $e->execute();
        $edit_tugas = $e->get_result()->fetch_assoc();
    }

    // Mata kuliah unik untuk filter
    $mk = $db->prepare('SELECT DISTINCT mata_kuliah FROM tugas WHERE user_id=? ORDER BY mata_kuliah');
    $mk->bind_param('i', $uid);
    $mk->execute();
    $matkul_list = $mk->get_result()->fetch_all(MYSQLI_ASSOC);
}

// URL helper
if (isset($_GET['updated'])) $msg = 'Status tugas diperbarui!';
if (isset($_GET['deleted'])) { $msg = 'Tugas dihapus.'; $msgType = 'error'; }

// ── HTML OUTPUT ───────────────────────────────
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>TaskKampus — Manajemen Tugas Kuliah</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
<style>
:root {
    --bg:       #0d0f14;
    --surface:  #161921;
    --card:     #1e2130;
    --border:   #2a2f45;
    --accent:   #6c63ff;
    --accent2:  #00d4aa;
    --warn:     #ff6b6b;
    --yellow:   #ffc947;
    --text:     #e8eaf0;
    --muted:    #8b90a8;
    --radius:   12px;
    --font:     'Space Grotesk', sans-serif;
    --font2:    'Inter', sans-serif;
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { background: var(--bg); color: var(--text); font-family: var(--font); min-height: 100vh; }

/* ── LAYOUT ── */
.shell { display: flex; min-height: 100vh; }
.sidebar {
    width: 240px; flex-shrink: 0;
    background: var(--surface);
    border-right: 1px solid var(--border);
    display: flex; flex-direction: column;
    padding: 24px 0;
    position: fixed; top: 0; left: 0; height: 100vh;
    overflow-y: auto;
}
.main { margin-left: 240px; flex: 1; padding: 32px; max-width: 1100px; }

/* ── SIDEBAR ── */
.logo {
    padding: 0 20px 24px;
    border-bottom: 1px solid var(--border);
    margin-bottom: 16px;
}
.logo h1 { font-size: 18px; font-weight: 700; color: var(--text); }
.logo span { color: var(--accent); }
.logo p { font-size: 11px; color: var(--muted); margin-top: 2px; font-family: var(--font2); }

.nav-item {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 20px; text-decoration: none;
    color: var(--muted); font-size: 14px; font-weight: 500;
    border-left: 3px solid transparent;
    transition: all 0.15s;
}
.nav-item:hover, .nav-item.active {
    color: var(--text); background: rgba(108,99,255,0.1);
    border-left-color: var(--accent);
}
.nav-item .icon { font-size: 16px; width: 20px; text-align: center; }

.notif-badge {
    margin-left: auto; background: var(--warn);
    color: #fff; font-size: 10px; font-weight: 700;
    padding: 2px 6px; border-radius: 20px;
}

.sidebar-user {
    margin-top: auto; padding: 16px 20px;
    border-top: 1px solid var(--border);
    font-size: 13px;
}
.sidebar-user strong { display: block; color: var(--text); }
.sidebar-user a { color: var(--warn); font-size: 12px; text-decoration: none; }

/* ── CARDS & COMPONENTS ── */
.page-header { margin-bottom: 28px; }
.page-header h2 { font-size: 24px; font-weight: 700; }
.page-header p  { color: var(--muted); font-size: 14px; margin-top: 4px; font-family: var(--font2); }

.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 16px; margin-bottom: 28px; }
.stat-card {
    background: var(--card); border: 1px solid var(--border); border-radius: var(--radius);
    padding: 20px; position: relative; overflow: hidden;
}
.stat-card::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
}
.stat-card.total::before   { background: var(--accent); }
.stat-card.selesai::before { background: var(--accent2); }
.stat-card.proses::before  { background: var(--yellow); }
.stat-card.telat::before   { background: var(--warn); }
.stat-card .num { font-size: 32px; font-weight: 700; line-height: 1; }
.stat-card .label { font-size: 12px; color: var(--muted); margin-top: 4px; font-family: var(--font2); }

.card {
    background: var(--card); border: 1px solid var(--border); border-radius: var(--radius);
    padding: 24px; margin-bottom: 20px;
}
.card-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid var(--border);
}
.card-header h3 { font-size: 16px; font-weight: 600; }

/* ── NOTIFIKASI ── */
.notif-bar {
    background: linear-gradient(135deg, rgba(255,107,107,0.15), rgba(255,107,107,0.05));
    border: 1px solid rgba(255,107,107,0.3); border-radius: var(--radius);
    padding: 16px 20px; margin-bottom: 24px;
}
.notif-bar h4 { font-size: 13px; font-weight: 600; color: var(--warn); margin-bottom: 10px; }
.notif-item { font-size: 13px; color: var(--text); padding: 6px 0; font-family: var(--font2); display: flex; gap: 8px; align-items: center; }
.notif-item .dot { width: 6px; height: 6px; background: var(--warn); border-radius: 50%; flex-shrink: 0; }

/* ── FILTER & SEARCH ── */
.filter-bar { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; align-items: center; }
.filter-bar input, .filter-bar select {
    background: var(--surface); border: 1px solid var(--border); border-radius: 8px;
    color: var(--text); padding: 9px 14px; font-size: 13px; font-family: var(--font);
    outline: none;
}
.filter-bar input:focus, .filter-bar select:focus { border-color: var(--accent); }
.filter-bar input { flex: 1; min-width: 200px; }

/* ── TUGAS TABLE / LIST ── */
.tugas-item {
    background: var(--surface); border: 1px solid var(--border); border-radius: 10px;
    padding: 16px 20px; margin-bottom: 12px; display: flex; gap: 16px; align-items: flex-start;
    transition: border-color 0.15s;
    position: relative; overflow: hidden;
}
.tugas-item::before {
    content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 3px;
}
.tugas-item.prioritas-tinggi::before { background: var(--warn); }
.tugas-item.prioritas-sedang::before { background: var(--yellow); }
.tugas-item.prioritas-rendah::before { background: var(--accent2); }
.tugas-item:hover { border-color: var(--accent); }
.tugas-item.done { opacity: 0.55; }

.tugas-info { flex: 1; }
.tugas-judul { font-weight: 600; font-size: 15px; margin-bottom: 4px; }
.tugas-judul.done { text-decoration: line-through; }
.tugas-matkul { font-size: 12px; color: var(--accent); font-weight: 500; margin-bottom: 6px; }
.tugas-meta { display: flex; gap: 12px; font-size: 12px; color: var(--muted); font-family: var(--font2); flex-wrap: wrap; }
.tugas-meta .telat { color: var(--warn); font-weight: 600; }
.tugas-desc { font-size: 13px; color: var(--muted); margin-top: 6px; font-family: var(--font2); line-height: 1.5; }

.tugas-actions { display: flex; flex-direction: column; gap: 6px; align-items: flex-end; flex-shrink: 0; }

/* ── BADGES ── */
.badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;
}
.badge-belum   { background: rgba(139,144,168,0.15); color: var(--muted); }
.badge-proses  { background: rgba(255,201,71,0.15); color: var(--yellow); }
.badge-selesai { background: rgba(0,212,170,0.15); color: var(--accent2); }
.badge-tinggi  { background: rgba(255,107,107,0.15); color: var(--warn); }
.badge-sedang  { background: rgba(255,201,71,0.15); color: var(--yellow); }
.badge-rendah  { background: rgba(0,212,170,0.15); color: var(--accent2); }

/* ── BUTTONS ── */
.btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 9px 18px; border-radius: 8px; font-size: 13px; font-weight: 600;
    font-family: var(--font); cursor: pointer; border: none; text-decoration: none;
    transition: all 0.15s;
}
.btn-primary   { background: var(--accent); color: #fff; }
.btn-primary:hover { background: #5a52e0; }
.btn-success   { background: rgba(0,212,170,0.15); color: var(--accent2); border: 1px solid rgba(0,212,170,0.3); }
.btn-success:hover { background: rgba(0,212,170,0.25); }
.btn-danger    { background: rgba(255,107,107,0.12); color: var(--warn); border: 1px solid rgba(255,107,107,0.3); font-size: 12px; padding: 6px 12px; }
.btn-danger:hover { background: rgba(255,107,107,0.22); }
.btn-ghost     { background: transparent; color: var(--muted); border: 1px solid var(--border); }
.btn-ghost:hover { color: var(--text); border-color: var(--accent); }
.btn-sm        { padding: 5px 12px; font-size: 12px; }

/* ── FORMS ── */
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.form-group { margin-bottom: 16px; }
.form-group.full { grid-column: 1 / -1; }
label { display: block; font-size: 12px; font-weight: 600; color: var(--muted); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
input[type=text], input[type=email], input[type=password], input[type=datetime-local],
textarea, select {
    width: 100%; background: var(--surface); border: 1px solid var(--border); border-radius: 8px;
    color: var(--text); padding: 10px 14px; font-size: 14px; font-family: var(--font);
    outline: none; transition: border-color 0.15s;
}
input:focus, textarea:focus, select:focus { border-color: var(--accent); }
textarea { resize: vertical; min-height: 80px; }
select option { background: var(--surface); }

/* ── AUTH ── */
.auth-wrap {
    min-height: 100vh; display: flex; align-items: center; justify-content: center;
    background: radial-gradient(ellipse at 30% 20%, rgba(108,99,255,0.12), transparent 50%),
                radial-gradient(ellipse at 70% 80%, rgba(0,212,170,0.08), transparent 50%),
                var(--bg);
}
.auth-box { width: 100%; max-width: 420px; padding: 20px; }
.auth-logo { text-align: center; margin-bottom: 32px; }
.auth-logo h1 { font-size: 28px; font-weight: 700; }
.auth-logo span { color: var(--accent); }
.auth-logo p { color: var(--muted); font-size: 14px; margin-top: 4px; }
.auth-card { background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 32px; }
.auth-card h2 { font-size: 20px; margin-bottom: 24px; }
.auth-footer { text-align: center; margin-top: 20px; font-size: 13px; color: var(--muted); }
.auth-footer a { color: var(--accent); text-decoration: none; }

/* ── ALERTS ── */
.alert {
    padding: 12px 16px; border-radius: 8px; margin-bottom: 20px;
    font-size: 13px; font-family: var(--font2); font-weight: 500;
}
.alert-success { background: rgba(0,212,170,0.12); color: var(--accent2); border: 1px solid rgba(0,212,170,0.3); }
.alert-error   { background: rgba(255,107,107,0.12); color: var(--warn); border: 1px solid rgba(255,107,107,0.3); }

/* ── STATUS SELECT ── */
.status-form { display: inline-flex; }
.status-select {
    background: var(--surface); border: 1px solid var(--border); border-radius: 6px;
    color: var(--text); font-size: 12px; padding: 4px 8px; font-family: var(--font); cursor: pointer;
}

/* ── PROGRESS BAR ── */
.progress-wrap { margin-bottom: 28px; }
.progress-label { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 8px; }
.progress-bar-bg { height: 8px; background: var(--border); border-radius: 4px; overflow: hidden; }
.progress-bar-fill { height: 100%; border-radius: 4px; background: linear-gradient(90deg, var(--accent), var(--accent2)); transition: width 0.5s; }

/* ── EMPTY STATE ── */
.empty-state { text-align: center; padding: 60px 20px; color: var(--muted); }
.empty-state .icon { font-size: 48px; margin-bottom: 16px; display: block; }
.empty-state h3 { font-size: 16px; color: var(--text); margin-bottom: 8px; }
.empty-state p { font-size: 14px; }

/* ── RESPONSIVE ── */
@media (max-width: 768px) {
    .sidebar { display: none; }
    .main { margin-left: 0; padding: 16px; }
    .form-grid { grid-template-columns: 1fr; }
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>
</head>
<body>

<?php if (!isLoggedIn() && $page !== 'register'): ?>
<!-- ============ LOGIN PAGE ============ -->
<div class="auth-wrap">
<div class="auth-box">
    <div class="auth-logo">
        <h1>Task<span>Kampus</span></h1>
        <p>Manajemen Tugas Kuliah</p>
    </div>
    <div class="auth-card">
        <h2>Masuk ke Akun</h2>
        <?php if ($msg): ?><div class="alert alert-<?= $msgType ?>"><?= sanitize($msg) ?></div><?php endif; ?>
        <form method="POST">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="login">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="email@kampus.ac.id" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:12px">
                Masuk →
            </button>
        </form>
        <p style="font-size:12px;color:var(--muted);margin-top:16px;text-align:center;font-family:var(--font2)">
            Demo: <code>demo@kampus.ac.id</code> / <code>password</code>
        </p>
    </div>
    <p class="auth-footer">Belum punya akun? <a href="?page=register">Daftar sekarang</a></p>
</div>
</div>

<?php elseif (!isLoggedIn() && $page === 'register'): ?>
<!-- ============ REGISTER PAGE ============ -->
<div class="auth-wrap">
<div class="auth-box">
    <div class="auth-logo">
        <h1>Task<span>Kampus</span></h1>
        <p>Manajemen Tugas Kuliah</p>
    </div>
    <div class="auth-card">
        <h2>Buat Akun Baru</h2>
        <?php if ($msg): ?><div class="alert alert-<?= $msgType ?>"><?= sanitize($msg) ?></div><?php endif; ?>
        <form method="POST">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="register">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" placeholder="Nama kamu" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="email@kampus.ac.id" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Min. 6 karakter" required>
            </div>
            <div class="form-group">
                <label>Konfirmasi Password</label>
                <input type="password" name="confirm" placeholder="Ulangi password" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:12px">
                Daftar →
            </button>
        </form>
    </div>
    <p class="auth-footer">Sudah punya akun? <a href="?page=login">Masuk</a></p>
</div>
</div>

<?php else: ?>
<!-- ============ APP SHELL ============ -->
<div class="shell">
<!-- SIDEBAR -->
<nav class="sidebar">
    <div class="logo">
        <h1>Task<span>Kampus</span></h1>
        <p>Manajemen Tugas Kuliah</p>
    </div>
    <a href="?page=dashboard" class="nav-item <?= $page==='dashboard'?'active':'' ?>">
        <span class="icon">📋</span> Dashboard
    </a>
    <a href="?page=tambah" class="nav-item <?= $page==='tambah'?'active':'' ?>">
        <span class="icon">➕</span> Tambah Tugas
    </a>
    <a href="?page=notifikasi" class="nav-item <?= $page==='notifikasi'?'active':'' ?>">
        <span class="icon">🔔</span> Notifikasi
        <?php if (count($notif_list) > 0): ?>
            <span class="notif-badge"><?= count($notif_list) ?></span>
        <?php endif; ?>
    </a>
    <div class="sidebar-user">
        <strong><?= sanitize($_SESSION['user_nama']) ?></strong>
        <a href="?page=logout">Keluar →</a>
    </div>
</nav>

<!-- MAIN -->
<main class="main">

<?php if ($msg && $page !== 'login'): ?>
    <div class="alert alert-<?= $msgType ?>"><?= sanitize($msg) ?></div>
<?php endif; ?>

<?php if (count($notif_list) > 0 && $page === 'dashboard'): ?>
<div class="notif-bar">
    <h4>🔔 Deadline Mendekat (3 hari ke depan)</h4>
    <?php foreach ($notif_list as $n): ?>
    <div class="notif-item">
        <span class="dot"></span>
        <span>
            <strong><?= sanitize($n['judul']) ?></strong> —
            <?= sanitize($n['mata_kuliah']) ?> —
            <?= date('d M Y H:i', strtotime($n['deadline'])) ?>
        </span>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php
// ─────────────────────────────────────────────
// DASHBOARD
// ─────────────────────────────────────────────
if ($page === 'dashboard'):
    $pct = ($stats['total'] > 0) ? round(($stats['selesai'] / $stats['total']) * 100) : 0;
?>

<div class="page-header">
    <h2>Dashboard</h2>
    <p>Selamat datang, <?= sanitize($_SESSION['user_nama']) ?>! 👋</p>
</div>

<!-- STATS -->
<div class="stats-grid">
    <div class="stat-card total">
        <div class="num"><?= $stats['total'] ?? 0 ?></div>
        <div class="label">Total Tugas</div>
    </div>
    <div class="stat-card selesai">
        <div class="num" style="color:var(--accent2)"><?= $stats['selesai'] ?? 0 ?></div>
        <div class="label">Selesai</div>
    </div>
    <div class="stat-card proses">
        <div class="num" style="color:var(--yellow)"><?= $stats['proses'] ?? 0 ?></div>
        <div class="label">Dalam Proses</div>
    </div>
    <div class="stat-card telat">
        <div class="num" style="color:var(--warn)"><?= $stats['terlambat'] ?? 0 ?></div>
        <div class="label">Terlambat</div>
    </div>
</div>

<!-- PROGRESS -->
<?php if ($stats['total'] > 0): ?>
<div class="progress-wrap">
    <div class="progress-label">
        <span style="font-size:13px;font-weight:600">Progress Keseluruhan</span>
        <span style="color:var(--accent2);font-weight:700"><?= $pct ?>%</span>
    </div>
    <div class="progress-bar-bg">
        <div class="progress-bar-fill" style="width:<?= $pct ?>%"></div>
    </div>
</div>
<?php endif; ?>

<!-- FILTER + LIST -->
<div class="card">
    <div class="card-header">
        <h3>Daftar Tugas</h3>
        <a href="?page=tambah" class="btn btn-primary btn-sm">+ Tambah</a>
    </div>

    <!-- Filter Bar -->
    <form method="GET" style="margin-bottom:0">
        <input type="hidden" name="page" value="dashboard">
        <div class="filter-bar">
            <input type="text" name="q" placeholder="🔍 Cari tugas..." value="<?= sanitize($_GET['q'] ?? '') ?>">
            <select name="status" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="belum"   <?= ($filter_status==='belum'  ?'selected':'') ?>>Belum</option>
                <option value="proses"  <?= ($filter_status==='proses' ?'selected':'') ?>>Proses</option>
                <option value="selesai" <?= ($filter_status==='selesai'?'selected':'') ?>>Selesai</option>
            </select>
            <?php if (!empty($matkul_list)): ?>
            <select name="matkul" onchange="this.form.submit()">
                <option value="">Semua Matkul</option>
                <?php foreach ($matkul_list as $mk): ?>
                <option value="<?= sanitize($mk['mata_kuliah']) ?>" <?= ($filter_matkul===$mk['mata_kuliah']?'selected':'') ?>>
                    <?= sanitize($mk['mata_kuliah']) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <?php endif; ?>
            <button type="submit" class="btn btn-ghost btn-sm">Filter</button>
            <?php if ($filter_status || $filter_matkul || !empty($_GET['q'])): ?>
            <a href="?page=dashboard" class="btn btn-ghost btn-sm">Reset</a>
            <?php endif; ?>
        </div>
    </form>

    <!-- Tugas List -->
    <?php if (empty($tugas_list)): ?>
    <div class="empty-state">
        <span class="icon">📭</span>
        <h3>Belum ada tugas</h3>
        <p>Klik "Tambah" untuk membuat tugas pertama kamu!</p>
    </div>
    <?php else: ?>
    <?php foreach ($tugas_list as $t):
        $selesai = $t['status'] === 'selesai';
        $telat   = strtotime($t['deadline']) < time() && !$selesai;
        $fmt_dl  = date('d M Y H:i', strtotime($t['deadline']));
    ?>
    <div class="tugas-item prioritas-<?= $t['prioritas'] ?> <?= $selesai ? 'done' : '' ?>">
        <div class="tugas-info">
            <div class="tugas-judul <?= $selesai ? 'done' : '' ?>"><?= sanitize($t['judul']) ?></div>
            <div class="tugas-matkul"><?= sanitize($t['mata_kuliah']) ?></div>
            <?php if ($t['deskripsi']): ?>
            <div class="tugas-desc"><?= sanitize($t['deskripsi']) ?></div>
            <?php endif; ?>
            <div class="tugas-meta">
                <span>📅 <?= $fmt_dl ?></span>
                <?php if ($telat): ?>
                    <span class="telat">⚠️ Terlambat!</span>
                <?php endif; ?>
                <span class="badge badge-<?= $t['prioritas'] ?>"><?= ucfirst($t['prioritas']) ?></span>
                <span class="badge badge-<?= $t['status'] ?>"><?= ucfirst($t['status']) ?></span>
            </div>
        </div>
        <div class="tugas-actions">
            <!-- Quick status update -->
            <form method="POST" class="status-form">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="tugas_id" value="<?= $t['id'] ?>">
                <select name="status" class="status-select" onchange="this.form.submit()">
                    <option value="belum"   <?= $t['status']==='belum'  ?'selected':'' ?>>⬜ Belum</option>
                    <option value="proses"  <?= $t['status']==='proses' ?'selected':'' ?>>🔄 Proses</option>
                    <option value="selesai" <?= $t['status']==='selesai'?'selected':'' ?>>✅ Selesai</option>
                </select>
            </form>
            <a href="?page=edit&id=<?= $t['id'] ?>" class="btn btn-ghost btn-sm">✏️ Edit</a>
            <form method="POST" onsubmit="return confirm('Hapus tugas ini?')">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="hapus_tugas">
                <input type="hidden" name="tugas_id" value="<?= $t['id'] ?>">
                <button type="submit" class="btn btn-danger">🗑️</button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php
// ─────────────────────────────────────────────
// TAMBAH TUGAS
// ─────────────────────────────────────────────
elseif ($page === 'tambah'):
?>
<div class="page-header">
    <h2>➕ Tambah Tugas Baru</h2>
    <p>Isi detail tugas kuliah kamu</p>
</div>
<div class="card">
<form method="POST">
    <?= csrfField() ?>
    <input type="hidden" name="action" value="tambah_tugas">
    <div class="form-grid">
        <div class="form-group full">
            <label>Judul Tugas</label>
            <input type="text" name="judul" placeholder="Contoh: Laporan Praktikum Algoritma" required>
        </div>
        <div class="form-group">
            <label>Mata Kuliah</label>
            <input type="text" name="mata_kuliah" placeholder="Contoh: Analisis Algoritma" required>
        </div>
        <div class="form-group">
            <label>Deadline</label>
            <input type="datetime-local" name="deadline" required>
        </div>
        <div class="form-group">
            <label>Prioritas</label>
            <select name="prioritas">
                <option value="rendah">🟢 Rendah</option>
                <option value="sedang" selected>🟡 Sedang</option>
                <option value="tinggi">🔴 Tinggi</option>
            </select>
        </div>
        <div class="form-group full">
            <label>Deskripsi (opsional)</label>
            <textarea name="deskripsi" placeholder="Detail tugas, catatan tambahan..."></textarea>
        </div>
    </div>
    <div style="display:flex;gap:10px">
        <button type="submit" class="btn btn-primary">💾 Simpan Tugas</button>
        <a href="?page=dashboard" class="btn btn-ghost">Batal</a>
    </div>
</form>
</div>

<?php
// ─────────────────────────────────────────────
// EDIT TUGAS
// ─────────────────────────────────────────────
elseif ($page === 'edit' && $edit_tugas):
    $t = $edit_tugas;
    $dl_fmt = date('Y-m-d\TH:i', strtotime($t['deadline']));
?>
<div class="page-header">
    <h2>✏️ Edit Tugas</h2>
    <p><?= sanitize($t['judul']) ?></p>
</div>
<div class="card">
<form method="POST">
    <?= csrfField() ?>
    <input type="hidden" name="action" value="edit_tugas">
    <input type="hidden" name="tugas_id" value="<?= $t['id'] ?>">
    <div class="form-grid">
        <div class="form-group full">
            <label>Judul Tugas</label>
            <input type="text" name="judul" value="<?= sanitize($t['judul']) ?>" required>
        </div>
        <div class="form-group">
            <label>Mata Kuliah</label>
            <input type="text" name="mata_kuliah" value="<?= sanitize($t['mata_kuliah']) ?>" required>
        </div>
        <div class="form-group">
            <label>Deadline</label>
            <input type="datetime-local" name="deadline" value="<?= $dl_fmt ?>" required>
        </div>
        <div class="form-group">
            <label>Prioritas</label>
            <select name="prioritas">
                <option value="rendah"  <?= $t['prioritas']==='rendah' ?'selected':'' ?>>🟢 Rendah</option>
                <option value="sedang"  <?= $t['prioritas']==='sedang' ?'selected':'' ?>>🟡 Sedang</option>
                <option value="tinggi"  <?= $t['prioritas']==='tinggi' ?'selected':'' ?>>🔴 Tinggi</option>
            </select>
        </div>
        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <option value="belum"   <?= $t['status']==='belum'  ?'selected':'' ?>>⬜ Belum</option>
                <option value="proses"  <?= $t['status']==='proses' ?'selected':'' ?>>🔄 Proses</option>
                <option value="selesai" <?= $t['status']==='selesai'?'selected':'' ?>>✅ Selesai</option>
            </select>
        </div>
        <div class="form-group full">
            <label>Deskripsi</label>
            <textarea name="deskripsi"><?= sanitize($t['deskripsi']) ?></textarea>
        </div>
    </div>
    <div style="display:flex;gap:10px">
        <button type="submit" class="btn btn-primary">💾 Update Tugas</button>
        <a href="?page=dashboard" class="btn btn-ghost">Batal</a>
    </div>
</form>
</div>

<?php
// ─────────────────────────────────────────────
// NOTIFIKASI
// ─────────────────────────────────────────────
elseif ($page === 'notifikasi'):
?>
<div class="page-header">
    <h2>🔔 Notifikasi Deadline</h2>
    <p>Tugas yang deadline-nya dalam 3 hari ke depan</p>
</div>

<?php if (empty($notif_list)): ?>
<div class="card">
<div class="empty-state">
    <span class="icon">🎉</span>
    <h3>Semua aman!</h3>
    <p>Tidak ada tugas yang deadline-nya mendekat. Good job!</p>
</div>
</div>
<?php else: ?>
<div class="card">
<?php foreach ($notif_list as $n):
    $sisa = ceil((strtotime($n['deadline']) - time()) / 3600);
    $sisa_label = $sisa <= 0 ? '⚠️ Sudah lewat!' : ($sisa < 24 ? "⏰ {$sisa} jam lagi" : '🗓️ ' . ceil($sisa/24) . ' hari lagi');
?>
<div class="tugas-item prioritas-<?= $n['prioritas'] ?>">
    <div class="tugas-info">
        <div class="tugas-judul"><?= sanitize($n['judul']) ?></div>
        <div class="tugas-matkul"><?= sanitize($n['mata_kuliah']) ?></div>
        <div class="tugas-meta">
            <span>📅 <?= date('d M Y H:i', strtotime($n['deadline'])) ?></span>
            <span style="color:var(--warn);font-weight:600"><?= $sisa_label ?></span>
            <span class="badge badge-<?= $n['prioritas'] ?>"><?= ucfirst($n['prioritas']) ?></span>
        </div>
    </div>
    <div class="tugas-actions">
        <a href="?page=edit&id=<?= $n['id'] ?>" class="btn btn-ghost btn-sm">✏️ Edit</a>
    </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php endif; ?>

</main>
</div>
<?php endif; ?>

<script>
// Auto-refresh notifikasi badge tiap 5 menit
setTimeout(() => location.reload(), 300000);

// Hapus flash message setelah 4 detik
const alert = document.querySelector('.alert');
if (alert) setTimeout(() => alert.style.opacity = '0', 4000);
</script>

</body>
</html>

