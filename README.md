# Website MTs Al-Hidayah Depok + Panel Admin (CMS)

Website madrasah lengkap dengan panel admin. Semua isi website bisa diubah dari admin tanpa menyentuh kode.

## Kebutuhan hosting

- PHP 8.1 atau lebih baru (ekstensi `pdo_sqlite` atau `pdo_mysql`, `gd`, `fileinfo`, `dom`). Hampir semua hosting cPanel sudah memenuhi.
- Apache dengan `mod_rewrite` (standar di cPanel). Nginx juga bisa, lihat bagian bawah.
- Database: **SQLite** (bawaan, tanpa pengaturan apa pun) atau **MySQL/MariaDB**.

## Cara memasang di cPanel

1. Unggah isi folder ini ke `public_html` (atau subfolder, misalnya `public_html/web-baru`) lewat File Manager, lalu ekstrak.
2. Pastikan folder `storage/` dan `uploads/` bisa ditulis (izin 755 atau 775).
3. Buka alamat website di browser. Halaman **Pemasangan** akan muncul otomatis.
4. Isi nama, nama pengguna, dan kata sandi admin. Biarkan centang "Isi dengan konten contoh" agar website langsung terisi sesuai desain.
5. Selesai. Panel admin ada di `alamat-website/admin`.

### Memakai MySQL (opsional)

1. Buat database dan user MySQL di cPanel.
2. Salin `config.sample.php` menjadi `config.php`, lalu ubah `db_driver` menjadi `'mysql'` dan isi `mysql_host`, `mysql_name`, `mysql_user`, `mysql_pass`.
3. Buka website, lalu lanjutkan dari langkah 3 di atas. Tabel dibuat otomatis.

## Mencoba di komputer sendiri

```
php -S localhost:8000 router.php
```

Lalu buka http://localhost:8000.

## Yang bisa diatur dari panel admin

| Menu admin | Fungsinya |
|---|---|
| Halaman | Buat halaman baru dari **template** (profil, kartu, galeri, berita, unduhan, FAQ, kontak, PPDB, dll) atau **duplikat** halaman yang sudah ada. Isi halaman disusun dari **blok** yang bisa ditambah, diurutkan (seret atau ↑ ↓), digandakan, disembunyikan, dan dihapus. |
| Menu | Tambah, ubah, urutkan menu utama, menu atas, dan menu bawah (footer). Menu bisa menuju halaman, kategori berita, album foto, bagian halaman (`profil#sejarah`), atau tautan luar. Mendukung submenu. |
| Berita | Tulis berita dengan editor teks, foto sampul, kategori, berita utama, tanggal terbit, draf. |
| Galeri foto | Album dengan unggah banyak foto sekaligus, keterangan, urutan, sampul. |
| Video, Agenda, Guru & staf, Unduhan | Data yang otomatis tampil di blok terkait. |
| Media | Pustaka gambar. Foto otomatis diperkecil agar website tetap ringan. |
| Pesan masuk | Pesan dari formulir kontak, dengan tombol balas via WhatsApp. |
| Pendaftar PPDB | Data pendaftar online, ubah status (baru, verifikasi, diterima, cadangan, ditolak), catatan, unduh Excel (CSV). |
| Pengaturan | Nama, logo, alamat, kontak, WhatsApp, media sosial, peta, NPSN/NSM, tombol di kanan atas, warna. |
| Pengguna | Tambah admin atau editor (editor tidak bisa mengubah pengaturan dan pengguna). |

### Jenis blok yang tersedia

Banner utama, judul halaman, navigasi bagian, layanan cepat, teks + gambar, kartu (foto/ikon/program unggulan), angka statistik, langkah bernomor, linimasa sejarah, visi & misi, struktur organisasi, tanya jawab, tabel, testimoni, kutipan/hadits, ajakan (CTA), teks bebas, galeri foto, daftar album, video, berita, agenda, guru & staf, unduhan, info kontak, formulir pesan + peta, formulir PPDB, cek status PPDB, kuota & hitung mundur.

## Mengganti konten contoh

Semua teks dalam tanda kurung siku, misalnya `[Nama Kepala Madrasah]` atau `[000]`, adalah tempat isian. Foto bawaan adalah foto stok sebagai contoh; ganti dengan foto asli madrasah lewat menu Media atau langsung di blok.

## Keamanan

- Kata sandi disimpan terenkripsi, login dibatasi 8 percobaan per 15 menit.
- Semua formulir dilindungi token CSRF; formulir publik memakai jebakan bot dan batas kiriman.
- Unggahan dicek jenis filenya dan diberi nama acak; PHP tidak bisa dijalankan di folder `uploads/`.
- Folder `app/`, `storage/`, dan `config.php` tidak bisa dibuka dari browser.
- **Cadangkan** folder `storage/` (database SQLite) dan `uploads/` secara berkala.

## Nginx

```
location / { try_files $uri $uri/ /index.php?$query_string; }
location ~ ^/(app|storage)/ { deny all; }
location ~ ^/uploads/.*\.php$ { deny all; }
location ~ \.(sqlite|md)$ { deny all; }
```
