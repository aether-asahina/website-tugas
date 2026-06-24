-- =============================================
-- SCHEMA UNTUK SUPABASE (POSTGRES)
-- Jalankan ini di: Supabase Dashboard -> SQL Editor -> New query
-- =============================================

CREATE TABLE IF NOT EXISTS users (
    id         SERIAL PRIMARY KEY,
    nama       VARCHAR(150)        NOT NULL,
    email      VARCHAR(150) UNIQUE NOT NULL,
    password   VARCHAR(255)        NOT NULL,
    created_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS tugas (
    id          SERIAL PRIMARY KEY,
    user_id     INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    judul       VARCHAR(255) NOT NULL,
    mata_kuliah VARCHAR(150) NOT NULL,
    deskripsi   TEXT,
    deadline    TIMESTAMP NOT NULL,
    prioritas   VARCHAR(10) NOT NULL DEFAULT 'sedang' CHECK (prioritas IN ('rendah','sedang','tinggi')),
    status      VARCHAR(10) NOT NULL DEFAULT 'belum'  CHECK (status IN ('belum','proses','selesai')),
    created_at  TIMESTAMP DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_tugas_user_id  ON tugas(user_id);
CREATE INDEX IF NOT EXISTS idx_tugas_deadline ON tugas(deadline);

-- Catatan:
-- - Kalau mau pakai Supabase Auth (bukan tabel users manual + password_hash sendiri),
--   itu perubahan arsitektur lebih besar. Schema ini masih asumsi auth manual seperti kode lama.
-- - Row Level Security (RLS) di Supabase TIDAK relevan di sini karena PHP konek
--   langsung pakai DB credentials (bukan dari browser/JS), jadi RLS boleh dibiarkan off
--   untuk tabel ini, atau ON dengan policy permissive untuk role yang dipakai PHP.

