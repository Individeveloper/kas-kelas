<?php
include '../config/app.php';
requireBendahara();

// Handle soft delete (set status ke Tidak Aktif)
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    query("UPDATE murid SET `status` = 'Tidak Aktif' WHERE id_murid = $id");
    header('Location: /cashflowKas/murid/index.php?msg=deleted');
    exit;
}

// Handle restore (set status ke Aktif)
if (isset($_GET['restore'])) {
    $id = (int)$_GET['restore'];
    query("UPDATE murid SET `status` = 'Aktif' WHERE id_murid = $id");
    header('Location: /cashflowKas/murid/index.php?show=inactive&msg=restored');
    exit;
}

// Handle permanent delete
if (isset($_GET['permanent_delete'])) {
    $id = (int)$_GET['permanent_delete'];
    query("DELETE FROM transaksi WHERE id_murid = $id");
    query("DELETE FROM murid WHERE id_murid = $id");
    header('Location: /cashflowKas/murid/index.php?show=inactive&msg=permanent_deleted');
    exit;
}

// Filter berdasarkan status
$showInactive = isset($_GET['show']) && $_GET['show'] === 'inactive';
if ($showInactive) {
    $muridList = query("SELECT * FROM murid WHERE `status` = 'Tidak Aktif' ORDER BY nama ASC");
} else {
    $muridList = query("SELECT * FROM murid WHERE `status` = 'Aktif' ORDER BY nama ASC");
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
    <title>Data Murid - Kas Kelas</title>
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <?php include '../layout/component/sidebar.php'; ?>
        <div class="flex-1 flex flex-col">
            <div class="flex-1 p-6">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">
                        <?= $showInactive ? 'Murid Tidak Aktif' : 'Data Murid' ?>
                    </h1>
                    <p class="text-gray-600">
                        <?= $showInactive ? 'Daftar murid yang sudah tidak aktif' : 'Kelola data murid' ?>
                    </p>
                </div>
                <div class="flex gap-2">
                    <?php if ($showInactive): ?>
                    <a href="/cashflowKas/murid/index.php" 
                       class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-users me-2"></i>Murid Aktif
                    </a>
                    <?php else: ?>
                    <a href="/cashflowKas/murid/index.php?show=inactive" 
                       class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition">
                        <i class="fas fa-user-slash me-2"></i>Tidak Aktif
                    </a>
                    <a href="/cashflowKas/murid/tambah.php" 
                       class="bg-gray-800 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                        <i class="fas fa-plus me-2"></i>Tambah Murid
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (isset($_GET['msg'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <i class="fas fa-check-circle me-2"></i>
                <?php
                $msgs = [
                    'added' => 'Murid berhasil ditambahkan!',
                    'updated' => 'Data murid berhasil diperbarui!',
                    'deleted' => 'Murid berhasil dinonaktifkan!',
                    'restored' => 'Murid berhasil diaktifkan kembali!',
                    'permanent_deleted' => 'Murid berhasil dihapus permanen!'
                ];
                echo $msgs[$_GET['msg']] ?? 'Operasi berhasil!';
                ?>
            </div>
            <?php endif; ?>
            
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-800 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left">No</th>
                            <th class="px-4 py-3 text-left">Nama</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php $no = 1; while ($murid = mysqli_fetch_assoc($muridList)): 
                            $statusClass = $murid['status'] == 'Aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3"><?= $no++ ?></td>
                            <td class="px-4 py-3 font-medium"><?= htmlspecialchars($murid['nama']) ?></td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 rounded text-xs <?= $statusClass ?>">
                                    <?= htmlspecialchars($murid['status']) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <?php if ($showInactive): ?>
                                <a href="/cashflowKas/murid/index.php?restore=<?= $murid['id_murid'] ?>" 
                                   onclick="return confirm('Aktifkan kembali murid ini?')"
                                   class="text-green-600 hover:text-green-800 me-3" title="Aktifkan">
                                    <i class="fas fa-undo"></i>
                                </a>
                                <a href="/cashflowKas/murid/index.php?permanent_delete=<?= $murid['id_murid'] ?>" 
                                   onclick="return confirm('PERINGATAN: Data akan dihapus permanen dan tidak dapat dikembalikan. Lanjutkan?')"
                                   class="text-red-600 hover:text-red-800" title="Hapus Permanen">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <?php else: ?>
                                <a href="/cashflowKas/murid/edit.php?id=<?= $murid['id_murid'] ?>" 
                                   class="text-blue-600 hover:text-blue-800 me-3" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="/cashflowKas/murid/index.php?delete=<?= $murid['id_murid'] ?>" 
                                   onclick="return confirm('Murid akan dinonaktifkan. Lanjutkan?')"
                                   class="text-red-600 hover:text-red-800" title="Nonaktifkan">
                                    <i class="fas fa-user-slash"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if (mysqli_num_rows($muridList) == 0): ?>
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                <i class="fas fa-users text-4xl mb-2"></i>
                                <p>Belum ada data murid</p>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            </div>
            <?php include '../layout/component/footer.php'; ?>
        </div>
    </div>
</body>
</html>
