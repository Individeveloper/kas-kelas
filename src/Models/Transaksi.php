<?php
/**
 * Transaksi Model
 * Handle semua operasi terkait transaksi
 */

namespace App\Models;

use App\Helpers\Database;

class Transaksi {
    /**
     * Get semua transaksi
     */
    public static function getAll($bulan = null, $tahun = null, $jenis = null) {
        $where = [];
        
        if ($bulan && $tahun) {
            $bulan = (int)$bulan;
            $tahun = (int)$tahun;
            $where[] = "MONTH(t.tanggal) = $bulan AND YEAR(t.tanggal) = $tahun";
        }
        
        if ($jenis) {
            $jenis = Database::escape($jenis);
            $where[] = "t.jenis = '$jenis'";
        }
        
        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $result = Database::query("SELECT t.*, m.nama FROM transaksi t 
                                LEFT JOIN murid m ON t.id_murid = m.id_murid 
                                $whereClause ORDER BY t.tanggal DESC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get transaksi by ID
     */
    public static function getById($id) {
        $id = (int)$id;
        $result = Database::query("SELECT * FROM transaksi WHERE id_transaksi = $id");
        return $result->fetch_assoc();
    }

    /**
     * Tambah transaksi
     */
    public static function create($id_murid, $tanggal, $jenis, $jumlah, $keterangan, $kategori = null) {
        $id_murid = (int)$id_murid;
        $tanggal = Database::escape($tanggal);
        $jenis = Database::escape($jenis);
        $jumlah = (int)$jumlah;
        $keterangan = Database::escape($keterangan);
        
        if ($kategori) {
            $kategori = Database::escape($kategori);
            $result = Database::query("INSERT INTO transaksi (id_murid, tanggal, jenis, kategori, jumlah, keterangan) 
                                    VALUES ($id_murid, '$tanggal', '$jenis', '$kategori', $jumlah, '$keterangan')");
        } else {
            $result = Database::query("INSERT INTO transaksi (id_murid, tanggal, jenis, jumlah, keterangan) 
                                    VALUES ($id_murid, '$tanggal', '$jenis', $jumlah, '$keterangan')");
        }
        
        return $result ? Database::lastId() : false;
    }

    /**
     * Update transaksi
     */
    public static function update($id, $tanggal, $jenis, $jumlah, $keterangan, $kategori = null) {
        $id = (int)$id;
        $tanggal = Database::escape($tanggal);
        $jenis = Database::escape($jenis);
        $jumlah = (int)$jumlah;
        $keterangan = Database::escape($keterangan);
        
        if ($kategori) {
            $kategori = Database::escape($kategori);
            $result = Database::query("UPDATE transaksi SET tanggal = '$tanggal', jenis = '$jenis', 
                                    kategori = '$kategori', jumlah = $jumlah, keterangan = '$keterangan' 
                                    WHERE id_transaksi = $id");
        } else {
            $result = Database::query("UPDATE transaksi SET tanggal = '$tanggal', jenis = '$jenis', 
                                    jumlah = $jumlah, keterangan = '$keterangan' WHERE id_transaksi = $id");
        }
        
        return $result;
    }

    /**
     * Hapus transaksi
     */
    public static function delete($id) {
        $id = (int)$id;
        $result = Database::query("DELETE FROM transaksi WHERE id_transaksi = $id");
        return $result;
    }

    /**
     * Get total kas masuk/keluar dalam periode
     */
    public static function getTotalByJenis($jenis, $bulan = null, $tahun = null) {
        $jenis = Database::escape($jenis);
        $where = "jenis = '$jenis'";
        
        if ($bulan && $tahun) {
            $bulan = (int)$bulan;
            $tahun = (int)$tahun;
            $where .= " AND MONTH(tanggal) = $bulan AND YEAR(tanggal) = $tahun";
        }
        
        $result = Database::query("SELECT COALESCE(SUM(jumlah), 0) as total FROM transaksi WHERE $where");
        $row = $result->fetch_assoc();
        return (int)$row['total'];
    }
}
