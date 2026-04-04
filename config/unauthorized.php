<?php
include 'app.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <title>Akses Ditolak - Kas Kelas</title>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md text-center">
        <i class="fas fa-ban text-6xl text-red-500 mb-4"></i>
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Akses Ditolak</h1>
        <p class="text-gray-500 mb-6">Anda tidak memiliki izin untuk mengakses halaman ini.</p>
        <a href="../layout/dashboard.php" 
           class="inline-block bg-gray-800 text-white py-2 px-6 rounded-lg hover:bg-gray-700 transition">
            <i class="fas fa-home me-2"></i>Kembali ke Dashboard
        </a>
    </div>
</body>
</html>
