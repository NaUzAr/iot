-- Init script untuk membuat multiple databases
-- File ini akan dijalankan otomatis saat PostgreSQL container pertama kali start

-- Database untuk SmartAgri
CREATE DATABASE smartagri;
CREATE USER smartagri_user WITH ENCRYPTED PASSWORD 'smartagri_secret';
GRANT ALL PRIVILEGES ON DATABASE smartagri TO smartagri_user;

-- Database untuk Web 2 (ganti nama sesuai kebutuhan)
CREATE DATABASE web2_db;
CREATE USER web2_user WITH ENCRYPTED PASSWORD 'web2_secret';
GRANT ALL PRIVILEGES ON DATABASE web2_db TO web2_user;

-- Tambahkan database lain jika perlu:
-- CREATE DATABASE web3_db;
-- CREATE USER web3_user WITH ENCRYPTED PASSWORD 'web3_secret';
-- GRANT ALL PRIVILEGES ON DATABASE web3_db TO web3_user;
