# Cashflow Kas (Kas Kelas)

Dokumentasi ini menjelaskan struktur, fitur, serta analisa proyek berdasarkan isi workspace saat ini.

## Ringkasan Proyek
Aplikasi **Cashflow Kas** adalah sistem pengelolaan kas kelas berbasis **PHP Native** dan **MySQL**. Fungsinya meliputi pencatatan kas masuk/keluar, pengelolaan tagihan bulanan murid, laporan kas, dan sistem login berbasis role.

> Catatan: Workspace saat ini masih dalam tahap **transisi** dari arsitektur prosedural (legacy) ke arsitektur **modular/MVC**.

---

## Fitur Utama
- **Login & Role Access**: Bendahara, Wali Kelas, Ketua Kelas.
- **Manajemen Murid**: CRUD data murid dan status aktif/tidak aktif.
- **Transaksi Kas**: Kas masuk/keluar dengan kategori dan filter per bulan/tahun.
- **Tagihan Bulanan**: Status tagihan per murid (Belum/Sebagian/Lunas).
- **Dashboard & Laporan**: Ringkasan kas masuk, kas keluar, dan saldo.

---

## Struktur Direktori (Analisa Workspace)
```
cashflowKas/
├── src/                    # Arsitektur modular/MVC baru
│   ├── Controllers/        # Logic aplikasi
│   ├── Models/             # Query database & entity
│   ├── Helpers/            # Helper utilities (Format & DB)
│   ├── Views/              # View (template)
│   └── Router.php          # Router custom
├── routes/                 # Definisi rute aplikasi
├── config/                 # Config legacy & koneksi DB
├── assets/                 # CSS & SQL backup
├── layout/                 # Komponen layout legacy
├── murid/, transaksi/, ... # Modul legacy
├── bootstrap.php           # Autoloader & init
└── index.php               # Entry point
```

### Status Migrasi
- **Legacy (prosedural):** folder `murid/`, `transaksi/`, `tagihan/`, `laporan/`.
- **Modular (MVC):** folder `src/` + `routes/`.

---

## Database
File SQL utama ada di `assets/database.sql` dengan tabel:
- `user` (login & role)
- `murid`
- `transaksi`
- `tagihan`
- `pengaturan`

Database default:
```
DB_NAME=kasKelas
```

---

## Konfigurasi Environment
File contoh konfigurasi ada di `.env.example`:
```
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=kasKelas
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost/cashflowKas
```

> Jika menggunakan `.env`, pastikan bootstrap atau config sudah membaca file tersebut.

---

## Routing
Routing modular terdapat di `routes/routes.php` dan menggunakan `src/Router.php`.
Contoh:
- `/login`
- `/logout`
- `/`

Modul lain masih diakses langsung via path file legacy.

---

## Catatan Teknis
- **Backend:** PHP Native
- **Database:** MySQL/MariaDB
- **Frontend:** TailwindCSS via CDN + FontAwesome

---

## Rekomendasi Lanjutan
1. Selesaikan migrasi modul legacy ke MVC (Controller + View di `src/`).
2. Tambahkan controller untuk `Murid`, `Transaksi`, `Tagihan`, `Laporan`, `Pengaturan`.
3. Konsolidasikan routing agar semua akses via `index.php` + router.
4. Hilangkan query langsung di file view legacy.
5. Tambahkan validasi & sanitasi input.

---

## Referensi Internal
- Struktur modular: [MODULAR_STRUCTURE.md](MODULAR_STRUCTURE.md)
- Database schema: [assets/database.sql](assets/database.sql)
- Routes: [routes/routes.php](routes/routes.php)
- Bootstrap: [bootstrap.php](bootstrap.php)
