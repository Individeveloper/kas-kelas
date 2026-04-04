<?php
$user = getUser();
$role = getRole();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$currentDir = basename(dirname($_SERVER['PHP_SELF']));

function isActive($page, $dir = '') {
    global $currentPage, $currentDir;
    if ($dir) {
        return $currentDir === $dir ? 'bg-gray-700' : '';
    }
    return $currentPage === $page ? 'bg-gray-700' : '';
}
?>
<nav class="bg-gray-800 text-white w-64 min-h-screen p-4 flex flex-col sticky top-0 h-screen overflow-y-auto">
    <div class="brand text-2xl font-bold mb-2">
        <i class="fas fa-wallet me-2"></i>Kas Kelas
    </div>
    <div class="text-gray-400 text-sm mb-6 pb-4 border-b border-gray-700">
        <i class="fas fa-user me-1"></i> <?= htmlspecialchars($user['username']) ?>
        <br>
        <span class="text-xs bg-gray-600 px-2 py-1 rounded mt-1 inline-block">
            <?= getRoleName($role) ?>
        </span>
    </div>
    
    <ul class="space-y-2 flex-1">
        <li>
            <a href="/cashflowKas/layout/dashboard.php" 
               class="block px-4 py-2 rounded hover:bg-gray-700 transition <?= isActive('dashboard') ?>">
                <i class="fas fa-tachometer-alt me-2 w-5"></i> Dashboard
            </a>
        </li>
        
        <?php if (isBendahara()): ?>
        <li>
            <a href="/cashflowKas/murid/index.php" 
               class="block px-4 py-2 rounded hover:bg-gray-700 transition <?= isActive('', 'murid') ?>">
                <i class="fas fa-users me-2 w-5"></i> Data Murid
            </a>
        </li>
        <li>
            <a href="/cashflowKas/tagihan/index.php" 
               class="block px-4 py-2 rounded hover:bg-gray-700 transition <?= isActive('', 'tagihan') ?>">
                <i class="fas fa-file-invoice me-2 w-5"></i> Tagihan Kas
            </a>
        </li>
        <li>
            <a href="/cashflowKas/transaksi/index.php" 
               class="block px-4 py-2 rounded hover:bg-gray-700 transition <?= isActive('', 'transaksi') ?>">
                <i class="fas fa-money-bill-wave me-2 w-5"></i> Transaksi Kas
            </a>
        </li>
        <li>
            <a href="/cashflowKas/pengaturan/index.php" 
               class="block px-4 py-2 rounded hover:bg-gray-700 transition <?= isActive('', 'pengaturan') ?>">
                <i class="fas fa-cog me-2 w-5"></i> Pengaturan
            </a>
        </li>
        <?php endif; ?>
        
        <li>
            <a href="/cashflowKas/laporan/index.php" 
               class="block px-4 py-2 rounded hover:bg-gray-700 transition <?= isActive('', 'laporan') ?>">
                <i class="fas fa-chart-bar me-2 w-5"></i> Laporan Bulanan
            </a>
        </li>
    </ul>
    
    <div class="mt-auto pt-4 border-t border-gray-700">
        <a href="/cashflowKas/logout.php" 
           class="block px-4 py-2 rounded hover:bg-red-600 transition text-red-400 hover:text-white">
            <i class="fas fa-sign-out-alt me-2 w-5"></i> Logout
        </a>
    </div>
</nav>