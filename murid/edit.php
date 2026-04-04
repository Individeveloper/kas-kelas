<?php
include '../config/app.php';
requireBendahara();

if (!isset($_GET['id'])) {
    header('Location: /cashflowKas/murid/index.php');
    exit;
}

$id = (int)$_GET['id'];
$murid = mysqli_fetch_assoc(query("SELECT * FROM murid WHERE id_murid = $id"));

if (!$murid) {
    header('Location: /cashflowKas/murid/index.php');
    exit;
}

$error = '';
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = mysqli_real_escape_string($db, $_POST['nama']);
    $kelas = mysqli_real_escape_string($db, $_POST['kelas']);
    
    // Status tidak diubah di sini, gunakan soft delete untuk nonaktifkan
    $result = query("UPDATE murid SET nama = '$nama', kelas = '$kelas' WHERE id_murid = $id");
    if ($result) {
        $success = true;
    } else {
        $error = 'Gagal mengupdate data: ' . mysqli_error($db);
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
    <title>Edit Murid - Kas Kelas</title>
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
                <h1 class="text-2xl font-bold text-gray-800 mt-2">Edit Data Murid</h1>
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
                                   value="<?= htmlspecialchars($murid['nama']) ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Kelas <span class="text-red-500">*</span></label>
                            <input type="text" name="kelas" required 
                                   value="<?= htmlspecialchars($murid['kelas']) ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                        </div>
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                            <p class="text-sm text-gray-700">
                                <i class="fas fa-info-circle me-2"></i>
                                Status saat ini: 
                                <span class="font-semibold <?= $murid['status'] == 'Aktif' ? 'text-green-600' : 'text-red-600' ?>">
                                    <?= htmlspecialchars($murid['status']) ?>
                                </span>
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                Untuk mengubah status, gunakan tombol Nonaktifkan/Aktifkan di halaman daftar murid.
                            </p>
                        </div>
                    </div>
                    <div class="mt-6">
                        <button type="submit" class="bg-gray-800 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition">
                            <i class="fas fa-save me-2"></i>Update
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
        alert('Data murid berhasil diperbarui!');
        window.location.href = '/cashflowKas/murid/index.php';
    </script>
    <?php endif; ?>
</body>
</html>
