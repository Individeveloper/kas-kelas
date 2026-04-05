<?php
include '../config/app.php';
requireLogin();

$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : date('n');
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

if (isBendahara()) {
    generateTagihanOtomatis($bulan, $tahun);
}

// Get all murid aktif with their tagihan status for selected month
$muridList = query("SELECT m.*, 
                    COALESCE(t.status_bayar, 'Belum') as status_tagihan,
                    COALESCE(t.jumlah_bayar, 0) as jumlah_bayar,
                    COALESCE(t.nominal, 0) as nominal_tagihan
                    FROM murid m 
                    LEFT JOIN tagihan t ON m.id_murid = t.id_murid AND t.bulan = $bulan AND t.tahun = $tahun
                    WHERE m.`status` = 'Aktif'
                    ORDER BY m.nama ASC");

// Statistics
$totalMurid = mysqli_fetch_assoc(query("SELECT COUNT(*) as total FROM murid WHERE `status` = 'Aktif'"))['total'];
$kasMasuk = mysqli_fetch_assoc(query("SELECT COALESCE(SUM(jumlah), 0) as total FROM transaksi WHERE jenis = 'Masuk' AND MONTH(tanggal) = $bulan AND YEAR(tanggal) = $tahun"))['total'];
$kasKeluar = mysqli_fetch_assoc(query("SELECT COALESCE(SUM(jumlah), 0) as total FROM transaksi WHERE jenis = 'Keluar' AND MONTH(tanggal) = $bulan AND YEAR(tanggal) = $tahun"))['total'];

// Status pembayaran dari tagihan bulan ini
$sudahBayar = mysqli_fetch_assoc(query("SELECT COUNT(*) as total FROM tagihan WHERE bulan = $bulan AND tahun = $tahun AND status_bayar = 'Lunas'"))['total'];
$bayarSebagian = mysqli_fetch_assoc(query("SELECT COUNT(*) as total FROM tagihan WHERE bulan = $bulan AND tahun = $tahun AND status_bayar = 'Sebagian'"))['total'];
$belumBayar = mysqli_fetch_assoc(query("SELECT COUNT(*) as total FROM tagihan WHERE bulan = $bulan AND tahun = $tahun AND status_bayar = 'Belum'"))['total'];

// Total saldo keseluruhan
$totalKasMasuk = mysqli_fetch_assoc(query("SELECT COALESCE(SUM(jumlah), 0) as total FROM transaksi WHERE jenis = 'Masuk'"))['total'];
$totalKasKeluar = mysqli_fetch_assoc(query("SELECT COALESCE(SUM(jumlah), 0) as total FROM transaksi WHERE jenis = 'Keluar'"))['total'];
$saldo = $totalKasMasuk - $totalKasKeluar;

// Get transaksi for selected month
$transaksiMasuk = query("SELECT t.*, m.nama FROM transaksi t LEFT JOIN murid m ON t.id_murid = m.id_murid WHERE t.jenis = 'Masuk' AND MONTH(t.tanggal) = $bulan AND YEAR(t.tanggal) = $tahun ORDER BY t.tanggal ASC");
$transaksiKeluar = query("SELECT * FROM transaksi WHERE jenis = 'Keluar' AND MONTH(tanggal) = $bulan AND YEAR(tanggal) = $tahun ORDER BY tanggal ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/cashflowKas/assets/css/style.css">
    <title>Laporan Bulanan - Kas Kelas</title>
    <style>
        @media print {
            .no-print { display: none !important; }
            .print-full { width: 100% !important; }
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <div class="no-print">
            <?php include '../layout/component/sidebar.php'; ?>
        </div>
        <div class="flex-1 flex flex-col print-full">
            <div class="flex-1 p-6">
            <div class="flex justify-between items-center mb-6 no-print">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Laporan Bulanan</h1>
                    <p class="text-gray-600">Laporan kas kelas per bulan</p>
                </div>
                <button onclick="downloadPDF()" class="bg-gray-800 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                    <i class="fas fa-print me-2"></i>Download PDF
                </button>
            </div>
            
            <!-- Filter -->
            <div class="bg-white rounded-lg shadow p-4 mb-6 no-print">
                <form method="GET" class="flex items-center gap-4">
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
                    <div class="pt-6">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                            <i class="fas fa-filter me-2"></i>Filter
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Print Header -->
            <div class="hidden print:block text-center mb-6">
                <h1 class="text-2xl font-bold">LAPORAN KAS KELAS</h1>
                <p>Periode: <?= getBulanNama($bulan) ?> <?= $tahun ?></p>
            </div>
            
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-600">
                            <i class="fas fa-arrow-down text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Kas Masuk</p>
                            <p class="text-xl font-bold text-green-600"><?= formatRupiah($kasMasuk) ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-red-100 text-red-600">
                            <i class="fas fa-arrow-up text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Kas Keluar</p>
                            <p class="text-xl font-bold text-red-600"><?= formatRupiah($kasKeluar) ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                            <i class="fas fa-balance-scale text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Saldo Bulan Ini</p>
                            <p class="text-xl font-bold text-blue-600"><?= formatRupiah($kasMasuk - $kasKeluar) ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                            <i class="fas fa-wallet text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Total Saldo</p>
                            <p class="text-xl font-bold text-purple-600"><?= formatRupiah($saldo) ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Payment Status Summary -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">
                    <i class="fas fa-chart-pie me-2"></i>Ringkasan Status Pembayaran Murid
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <p class="text-3xl font-bold text-gray-800"><?= $totalMurid ?></p>
                        <p class="text-sm text-gray-500">Total Murid</p>
                    </div>
                    <div class="text-center p-4 bg-green-50 rounded-lg">
                        <p class="text-3xl font-bold text-green-600"><?= $sudahBayar ?></p>
                        <p class="text-sm text-gray-500">Sudah Lunas</p>
                    </div>
                    <div class="text-center p-4 bg-yellow-50 rounded-lg">
                        <p class="text-3xl font-bold text-yellow-600"><?= $bayarSebagian ?></p>
                        <p class="text-sm text-gray-500">Bayar Sebagian</p>
                    </div>
                    <div class="text-center p-4 bg-red-50 rounded-lg">
                        <p class="text-3xl font-bold text-red-600"><?= $belumBayar ?></p>
                        <p class="text-sm text-gray-500">Belum Bayar</p>
                    </div>
                </div>
            </div>
            
            <!-- Daftar Murid -->
            <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
                <div class="p-4 bg-gray-50 border-b">
                    <h2 class="font-bold text-gray-800">
                        <i class="fas fa-users me-2"></i>Daftar Status Pembayaran Murid
                    </h2>
                </div>
                <table class="w-full">
                    <thead class="bg-gray-800 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left">No</th>
                            <th class="px-4 py-3 text-left">Nama Murid</th>
                            <th class="px-4 py-3 text-right">Tagihan</th>
                            <th class="px-4 py-3 text-right">Dibayar</th>
                            <th class="px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php 
                        $no = 1;
                        mysqli_data_seek($muridList, 0);
                        while ($murid = mysqli_fetch_assoc($muridList)): 
                            $statusClass = 'bg-gray-100 text-gray-800';
                            $statusText = 'Belum Ada';
                            if ($murid['status_tagihan'] == 'Lunas') {
                                $statusClass = 'bg-green-100 text-green-800';
                                $statusText = 'Lunas';
                            } elseif ($murid['status_tagihan'] == 'Sebagian') {
                                $statusClass = 'bg-yellow-100 text-yellow-800';
                                $statusText = 'Sebagian';
                            } elseif ($murid['status_tagihan'] == 'Belum') {
                                $statusClass = 'bg-red-100 text-red-800';
                                $statusText = 'Belum Bayar';
                            }
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3"><?= $no++ ?></td>
                            <td class="px-4 py-3 font-medium"><?= htmlspecialchars($murid['nama']) ?></td>
                            <td class="px-4 py-3 text-right"><?= formatRupiah($murid['nominal_tagihan']) ?></td>
                            <td class="px-4 py-3 text-right text-green-600"><?= formatRupiah($murid['jumlah_bayar']) ?></td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 rounded text-xs <?= $statusClass ?>">
                                    <?= $statusText ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Detail Transaksi Masuk -->
            <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
                <div class="p-4 bg-gray-50 border-b">
                    <h2 class="font-bold text-gray-800">
                        <i class="fas fa-arrow-down text-green-600 me-2"></i>Detail Kas Masuk - <?= getBulanNama($bulan) ?> <?= $tahun ?>
                    </h2>
                </div>
                <table class="w-full">
                    <thead class="bg-gray-800 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left">No</th>
                            <th class="px-4 py-3 text-left">Tanggal</th>
                            <th class="px-4 py-3 text-left">Murid</th>
                            <th class="px-4 py-3 text-left">Keterangan</th>
                            <th class="px-4 py-3 text-right">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php 
                        $no = 1;
                        $totalMasukDetail = 0;
                        while ($trx = mysqli_fetch_assoc($transaksiMasuk)): 
                            $totalMasukDetail += $trx['jumlah'];
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3"><?= $no++ ?></td>
                            <td class="px-4 py-3"><?= date('d/m/Y', strtotime($trx['tanggal'])) ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($trx['nama'] ?? '-') ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($trx['keterangan']) ?></td>
                            <td class="px-4 py-3 text-right font-bold text-green-600">
                                <?= formatRupiah($trx['jumlah']) ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if ($no == 1): ?>
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                Tidak ada kas masuk bulan ini
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                    <?php if ($totalMasukDetail > 0): ?>
                    <tfoot class="bg-gray-100 font-bold">
                        <tr>
                            <td colspan="4" class="px-4 py-3 text-right">TOTAL KAS MASUK:</td>
                            <td class="px-4 py-3 text-right text-green-600"><?= formatRupiah($totalMasukDetail) ?></td>
                        </tr>
                    </tfoot>
                    <?php endif; ?>
                </table>
            </div>
            
            <!-- Detail Transaksi Keluar -->
            <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
                <div class="p-4 bg-gray-50 border-b">
                    <h2 class="font-bold text-gray-800">
                        <i class="fas fa-arrow-up text-red-600 me-2"></i>Detail Kas Keluar - <?= getBulanNama($bulan) ?> <?= $tahun ?>
                    </h2>
                </div>
                <table class="w-full">
                    <thead class="bg-gray-800 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left">No</th>
                            <th class="px-4 py-3 text-left">Tanggal</th>
                            <th class="px-4 py-3 text-left">Keterangan</th>
                            <th class="px-4 py-3 text-right">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php 
                        $no = 1;
                        $totalKeluarDetail = 0;
                        while ($trx = mysqli_fetch_assoc($transaksiKeluar)): 
                            $totalKeluarDetail += $trx['jumlah'];
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3"><?= $no++ ?></td>
                            <td class="px-4 py-3"><?= date('d/m/Y', strtotime($trx['tanggal'])) ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($trx['keterangan']) ?></td>
                            <td class="px-4 py-3 text-right font-bold text-red-600">
                                <?= formatRupiah($trx['jumlah']) ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if ($no == 1): ?>
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                Tidak ada kas keluar bulan ini
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                    <?php if ($totalKeluarDetail > 0): ?>
                    <tfoot class="bg-gray-100 font-bold">
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-right">TOTAL KAS KELUAR:</td>
                            <td class="px-4 py-3 text-right text-red-600"><?= formatRupiah($totalKeluarDetail) ?></td>
                        </tr>
                    </tfoot>
                    <?php endif; ?>
                </table>
            </div>
            
            <!-- Print Footer -->
            <div class="hidden print:block mt-8">
                <div class="flex justify-between">
                    <div class="text-center">
                        <p>Mengetahui,</p>
                        <p>Wali Kelas</p>
                        <br><br><br>
                        <p>_________________</p>
                    </div>
                    <div class="text-center">
                        <p><?= date('d') ?> <?= getBulanNama(date('n')) ?> <?= date('Y') ?></p>
                        <p>Bendahara Kelas</p>
                        <br><br><br>
                        <p>_________________</p>
                    </div>
                </div>
            </div>
            </div>
            <div class="no-print">
                <?php include '../layout/component/footer.php'; ?>
            </div>
        </div>
    </div>
</body>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</html>
