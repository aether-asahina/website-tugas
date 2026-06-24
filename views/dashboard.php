<?php $pct = ($stats['total'] > 0) ? round(($stats['selesai'] / $stats['total']) * 100) : 0; ?>

<div class="page-header">
    <h2>Dashboard</h2>
    <p>Selamat datang, <?= sanitize($_SESSION['user_nama']) ?>! 👋</p>
</div>

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

<div class="card">
    <div class="card-header">
        <h3>Daftar Tugas</h3>
        <a href="?page=tambah" class="btn btn-primary btn-sm">+ Tambah</a>
    </div>

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
