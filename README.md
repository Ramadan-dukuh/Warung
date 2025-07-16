# Warung POS System

Sistem Point of Sale (POS) sederhana berbasis web untuk manajemen penjualan, inventaris, kategori produk, dan laporan penjualan. Dibuat menggunakan PHP dan MySQL, cocok untuk UMKM, toko kelontong, atau warung.

## Fitur Utama
- **Dashboard**: Statistik produk, kategori, stok rendah, dan penjualan harian.
- **Manajemen Produk**: Tambah, edit, hapus produk dengan upload gambar dan kategori.
- **Manajemen Kategori**: Kelola kategori produk.
- **Penjualan**: Sistem kasir, keranjang belanja, checkout, dan cetak struk.
- **Laporan**: Grafik penjualan, produk/kategori terlaris, dan filter tanggal.
- **Peringatan Stok Rendah**: Notifikasi produk dengan stok menipis.

## Instalasi
1. **Clone repository**
   ```bash
   git clone https://github.com/ramadan-dukuh/warung.git
   ```
2. **Setup Database**
   - Import file SQL ke MySQL (buat database dan tabel sesuai kebutuhan).
   - Edit `koneksi.php` untuk konfigurasi database.
3. **Jalankan di Localhost**
   - Tempatkan folder di `htdocs` (XAMPP/Laragon).
   - Akses via browser: `http://localhost/Warung`

## Struktur Folder
- `index.php`         : Dashboard utama
- `pos_inventory.php` : Manajemen produk
- `pos_sale.php`      : Modul penjualan/kasir
- `pos_reports.php`   : Laporan dan grafik
- `pos_categories.php`: Manajemen kategori
- `crudBarang.php`    : Fungsi CRUD utama
- `uploads/`          : Folder gambar produk
- `style.css`         : CSS utama

## Kebutuhan
- PHP 7+
- MySQL/MariaDB
- Web server (XAMPP, Laragon, dsb)

## Kontribusi
Pull request dan issue sangat diterima! Silakan fork dan kembangkan sesuai kebutuhan.

## Lisensi
MIT

---

> Dibuat oleh [ramadan-dukuh](https://github.com/ramadan-dukuh) untuk membantu digitalisasi warung dan UMKM Indonesia.
