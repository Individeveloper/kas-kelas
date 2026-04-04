<?php
include '../config/app.php';
requireBendahara();

$jenis = isset($_GET['jenis']) ? $_GET['jenis'] : 'Masuk';
if ($jenis != 'Masuk' && $jenis != 'Keluar') {
    $jenis = 'Masuk';
}

$error = '';
$success = false;
$redirectBulan = date('n');
$redirectTahun = date('Y');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal = mysqli_real_escape_string($db, $_POST['tanggal']);
    $jenis = mysqli_real_escape_string($db, $_POST['jenis']);
    $kategori = isset($_POST['kategori']) ? mysqli_real_escape_string($db, $_POST['kategori']) : null;
    $jumlah = (int)$_POST['jumlah'];
    $keterangan = mysqli_real_escape_string($db, $_POST['keterangan']);
    
    // Insert dengan atau tanpa kategori
    if ($kategori) {
        $result = query("INSERT INTO transaksi (id_murid, tanggal, jenis, kategori, jumlah, keterangan) VALUES (1, '$tanggal', '$jenis', '$kategori', $jumlah, '$keterangan')");
    } else {
        $result = query("INSERT INTO transaksi (id_murid, tanggal, jenis, jumlah, keterangan) VALUES (1, '$tanggal', '$jenis', $jumlah, '$keterangan')");
    }
    
    if ($result) {
        $redirectBulan = date('n', strtotime($tanggal));
        $redirectTahun = date('Y', strtotime($tanggal));
        $success = true;
    } else {
        $error = 'Gagal menyimpan transaksi: ' . mysqli_error($db);
    }
}

// Get murid list (untuk default id_murid)
$muridDefault = mysqli_fetch_assoc(query("SELECT id_murid FROM murid WHERE `status` = 'Aktif' ORDER BY id_murid ASC LIMIT 1"));
$defaultMuridId = $muridDefault ? $muridDefault['id_murid'] : 1;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/cashflowKas/assets/css/style.css">
    <title>Tambah Transaksi - Kas Kelas</title>
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <?php include '../layout/component/sidebar.php'; ?>
        <div class="flex-1 flex flex-col">
            <div class="flex-1 p-6">
            <div class="mb-6">
                <a href="/cashflowKas/transaksi/index.php" class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
                <h1 class="text-2xl font-bold text-gray-800 mt-2">
                    Tambah Transaksi <?= $jenis == 'Masuk' ? 'Kas Masuk' : 'Kas Keluar' ?>
                </h1>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
                <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
                </div>
                <?php endif; ?>
                
                <form method="POST">
                    <input type="hidden" name="jenis" value="<?= $jenis ?>">
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Jenis Transaksi</label>
                            <div class="flex gap-4">
                                <label class="flex items-center">
                                    <input type="radio" name="jenis" value="Masuk" <?= $jenis == 'Masuk' ? 'checked' : '' ?> class="me-2" onchange="toggleKategori()">
                                    <span class="text-green-600 font-medium">Kas Masuk</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="jenis" value="Keluar" <?= $jenis == 'Keluar' ? 'checked' : '' ?> class="me-2" onchange="toggleKategori()">
                                    <span class="text-red-600 font-medium">Kas Keluar</span>
                                </label>
                            </div>
                        </div>
                        
                        <div id="kategoriField" style="display: <?= $jenis == 'Masuk' ? 'block' : 'none' ?>">
                            <label class="block text-gray-700 font-medium mb-2">Kategori</label>
                            <select name="kategori" id="kategoriSelect"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                                <option value="Kas">Kas</option>
                                <option value="Keperluan Kelas">Keperluan Kelas</option>
                                <option value="Sumbangan">Sumbangan</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Tanggal <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal" required 
                                   value="<?= date('Y-m-d') ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Jumlah (Rp) <span class="text-red-500">*</span></label>
                            <input type="number" name="jumlah" required min="1"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                                   placeholder="Masukkan jumlah">
                        </div>
                        
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Keterangan <span class="text-red-500">*</span></label>
                            <textarea name="keterangan" rows="3" required
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                                      placeholder="Keterangan transaksi"></textarea>
                        </div>
                    </div>
                    <div class="mt-6">
                        <button type="submit" class="bg-gray-800 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition">
                            <i class="fas fa-save me-2"></i>Simpan Transaksi
                        </button>
                    </div>
                </form>
            </div>
            </div>
            <?php include '../layout/component/footer.php'; ?>
        </div>
    </div>
    <script>
        function toggleKategori() {
            const jenisRadios = document.getElementsByName('jenis');
            const kategoriField = document.getElementById('kategoriField');
            const kategoriSelect = document.getElementById('kategoriSelect');
            
            let selectedJenis = '';
            jenisRadios.forEach(radio => {
                if (radio.checked) {
                    selectedJenis = radio.value;
                }
            });
            
            if (selectedJenis === 'Masuk') {
                kategoriField.style.display = 'block';
                kategoriSelect.required = true;
            } else {
                kategoriField.style.display = 'none';
                kategoriSelect.required = false;
                kategoriSelect.value = 'Kas'; // reset ke default
            }
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', toggleKategori);
    </script>
    
    <?php if ($success): ?>
    <script>
        alert('Transaksi berhasil disimpan!');
        window.location.href = '/cashflowKas/transaksi/index.php?bulan=<?= $redirectBulan ?>&tahun=<?= $redirectTahun ?>';
    </script>
    <?php endif; ?>
</body>
</html>
