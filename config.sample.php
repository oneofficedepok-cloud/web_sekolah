<?php
// Salin file ini menjadi config.php lalu sesuaikan.
// Tanpa config.php, website memakai pengaturan di bawah (SQLite, tanpa perlu setup database).
return [
    // 'sqlite' (default, data di storage/database.sqlite) atau 'mysql'
    'db_driver' => 'sqlite',
    'sqlite_path' => __DIR__ . '/storage/database.sqlite',

    // Isi bila memakai MySQL/MariaDB (misalnya di cPanel)
    'mysql_host' => 'localhost',
    'mysql_port' => 3306,
    'mysql_database' => 'mts_cms',
    'mysql_user' => 'root',
    'mysql_password' => '',

    'timezone' => 'Asia/Jakarta',
    // true hanya saat pengembangan; tampilkan pesan error PHP
    'debug' => false,
    // Batas ukuran unggahan (MB)
    'max_upload_mb' => 8,
];
