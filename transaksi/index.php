<?php
include '../config/app.php';
requireBendahara();

$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : date('n');
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');
$jenis = isset($_GET['jenis']) ? $_GET['jenis'] : '';
$kategori = isset($_GET['kategori']) ? $_GET['kategori'] : '';

// Build query with filters
$where = "WHERE MONTH(tanggal) = $bulan AND YEAR(tanggal) = $tahun";
if ($jenis == 'Masuk' || $jenis == 'Keluar') {
    $where .= " AND jenis = '$jenis'";
}
if ($kategori && in_array($kategori, ['Kas', 'Keperluan Kelas', 'Sumbangan', 'Lainnya'])) {
    $where .= " AND kategori = '$kategori'";
}

// Get transaksi
$transaksiList = query("SELECT t.*, m.nama FROM transaksi t LEFT JOIN murid m ON t.id_murid = m.id_murid $where ORDER BY t.tanggal DESC, t.id_transaksi DESC");

// Get totals
$totalMasuk = mysqli_fetch_assoc(query("SELECT COALESCE(SUM(jumlah), 0) as total FROM transaksi WHERE jenis = 'Masuk' AND MONTH(tanggal) = $bulan AND YEAR(tanggal) = $tahun"))['total'];
$totalKeluar = mysqli_fetch_assoc(query("SELECT COALESCE(SUM(jumlah), 0) as total FROM transaksi WHERE jenis = 'Keluar' AND MONTH(tanggal) = $bulan AND YEAR(tanggal) = $tahun"))['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/cashflowKas/assets/css/style.css">
    <title>Transaksi Kas - Kas Kelas</title>
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <?php include '../layout/component/sidebar.php'; ?>
        <div class="flex-1 flex flex-col">
            <div class="flex-1 p-6">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Transaksi Kas</h1>
                    <p class="text-gray-600">Kelola transaksi kas masuk dan keluar</p>
                </div>
                <div class="flex gap-2">
                    <a href="/cashflowKas/transaksi/tambah.php?jenis=Masuk" 
                       class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                        <i class="fas fa-plus me-2"></i>Kas Masuk
                    </a>
                    <a href="/cashflowKas/transaksi/tambah.php?jenis=Keluar" 
                       class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">
                        <i class="fas fa-minus me-2"></i>Kas Keluar
                    </a>
                </div>
            </div>
            
            <!-- Filter -->
            <div class="bg-white rounded-lg shadow p-4 mb-6">
                <form method="GET" class="flex items-center gap-4 flex-wrap">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Bulan</label>
                        <select name="bulan" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                            <?php for ($i = 1; $i <= 12; $i++): ?>
                            <option value="<?= $i ?>" <?= $bulan == $i ? 'selected' : '' ?>><?= getBulanNama($i) ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Tahun</label>
                        <select name="tahun" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                            <?php for ($y = date('Y') - 2; $y <= date('Y') + 1; $y++): ?>
                            <option value="<?= $y ?>" <?= $tahun == $y ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Jenis</label>
                        <select name="jenis" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                            <option value="">Semua</option>
                            <option value="Masuk" <?= $jenis == 'Masuk' ? 'selected' : '' ?>>Kas Masuk</option>
                            <option value="Keluar" <?= $jenis == 'Keluar' ? 'selected' : '' ?>>Kas Keluar</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Kategori</label>
                        <select name="kategori" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                            <option value="">Semua</option>
                            <option value="Kas" <?= $kategori == 'Kas' ? 'selected' : '' ?>>Kas</option>
                            <option value="Keperluan Kelas" <?= $kategori == 'Keperluan Kelas' ? 'selected' : '' ?>>Keperluan Kelas</option>
                            <option value="Sumbangan" <?= $kategori == 'Sumbangan' ? 'selected' : '' ?>>Sumbangan</option>
                            <option value="Lainnya" <?= $kategori == 'Lainnya' ? 'selected' : '' ?>>Lainnya</option>
                        </select>
                    </div>
                    <div class="pt-6">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                            <i class="fas fa-filter me-2"></i>Filter
                        </button>
                    </div>
                </form>
            </div>
            
            <?php if (isset($_GET['msg'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <i class="fas fa-check-circle me-2"></i>
                <?php
                $msgs = [
                    'added' => 'Transaksi berhasil dicatat!',
                    'updated' => 'Transaksi berhasil diperbarui!',
                    'deleted' => 'Transaksi berhasil dihapus!'
                ];
                echo $msgs[$_GET['msg']] ?? 'Operasi berhasil!';
                ?>
            </div>
            <?php endif; ?>
            
            <!-- Summary -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">Kas Masuk</span>
                        <span class="text-xl font-bold text-green-600"><?= formatRupiah($totalMasuk) ?></span>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">Kas Keluar</span>
                        <span class="text-xl font-bold text-red-600"><?= formatRupiah($totalKeluar) ?></span>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">Saldo Bulan Ini</span>
                        <span class="text-xl font-bold text-blue-600"><?= formatRupiah($totalMasuk - $totalKeluar) ?></span>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="p-4 bg-gray-50 border-b">
                    <h2 class="font-bold text-gray-800">
                        <i class="fas fa-list me-2"></i>Daftar Transaksi - <?= getBulanNama($bulan) ?> <?= $tahun ?>
                    </h2>
                </div>
                <div class="overflow-y-auto max-h-96">
                    <table class="w-full">
                        <thead class="bg-gray-800 text-white sticky top-0">
                            <tr>
                                <th class="px-4 py-3 text-left">Tanggal</th>
                                <th class="px-4 py-3 text-left">Jenis</th>
                                <th class="px-4 py-3 text-left">Kategori</th>
                                <th class="px-4 py-3 text-left">Keterangan</th>
                                <th class="px-4 py-3 text-right">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php while ($trx = mysqli_fetch_assoc($transaksiList)): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3"><?= date('d/m/Y', strtotime($trx['tanggal'])) ?></td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded text-xs <?= $trx['jenis'] == 'Masuk' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                        <?= $trx['jenis'] ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded text-xs bg-blue-100 text-blue-800">
                                        <?= htmlspecialchars($trx['kategori'] ?? 'Kas') ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3"><?= htmlspecialchars($trx['keterangan']) ?></td>
                                <td class="px-4 py-3 text-right font-bold <?= $trx['jenis'] == 'Masuk' ? 'text-green-600' : 'text-red-600' ?>">
                                    <?= $trx['jenis'] == 'Masuk' ? '+' : '-' ?><?= formatRupiah($trx['jumlah']) ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if (mysqli_num_rows($transaksiList) == 0): ?>
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                    <i class="fas fa-inbox text-4xl mb-2"></i>
                                    <p>Belum ada transaksi bulan ini</p>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            </div>
            <?php include '../layout/component/footer.php'; ?>
        </div>
    </div>
</body>
</html>
