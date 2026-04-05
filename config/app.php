<?php
session_start();
include 'connect.php';
function query($q){
    global $db;
    return mysqli_query($db, $q);
}

function login($username, $password) {
    global $db;
    $username = mysqli_real_escape_string($db, $username);
    $result = query("SELECT * FROM user WHERE username = '$username'");
    
    if ($row = mysqli_fetch_assoc($result)) {
        // Cek password (plain text sesuai struktur database)
        if ($password === $row['password']) {
            $_SESSION['user_id'] = $row['id_user'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            return true;
        }
    }
    return false;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUser() {
    if (isLoggedIn()) {
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'role' => $_SESSION['role']
        ];
    }
    return null;
}

function getRole() {
    return $_SESSION['role'] ?? null;
}

function isBendahara() {
    return getRole() === 'bendahara';
}

function isWaliKelas() {
    return getRole() === 'wali kelas';
}

function isKetuaKelas() {
    return getRole() === 'ketua kelas';
}

function canManage() {
    return isBendahara();
}

function canViewReport() {
    return isLoggedIn();
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../login.php');
        exit;
    }
}

function requireBendahara() {
    requireLogin();
    if (!isBendahara()) {
        header('Location: unauthorized.php');
        exit;
    }
}

function logout() {
    session_destroy();
    header('Location: ../login.php');
    exit;
}

function getRoleName($role) {
    $roles = [
        'bendahara' => 'Bendahara',
        'wali kelas' => 'Wali Kelas',
        'ketua kelas' => 'Ketua Kelas'
    ];
    return $roles[$role] ?? 'Unknown';
}

function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

function getBulanNama($bulan) {
    $nama = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    return $nama[$bulan] ?? '';
}

function getNominalKasAktif() {
    $result = query("SELECT nilai FROM pengaturan WHERE nama_pengaturan = 'nominal_kas' LIMIT 1");

    if ($result && ($row = mysqli_fetch_assoc($result))) {
        return max(1000, (int)$row['nilai']);
    }

    return 20000;
}

function generateTagihanOtomatis($bulan = null, $tahun = null) {
    global $db;

    // Hanya bendahara yang boleh memicu proses generate agar hak akses tetap terjaga.
    if (!isBendahara()) {
        return 0;
    }

    $bulan = $bulan ? (int)$bulan : (int)date('n');
    $tahun = $tahun ? (int)$tahun : (int)date('Y');

    if ($bulan < 1 || $bulan > 12) {
        $bulan = (int)date('n');
    }

    if ($tahun < 2000 || $tahun > 2100) {
        $tahun = (int)date('Y');
    }

    $nominalKas = getNominalKasAktif();

    $insertQuery = "INSERT IGNORE INTO tagihan (id_murid, bulan, tahun, nominal, jumlah_bayar, status_bayar)
                    SELECT m.id_murid, $bulan, $tahun, $nominalKas, 0, 'Belum'
                    FROM murid m
                    LEFT JOIN tagihan t ON t.id_murid = m.id_murid AND t.bulan = $bulan AND t.tahun = $tahun
                    WHERE m.`status` = 'Aktif' AND t.id_tagihan IS NULL";

    $result = query($insertQuery);
    if (!$result) {
        return 0;
    }

    return mysqli_affected_rows($db);
}
?>