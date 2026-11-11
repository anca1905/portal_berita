# 📰 Portal Berita Modern & Sistem Monetisasi Mandiri

Portal Berita modern, responsif, dan siap produksi (production-ready) yang dilengkapi dengan fitur publikasi berita, live streaming TV, data pasar real-time, mode gelap/terang, dan **sistem monetisasi mandiri (Rate Card, Advertorial & Direct Banner Ads)** tanpa ketergantungan pada Google AdSense.

---

## ✨ Fitur Unggulan

- **Halaman Depan Dinamis:** Headline berita utama, grid kategori berita, berita terpopuler, dan breaking news ticker.
- **Sistem Monetisasi Mandiri (Direct Ads):**
  - Halaman publik **Rate Card & Media Kit (`pasang-iklan.php`)** dengan 4 paket tarif terstruktur (Advertorial, Banner Header 728x90, Banner Sidebar 300x250, dan Kemitraan Instansi).
  - Generator pesan WhatsApp otomatis untuk mempermudah calon klien memesan iklan.
  - Slot banner placeholder otomatis yang menampilkan penawaran sewa slot saat belum ada iklan aktif.
  - Manajemen iklan lengkap di admin panel (`admin/ads.php`).
- **Live TV Streaming (`tv.php`):** Nonton saluran siaran berita secara langsung.
- **Dark Mode Anti-FOUC:** Pergantian tema gelap/terang mulus yang tersimpan di localStorage.
- **Pencarian Cepat & Live Search:** Hasil pencarian instan dengan highlight teks.
- **Admin Dashboard Lengkap:** Manajemen artikel (tambah, edit, draft, publish, upload foto), kategori, komentar, pengguna, mitra, dan pengaturan identitas situs.
- **SEO & Social Sharing Ready:** OpenGraph, Twitter Card, Schema.org JSON-LD terintegrasi.
- **Multi-Environment Support:** Auto-detection `APP_URL` serta dukungan file `.env`.

---

## 🚀 Panduan Instalasi Lokal (Laragon / XAMPP)

1. Clone repositori ke folder web server Anda:
   ```bash
   git clone https://github.com/USERNAME/REPO_NAME.git portal_berita
   ```
2. Buat database baru bernama `xnn` di MySQL / phpMyAdmin.
3. Import file `database_xnn.sql` ke database `xnn`.
4. (Opsional) Buat file `.env` dari `.env.example` jika menggunakan port atau password database khusus:
   ```env
   DB_HOST=localhost
   DB_NAME=xnn
   DB_USER=root
   DB_PASS=
   ```
5. Akses website di browser:
   - Frontend: `http://localhost/portal_berita/`
   - Pasang Iklan: `http://localhost/portal_berita/pasang-iklan.php`
   - Admin Panel: `http://localhost/portal_berita/admin/`

---

## 🌐 Panduan Upload ke Live Server / Hosting (cPanel / DirectAdmin / InfinityFree)

1. **Upload File Web:**
   - Compress semua file project menjadi `.zip` (kecuali `.git` dan `.env`).
   - Buka File Manager di cPanel / hosting Anda, masuk ke folder `public_html` (atau subdomain).
   - Upload file zip dan ekstrak.
2. **Setup Database di Hosting:**
   - Buat MySQL Database dan MySQL User baru di menu **MySQL Databases** hosting Anda.
   - Sambungkan user ke database dengan hak akses penuh (*ALL PRIVILEGES*).
   - Buka **phpMyAdmin** di hosting, pilih database yang baru dibuat, lalu klik tab **Import** dan pilih file `database_xnn.sql`.
3. **Konfigurasi Koneksi (`.env`):**
   - Buat file `.env` di direktori utama web Anda:
     ```env
     DB_HOST=localhost (atau host database hosting Anda)
     DB_NAME=nama_database_hosting
     DB_USER=nama_user_hosting
     DB_PASS=password_database_hosting
     APP_URL=https://domainanda.com
     ```
   - *Catatan: Jika tidak menggunakan `.env`, Anda juga bisa langsung menyesuaikan nilai di `includes/config.php`.*
4. **Selesai!** Website berita Anda kini aktif di internet.

---

## 🔐 Kredensial Login Admin Default

- **URL Login Admin:** `/admin/login.php`
- **Username:** `admin` atau `anca`
- *(Pastikan segera mengganti password di menu Pengguna setelah deploy ke server publik)*

---

## 📄 Lisensi
Hak Cipta Dilindungi Undang-Undang. Dikembangkan untuk portal berita modern dan profesional.
