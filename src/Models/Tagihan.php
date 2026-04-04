<?php
/**
 * Tagihan Model
 * Handle semua operasi terkait tagihan
 */

namespace App\Models;

use App\Helpers\Database;

class Tagihan {
    /**
     * Get semua tagihan
     */
    public static function getAll($bulan = null, $tahun = null, $status = null) {
        $where = [];
        
        if ($bulan && $tahun) {
            $bulan = (int)$bulan;
            $tahun = (int)$tahun;
            $where[] = "t.bulan = $bulan AND t.tahun = $tahun";
        }
        
        if ($status) {
            $status = Database::escape($status);
            $where[] = "t.status_bayar = '$status'";
        }
        
        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $result = Database::query("SELECT t.*, m.nama FROM tagihan t 
                                JOIN murid m ON t.id_murid = m.id_murid 
                                $whereClause ORDER BY t.bulan DESC, m.nama ASC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get tagihan by ID
     */
    public static function getById($id) {
        $id = (int)$id;
        $result = Database::query("SELECT * FROM tagihan WHERE id_tagihan = $id");
        return $result->fetch_assoc();
    }

    /**
     * Get tagihan by murid dan periode
     */
    public static function getByMuridAndPeriode($id_murid, $bulan, $tahun) {
        $id_murid = (int)$id_murid;
        $bulan = (int)$bulan;
        $tahun = (int)$tahun;
        
        $result = Database::query("SELECT * FROM tagihan WHERE id_murid = $id_murid AND bulan = $bulan AND tahun = $tahun");
        return $result->fetch_assoc();
    }

    /**
     * Generate tagihan untuk semua murid
     */
    public static function generateForMonth($bulan, $tahun, $nominal) {
        $bulan = (int)$bulan;
        $tahun = (int)$tahun;
        $nominal = (int)$nominal;
        
        // Get semua murid aktif
        $muridList = Database::query("SELECT id_murid FROM murid WHERE `status` = 'Aktif'");
        
        $count = 0;
        while ($murid = $muridList->fetch_assoc()) {
            $id_murid = $murid['id_murid'];
            
            // Check if tagihan sudah ada
            $existing = Database::query("SELECT id_tagihan FROM tagihan WHERE id_murid = $id_murid AND bulan = $bulan AND tahun = $tahun");
            
            if ($existing->num_rows == 0) {
                Database::query("INSERT INTO tagihan (id_murid, bulan, tahun, nominal, status_bayar) 
                            VALUES ($id_murid, $bulan, $tahun, $nominal, 'Belum')");
                $count++;
            }
        }
        
        return $count;
    }

    /**
     * Update status bayar
     */
    public static function updateStatusBayar($id, $status, $jumlah_bayar = 0) {
        $id = (int)$id;
        $status = Database::escape($status);
        $jumlah_bayar = (int)$jumlah_bayar;
        
        $result = Database::query("UPDATE tagihan SET status_bayar = '$status', jumlah_bayar = $jumlah_bayar WHERE id_tagihan = $id");
        return $result;
    }

    /**
     * Count tagihan by status
     */
    public static function countByStatus($status, $bulan = null, $tahun = null) {
        $status = Database::escape($status);
        $where = "status_bayar = '$status'";
        
        if ($bulan && $tahun) {
            $bulan = (int)$bulan;
            $tahun = (int)$tahun;
            $where .= " AND bulan = $bulan AND tahun = $tahun";
        }
        
        $result = Database::query("SELECT COUNT(*) as total FROM tagihan WHERE $where");
        $row = $result->fetch_assoc();
        return $row['total'];
    }
}
