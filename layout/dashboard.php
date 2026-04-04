<?php
include '../config/app.php';
requireLogin();

$user = getUser();

// Get statistics
$bulanIni = date('n');
$tahunIni = date('Y');

// Total murid aktif
$totalMurid = mysqli_fetch_assoc(query("SELECT COUNT(*) as total FROM murid WHERE `status` = 'Aktif'"))['total'];
$muridTidakAktif = mysqli_fetch_assoc(query("SELECT COUNT(*) as total FROM murid WHERE `status` = 'Tidak Aktif'"))['total'];

// Total kas masuk bulan ini
$kasMasuk = mysqli_fetch_assoc(query("SELECT COALESCE(SUM(jumlah), 0) as total FROM transaksi WHERE jenis = 'Masuk' AND MONTH(tanggal) = $bulanIni AND YEAR(tanggal) = $tahunIni"))['total'];

// Total kas keluar bulan ini
$kasKeluar = mysqli_fetch_assoc(query("SELECT COALESCE(SUM(jumlah), 0) as total FROM transaksi WHERE jenis = 'Keluar' AND MONTH(tanggal) = $bulanIni AND YEAR(tanggal) = $tahunIni"))['total'];

// Total saldo keseluruhan
$totalKasMasuk = mysqli_fetch_assoc(query("SELECT COALESCE(SUM(jumlah), 0) as total FROM transaksi WHERE jenis = 'Masuk'"))['total'];
$totalKasKeluar = mysqli_fetch_assoc(query("SELECT COALESCE(SUM(jumlah), 0) as total FROM transaksi WHERE jenis = 'Keluar'"))['total'];
$saldo = $totalKasMasuk - $totalKasKeluar;

// Tagihan bulan ini
$tagihanBulanIni = mysqli_fetch_assoc(query("SELECT COUNT(*) as total FROM tagihan WHERE bulan = $bulanIni AND tahun = $tahunIni"))['total'];
$sudahBayarBulanIni = mysqli_fetch_assoc(query("SELECT COUNT(*) as total FROM tagihan WHERE bulan = $bulanIni AND tahun = $tahunIni AND status_bayar = 'Lunas'"))['total'];
$belumBayarBulanIni = $tagihanBulanIni - $sudahBayarBulanIni;

// Recent transactions
$recentTransactions = query("SELECT t.*, m.nama FROM transaksi t LEFT JOIN murid m ON t.id_murid = m.id_murid ORDER BY t.tanggal DESC, t.id_transaksi DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/cashflowKas/assets/css/style.css">
    <title>Dashboard - Kas Kelas</title>
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <?php include 'component/sidebar.php'; ?>
        <div class="flex-1 flex flex-col">
            <div class="flex-1 p-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
                <p class="text-gray-600">Selamat datang, <?= htmlspecialchars($user['username']) ?>!</p>
            </div>
            
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                            <i class="fas fa-users text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Total Murid</p>
                            <p class="text-2xl font-bold text-gray-800"><?= $totalMurid ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-600">
                            <i class="fas fa-arrow-down text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Kas Masuk (<?= getBulanNama($bulanIni) ?>)</p>
                            <p class="text-2xl font-bold text-gray-800"><?= formatRupiah($kasMasuk) ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-red-100 text-red-600">
                            <i class="fas fa-arrow-up text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Kas Keluar (<?= getBulanNama($bulanIni) ?>)</p>
                            <p class="text-2xl font-bold text-gray-800"><?= formatRupiah($kasKeluar) ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                            <i class="fas fa-wallet text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Total Saldo</p>
                            <p class="text-2xl font-bold text-gray-800"><?= formatRupiah($saldo) ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Status Murid & Tagihan -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Tagihan Bulan Ini -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">
                        <i class="fas fa-file-invoice me-2"></i>Tagihan <?= getBulanNama($bulanIni) ?> <?= $tahunIni ?>
                    </h2>
                    <?php if ($tagihanBulanIni > 0): ?>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-500 text-xl me-3"></i>
                                <span class="text-gray-700">Sudah Bayar</span>
                            </div>
                            <span class="text-xl font-bold text-green-600"><?= $sudahBayarBulanIni ?> murid</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-clock text-red-500 text-xl me-3"></i>
                                <span class="text-gray-700">Belum Bayar</span>
                            </div>
                            <span class="text-xl font-bold text-red-600"><?= $belumBayarBulanIni ?> murid</span>
                        </div>                        
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-exclamation-circle text-yellow-500 text-2xl mb-2"></i>
                        <p class="text-gray-500">Belum ada tagihan bulan ini</p>
                        <?php if (isBendahara()): ?>
                        <a href="/cashflowKas/tagihan/index.php" class="text-blue-600 hover:underline text-sm mt-2 inline-block">
                            Generate Tagihan
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    <?php if (isBendahara()): ?>
                    <div class="mt-4">
                        <a href="/cashflowKas/tagihan/index.php" class="text-blue-600 hover:underline text-sm">
                            <i class="fas fa-arrow-right me-1"></i>Kelola Tagihan
                        </a>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Status Murid -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">
                        <i class="fas fa-users me-2"></i>Status Murid
                    </h2>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-user-check text-green-500 text-xl me-3"></i>
                                <span class="text-gray-700">Murid Aktif</span>
                            </div>
                            <span class="text-xl font-bold text-green-600"><?= $totalMurid ?> murid</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-user-slash text-gray-500 text-xl me-3"></i>
                                <span class="text-gray-700">Murid Tidak Aktif</span>
                            </div>
                            <span class="text-xl font-bold text-gray-600"><?= $muridTidakAktif ?> murid</span>
                        </div>
                    </div>
                    <?php if (isBendahara()): ?>
                    <div class="mt-4 flex gap-3">
                        <a href="/cashflowKas/murid/index.php" class="text-blue-600 hover:underline text-sm">
                            <i class="fas fa-arrow-right me-1"></i>Kelola Murid
                        </a>
                        <?php if ($muridTidakAktif > 0): ?>
                        <a href="/cashflowKas/murid/index.php?show=inactive" class="text-gray-600 hover:underline text-sm">
                            <i class="fas fa-eye me-1"></i>Lihat Tidak Aktif
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Transaksi Terbaru -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">
                    <i class="fas fa-history me-2"></i>Transaksi Terbaru
                </h2>
                <?php if (mysqli_num_rows($recentTransactions) > 0): ?>
                <div class="space-y-3">
                    <?php while ($trx = mysqli_fetch_assoc($recentTransactions)): ?>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-800">
                                <?= $trx['jenis'] == 'Masuk' ? htmlspecialchars($trx['nama'] ?? 'Kas Masuk') : htmlspecialchars($trx['keterangan']) ?>
                            </p>
                            <p class="text-sm text-gray-500"><?= date('d/m/Y', strtotime($trx['tanggal'])) ?> - <?= $trx['jenis'] ?></p>
                            <?php if (!empty($trx['kategori'])): ?>
                            <span class="inline-block mt-1 px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-700">
                                <?= htmlspecialchars($trx['kategori']) ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        <span class="font-bold <?= $trx['jenis'] == 'Masuk' ? 'text-green-600' : 'text-red-600' ?>">
                            <?= $trx['jenis'] == 'Masuk' ? '+' : '-' ?><?= formatRupiah($trx['jumlah']) ?>
                        </span>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php else: ?>
                <p class="text-gray-500 text-center py-4">Belum ada transaksi</p>
                <?php endif; ?>
            </div>
            </div>
            <?php include 'component/footer.php'; ?>
        </div>
    </div>
</body>
</html>