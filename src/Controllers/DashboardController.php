<?php
/**
 * Dashboard Controller
 * Handle dashboard page
 */

namespace App\Controllers;

use App\Models\User;
use App\Models\Murid;
use App\Models\Transaksi;
use App\Models\Pengaturan;

class DashboardController {
    public static function index() {
        requireLogin();
        
        $bulanIni = date('n');
        $tahunIni = date('Y');
        
        // Get statistics
        $totalMurid = Murid::countAktif();
        $kasMasuk = Transaksi::getTotalByJenis('Masuk', $bulanIni, $tahunIni);
        $kasKeluar = Transaksi::getTotalByJenis('Keluar', $bulanIni, $tahunIni);
        $saldoBulanIni = $kasMasuk - $kasKeluar;
        
        // Total saldo keseluruhan
        $totalKasMasuk = Transaksi::getTotalByJenis('Masuk');
        $totalKasKeluar = Transaksi::getTotalByJenis('Keluar');
        $totalSaldo = $totalKasMasuk - $totalKasKeluar;
        
        $user = getUser();
        
        require SRC_PATH . '/Views/dashboard.php';
    }
}
