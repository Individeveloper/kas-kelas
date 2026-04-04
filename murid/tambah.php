<?php
include '../config/app.php';
requireBendahara();

$error = '';
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = mysqli_real_escape_string($db, $_POST['nama']);
    $kelas = mysqli_real_escape_string($db, $_POST['kelas']);
    
    // Status default Aktif untuk murid baru
    $result = query("INSERT INTO murid (nama, kelas, `status`) VALUES ('$nama', '$kelas', 'Aktif')");
    if ($result) {
        $success = true;
    } else {
        $error = 'Gagal menambahkan data: ' . mysqli_error($db);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/cashflowKas/assets/css/style.css">
    <title>Tambah Murid - Kas Kelas</title>
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <?php include '../layout/component/sidebar.php'; ?>
        <div class="flex-1 flex flex-col">
            <div class="flex-1 p-6">
            <div class="mb-6">
                <a href="/cashflowKas/murid/index.php" class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
                <h1 class="text-2xl font-bold text-gray-800 mt-2">Tambah Murid Baru</h1>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
                <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
                </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" name="nama" required 
                                   value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                                   placeholder="Nama lengkap murid">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Kelas <span class="text-red-500">*</span></label>
                            <input type="text" name="kelas" required 
                                   value="<?= htmlspecialchars($_POST['kelas'] ?? '') ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                                   placeholder="Contoh: XII IPA 1">
                        </div>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <p class="text-sm text-blue-700">
                                <i class="fas fa-info-circle me-2"></i>
                                Status murid akan otomatis diset <strong>Aktif</strong> saat ditambahkan.
                            </p>
                        </div>
                    </div>
                    <div class="mt-6">
                        <button type="submit" class="bg-gray-800 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition">
                            <i class="fas fa-save me-2"></i>Simpan
                        </button>
                    </div>
                </form>
            </div>
            </div>
            <?php include '../layout/component/footer.php'; ?>
        </div>
    </div>
    <?php if ($success): ?>
    <script>
        alert('Murid berhasil ditambahkan!');
        window.location.href = '/cashflowKas/murid/index.php';
    </script>
    <?php endif; ?>
</body>
</html>
