-- =============================================
-- SETUP DATABASE: Manajemen Tugas Kuliah
-- Jalankan file ini di phpMyAdmin atau MySQL CLI
-- =============================================

CREATE DATABASE IF NOT EXISTS tugas_kuliah CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tugas_kuliah;

-- Tabel Users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Tugas
CREATE TABLE IF NOT EXISTS tugas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    judul VARCHAR(200) NOT NULL,
    mata_kuliah VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    deadline DATETIME NOT NULL,
    prioritas ENUM('rendah', 'sedang', 'tinggi') DEFAULT 'sedang',
    status ENUM('belum', 'proses', 'selesai') DEFAULT 'belum',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- User demo (password: demo123)
INSERT INTO users (nama, email, password) VALUES
('Demo User', 'demo@kampus.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Data tugas contoh
INSERT INTO tugas (user_id, judul, mata_kuliah, deskripsi, deadline, prioritas, status) VALUES
(1, 'Laporan Analisis Algoritma', 'Analisis Algoritma', 'Buat laporan tentang Exponentiation by Squaring dan kompleksitas O(log n)', DATE_ADD(NOW(), INTERVAL 3 DAY), 'tinggi', 'proses'),
(1, 'Presentasi IoT Smart Pakcoy', 'Internet of Things', 'Presentasi sistem penyiraman otomatis menggunakan ESP32 dan sensor kelembaban', DATE_ADD(NOW(), INTERVAL 7 DAY), 'tinggi', 'belum'),
(1, 'UTS Struktur Data', 'Struktur Data', 'Ujian Tengah Semester materi tree dan graph', DATE_ADD(NOW(), INTERVAL 1 DAY), 'tinggi', 'belum'),
(1, 'Tugas Web Programming', 'Pemrograman Web', 'Buat CRUD sederhana dengan PHP dan MySQL', DATE_ADD(NOW(), INTERVAL 14 DAY), 'sedang', 'selesai');
