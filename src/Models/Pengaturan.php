<?php
/**
 * Pengaturan Model
 * Handle semua operasi terkait pengaturan aplikasi
 */

namespace App\Models;

use App\Helpers\Database;

class Pengaturan {
    /**
     * Get pengaturan by nama
     */
    public static function get($nama) {
        $nama = Database::escape($nama);
        $result = Database::query("SELECT * FROM pengaturan WHERE nama_pengaturan = '$nama'");
        $row = $result->fetch_assoc();
        return $row ? $row['nilai'] : null;
    }

    /**
     * Set pengaturan
     */
    public static function set($nama, $nilai) {
        $nama = Database::escape($nama);
        $nilai = Database::escape($nilai);
        
        $result = Database::query("INSERT INTO pengaturan (nama_pengaturan, nilai) VALUES ('$nama', '$nilai') 
                                ON DUPLICATE KEY UPDATE nilai = '$nilai'");
        return $result;
    }

    /**
     * Get nominal kas
     */
    public static function getNominalKas() {
        $nominal = self::get('nominal_kas');
        return $nominal ? (int)$nominal : 20000;
    }

    /**
     * Update nominal kas
     */
    public static function setNominalKas($nominal) {
        return self::set('nominal_kas', (int)$nominal);
    }

    /**
     * Get all pengaturan
     */
    public static function getAll() {
        $result = Database::query("SELECT * FROM pengaturan");
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
