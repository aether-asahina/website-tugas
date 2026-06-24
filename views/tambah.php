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

