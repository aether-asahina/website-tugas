<?php
// =============================================
// AUTH ACTIONS (login, register, logout)
// Dipanggil dari index.php berdasarkan $action / $page
// =============================================

// LOGIN
if ($action === 'login') {
    checkCsrf();
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $db   = getDB();
    $stmt = $db->prepare('SELECT id, nama, password FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $row = $stmt->fetch();
    if ($row && password_verify($password, $row['password'])) {
        $_SESSION['user_id']   = $row['id'];
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
        $db   = getDB();
        $hash = password_hash($password, PASSWORD_BCRYPT);
        try {
            $stmt = $db->prepare('INSERT INTO users (nama, email, password) VALUES (?, ?, ?)');
            $stmt->execute([$nama, $email, $hash]);
            $msg = 'Registrasi berhasil! Silakan login.'; $msgType = 'success'; $page = 'login';
        } catch (PDOException $e) {
            // 23505 = unique_violation di Postgres (email sudah dipakai)
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
