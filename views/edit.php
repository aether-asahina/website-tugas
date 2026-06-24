<?php
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

