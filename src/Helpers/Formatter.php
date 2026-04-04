<?php
/**
 * Formatter Helper
 * Berisi fungsi untuk format data
 */

namespace App\Helpers;

class Formatter {
    /**
     * Format Rupiah
     */
    public static function rupiah($amount) {
        return 'Rp ' . number_format((int)$amount, 0, ',', '.');
    }

    /**
     * Nama Bulan
     */
    public static function bulanNama($bulan) {
        $bulanArray = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];
        return $bulanArray[$bulan] ?? 'Bulan Tidak Valid';
    }

    /**
     * Format Tanggal Indonesia
     */
    public static function tanggalIndonesia($tanggal) {
        $date = new \DateTime($tanggal);
        $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $hariIndex = $date->format('w');
        $bulanNama = self::bulanNama((int)$date->format('n'));
        return $hari[$hariIndex] . ', ' . $date->format('d') . ' ' . $bulanNama . ' ' . $date->format('Y');
    }

    /**
     * Status Badge
     */
    public static function statusBadge($status) {
        $badges = [
            'Lunas' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'label' => 'Lunas'],
            'Sebagian' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'label' => 'Sebagian Bayar'],
            'Belum' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'label' => 'Belum Bayar'],
        ];
        return $badges[$status] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => $status];
    }
}
