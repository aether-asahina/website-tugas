<?php
// =============================================
// MANAJEMEN TUGAS KULIAH — ENTRY POINT
// Simpan folder ini di: C:/xampp/htdocs/tugas_kuliah/
// Database: Supabase (Postgres) — lihat config.php
// =============================================

session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

// ── ROUTING & STATE ──────────────────────────
$page    = $_GET['page']   ?? 'dashboard';
$action  = $_POST['action'] ?? '';
$msg     = '';
$msgType = 'success';

// ── ACTIONS (login/register/logout, lalu CRUD tugas) ──
require __DIR__ . '/auth.php';
require __DIR__ . '/tugas_actions.php';

// ── DATA UNTUK VIEW (hanya kalau sudah login) ──
require __DIR__ . '/data.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>TaskKampus — Manajemen Tugas Kuliah</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php if (!isLoggedIn() && $page !== 'register'): ?>

    <?php include __DIR__ . '/views/login.php'; ?>

<?php elseif (!isLoggedIn() && $page === 'register'): ?>

    <?php include __DIR__ . '/views/register.php'; ?>

<?php else: ?>

<div class="shell">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>

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
        if ($page === 'dashboard') {
            include __DIR__ . '/views/dashboard.php';
        } elseif ($page === 'tambah') {
            include __DIR__ . '/views/tambah.php';
        } elseif ($page === 'edit' && $edit_tugas) {
            include __DIR__ . '/views/edit.php';
        } elseif ($page === 'notifikasi') {
            include __DIR__ . '/views/notifikasi.php';
        }
        ?>
    </main>
</div>

<?php endif; ?>

<script>
setTimeout(() => location.reload(), 300000);
const alertBox = document.querySelector('.alert');
if (alertBox) setTimeout(() => alertBox.style.opacity = '0', 4000);
</script>

</body>
</html>
