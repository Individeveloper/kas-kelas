# Struktur Project Modular - Kas Kelas

## Deskripsi
Project ini telah direfaktor menjadi struktur modular untuk memudahkan maintenance dan development.

## Struktur Direktori

```
cashflowKas/
├── src/
│   ├── Controllers/          # Berisi controller untuk handle request/response
│   ├── Models/               # Berisi model untuk database operations
│   ├── Helpers/              # Berisi helper functions dan utilities
│   ├── Views/                # Berisi template/view files
│   └── Router.php            # Main router class
├── routes/
│   └── routes.php            # Definisi semua rute aplikasi
├── config/
│   ├── connect.php           # Database connection
│   └── app.php               # Application config (deprecated, use bootstrap.php)
├── assets/
│   ├── css/                  # Stylesheet
│   └── database_*.sql        # Database backups
├── layout/                   # Old layout files (akan dipindahkan ke src/Views)
├── bootstrap.php             # Bootstrap file untuk autoloader & init
└── index.php                 # Entry point aplikasi
```

## Komponen Utama

### 1. Models (`src/Models/`)
Menangani semua operasi database:
- `User.php` - Login, autentikasi, user management
- `Murid.php` - CRUD murid, get murid dengan status pembayaran
- `Transaksi.php` - CRUD transaksi, get total kas masuk/keluar
- `Tagihan.php` - CRUD tagihan, generate tagihan bulanan
- `Pengaturan.php` - Get/set pengaturan aplikasi

**Cara Penggunaan:**
```php
use App\Models\User;
use App\Models\Murid;

// Login
User::login($username, $password);

// Get semua murid aktif
$muridList = Murid::getAllAktif();

// Create murid baru
Murid::create('Budi', '7A', 'Aktif');
```

### 2. Controllers (`src/Controllers/`)
Menangani business logic dan flow aplikasi:
- `AuthController.php` - Login, logout, unauthorized
- `DashboardController.php` - Dashboard page
- (Tambahkan controller lainnya sesuai kebutuhan)

**Cara Penggunaan:**
```php
namespace App\Controllers;
use App\Models\Murid;

class MuridController {
    public static function index() {
        requireLogin();
        $muridList = Murid::getAllAktif();
        require SRC_PATH . '/Views/murid/index.php';
    }
}
```

### 3. Helpers (`src/Helpers/`)
Utility functions dan helper classes:
- `Formatter.php` - Format data (rupiah, tanggal, dll)
- `Database.php` - Database connection dan query helper

**Cara Penggunaan:**
```php
use App\Helpers\Formatter;

echo Formatter::rupiah(50000);           // Rp 50.000
echo Formatter::bulanNama(1);            // Januari
echo Formatter::tanggalIndonesia('2026-03-27'); // Jumat, 27 Maret 2026
```

### 4. Router (`src/Router.php`)
Menangani routing aplikasi:
```php
use App\Router;

Router::get('/murid', 'MuridController@index');
Router::post('/murid', 'MuridController@store');
```

### 5. Bootstrap (`bootstrap.php`)
File inisialisasi:
- Autoloader untuk class App\
- Environment setup
- Backward compatibility functions
- Database initialization

## Workflow Migration

### Langkah 1: Update Bootstrap
Setiap halaman mulai dengan:
```php
<?php
require_once __DIR__ . '/../../bootstrap.php';
```

### Langkah 2: Pisahkan Logic ke Controller
```php
// Sebelum (di view)
<?php
$muridList = query("SELECT * FROM murid WHERE status = 'Aktif'");
?>

// Sesudah (di controller)
$muridList = Murid::getAllAktif();
require SRC_PATH . '/Views/murid/index.php';
```

### Langkah 3: Gunakan Models untuk Database
```php
// Sebelum
$result = query("INSERT INTO murid (nama, kelas) VALUES ('$nama', '$kelas')");

// Sesudah
Murid::create($nama, $kelas);
```

### Langkah 4: Pindahkan Views
Pindahkan file view ke `src/Views/` dengan struktur:
```
src/Views/
├── auth/
│   ├── login.php
│   └── unauthorized.php
├── dashboard.php
├── murid/
│   ├── index.php
│   ├── create.php
│   └── edit.php
└── layout/
    ├── sidebar.php
    └── footer.php
```

## Keuntungan Modular

✅ **Mudah di-maintain** - Kode terorganisir dan mudah dicari
✅ **Reusable** - Models dan Controllers bisa digunakan di banyak tempat
✅ **Scalable** - Mudah menambah fitur baru
✅ **Testable** - Kode lebih modular, mudah di-test
✅ **Collaboration** - Tim lebih mudah bekerja di file berbeda
✅ **Backward Compatible** - Helper functions masih berfungsi seperti sebelumnya

## Checklist Migration

- [ ] Update semua file ke struktur modular
- [ ] Pindahkan views ke src/Views/
- [ ] Refactor logic ke Controllers
- [ ] Update include paths di semua file
- [ ] Update routes di routes/routes.php
- [ ] Test login/logout functionality
- [ ] Test semua fitur utama
- [ ] Remove old files setelah migration complete

## Next Steps

1. Buat controllers untuk Murid, Transaksi, Tagihan, Laporan, Pengaturan
2. Pindahkan semua views ke src/Views/
3. Update routes untuk semua endpoint
4. Implementasi view rendering system yang lebih baik (bisa pakai Blade atau Twig)
5. Buat unit tests untuk models

---

Untuk informasi lebih lanjut, lihat file bootstrap.php dan routes/routes.php
