<?php
// =============================================
// DATA UNTUK VIEW
// Catatan perbedaan vs MySQL:
// - FIELD(...) (MySQL) -> diganti CASE WHEN (Postgres)
// - SUM(kondisi_boolean) (MySQL) -> SUM(CASE WHEN ... THEN 1 ELSE 0 END) (Postgres)
// - DATE_ADD(NOW(), INTERVAL 3 DAY) -> NOW() + INTERVAL '3 days'
// =============================================

$tugas_list  = [];
$stats       = ['total' => 0, 'selesai' => 0, 'proses' => 0, 'belum' => 0, 'terlambat' => 0];
$notif_list  = [];
$edit_tugas  = null;
$matkul_list = [];

$filter_status = '';
$filter_matkul = '';
$search        = '';

if (isLoggedIn()) {
    $db  = getDB();
    $uid = $_SESSION['user_id'];

    // Filter
    $filter_status = $_GET['status'] ?? '';
    $filter_matkul = $_GET['matkul'] ?? '';
    $search        = $_GET['q'] ?? '';

    $where  = 'WHERE t.user_id = ?';
    $params = [$uid];

    if ($filter_status) { $where .= ' AND t.status = ?'; $params[] = $filter_status; }
    if ($filter_matkul) { $where .= ' AND t.mata_kuliah = ?'; $params[] = $filter_matkul; }
    if ($search)        { $where .= ' AND (t.judul ILIKE ? OR t.mata_kuliah ILIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; }

    $sql = "SELECT * FROM tugas t $where
            ORDER BY
                CASE t.prioritas
                    WHEN 'tinggi' THEN 1
                    WHEN 'sedang' THEN 2
                    WHEN 'rendah' THEN 3
                    ELSE 4
                END,
                t.deadline ASC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $tugas_list = $stmt->fetchAll();

    // Stats
    $r = $db->prepare("SELECT
            COUNT(*) AS total,
            SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) AS selesai,
            SUM(CASE WHEN status = 'proses'  THEN 1 ELSE 0 END) AS proses,
            SUM(CASE WHEN status = 'belum'   THEN 1 ELSE 0 END) AS belum,
            SUM(CASE WHEN deadline < NOW() AND status != 'selesai' THEN 1 ELSE 0 END) AS terlambat
        FROM tugas WHERE user_id = ?");
    $r->execute([$uid]);
    $stats = $r->fetch() ?: $stats;

    // Notifikasi: deadline dalam 3 hari & belum selesai
    $n = $db->prepare("SELECT * FROM tugas
        WHERE user_id = ?
          AND status != 'selesai'
          AND deadline BETWEEN NOW() AND (NOW() + INTERVAL '3 days')
        ORDER BY deadline ASC");
    $n->execute([$uid]);
    $notif_list = $n->fetchAll();

    // Edit form data
    if ($page === 'edit' && isset($_GET['id'])) {
        $eid = (int)$_GET['id'];
        $e = $db->prepare('SELECT * FROM tugas WHERE id = ? AND user_id = ?');
        $e->execute([$eid, $uid]);
        $edit_tugas = $e->fetch() ?: null;
    }

    // Mata kuliah unik untuk filter
    $mk = $db->prepare('SELECT DISTINCT mata_kuliah FROM tugas WHERE user_id = ? ORDER BY mata_kuliah');
    $mk->execute([$uid]);
    $matkul_list = $mk->fetchAll();
}

// URL helper
if (isset($_GET['updated'])) { $msg = 'Status tugas diperbarui!'; $msgType = 'success'; }
if (isset($_GET['deleted'])) { $msg = 'Tugas dihapus.'; $msgType = 'error'; }

