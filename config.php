<?php
// =============================================
// KONFIGURASI DATABASE (SUPABASE / POSTGRES)
// =============================================
// Kredensial diambil dari file .env (TIDAK ikut di-commit ke git).

function loadEnv($path) {
    if (!file_exists($path)) {
        die('<div style="padding:20px;color:red;font-family:sans-serif;">
            ❌ File .env tidak ditemukan di ' . htmlspecialchars($path) . '<br>
            Copy .env.example jadi .env, lalu isi kredensial Supabase kamu.
        </div>');
    }
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
        $key   = trim($key);
        $value = trim($value, " \t\"'");
        if ($key !== '' && getenv($key) === false) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

loadEnv(__DIR__ . '/.env');

define('DB_HOST', $_ENV['DB_HOST'] ?? '');
define('DB_PORT', $_ENV['DB_PORT'] ?? '5432');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'postgres');
define('DB_USER', $_ENV['DB_USER'] ?? 'postgres');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = sprintf(
                'pgsql:host=%s;port=%s;dbname=%s;sslmode=require',
                DB_HOST, DB_PORT, DB_NAME
            );
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            die('<div style="padding:20px;color:red;font-family:sans-serif;">
                ❌ Koneksi database (Supabase) gagal: ' . htmlspecialchars($e->getMessage()) . '
            </div>');
        }
    }
    return $pdo;
}
