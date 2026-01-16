-- ========================================
-- TEMPLATE: SQL untuk tambah database baru
-- ========================================
-- Jalankan di PostgreSQL untuk setiap web baru
--
-- YANG PERLU DIUBAH:
-- 1. webname_db     → Nama database
-- 2. webname_user   → Username database
-- 3. webname_secret → Password database
-- ========================================

-- ⬇️ UBAH: Ganti "webname" dengan nama project

CREATE DATABASE webname_db;

CREATE USER webname_user WITH ENCRYPTED PASSWORD 'webname_secret';

GRANT ALL PRIVILEGES ON DATABASE webname_db TO webname_user;

-- Grant schema privileges (untuk PostgreSQL 15+)
\c webname_db
GRANT ALL ON SCHEMA public TO webname_user;
