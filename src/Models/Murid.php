<?php
/**
 * Murid Model
 * Handle semua operasi terkait data murid
 */

namespace App\Models;

use App\Helpers\Database;

class Murid {
    /**
     * Get semua murid aktif
     */
    public static function getAllAktif($orderBy = 'nama ASC') {
        $result = Database::query("SELECT * FROM murid WHERE `status` = 'Aktif' ORDER BY $orderBy");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get murid by ID
     */
    public static function getById($id) {
        $id = (int)$id;
        $result = Database::query("SELECT * FROM murid WHERE id_murid = $id");
        return $result->fetch_assoc();
    }

    /**
     * Tambah murid baru
     */
    public static function create($nama, $kelas, $status = 'Aktif') {
        $nama = Database::escape($nama);
        $kelas = Database::escape($kelas);
        $status = Database::escape($status);
        
        $result = Database::query("INSERT INTO murid (nama, kelas, `status`) VALUES ('$nama', '$kelas', '$status')");
        return $result ? Database::lastId() : false;
    }

    /**
     * Update murid
     */
    public static function update($id, $nama, $kelas, $status) {
        $id = (int)$id;
        $nama = Database::escape($nama);
        $kelas = Database::escape($kelas);
        $status = Database::escape($status);
        
        $result = Database::query("UPDATE murid SET nama = '$nama', kelas = '$kelas', `status` = '$status' WHERE id_murid = $id");
        return $result;
    }

    /**
     * Hapus murid
     */
    public static function delete($id) {
        $id = (int)$id;
        $result = Database::query("DELETE FROM murid WHERE id_murid = $id");
        return $result;
    }

    /**
     * Count murid aktif
     */
    public static function countAktif() {
        $result = Database::query("SELECT COUNT(*) as total FROM murid WHERE `status` = 'Aktif'");
        $row = $result->fetch_assoc();
        return $row['total'];
    }

    /**
     * Get murid dengan status pembayaran
     */
    public static function getWithPaymentStatus($bulan, $tahun) {
        $bulan = (int)$bulan;
        $tahun = (int)$tahun;
        
        $result = Database::query("SELECT m.*, 
                    COALESCE(t.status_bayar, 'Belum') as status_tagihan,
                    COALESCE(t.jumlah_bayar, 0) as jumlah_bayar,
                    COALESCE(t.nominal, 0) as nominal_tagihan
                    FROM murid m 
                    LEFT JOIN tagihan t ON m.id_murid = t.id_murid AND t.bulan = $bulan AND t.tahun = $tahun
                    WHERE m.`status` = 'Aktif'
                    ORDER BY m.nama ASC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
