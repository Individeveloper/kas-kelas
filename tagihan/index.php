<?php
include '../config/app.php';
requireBendahara();

// Redirect jika ada parameter tidak valid di URL
if (isset($_GET['generate']) || isset($_GET['bayar']) || isset($_GET['batal_bayar'])) {
    header('Location: /cashflowKas/tagihan/index.php?bulan=' . (isset($_GET['bulan']) ? (int)$_GET['bulan'] : date('n')) . '&tahun=' . (isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y')));
    exit;
}

$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : date('n');
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

// Get nominal kas dari pengaturan
$nominalKas = getNominalKasAktif();
$tagihanTerbuatOtomatis = generateTagihanOtomatis($bulan, $tahun);

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $tagihanTerbuatOtomatis > 0) {
    $success = $tagihanTerbuatOtomatis . " tagihan otomatis berhasil dibuat untuk " . getBulanNama($bulan) . " " . $tahun;
}

// ============================================
// HANDLE POST REQUESTS
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Handle bayar tagihan (bisa dari template atau tagihan existing)
    if (isset($_POST['bayar']) && isset($_POST['jumlah_dibayar'])) {
        $jumlah_dibayar = (int)$_POST['jumlah_dibayar'];
        $tanggal_bayar = date('Y-m-d');
        
        // Cek apakah dari tagihan existing atau dari template murid
        if (isset($_POST['id_tagihan']) && $_POST['id_tagihan'] > 0) {
            // Tagihan sudah ada di database
            $id_tagihan = (int)$_POST['id_tagihan'];
            $tagihan = mysqli_fetch_assoc(query("SELECT t.*, m.nama FROM tagihan t JOIN murid m ON t.id_murid = m.id_murid WHERE t.id_tagihan = $id_tagihan"));
            
            if ($tagihan) {
                $sisa = $tagihan['nominal'] - $tagihan['jumlah_bayar'];
                $id_murid = $tagihan['id_murid'];
                $nama = $tagihan['nama'];
                $nominal = $tagihan['nominal'];
                $jumlah_bayar_lama = $tagihan['jumlah_bayar'];
            } else {
                $error = "Tagihan tidak ditemukan";
            }
        } elseif (isset($_POST['id_murid'])) {
            // Cek dulu tagihan periode ini, baru insert jika memang belum ada.
            $id_murid = (int)$_POST['id_murid'];
            $tagihan = mysqli_fetch_assoc(query("SELECT t.*, m.nama FROM tagihan t JOIN murid m ON t.id_murid = m.id_murid WHERE t.id_murid = $id_murid AND t.bulan = $bulan AND t.tahun = $tahun LIMIT 1"));

            if ($tagihan) {
                $id_tagihan = $tagihan['id_tagihan'];
                $sisa = $tagihan['nominal'] - $tagihan['jumlah_bayar'];
                $nama = $tagihan['nama'];
                $nominal = $tagihan['nominal'];
                $jumlah_bayar_lama = $tagihan['jumlah_bayar'];
            } else {
                $murid = mysqli_fetch_assoc(query("SELECT * FROM murid WHERE id_murid = $id_murid"));

                if ($murid) {
                    $nama = $murid['nama'];
                    $nominal = $nominalKas;
                    $jumlah_bayar_lama = 0;
                    $sisa = $nominal;

                    $result = query("INSERT INTO tagihan (id_murid, bulan, tahun, nominal, jumlah_bayar, status_bayar) VALUES ($id_murid, $bulan, $tahun, $nominal, 0, 'Belum')");
                    if ($result) {
                        $id_tagihan = mysqli_insert_id($GLOBALS['db']);
                        $tagihan = mysqli_fetch_assoc(query("SELECT t.*, m.nama FROM tagihan t JOIN murid m ON t.id_murid = m.id_murid WHERE t.id_tagihan = $id_tagihan"));
                    } else {
                        // Antisipasi race condition jika record terbuat di request lain.
                        $tagihan = mysqli_fetch_assoc(query("SELECT t.*, m.nama FROM tagihan t JOIN murid m ON t.id_murid = m.id_murid WHERE t.id_murid = $id_murid AND t.bulan = $bulan AND t.tahun = $tahun LIMIT 1"));
                        if ($tagihan) {
                            $id_tagihan = $tagihan['id_tagihan'];
                            $sisa = $tagihan['nominal'] - $tagihan['jumlah_bayar'];
                            $nama = $tagihan['nama'];
                            $nominal = $tagihan['nominal'];
                            $jumlah_bayar_lama = $tagihan['jumlah_bayar'];
                        } else {
                            $error = "Gagal membuat tagihan: " . mysqli_error($GLOBALS['db']);
                        }
                    }
                } else {
                    $error = "Murid tidak ditemukan";
                }
            }
        }
        
        // Proses pembayaran jika tidak ada error
        if (!$error && isset($tagihan)) {
            if ($jumlah_dibayar < 0) {
                $error = "Jumlah pembayaran tidak boleh negatif";
            } elseif ($jumlah_dibayar === 0) {
                $status_baru = $jumlah_bayar_lama >= $nominal
                    ? 'Lunas'
                    : ($jumlah_bayar_lama > 0 ? 'Sebagian' : 'Belum');

                $result = query("UPDATE tagihan SET status_bayar = '$status_baru' WHERE id_tagihan = $id_tagihan");
                if ($result) {
                    $success = "Pembayaran $nama tetap " . strtolower($status_baru) . " (nominal input: Rp 0).";
                } else {
                    $error = "Gagal memperbarui status: " . mysqli_error($GLOBALS['db']);
                }
            } else {
                $total_input = $jumlah_dibayar;
                $kelebihan = $jumlah_dibayar;
                $detailAlokasi = [];

                mysqli_begin_transaction($GLOBALS['db']);

                try {
                    // 1) Alokasi ke tagihan bulan aktif terlebih dahulu
                    $sisa_bulan_ini = max(0, $nominal - $jumlah_bayar_lama);
                    $dibayar_bulan_ini = min($kelebihan, $sisa_bulan_ini);
                    $jumlah_bayar_baru = $jumlah_bayar_lama + $dibayar_bulan_ini;

                    $status_baru = $jumlah_bayar_baru >= $nominal
                        ? 'Lunas'
                        : ($jumlah_bayar_baru > 0 ? 'Sebagian' : 'Belum');

                    $result = query("UPDATE tagihan SET jumlah_bayar = $jumlah_bayar_baru, status_bayar = '$status_baru', tanggal_bayar = '$tanggal_bayar' WHERE id_tagihan = $id_tagihan");
                    if (!$result) {
                        throw new \Exception("Gagal update tagihan bulan aktif: " . mysqli_error($GLOBALS['db']));
                    }

                    if ($dibayar_bulan_ini > 0) {
                        $detailAlokasi[] = [
                            'bulan' => $bulan,
                            'tahun' => $tahun,
                            'jumlah' => $dibayar_bulan_ini,
                        ];
                    }

                    $kelebihan -= $dibayar_bulan_ini;

                    // 2) Jika ada kelebihan, lanjut alokasi ke bulan berikutnya
                    $bulanAlokasi = $bulan;
                    $tahunAlokasi = $tahun;
                    $iterasiMaks = 60; // pengaman loop

                    while ($kelebihan > 0 && $iterasiMaks-- > 0) {
                        $bulanAlokasi++;
                        if ($bulanAlokasi > 12) {
                            $bulanAlokasi = 1;
                            $tahunAlokasi++;
                        }

                        $tagihanLanjutan = mysqli_fetch_assoc(query("SELECT * FROM tagihan WHERE id_murid = $id_murid AND bulan = $bulanAlokasi AND tahun = $tahunAlokasi LIMIT 1"));

                        if (!$tagihanLanjutan) {
                            $buatTagihanLanjutan = query("INSERT INTO tagihan (id_murid, bulan, tahun, nominal, jumlah_bayar, status_bayar) VALUES ($id_murid, $bulanAlokasi, $tahunAlokasi, $nominalKas, 0, 'Belum')");
                            if (!$buatTagihanLanjutan) {
                                throw new \Exception("Gagal membuat tagihan lanjutan: " . mysqli_error($GLOBALS['db']));
                            }

                            $idTagihanLanjutan = mysqli_insert_id($GLOBALS['db']);
                            $tagihanLanjutan = mysqli_fetch_assoc(query("SELECT * FROM tagihan WHERE id_tagihan = $idTagihanLanjutan LIMIT 1"));
                        }

                        if (!$tagihanLanjutan) {
                            throw new \Exception("Tagihan lanjutan tidak ditemukan");
                        }

                        $sisaLanjutan = max(0, (int)$tagihanLanjutan['nominal'] - (int)$tagihanLanjutan['jumlah_bayar']);
                        if ($sisaLanjutan <= 0) {
                            continue;
                        }

                        $dibayarLanjutan = min($kelebihan, $sisaLanjutan);
                        $jumlahBayarLanjutanBaru = (int)$tagihanLanjutan['jumlah_bayar'] + $dibayarLanjutan;

                        $statusLanjutanBaru = $jumlahBayarLanjutanBaru >= (int)$tagihanLanjutan['nominal']
                            ? 'Lunas'
                            : ($jumlahBayarLanjutanBaru > 0 ? 'Sebagian' : 'Belum');

                        $updateLanjutan = query("UPDATE tagihan SET jumlah_bayar = $jumlahBayarLanjutanBaru, status_bayar = '$statusLanjutanBaru', tanggal_bayar = '$tanggal_bayar' WHERE id_tagihan = {$tagihanLanjutan['id_tagihan']}");
                        if (!$updateLanjutan) {
                            throw new \Exception("Gagal update tagihan lanjutan: " . mysqli_error($GLOBALS['db']));
                        }

                        $detailAlokasi[] = [
                            'bulan' => $bulanAlokasi,
                            'tahun' => $tahunAlokasi,
                            'jumlah' => $dibayarLanjutan,
                        ];

                        $kelebihan -= $dibayarLanjutan;
                    }

                    if ($kelebihan > 0) {
                        throw new \Exception("Kelebihan pembayaran terlalu besar untuk dialokasikan otomatis");
                    }

                    $keterangan = "Pembayaran kas " . getBulanNama($bulan) . " " . $tahun . " - " . $nama;
                    if (count($detailAlokasi) > 1) {
                        $alokasiTerakhir = end($detailAlokasi);
                        $keterangan .= " (dialokasikan sampai " . getBulanNama($alokasiTerakhir['bulan']) . " " . $alokasiTerakhir['tahun'] . ")";
                    } elseif ($status_baru == 'Sebagian') {
                        $keterangan .= " (sebagian)";
                    }

                    $insertResult = query("INSERT INTO transaksi (id_murid, tanggal, jenis, jumlah, keterangan) VALUES ($id_murid, '$tanggal_bayar', 'Masuk', $total_input, '$keterangan')");
                    if (!$insertResult) {
                        throw new \Exception("Tagihan terupdate tapi gagal mencatat transaksi: " . mysqli_error($GLOBALS['db']));
                    }

                    mysqli_commit($GLOBALS['db']);

                    if (count($detailAlokasi) > 1) {
                        $success = "Pembayaran " . formatRupiah($total_input) . " dari $nama berhasil dicatat dan dialokasikan hingga bulan berikutnya.";
                    } else {
                        $success = "Pembayaran " . formatRupiah($total_input) . " dari $nama berhasil dicatat!";
                    }
                } catch (\Exception $e) {
                    mysqli_rollback($GLOBALS['db']);
                    $error = $e->getMessage();
                }
            }
        }
    }
    
    // Handle batalkan pembayaran
    if (isset($_POST['batal_bayar']) && isset($_POST['id_tagihan'])) {
        $id_tagihan = (int)$_POST['id_tagihan'];
        
        $tagihan = mysqli_fetch_assoc(query("SELECT t.*, m.nama FROM tagihan t JOIN murid m ON t.id_murid = m.id_murid WHERE t.id_tagihan = $id_tagihan"));
        
        if ($tagihan && $tagihan['jumlah_bayar'] > 0) {
            // Hapus record tagihan (kembali ke template)
            query("DELETE FROM tagihan WHERE id_tagihan = $id_tagihan");
            
            // Hapus transaksi terkait
            $keteranganPattern = "Pembayaran kas " . getBulanNama($tagihan['bulan']) . " " . $tagihan['tahun'] . " - " . $tagihan['nama'];
            query("DELETE FROM transaksi WHERE id_murid = {$tagihan['id_murid']} AND keterangan LIKE '$keteranganPattern%'");
            
            $success = "Semua pembayaran {$tagihan['nama']} dibatalkan";
        }
    }
}

