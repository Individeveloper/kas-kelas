<?php
include '../config/app.php';
requireBendahara();

$success = false;
$error = '';

// Get current setting
$pengaturan = mysqli_fetch_assoc(query("SELECT * FROM pengaturan WHERE nama_pengaturan = 'nominal_kas'"));
$nominalKas = $pengaturan ? (int)$pengaturan['nilai'] : 20000;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nominalBaru = (int)$_POST['nominal_kas'];
    
    if ($nominalBaru < 1000) {
        $error = 'Nominal kas minimal Rp 1.000';
    } else {
        // Update atau insert pengaturan
        $result = query("INSERT INTO pengaturan (nama_pengaturan, nilai) VALUES ('nominal_kas', '$nominalBaru') 
                        ON DUPLICATE KEY UPDATE nilai = '$nominalBaru'");
        if ($result) {
            $nominalKas = $nominalBaru;
            $success = true;
        } else {
            $error = 'Gagal menyimpan pengaturan: ' . mysqli_error($GLOBALS['db']);
        }
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
    <title>Pengaturan Kas - Kas Kelas</title>
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <?php include '../layout/component/sidebar.php'; ?>
        <div class="flex-1 flex flex-col">
            <div class="flex-1 p-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Pengaturan Kas Kelas</h1>
                <p class="text-gray-600">Atur nominal kas kelas per bulan</p>
            </div>
            
            <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <i class="fas fa-check-circle me-2"></i>Nominal kas berhasil diperbarui!
            </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
            </div>
            <?php endif; ?>
            
            <div class="bg-white rounded-lg shadow p-6 max-w-xl">
                <form method="POST">
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">
                            <i class="fas fa-money-bill-wave me-2 text-green-600"></i>
                            Nominal Kas Per Bulan
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-gray-500">Rp</span>
                            <input type="number" name="nominal_kas" 
                                   value="<?= $nominalKas ?>" 
                                   min="1000" step="1000" required
                                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                        </div>
                        <p class="text-sm text-gray-500 mt-1">
                            Nominal ini akan digunakan saat generate tagihan baru
                        </p>
                    </div>
                    
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <h3 class="font-medium text-blue-800 mb-2">
                            <i class="fas fa-info-circle me-2"></i>Informasi
                        </h3>
                        <ul class="text-sm text-blue-700 space-y-1">
                            <li>• Perubahan nominal hanya berlaku untuk tagihan baru</li>
                            <li>• Tagihan yang sudah ada tidak akan berubah</li>
                            <li>• Minimal nominal adalah Rp 1.000</li>
                        </ul>
                    </div>
                    
                    <button type="submit" class="bg-gray-800 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition">
                        <i class="fas fa-save me-2"></i>Simpan Pengaturan
                    </button>
                </form>
            </div>
            
            <!-- Quick Stats -->
            <div class="mt-6 bg-white rounded-lg shadow p-6 max-w-xl">
                <h3 class="font-bold text-gray-800 mb-4">
                    <i class="fas fa-chart-bar me-2"></i>Ringkasan Pengaturan
                </h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-green-50 rounded-lg p-4 text-center">
                        <p class="text-sm text-gray-600">Nominal Saat Ini</p>
                        <p class="text-xl font-bold text-green-600"><?= formatRupiah($nominalKas) ?></p>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-4 text-center">
                        <p class="text-sm text-gray-600">Per Bulan / Murid</p>
                        <p class="text-xl font-bold text-blue-600"><?= formatRupiah($nominalKas) ?></p>
                    </div>
                </div>
            </div>
            </div>
            <?php include '../layout/component/footer.php'; ?>
        </div>
    </div>
</body>
</html>
