<?php
/**
 * Bootstrap File
 * Inisialisasi autoloader dan konfigurasi dasar
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define base paths
define('BASE_PATH', dirname(__FILE__));
define('SRC_PATH', BASE_PATH . '/src');
define('CONFIG_PATH', BASE_PATH . '/config');
define('PUBLIC_PATH', BASE_PATH . '/public');

// Load environment variables (opsional, jika menggunakan .env)
if (file_exists(BASE_PATH . '/.env')) {
    $env = parse_ini_file(BASE_PATH . '/.env');
    foreach ($env as $key => $value) {
        $_ENV[$key] = $value;
    }
}

// Autoloader
spl_autoload_register(function($class) {
    $prefix = 'App\\';
    $baseDir = SRC_PATH . '/';
    
    if (strpos($class, $prefix) === 0) {
        $relative_class = substr($class, strlen($prefix));
        $file = $baseDir . str_replace('\\', '/', $relative_class) . '.php';
        
        if (file_exists($file)) {
            require $file;
        }
    }
});

// Require config
require_once CONFIG_PATH . '/connect.php';

// Helper functions untuk backward compatibility
function formatRupiah($amount) {
    return \App\Helpers\Formatter::rupiah($amount);
}

function getBulanNama($bulan) {
    return \App\Helpers\Formatter::bulanNama($bulan);
}

function getTanggalIndonesia($tanggal) {
    return \App\Helpers\Formatter::tanggalIndonesia($tanggal);
}

// Auth helper functions
function isLoggedIn() {
    return \App\Models\User::isLoggedIn();
}

function getUser() {
    return \App\Models\User::getCurrentUser();
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /cashflowKas/login.php');
        exit;
    }
}

function requireBendahara() {
    requireLogin();
    if (!\App\Models\User::isBendahara()) {
        header('Location: /cashflowKas/config/unauthorized.php');
        exit;
    }
}

function logout() {
    \App\Models\User::logout();
}