// ============================================
// GET DATA - Template dari murid + tagihan existing
// ============================================
// Query: Ambil semua murid aktif, LEFT JOIN dengan tagihan bulan ini
$tagihanList = query("SELECT m.id_murid, m.nama, 
                             t.id_tagihan, 
                             COALESCE(t.nominal, $nominalKas) as nominal,
                             COALESCE(t.jumlah_bayar, 0) as jumlah_bayar,
                             COALESCE(t.status_bayar, 'Belum') as status_bayar,
                             t.tanggal_bayar
                      FROM murid m 
                      LEFT JOIN tagihan t ON m.id_murid = t.id_murid 
                           AND t.bulan = $bulan AND t.tahun = $tahun
                      WHERE m.status = 'Aktif'
                      ORDER BY FIELD(COALESCE(t.status_bayar, 'Belum'), 'Belum', 'Sebagian', 'Lunas'), m.nama ASC");

// Stats - hitung dari kombinasi murid + tagihan
$totalMurid = mysqli_fetch_assoc(query("SELECT COUNT(*) as total FROM murid WHERE `status` = 'Aktif'"))['total'];
$sudahLunas = mysqli_fetch_assoc(query("SELECT COUNT(*) as total FROM tagihan WHERE bulan = $bulan AND tahun = $tahun AND status_bayar = 'Lunas'"))['total'];
$bayarSebagian = mysqli_fetch_assoc(query("SELECT COUNT(*) as total FROM tagihan WHERE bulan = $bulan AND tahun = $tahun AND status_bayar = 'Sebagian'"))['total'];
$belumBayar = $totalMurid - $sudahLunas - $bayarSebagian;
$totalTerkumpul = mysqli_fetch_assoc(query("SELECT COALESCE(SUM(jumlah_bayar), 0) as total FROM tagihan WHERE bulan = $bulan AND tahun = $tahun"))['total'];
$totalTarget = $totalMurid * $nominalKas;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/cashflowKas/assets/css/style.css">
    <title>Tagihan Kas - Kas Kelas</title>
</head>
<body class="bg-gray-50">
    <div class="flex min-h-screen">
        <?php include '../layout/component/sidebar.php'; ?>
        <div class="flex-1 flex flex-col">
            <div class="flex-1 p-4 md:p-6">
            
            <!-- Header -->
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-lg. font-medium text-gray-600">Tagihan Kas</h1>
                <a href="/cashflowKas/pengaturan/index.php" class="text-sm text-gray-400 hover:text-gray-600">
                    <i class="fas fa-cog me-1"></i>Pengaturan
                </a>
            </div>
            
            <?php if ($success): ?>
            <div class="bg-gray-100 text-gray-600 px-3 py-2 rounded text-sm mb-3">
                <?= htmlspecialchars($success) ?>
            </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
            <div class="bg-gray-100 text-gray-600 px-3 py-2 rounded text-sm mb-3">
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>
            
            <!-- Filter & Stats -->
            <div class="bg-white border border-gray-200 rounded-lg p-3 mb-4">
                <form method="GET" class="flex flex-wrap items-center gap-3 mb-3">
                    <select name="bulan" class="px-3 py-1.5 text-sm border border-gray-200 rounded text-gray-600" onchange="this.form.submit()">
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                        <option value="<?= $i ?>" <?= $i == $bulan ? 'selected' : '' ?>><?= getBulanNama($i) ?></option>
                        <?php endfor; ?>
                    </select>
                    <select name="tahun" class="px-3 py-1.5 text-sm border border-gray-200 rounded text-gray-600" onchange="this.form.submit()">
                        <?php for ($y = date('Y') - 2; $y <= date('Y') + 1; $y++): ?>
                        <option value="<?= $y ?>" <?= $y == $tahun ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                    <input type="text" id="searchInput" placeholder="Cari nama..." 
                           class="flex-1 min-w-[150px] px-3 py-1.5 text-sm border border-gray-200 rounded focus:outline-none focus:border-gray-300 text-gray-600">
                </form>
                <div class="flex flex-wrap gap-4 text-sm">
                    <span class="text-gray-400">Total: <span class="text-gray-500"><?= $totalMurid ?></span></span>
                    <span class="text-gray-400">Lunas: <span class="text-gray-500"><?= $sudahLunas ?></span></span>
                    <span class="text-gray-400">Sebagian: <span class="text-gray-500"><?= $bayarSebagian ?></span></span>
                    <span class="text-gray-400">Belum: <span class="text-gray-500"><?= $belumBayar ?></span></span>
                    <span class="text-gray-400 ml-auto">Terkumpul: <span class="text-gray-500"><?= formatRupiah($totalTerkumpul) ?></span></span>
                </div>
            </div>
            
            <!-- Table -->
            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                <div class="px-3 py-2 border-b border-gray-100 text-sm text-gray-500 flex justify-between">
                    <span>Nominal: <?= formatRupiah($nominalKas) ?>/murid</span>
                    <span id="searchResult" class="hidden"></span>
                </div>
                <?php if ($totalMurid > 0): ?>
                <div class="overflow-x-auto max-h-[60vh] overflow-y-auto">
                    <table id="tagihanTable" class="w-full text-sm">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr class="text-gray-500 text-xs uppercase">
                                <th class="px-3 py-2 text-left font-medium">#</th>
                                <th class="px-3 py-2 text-left font-medium">Nama</th>
                                <th class="px-3 py-2 text-right font-medium">Sisa</th>
                                <th class="px-3 py-2 text-center font-medium">Status</th>
                                <th class="px-3 py-2 text-center font-medium">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php $no = 1; while ($t = mysqli_fetch_assoc($tagihanList)): 
                                $sisa = $t['nominal'] - $t['jumlah_bayar'];
                                $hasTagihan = !empty($t['id_tagihan']);
                            ?>
                            <tr class="hover:bg-gray-50" data-name="<?= htmlspecialchars($t['nama']) ?>">
                                <td class="px-3 py-2 text-gray-400"><?= $no++ ?></td>
                                <td class="px-3 py-2 text-gray-600"><?= htmlspecialchars($t['nama']) ?></td>
                                <td class="px-3 py-2 text-right text-gray-500"><?= formatRupiah($sisa) ?></td>
                                <td class="px-3 py-2 text-center">
                                    <?php if ($t['status_bayar'] == 'Lunas'): ?>
                                    <span class="text-gray-500 text-xs">✓ Lunas</span>
                                    <?php elseif ($t['status_bayar'] == 'Sebagian'): ?>
                                    <span class="text-gray-500 text-xs">◐ Sebagian</span>
                                    <?php else: ?>
                                    <span class="text-gray-400 text-xs">○ Belum</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <?php if ($t['status_bayar'] != 'Lunas'): ?>
                                    <div class="inline-flex gap-1">
                                        <button type="button" onclick="showBayarModal(<?= $hasTagihan ? $t['id_tagihan'] : 0 ?>, <?= $t['id_murid'] ?>, '<?= htmlspecialchars(addslashes($t['nama'])) ?>', <?= $sisa ?>)"
                                                class="text-gray-400 hover:text-gray-600 px-1" title="Bayar">
                                            <i class="fas fa-coins"></i>
                                        </button>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($hasTagihan && $t['jumlah_bayar'] > 0): ?>
                                    <form method="POST" class="inline" onsubmit="return confirm('Batalkan pembayaran <?= htmlspecialchars(addslashes($t['nama'])) ?>?')">
                                        <input type="hidden" name="id_tagihan" value="<?= $t['id_tagihan'] ?>">
                                        <input type="hidden" name="batal_bayar" value="1">
                                        <button type="submit" class="text-gray-300 hover:text-gray-500 px-1" title="Batal">
                                            <i class="fas fa-undo text-xs"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="p-6 text-center text-gray-400">
                    <p class="mb-2">Tidak ada murid aktif</p>
                    <a href="/cashflowKas/murid/tambah.php" class="text-gray-500 hover:underline text-sm">+ Tambah Murid</a>
                </div>
                <?php endif; ?>
            </div>
            </div>
            <?php include '../layout/component/footer.php'; ?>
        </div>
    </div>
    
    <!-- Modal -->
    <div id="bayarModal" class="fixed inset-0 bg-black/20 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-5 w-full max-w-sm mx-4 shadow-lg">
            <h3 class="text-gray-600 mb-4">Pembayaran Kas</h3>
            <form method="POST" id="formBayar">
                <input type="hidden" name="id_tagihan" id="modal_id_tagihan" value="0">
                <input type="hidden" name="id_murid" id="modal_id_murid" value="0">
                <input type="hidden" name="bayar" value="1">
                <div class="mb-3">
                    <label class="text-sm text-gray-400">Nama</label>
                    <p id="modal_nama" class="text-gray-600"></p>
                </div>
                <div class="mb-3">
                    <label class="text-sm text-gray-400">Sisa Tagihan</label>
                    <p id="modal_sisa" class="text-gray-600"></p>
                </div>
                <div class="mb-4">
                    <label class="text-sm text-gray-400">Jumlah Bayar</label>
                    <input type="number" name="jumlah_dibayar" id="modal_jumlah" required min="0" step="1000"
                           class="w-full mt-1 px-3 py-2 border border-gray-200 rounded focus:outline-none focus:border-gray-300 text-gray-600">
                    <p class="text-xs text-gray-400 mt-1">
                        Boleh isi 0 (belum bayar), sebagian, pas nominal (lunas), atau lebih (otomatis lanjut bulan berikutnya).
                    </p>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-gray-700 text-white py-2 rounded hover:bg-gray-600 text-sm">
                        Bayar
                    </button>
                    <button type="button" onclick="closeBayarModal()" class="flex-1 border border-gray-200 text-gray-500 py-2 rounded hover:bg-gray-50 text-sm">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function showBayarModal(idTagihan, idMurid, nama, sisa) {
            document.getElementById('modal_id_tagihan').value = idTagihan;
            document.getElementById('modal_id_murid').value = idMurid;
            document.getElementById('modal_nama').textContent = nama;
            document.getElementById('modal_sisa').textContent = 'Rp ' + sisa.toLocaleString('id-ID');
            document.getElementById('modal_jumlah').value = '';
            document.getElementById('bayarModal').classList.remove('hidden');
            document.getElementById('bayarModal').classList.add('flex');
            document.getElementById('modal_jumlah').focus();
        }
        
        function closeBayarModal() {
            document.getElementById('bayarModal').classList.add('hidden');
            document.getElementById('bayarModal').classList.remove('flex');
        }
        
        document.getElementById('bayarModal').addEventListener('click', function(e) {
            if (e.target === this) closeBayarModal();
        });
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeBayarModal();
        });
        
        // Search
        const searchInput = document.getElementById('searchInput');
        const searchResult = document.getElementById('searchResult');
        const tableBody = document.querySelector('#tagihanTable tbody');
        
        if (searchInput && tableBody) {
            searchInput.addEventListener('input', function() {
                const term = this.value.toLowerCase().trim();
                const rows = tableBody.querySelectorAll('tr[data-name]');
                let visible = 0;
                
                rows.forEach(row => {
                    const match = term === '' || row.getAttribute('data-name').toLowerCase().includes(term);
                    row.style.display = match ? '' : 'none';
                    if (match) visible++;
                });
                
                if (term !== '') {
                    searchResult.textContent = `${visible}/${rows.length}`;
                    searchResult.classList.remove('hidden');
                } else {
                    searchResult.classList.add('hidden');
                }
            });
        }
    </script>
</body>
</html>
