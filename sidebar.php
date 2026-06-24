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

