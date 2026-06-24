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

