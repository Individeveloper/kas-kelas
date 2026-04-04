-- ============================================================
-- DATABASE KAS KELAS - STRUKTUR LENGKAP DENGAN RELASI
-- ============================================================
-- Jalankan file ini di phpMyAdmin untuk membuat semua tabel
-- ============================================================

-- Buat database (jika belum ada)
CREATE DATABASE IF NOT EXISTS kasKelas;
USE kasKelas;

-- ============================================================
-- TABEL USER (untuk login)
-- ============================================================
CREATE TABLE IF NOT EXISTS user (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('bendahara', 'wali kelas', 'ketua kelas') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Insert default user
INSERT INTO user (username, password, role) VALUES 
('bendahara', 'bendahara123', 'bendahara'),
('walikelas', 'walikelas123', 'wali kelas'),
('ketua', 'ketua123', 'ketua kelas')
ON DUPLICATE KEY UPDATE username = username;

-- ============================================================
-- TABEL MURID
-- ============================================================
CREATE TABLE IF NOT EXISTS murid (
    id_murid INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    kelas VARCHAR(20) NOT NULL,
    `status` ENUM('Aktif', 'Tidak Aktif') DEFAULT 'Aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABEL TRANSAKSI (dengan relasi ke murid)
-- ============================================================
CREATE TABLE IF NOT EXISTS transaksi (
    id_transaksi INT AUTO_INCREMENT PRIMARY KEY,
    id_murid INT NOT NULL,
    tanggal DATE NOT NULL,
    jenis ENUM('Masuk', 'Keluar') NOT NULL,
    jumlah INT NOT NULL,
    keterangan VARCHAR(150) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- INDEX untuk mempercepat query
    INDEX idx_transaksi_murid (id_murid),
    INDEX idx_transaksi_tanggal (tanggal),
    INDEX idx_transaksi_jenis (jenis),
    
    -- FOREIGN KEY RELASI ke tabel murid
    CONSTRAINT fk_transaksi_murid 
        FOREIGN KEY (id_murid) REFERENCES murid(id_murid) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABEL PENGATURAN (untuk nominal kas)
-- ============================================================
CREATE TABLE IF NOT EXISTS pengaturan (
    id_pengaturan INT AUTO_INCREMENT PRIMARY KEY,
    nama_pengaturan VARCHAR(50) NOT NULL UNIQUE,
    nilai VARCHAR(255) NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Insert default nominal kas
INSERT INTO pengaturan (nama_pengaturan, nilai) VALUES ('nominal_kas', '20000')
ON DUPLICATE KEY UPDATE nilai = nilai;

-- ============================================================
-- TABEL TAGIHAN (dengan relasi ke murid)
-- ============================================================
CREATE TABLE IF NOT EXISTS tagihan (
    id_tagihan INT AUTO_INCREMENT PRIMARY KEY,
    id_murid INT NOT NULL,
    bulan INT NOT NULL,
    tahun INT NOT NULL,
    nominal INT NOT NULL DEFAULT 20000,
    jumlah_bayar INT NOT NULL DEFAULT 0,
    status_bayar ENUM('Belum', 'Sebagian', 'Lunas') DEFAULT 'Belum',
    tanggal_bayar DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- INDEX untuk mempercepat query
    INDEX idx_tagihan_bulan_tahun (bulan, tahun),
    INDEX idx_tagihan_status (status_bayar),
    
    -- UNIQUE: 1 murid hanya punya 1 tagihan per bulan
    UNIQUE KEY unique_tagihan (id_murid, bulan, tahun),
    
    -- FOREIGN KEY RELASI ke tabel murid
    CONSTRAINT fk_tagihan_murid 
        FOREIGN KEY (id_murid) REFERENCES murid(id_murid) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;


-- ============================================================
-- QUERY UNTUK UPDATE TABEL YANG SUDAH ADA
-- ============================================================
-- Jalankan query di bawah ini SATU PER SATU jika tabel sudah ada
-- dan perlu diperbaiki strukturnya:

-- 1. Perbaiki tabel transaksi (tambah AUTO_INCREMENT & relasi)
/*
ALTER TABLE transaksi MODIFY COLUMN id_transaksi INT AUTO_INCREMENT;
ALTER TABLE transaksi DROP FOREIGN KEY IF EXISTS transaksi_ibfk_1;
ALTER TABLE transaksi DROP INDEX IF EXISTS id_murid;
ALTER TABLE transaksi ADD INDEX idx_transaksi_murid (id_murid);
ALTER TABLE transaksi ADD CONSTRAINT fk_transaksi_murid 
    FOREIGN KEY (id_murid) REFERENCES murid(id_murid) 
    ON DELETE CASCADE ON UPDATE CASCADE;
*/

-- 2. Perbaiki tabel tagihan (tambah kolom jumlah_bayar)
/*
ALTER TABLE tagihan ADD COLUMN jumlah_bayar INT NOT NULL DEFAULT 0 AFTER nominal;
ALTER TABLE tagihan MODIFY COLUMN status_bayar ENUM('Belum', 'Sebagian', 'Lunas') DEFAULT 'Belum';
*/
