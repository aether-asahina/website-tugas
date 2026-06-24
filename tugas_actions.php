<?php
// =============================================
// TUGAS ACTIONS (CRUD)
// =============================================

// TAMBAH TUGAS
if ($action === 'tambah_tugas') {
    requireLogin(); checkCsrf();
    $judul     = trim($_POST['judul'] ?? '');
    $matkul    = trim($_POST['mata_kuliah'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $deadline  = $_POST['deadline'] ?? '';
    $prioritas = $_POST['prioritas'] ?? 'sedang';
    $uid = $_SESSION['user_id'];

    $db   = getDB();
    $stmt = $db->prepare(
        'INSERT INTO tugas (user_id, judul, mata_kuliah, deskripsi, deadline, prioritas)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([$uid, $judul, $matkul, $deskripsi, $deadline, $prioritas]);
    $msg  = 'Tugas berhasil ditambahkan!';
    $page = 'dashboard';
}

// UPDATE STATUS (quick update dari dropdown)
if ($action === 'update_status') {
    requireLogin(); checkCsrf();
    $tid    = (int)($_POST['tugas_id'] ?? 0);
    $status = $_POST['status'] ?? 'belum';
    $uid    = $_SESSION['user_id'];

    $db   = getDB();
    $stmt = $db->prepare('UPDATE tugas SET status = ? WHERE id = ? AND user_id = ?');
    $stmt->execute([$status, $tid, $uid]);
    header('Location: ?page=dashboard&updated=1');
    exit;
}

// HAPUS TUGAS
if ($action === 'hapus_tugas') {
    requireLogin(); checkCsrf();
    $tid = (int)($_POST['tugas_id'] ?? 0);
    $uid = $_SESSION['user_id'];

    $db   = getDB();
    $stmt = $db->prepare('DELETE FROM tugas WHERE id = ? AND user_id = ?');
    $stmt->execute([$tid, $uid]);
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
    $uid       = $_SESSION['user_id'];

    $db   = getDB();
    $stmt = $db->prepare(
        'UPDATE tugas
         SET judul = ?, mata_kuliah = ?, deskripsi = ?, deadline = ?, prioritas = ?, status = ?
         WHERE id = ? AND user_id = ?'
    );
    $stmt->execute([$judul, $matkul, $deskripsi, $deadline, $prioritas, $status, $tid, $uid]);
    $msg  = 'Tugas berhasil diupdate!';
    $page = 'dashboard';
}
