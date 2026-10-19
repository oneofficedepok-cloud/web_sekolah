<?php
declare(strict_types=1);

/** Starting layouts offered when the admin creates a new page. */
function page_templates(): array
{
    $ph = fn($t = '', $s = '') => ['type' => 'page_header', 'title' => $t, 'subtitle' => $s, 'image' => ''];
    return [
        'kosong' => ['label' => 'Kosong', 'desc' => 'Mulai dari nol, tambahkan blok sendiri.', 'blocks' => [$ph()]],
        'standar' => ['label' => 'Halaman teks', 'desc' => 'Judul halaman dan satu bagian teks. Cocok untuk info, sejarah, atau pengumuman.', 'blocks' => [
            $ph(), ['type' => 'text_image', 'eyebrow' => '', 'title' => 'Judul bagian', 'body' => '<p>Tulis isi halaman di sini.</p>', 'image' => '', 'image_side' => 'right'],
        ]],
        'profil' => ['label' => 'Profil / tentang', 'desc' => 'Pintasan bagian, teks + foto, visi misi, struktur organisasi, dan guru.', 'blocks' => [
            $ph(), ['type' => 'subnav', 'items' => [['label' => 'Tentang', 'url' => '#tentang'], ['label' => 'Visi & misi', 'url' => '#visi'], ['label' => 'Struktur', 'url' => '#struktur']]],
            ['type' => 'text_image', '_anchor' => 'tentang', 'eyebrow' => 'TENTANG', 'title' => 'Judul bagian', 'body' => '<p>Isi.</p>', 'image_side' => 'right'],
            ['type' => 'vision', '_anchor' => 'visi', 'eyebrow' => 'VISI & MISI', 'title' => 'Arah kami', 'vision' => '', 'missions' => [['text' => '']], 'goals' => []],
            ['type' => 'org_chart', '_anchor' => 'struktur', 'eyebrow' => 'STRUKTUR', 'title' => 'Pengurus', 'items' => [['level' => '1', 'role' => 'Ketua', 'name' => '[Nama]', 'highlight' => true]]],
        ]],
        'kartu' => ['label' => 'Daftar kartu bergambar', 'desc' => 'Untuk program, ekstrakurikuler, fasilitas, atau layanan.', 'blocks' => [
            $ph(), ['type' => 'cards', 'eyebrow' => 'PROGRAM', 'title' => 'Judul bagian', 'style' => 'image', 'columns' => '3', 'items' => [['title' => 'Kartu 1', 'text' => ''], ['title' => 'Kartu 2', 'text' => ''], ['title' => 'Kartu 3', 'text' => '']]],
        ]],
        'galeri' => ['label' => 'Galeri', 'desc' => 'Daftar album foto dan video.', 'blocks' => [
            $ph('', 'Dokumentasi kegiatan madrasah.'), ['type' => 'albums', 'eyebrow' => 'ALBUM FOTO', 'title' => 'Album kegiatan', 'limit' => '0'],
            ['type' => 'videos', '_bg' => 'soft', 'eyebrow' => 'VIDEO', 'title' => 'Video kegiatan', 'limit' => '0'],
        ]],
        'foto' => ['label' => 'Album foto bebas', 'desc' => 'Satu galeri foto pilihan dengan keterangan.', 'blocks' => [
            $ph(), ['type' => 'gallery', 'title' => 'Galeri foto', 'items' => []],
        ]],
        'berita' => ['label' => 'Daftar berita', 'desc' => 'Berita lengkap dengan cari, filter kategori, dan halaman.', 'blocks' => [
            $ph('', 'Kabar terbaru dari madrasah.'), ['type' => 'posts', 'mode' => 'full', 'limit' => '9', 'category' => ''],
        ]],
        'unduhan' => ['label' => 'Unduhan dokumen', 'desc' => 'Daftar file yang bisa diunduh pengunjung.', 'blocks' => [
            $ph(), ['type' => 'downloads', '_bg' => 'white', 'eyebrow' => 'UNDUHAN', 'title' => 'Dokumen', 'category' => ''],
        ]],
        'faq' => ['label' => 'Tanya jawab', 'desc' => 'Pertanyaan umum dalam bentuk akordeon.', 'blocks' => [
            $ph(), ['type' => 'faq', 'eyebrow' => 'FAQ', 'title' => 'Pertanyaan yang sering diajukan', 'items' => [['q' => 'Pertanyaan?', 'a' => 'Jawaban.']]],
        ]],
        'kontak' => ['label' => 'Kontak', 'desc' => 'Info kontak, formulir pesan, dan peta.', 'blocks' => [
            $ph('Hubungi kami', ''), ['type' => 'contact_info'], ['type' => 'contact_form', 'title' => 'Kirim pesan', 'show_map' => true],
        ]],
        'pendaftaran' => ['label' => 'Pendaftaran / PPDB', 'desc' => 'Kuota, alur, biaya, formulir online, dan FAQ.', 'blocks' => [
            ['type' => 'countdown', 'eyebrow' => 'PENDAFTARAN', 'title' => 'Penerimaan peserta didik baru', 'label' => 'KUOTA', 'quota' => '100', 'deadline' => '', 'btn1_label' => 'Daftar sekarang', 'btn1_url' => '#form'],
            ['type' => 'steps', 'eyebrow' => 'ALUR', 'title' => 'Langkah mendaftar', 'items' => [['title' => 'Isi formulir', 'text' => ''], ['title' => 'Verifikasi', 'text' => ''], ['title' => 'Tes', 'text' => ''], ['title' => 'Pengumuman', 'text' => '']]],
            ['type' => 'ppdb_form', '_anchor' => 'form', 'title' => 'Formulir pendaftaran'],
        ]],
    ];
}
