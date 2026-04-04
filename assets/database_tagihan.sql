-- Tabel untuk menyimpan pengaturan kas kelas
CREATE TABLE IF NOT EXISTS pengaturan (
    id_pengaturan INT AUTO_INCREMENT PRIMARY KEY,
    nama_pengaturan VARCHAR(50) NOT NULL UNIQUE,
    nilai VARCHAR(255) NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default kas kelas 20.000
INSERT INTO pengaturan (nama_pengaturan, nilai) VALUES ('nominal_kas', '20000')
ON DUPLICATE KEY UPDATE nilai = nilai;

-- Tabel tagihan kas per murid per bulan
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
    FOREIGN KEY (id_murid) REFERENCES murid(id_murid) ON DELETE CASCADE,
    UNIQUE KEY unique_tagihan (id_murid, bulan, tahun)
);

-- Index untuk mempercepat query
CREATE INDEX idx_tagihan_bulan_tahun ON tagihan(bulan, tahun);
CREATE INDEX idx_tagihan_status ON tagihan(status_bayar);

-- ============================================================
-- JIKA TABEL SUDAH ADA, JALANKAN QUERY BERIKUT UNTUK UPDATE:
-- ============================================================
-- ALTER TABLE tagihan ADD COLUMN jumlah_bayar INT NOT NULL DEFAULT 0 AFTER nominal;
-- ALTER TABLE tagihan MODIFY COLUMN status_bayar ENUM('Belum', 'Sebagian', 'Lunas') DEFAULT 'Belum';
