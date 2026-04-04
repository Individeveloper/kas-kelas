<?php
/**
 * View: Auth/Unauthorized
 * Halaman akses ditolak
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <title>Akses Ditolak - Kas Kelas</title>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md text-center">
        <div class="mb-6">
            <i class="fas fa-lock text-6xl text-red-600 mb-4"></i>
            <h1 class="text-2xl font-bold text-gray-800">Akses Ditolak</h1>
        </div>
        
        <p class="text-gray-600 mb-6">
            Maaf, Anda tidak memiliki izin untuk mengakses halaman ini.
        </p>
        
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6 text-sm text-red-700">
            <i class="fas fa-info-circle me-2"></i>
            Hubungi administrator jika Anda merasa ini adalah kesalahan.
        </div>
        
        <a href="/cashflowKas/index.php" class="inline-block bg-gray-800 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition">
            <i class="fas fa-home me-2"></i>Kembali ke Beranda
        </a>
    </div>
</body>
</html>
