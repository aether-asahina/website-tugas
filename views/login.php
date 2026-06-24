<div class="auth-wrap">
<div class="auth-box">
    <div class="auth-logo">
        <h1>Task<span>Kampus</span></h1>
        <p>Manajemen Tugas Kuliah</p>
    </div>
    <div class="auth-card">
        <h2>Masuk ke Akun</h2>
        <?php if ($msg): ?><div class="alert alert-<?= $msgType ?>"><?= sanitize($msg) ?></div><?php endif; ?>
        <form method="POST">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="login">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="email@kampus.ac.id" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:12px">
                Masuk →
            </button>
        </form>
    </div>
    <p class="auth-footer">Belum punya akun? <a href="?page=register">Daftar sekarang</a></p>
</div>
</div>

