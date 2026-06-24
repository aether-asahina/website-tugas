<?php
// =============================================
// HELPER FUNCTIONS UMUM
// =============================================

function sanitize($val) {
    return htmlspecialchars(trim($val ?? ''), ENT_QUOTES, 'UTF-8');
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
