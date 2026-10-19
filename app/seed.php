<?php
declare(strict_types=1);

/*
 * Starter content that mirrors the approved design. Everything in [brackets] is a placeholder
 * for the school to replace from the admin panel. Photos live in uploads/seed/.
 */

function seed_content(int $authorId): void
{
    $img = fn(string $n) => 'uploads/seed/' . $n . '.jpg';
    $now = now();

    foreach ([
        'site_name' => 'MTs Al-Hidayah', 'site_city' => 'Depok', 'tagline' => 'Madrasah Tsanawiyah · Depok',
        'address' => '[Alamat lengkap madrasah], Kota Depok, Jawa Barat [Kode pos]', 'email' => 'info@mtsalhidayahdepok.sch.id',
        'phone' => '[021-0000-0000]', 'whatsapp' => '6281200000000', 'office_hours' => "Senin – Jumat: [07.00 – 15.00 WIB]\nSabtu: [07.00 – 12.00 WIB]",
        'footer_about' => 'Madrasah Tsanawiyah di bawah naungan [Nama Yayasan] dan pembinaan Kementerian Agama.',
        'instagram' => 'https://instagram.com/', 'youtube' => 'https://youtube.com/', 'facebook' => 'https://facebook.com/', 'tiktok' => '',
        'whatsapp_channel' => '', 'npsn' => '[00000000]', 'nsm' => '[000000000000]', 'cta_label' => 'Daftar PPDB', 'cta_url' => 'ppdb',
        'primary_color' => '#0E4D3A', 'meta_description' => 'Website resmi MTs Al-Hidayah Depok: profil madrasah, berita, galeri, dan PPDB online.',
        'map_embed' => '', 'map_link' => 'https://maps.google.com/?q=MTs+Al-Hidayah+Depok',
    ] as $k => $v) set_setting($k, $v);

    // Categories and posts
    $cat = [];
    foreach (['Kegiatan', 'Prestasi', 'Pengumuman', 'Artikel'] as $c) $cat[$c] = insert('categories', ['name' => $c, 'slug' => slugify($c)]);
    $note = '<p><em>Ini contoh berita bawaan. Ubah atau hapus dari panel admin, menu Berita.</em></p>';
    $posts = [
        ['Siswa MTs Al-Hidayah raih juara KSM Matematika tingkat kota', 'Prestasi', 'trofi', 1, 'Tim olimpiade madrasah membawa pulang juara pada Kompetisi Sains Madrasah tingkat Kota Depok.'],
        ['Peringatan Maulid Nabi bersama wali murid', 'Kegiatan', 'berita2', 0, 'Seluruh warga madrasah memperingati Maulid Nabi dengan shalawat, tausiyah, dan santunan.'],
        ['Jadwal Asesmen Akhir Semester ganjil', 'Pengumuman', 'ujian', 0, 'Asesmen Akhir Semester ganjil dilaksanakan berbasis komputer. Simak jadwal dan ketentuannya.'],
        ['Wisuda tahfidz angkatan [tahun]', 'Kegiatan', 'wisuda', 0, 'Puluhan siswa diwisuda setelah menuntaskan target hafalan Al-Qur\'an.'],
        ['Tim pencak silat juara O2SN Kota Depok', 'Prestasi', 'silat', 0, 'Atlet pencak silat madrasah meraih medali pada ajang O2SN tingkat kota.'],
        ['Tips mendampingi anak belajar di rumah', 'Artikel', 'kelas', 0, 'Beberapa kebiasaan sederhana yang membantu anak belajar lebih teratur di rumah.'],
    ];
    foreach ($posts as $i => [$title, $c, $cover, $feat, $exc]) {
        $date = date('Y-m-d H:i:s', strtotime('-' . ($i * 6 + 1) . ' days 09:00'));
        insert('posts', ['title' => $title, 'slug' => unique_slug('posts', slugify($title)), 'category_id' => $cat[$c], 'cover' => $img($cover),
            'excerpt' => $exc, 'body' => $note . '<p>' . e($exc) . '</p><p>[Isi lengkap berita: siapa, apa, kapan, di mana, dan bagaimana kegiatan berlangsung.]</p><h2>Rangkaian kegiatan</h2><ul><li>[Poin pertama]</li><li>[Poin kedua]</li></ul><blockquote>[Kutipan dari kepala madrasah atau guru pembina.]</blockquote>',
            'status' => 'published', 'featured' => $feat, 'views' => 0, 'author_id' => $authorId, 'published_at' => $date, 'created_at' => $date, 'updated_at' => $date]);
    }

    // Agenda
    foreach ([[18, 'Asesmen Akhir Semester ganjil', '07.00 – 12.00', 'Lab komputer'], [30, 'Pembagian rapor semester ganjil', '08.00 – 11.00', 'Ruang kelas'],
              [45, 'Pembukaan PPDB gelombang 1', '08.00', 'Online & kantor TU'], [60, 'Pesantren kilat', '07.00 – 15.00', 'Masjid madrasah']] as [$d, $t, $time, $place]) {
        insert('agenda', ['title' => $t, 'event_date' => date('Y-m-d', strtotime("+$d days")), 'time_text' => $time, 'place' => $place, 'description' => '']);
    }

    // Albums
    $albums = [
        'Kegiatan madrasah' => ['gal1', 'gal2', 'gal3', 'gal4', 'gal5', 'gal6', 'upacara', 'bersama'],
        'Ekstrakurikuler' => ['pramuka2', 'paskibra', 'silat', 'futsal', 'kaligrafi', 'tutor'],
        'Wisuda & prestasi' => ['wisuda', 'trofi', 'siswi', 'berita1'],
        'Fasilitas madrasah' => ['masjid', 'perpus', 'labkomp', 'kelas', 'lapangan'],
    ];
    $albumIds = [];
    $k = 0;
    foreach ($albums as $title => $photos) {
        $aid = insert('albums', ['title' => $title, 'slug' => unique_slug('albums', slugify($title)), 'description' => 'Dokumentasi ' . strtolower($title) . ' MTs Al-Hidayah Depok.',
            'cover' => $img($photos[0]), 'event_date' => date('Y-m-d', strtotime('-' . (++$k * 12) . ' days')), 'created_at' => $now]);
        $albumIds[$title] = $aid;
        foreach ($photos as $i => $p) insert('photos', ['album_id' => $aid, 'file' => $img($p), 'caption' => '', 'sort' => $i]);
    }

    // Staff
    foreach ([['Kepala Madrasah', '', 'Pimpinan'], ['Guru', 'Al-Qur\'an Hadis', 'Guru PAI'], ['Guru', 'Bahasa Arab', 'Guru PAI'], ['Guru', 'Fikih', 'Guru PAI'],
              ['Guru', 'Matematika', 'Guru umum'], ['Guru', 'IPA', 'Guru umum'], ['Guru', 'Bahasa Inggris', 'Guru umum'], ['Guru', 'Bahasa Indonesia', 'Guru umum'],
              ['Kepala Tata Usaha', '', 'Tenaga kependidikan'], ['Operator', '', 'Tenaga kependidikan']] as $i => [$pos, $subj, $grp]) {
        insert('staff', ['name' => $i === 0 ? '[Nama Kepala Madrasah]' : '[Nama ' . ($grp === 'Tenaga kependidikan' ? 'Staf' : 'Guru') . ']', 'position' => $pos,
            'subject' => $subj, 'group_name' => $grp, 'photo' => $i === 0 ? $img('kepala') : '', 'sort' => $i]);
    }

    // Downloads (small placeholder PDFs)
    foreach ([['Kalender akademik [2026/2027]', 'Akademik'], ['Daftar buku pelajaran', 'Akademik'], ['Modul tahfidz juz 30', 'Akademik'],
              ['Tata tertib siswa', 'Kesiswaan'], ['Brosur PPDB [2027/2028]', 'PPDB'], ['Sertifikat akreditasi', 'Profil']] as [$t, $c]) {
        $rel = 'uploads/seed/' . slugify($t) . '.pdf';
        $size = seed_pdf(ROOT . '/' . $rel, $t);
        insert('downloads', ['title' => $t, 'file' => $rel, 'category' => $c, 'size' => $size, 'created_at' => $now]);
    }

    // Pages
    $P = fn(string $t, string $s = '', string $i = '') => ['type' => 'page_header', 'title' => $t, 'subtitle' => $s, 'image' => $i];
    $pages = [];
    $pages['beranda'] = ['Beranda', [
        ['type' => 'hero', 'eyebrow' => 'PPDB TAHUN AJARAN [2027/2028] TELAH DIBUKA', 'title' => 'Tumbuh berilmu, beriman, dan berakhlak mulia.',
            'subtitle' => 'MTs Al-Hidayah Depok mendampingi setiap siswa menguasai ilmu pengetahuan, mencintai Al-Qur\'an, dan siap melangkah ke jenjang berikutnya.',
            'image' => $img('hero'), 'btn1_label' => 'Daftar Sekarang', 'btn1_url' => 'ppdb', 'btn2_label' => 'Kenali Madrasah', 'btn2_url' => 'profil',
            'badges' => [['icon' => 'medal', 'text' => 'Terakreditasi [A]'], ['icon' => 'book', 'text' => 'Kurikulum Merdeka · Kemenag'], ['icon' => 'check', 'text' => 'NPSN [00000000]']],
            'card_label' => 'Gelombang 1 ditutup', 'card_text' => '[Tanggal penutupan]'],
        ['type' => 'quick_links', 'items' => [
            ['icon' => 'clipboard', 'title' => 'PPDB Online', 'text' => 'Daftar dan pantau status', 'url' => 'ppdb'],
            ['icon' => 'laptop', 'title' => 'E-Learning', 'text' => 'Materi dan tugas kelas', 'url' => 'akademik#digital'],
            ['icon' => 'test', 'title' => 'Ujian CBT', 'text' => 'Asesmen berbasis komputer', 'url' => 'akademik#digital'],
            ['icon' => 'medal', 'title' => 'Cek Kelulusan', 'text' => 'Pengumuman kelas IX', 'url' => 'kesiswaan#kelulusan'],
            ['icon' => 'calendar', 'title' => 'Kalender', 'text' => 'Agenda akademik', 'url' => 'akademik#kalender']]],
        ['type' => 'text_image', 'eyebrow' => 'SAMBUTAN KEPALA MADRASAH', 'title' => 'Assalamu\'alaikum warahmatullahi wabarakatuh.',
            'body' => '<p>[Paragraf sambutan: ucapan selamat datang, komitmen madrasah terhadap mutu pendidikan, pembinaan akhlak, dan ajakan kepada orang tua untuk bersama-sama mendidik putra-putri.]</p><p>[Paragraf kedua: harapan untuk siswa dan peran website sebagai jembatan informasi madrasah.]</p>',
            'image' => $img('kepala'), 'image_side' => 'left', 'quote_mark' => true, 'sign_name' => '[Nama Kepala Madrasah], [Gelar]', 'sign_role' => 'Kepala MTs Al-Hidayah Depok',
            'btn_label' => 'Baca sambutan lengkap', 'btn_url' => 'profil#sambutan'],
        ['type' => 'stats', 'items' => [['value' => '[000]', 'label' => 'Siswa aktif'], ['value' => '[00]', 'label' => 'Guru dan tenaga kependidikan'],
            ['value' => '[00]', 'label' => 'Prestasi tahun ini'], ['value' => '[00]', 'label' => 'Ekstrakurikuler']]],
        ['type' => 'cards', 'eyebrow' => 'PROGRAM UNGGULAN', 'title' => 'Kurikulum nasional, diperkaya nilai-nilai pesantren.', 'link_label' => 'Semua program', 'link_url' => 'akademik#program',
            'style' => 'image', 'columns' => '4', 'items' => [
                ['image' => $img('prog1'), 'icon' => 'book', 'title' => 'Tahfidz Al-Qur\'an', 'text' => 'Target hafalan bertahap dengan mutaba\'ah harian dan wisuda tahfidz.', 'meta' => '', 'url' => ''],
                ['image' => $img('prog2'), 'icon' => 'lang', 'title' => 'Bahasa Arab & Inggris', 'text' => 'Pembiasaan percakapan harian, klub bahasa, dan lomba pidato.', 'meta' => '', 'url' => ''],
                ['image' => $img('prog3'), 'icon' => 'flask', 'title' => 'Sains & Teknologi', 'text' => 'Praktikum IPA, literasi digital, dan persiapan olimpiade madrasah.', 'meta' => '', 'url' => ''],
                ['image' => $img('prog4'), 'icon' => 'heart', 'title' => 'Pembinaan Karakter', 'text' => 'Shalat berjamaah, kajian pekanan, dan kegiatan sosial siswa.', 'meta' => '', 'url' => '']]],
        ['type' => 'posts', '_bg' => 'soft', 'eyebrow' => 'KABAR MADRASAH', 'title' => 'Berita terbaru', 'link_label' => 'Semua berita', 'link_url' => 'berita', 'category' => '', 'limit' => '3', 'mode' => 'grid'],
        ['type' => 'gallery', 'eyebrow' => 'KEHIDUPAN SISWA', 'title' => 'Galeri & ekstrakurikuler', 'link_label' => 'Buka galeri', 'link_url' => 'galeri',
            'album' => (string)$albumIds['Kegiatan madrasah'], 'limit' => '6', 'items' => []],
        ['type' => 'quote', 'arabic' => 'طَلَبُ الْعِلْمِ فَرِيضَةٌ عَلَى كُلِّ مُسْلِمٍ', 'text' => '"Menuntut ilmu itu wajib atas setiap muslim."', 'source' => 'HR. Ibnu Majah'],
        ['type' => 'cta', 'title' => 'Bergabung bersama keluarga besar MTs Al-Hidayah.', 'text' => 'Pendaftaran peserta didik baru tahun ajaran [2027/2028] dapat dilakukan sepenuhnya secara online.',
            'image' => '', 'btn1_label' => 'Daftar PPDB Online', 'btn1_url' => 'ppdb', 'btn2_label' => 'Tanya via WhatsApp', 'btn2_url' => 'https://wa.me/6281200000000'],
    ]];

    $pages['profil'] = ['Profil', [
        $P('Profil madrasah', 'Mengenal sejarah, visi, pengelola, dan fasilitas MTs Al-Hidayah Depok.', $img('masjid')),
        ['type' => 'subnav', 'items' => array_map(fn($x) => ['label' => $x[0], 'url' => '#' . $x[1]], [['Sejarah', 'sejarah'], ['Visi & misi', 'visi-misi'], ['Sambutan', 'sambutan'],
            ['Struktur organisasi', 'struktur'], ['Guru & staf', 'guru'], ['Sarana prasarana', 'sarana'], ['Akreditasi', 'akreditasi'], ['Komite', 'komite']])],
        ['type' => 'timeline', '_anchor' => 'sejarah', 'eyebrow' => 'SEJARAH', 'title' => 'Perjalanan MTs Al-Hidayah Depok',
            'intro' => '[Paragraf sejarah: latar belakang berdirinya madrasah, tokoh pendiri, dan perkembangan dari tahun ke tahun.]', 'items' => [
                ['year' => '[Tahun]', 'text' => '[Madrasah didirikan oleh Yayasan Al-Hidayah dengan jumlah siswa angkatan pertama.]'],
                ['year' => '[Tahun]', 'text' => '[Memperoleh izin operasional dan NSM dari Kementerian Agama.]'],
                ['year' => '[Tahun]', 'text' => '[Pembangunan gedung baru, masjid, dan laboratorium.]'],
                ['year' => '[Tahun]', 'text' => '[Terakreditasi [A] oleh BAN-S/M.]']]],
        ['type' => 'vision', '_anchor' => 'visi-misi', '_bg' => 'soft', 'eyebrow' => 'VISI, MISI & TUJUAN', 'title' => 'Arah pendidikan kami',
            'vision' => '[Terwujudnya generasi yang beriman, berilmu, berakhlak mulia, dan berprestasi.]',
            'missions' => array_map(fn($t) => ['text' => $t], ['Menyelenggarakan pembelajaran yang aktif, kreatif, dan berbasis nilai-nilai Islam.',
                'Membiasakan ibadah, akhlak mulia, dan cinta Al-Qur\'an dalam kehidupan sehari-hari.', 'Mengembangkan potensi akademik dan nonakademik siswa secara seimbang.',
                'Membekali siswa dengan literasi digital dan kemampuan berbahasa asing.', '[Misi lain sesuai dokumen resmi madrasah.]']),
            'goals' => array_map(fn($t) => ['text' => $t], ['[Lulusan hafal minimal [0] juz Al-Qur\'an.]', '[Lulusan diterima di SMA/MA/SMK favorit.]', '[Siswa meraih prestasi di tingkat kota hingga nasional.]'])],
        ['type' => 'text_image', '_anchor' => 'sambutan', 'eyebrow' => 'SAMBUTAN KEPALA MADRASAH', 'title' => 'Assalamu\'alaikum warahmatullahi wabarakatuh.',
            'body' => '<p>[Isi sambutan lengkap kepala madrasah: ucapan selamat datang, komitmen mutu pendidikan, pembinaan akhlak, dan ajakan kepada orang tua untuk bersama mendidik putra-putri.]</p><p>[Paragraf penutup dan harapan.]</p>',
            'image' => $img('kepala'), 'image_side' => 'right', 'quote_mark' => true, 'sign_name' => '[Nama Kepala Madrasah], [Gelar]', 'sign_role' => 'Kepala MTs Al-Hidayah Depok'],
        ['type' => 'org_chart', '_anchor' => 'struktur', '_bg' => 'soft', 'eyebrow' => 'STRUKTUR ORGANISASI', 'title' => 'Pengelola madrasah', 'items' => [
            ['level' => '1', 'role' => 'Ketua Yayasan', 'name' => '[Nama]', 'highlight' => false], ['level' => '1', 'role' => 'Kepala Madrasah', 'name' => '[Nama]', 'highlight' => true],
            ['level' => '1', 'role' => 'Ketua Komite', 'name' => '[Nama]', 'highlight' => false],
            ['level' => '2', 'role' => 'Waka Kurikulum', 'name' => '[Nama]', 'highlight' => false], ['level' => '2', 'role' => 'Waka Kesiswaan', 'name' => '[Nama]', 'highlight' => false],
            ['level' => '2', 'role' => 'Waka Sarpras', 'name' => '[Nama]', 'highlight' => false], ['level' => '2', 'role' => 'Waka Humas', 'name' => '[Nama]', 'highlight' => false],
            ['level' => '2', 'role' => 'Kepala Tata Usaha', 'name' => '[Nama]', 'highlight' => false],
            ['level' => '3', 'role' => 'Wali Kelas', 'name' => 'VII, VIII, IX', 'highlight' => false], ['level' => '3', 'role' => 'Guru Mata Pelajaran', 'name' => '[00] orang', 'highlight' => false],
            ['level' => '3', 'role' => 'Bimbingan Konseling', 'name' => '[Nama]', 'highlight' => false], ['level' => '3', 'role' => 'Pustakawan & Laboran', 'name' => '[Nama]', 'highlight' => false]]],
        ['type' => 'staff', '_anchor' => 'guru', 'eyebrow' => 'GURU & STAF', 'title' => 'Pendidik yang mendampingi siswa', 'group' => '', 'filter' => true],
        ['type' => 'cards', '_anchor' => 'sarana', '_bg' => 'soft', 'eyebrow' => 'SARANA PRASARANA', 'title' => 'Fasilitas belajar', 'style' => 'image', 'columns' => '3', 'items' => [
            ['image' => $img('masjid'), 'title' => 'Masjid', 'text' => 'Tempat shalat berjamaah, kajian, dan kegiatan keagamaan.'],
            ['image' => $img('perpus'), 'title' => 'Perpustakaan', 'text' => 'Koleksi buku pelajaran, kitab, dan bacaan umum.'],
            ['image' => $img('labkomp'), 'title' => 'Laboratorium komputer', 'text' => 'Untuk pembelajaran TIK dan Ujian CBT.'],
            ['image' => $img('prog3'), 'title' => 'Laboratorium IPA', 'text' => 'Praktikum fisika, biologi, dan kimia dasar.'],
            ['image' => $img('kelas'), 'title' => 'Ruang kelas', 'text' => '[00] ruang kelas yang nyaman dan berproyektor.'],
            ['image' => $img('lapangan'), 'title' => 'Lapangan olahraga', 'text' => 'Upacara, olahraga, dan kegiatan ekstrakurikuler.']]],
        ['type' => 'table', '_anchor' => 'akreditasi', 'eyebrow' => 'AKREDITASI & LEGALITAS', 'title' => 'Identitas madrasah', 'link_label' => 'Unduh sertifikat akreditasi', 'link_url' => 'unduhan',
            'header' => 'Data|Keterangan', 'rows' => "NPSN|[00000000]\nNSM|[000000000000]\nAkreditasi|[A] · BAN-S/M [tahun]\nSK pendirian|[Nomor SK]\nPenyelenggara|[Nama Yayasan]\nKurikulum|Kurikulum Merdeka · Kemenag", 'note' => ''],
        ['type' => 'text_image', '_anchor' => 'komite', '_bg' => 'soft', 'eyebrow' => 'KOMITE MADRASAH', 'title' => 'Mitra orang tua dan madrasah',
            'body' => '<p>Komite menampung aspirasi wali murid, mendukung program madrasah, dan ikut mengawasi mutu layanan pendidikan.</p>',
            'image' => $img('bersama'), 'image_side' => 'right', 'sign_name' => '[Nama Ketua Komite]', 'sign_role' => 'Ketua Komite periode [tahun]'],
    ]];

    $pages['akademik'] = ['Akademik', [
        $P('Akademik', 'Kurikulum, program unggulan, kalender, jadwal, dan layanan belajar digital MTs Al-Hidayah.', $img('kelas')),
        ['type' => 'subnav', 'items' => array_map(fn($x) => ['label' => $x[0], 'url' => '#' . $x[1]], [['Kurikulum', 'kurikulum'], ['Program unggulan', 'program'],
            ['Kalender akademik', 'kalender'], ['Jadwal pelajaran', 'jadwal'], ['E-Learning & CBT', 'digital'], ['Unduhan', 'unduhan']])],
        ['type' => 'text_image', '_anchor' => 'kurikulum', 'eyebrow' => 'KURIKULUM', 'title' => 'Kurikulum Merdeka, berciri khas madrasah', 'image' => $img('tutor'), 'image_side' => 'right',
            'body' => '<p>Pembelajaran mengikuti Kurikulum Merdeka dari Kementerian Agama, diperkaya pendidikan agama Islam, bahasa Arab, dan program pembiasaan harian.</p>'
                . '<h3>Kelompok umum</h3><p>Pendidikan Pancasila, Bahasa Indonesia, Matematika, IPA, IPS, Bahasa Inggris, PJOK, Seni Budaya, Informatika.</p>'
                . '<h3>Pendidikan Agama Islam</h3><p>Al-Qur\'an Hadis, Akidah Akhlak, Fikih, Sejarah Kebudayaan Islam, Bahasa Arab.</p>'
                . '<h3>Muatan lokal & pembiasaan</h3><p>Tahfidz Al-Qur\'an, Bahasa Sunda, Baca Tulis Al-Qur\'an, Projek P5 & P2RA, [Mapel lain].</p>'],
        ['type' => 'stats', 'items' => [['value' => '[00]', 'label' => 'Jam pelajaran per pekan'], ['value' => '[00]', 'label' => 'Rombongan belajar'], ['value' => '[00]', 'label' => 'Siswa per kelas']]],
        ['type' => 'cards', '_anchor' => 'program', 'eyebrow' => 'PROGRAM UNGGULAN', 'title' => 'Empat program andalan madrasah', 'style' => 'feature', 'columns' => '4', 'items' => [
            ['icon' => 'book', 'title' => 'Tahfidz Al-Qur\'an', 'text' => 'Setoran hafalan harian bersama musyrif, target [0] juz selama tiga tahun, dan wisuda tahfidz setiap akhir tahun.'],
            ['icon' => 'lang', 'title' => 'Bahasa Arab & Inggris', 'text' => 'Hari berbahasa, klub debat, lomba pidato tiga bahasa, dan kosakata harian di lingkungan madrasah.'],
            ['icon' => 'flask', 'title' => 'Sains & Teknologi', 'text' => 'Praktikum IPA rutin, kelas coding dasar, dan pembinaan olimpiade KSM serta OSN.'],
            ['icon' => 'heart', 'title' => 'Pembinaan Karakter', 'text' => 'Shalat dhuha dan dzuhur berjamaah, kajian pekanan, mabit, dan bakti sosial.']]],
        ['type' => 'table', '_anchor' => 'kalender', '_bg' => 'soft', 'eyebrow' => 'KALENDER AKADEMIK', 'title' => 'Tahun ajaran [2026/2027]', 'link_label' => 'Unduh kalender (PDF)', 'link_url' => 'unduhan',
            'header' => 'Bulan|Tanggal|Agenda|Jenis', 'rows' => "Juli|[tgl] – [tgl]|Masa Pengenalan Lingkungan Madrasah (MPLS)|Kegiatan\nAgustus|[tgl] – [tgl]|Peringatan HUT RI dan lomba antarkelas|Kegiatan\nSeptember|[tgl] – [tgl]|Asesmen Tengah Semester ganjil|Asesmen\nDesember|[tgl] – [tgl]|Asesmen Akhir Semester dan pembagian rapor|Asesmen\nJanuari|[tgl] – [tgl]|Awal semester genap|Akademik\nMaret|[tgl] – [tgl]|Pesantren kilat Ramadhan|Kegiatan\nMei|[tgl] – [tgl]|Asesmen Akhir Jenjang kelas IX|Asesmen\nJuni|[tgl] – [tgl]|Wisuda dan kenaikan kelas|Kegiatan", 'note' => ''],
        ['type' => 'table', '_anchor' => 'jadwal', 'eyebrow' => 'JADWAL PELAJARAN', 'title' => 'Jadwal mingguan kelas VII-A',
            'header' => 'Jam|Senin|Selasa|Rabu|Kamis|Jumat', 'rows' => "07.00|Upacara|Tahfidz|Tahfidz|Tahfidz|Senam & Tahfidz\n07.40|Matematika|Bahasa Arab|IPA|Al-Qur'an Hadis|Fikih\n08.20|Matematika|Bahasa Arab|IPA|Bahasa Indonesia|Fikih\n09.15|Bahasa Indonesia|IPS|Bahasa Inggris|Akidah Akhlak|PJOK\n11.45|Shalat dzuhur berjamaah|Shalat dzuhur berjamaah|Shalat dzuhur berjamaah|Shalat dzuhur berjamaah|Shalat Jumat\n12.30|Pendidikan Pancasila|SKI|Informatika|Seni Budaya|Pulang\n13.10|Bahasa Inggris|Matematika|Informatika|IPS|",
            'note' => 'Contoh susunan jadwal. Tambahkan tabel lain untuk kelas VIII dan IX dari panel admin.'],
        ['type' => 'cards', '_anchor' => 'digital', '_bg' => 'soft', 'eyebrow' => 'LAYANAN DIGITAL', 'title' => 'Belajar dan ujian secara online', 'style' => 'icon', 'columns' => '2', 'items' => [
            ['icon' => 'laptop', 'title' => 'E-Learning', 'text' => 'Materi, tugas, dan nilai harian dalam satu tempat. Siswa masuk dengan NISN, orang tua bisa memantau.', 'meta' => 'Masuk E-Learning →', 'url' => '#'],
            ['icon' => 'test', 'title' => 'Ujian CBT', 'text' => 'Asesmen berbasis komputer untuk ulangan harian, ATS, dan AAS dengan hasil yang langsung terekap.', 'meta' => 'Masuk Ujian CBT →', 'url' => '#']]],
        ['type' => 'downloads', '_anchor' => 'unduhan', 'eyebrow' => 'UNDUHAN', 'title' => 'Dokumen akademik', 'category' => 'Akademik'],
    ]];

    $pages['kesiswaan'] = ['Kesiswaan', [
        $P('Kesiswaan', 'Organisasi siswa, ekstrakurikuler, prestasi, tata tertib, kelulusan, dan alumni.', $img('upacara')),
        ['type' => 'subnav', 'items' => array_map(fn($x) => ['label' => $x[0], 'url' => '#' . $x[1]], [['OSIM', 'osim'], ['Ekstrakurikuler', 'ekstrakurikuler'],
            ['Prestasi', 'prestasi'], ['Tata tertib', 'tata-tertib'], ['Cek kelulusan', 'kelulusan'], ['Alumni', 'alumni']])],
        ['type' => 'text_image', '_anchor' => 'osim', 'eyebrow' => 'OSIM', 'title' => 'Organisasi Siswa Intra Madrasah', 'image' => $img('bersama'), 'image_side' => 'right',
            'body' => '<p>Wadah siswa belajar memimpin, berorganisasi, dan menyelenggarakan kegiatan madrasah.</p><ul><li>Peringatan hari besar Islam dan nasional</li><li>Class meeting dan pentas seni</li><li>Bakti sosial dan Jumat berbagi</li><li>Majalah dinding dan media sosial OSIM</li></ul>'],
        ['type' => 'org_chart', 'title' => 'Pengurus OSIM', 'items' => [['level' => '1', 'role' => 'Ketua OSIM', 'name' => '[Nama siswa]', 'highlight' => true],
            ['level' => '2', 'role' => 'Wakil ketua', 'name' => '[Nama siswa]', 'highlight' => false], ['level' => '2', 'role' => 'Sekretaris', 'name' => '[Nama siswa]', 'highlight' => false],
            ['level' => '2', 'role' => 'Bendahara', 'name' => '[Nama siswa]', 'highlight' => false]]],
        ['type' => 'cards', '_anchor' => 'ekstrakurikuler', '_bg' => 'soft', 'eyebrow' => 'EKSTRAKURIKULER', 'title' => 'Temukan minat dan bakat',
            'intro' => 'Setiap siswa memilih minimal satu ekstrakurikuler selain Pramuka.', 'style' => 'image', 'columns' => '4', 'items' => [
                ['image' => $img('pramuka2'), 'title' => 'Pramuka', 'meta' => 'Wajib kelas VII · [Hari], [jam]'],
                ['image' => $img('paskibra'), 'title' => 'Paskibra', 'meta' => '[Hari], [jam] · Pembina [Nama]'],
                ['image' => $img('gal4'), 'title' => 'Hadroh & Marawis', 'meta' => '[Hari], [jam] · Pembina [Nama]'],
                ['image' => $img('silat'), 'title' => 'Pencak Silat', 'meta' => '[Hari], [jam] · Pelatih [Nama]'],
                ['image' => $img('futsal'), 'title' => 'Futsal', 'meta' => '[Hari], [jam] · Pelatih [Nama]'],
                ['image' => $img('kaligrafi'), 'title' => 'Kaligrafi', 'meta' => '[Hari], [jam] · Pembina [Nama]'],
                ['image' => $img('tutor'), 'title' => 'Tilawah & Tahfidz', 'meta' => '[Hari], [jam] · Pembina [Nama]'],
                ['image' => $img('siswi'), 'title' => 'PMR', 'meta' => '[Hari], [jam] · Pembina [Nama]']]],
        ['type' => 'stats', '_anchor' => 'prestasi', 'items' => [['value' => '[00]', 'label' => 'Piala tahun ini'], ['value' => '[000]', 'label' => 'Total prestasi']]],
        ['type' => 'table', 'eyebrow' => 'PRESTASI SISWA', 'title' => 'Kebanggaan madrasah', 'header' => 'Tahun|Lomba|Tingkat|Hasil',
            'rows' => "[Tahun]|[KSM Matematika]|Kota Depok|Juara [1]\n[Tahun]|[MTQ cabang tahfidz 5 juz]|Provinsi Jawa Barat|Juara [2]\n[Tahun]|[Pencak silat O2SN]|Kota Depok|Juara [1]\n[Tahun]|[Lomba pidato bahasa Arab]|Se-Jabodetabek|Juara [3]\n[Tahun]|[Kaligrafi AKSIOMA]|Provinsi Jawa Barat|Harapan [1]", 'note' => ''],
        ['type' => 'faq', '_anchor' => 'tata-tertib', '_bg' => 'soft', 'eyebrow' => 'TATA TERTIB', 'title' => 'Aturan bersama di madrasah', 'link_label' => 'Unduh tata tertib lengkap', 'link_url' => 'unduhan', 'items' => [
            ['q' => 'Kehadiran', 'a' => 'Siswa hadir paling lambat pukul [06.45]. Izin tidak masuk disampaikan orang tua melalui wali kelas.'],
            ['q' => 'Seragam & penampilan', 'a' => 'Seragam sesuai jadwal harian, rapi, dan menutup aurat. [Rincian seragam per hari.]'],
            ['q' => 'Ibadah', 'a' => 'Mengikuti shalat dhuha, dzuhur berjamaah, dan kegiatan tahfidz sesuai jadwal.'],
            ['q' => 'Sikap & pergaulan', 'a' => 'Menghormati guru dan sesama, menjaga kebersihan, dan tidak membawa barang terlarang.'],
            ['q' => 'Penggunaan gawai', 'a' => '[Aturan membawa dan memakai HP di lingkungan madrasah.]'],
            ['q' => 'Poin pelanggaran & sanksi', 'a' => '[Tahapan pembinaan: teguran, poin, panggilan orang tua.]']]],
        ['type' => 'cta', '_anchor' => 'kelulusan', 'title' => 'Pengumuman kelulusan kelas IX', 'text' => 'Dibuka pada [tanggal, pukul]. Hasil kelulusan diumumkan melalui halaman ini dan grup wali kelas.',
            'image' => $img('wisuda'), 'btn1_label' => 'Lihat pengumuman', 'btn1_url' => 'berita', 'btn2_label' => '', 'btn2_url' => ''],
        ['type' => 'stats', '_anchor' => 'alumni', 'items' => [['value' => '[0.000]', 'label' => 'Alumni terdata'], ['value' => '[00]%', 'label' => 'Lanjut ke MA/SMA negeri'], ['value' => '[000]', 'label' => 'Alumni penghafal Al-Qur\'an']]],
        ['type' => 'testimonials', 'eyebrow' => 'ALUMNI', 'title' => 'Jejak lulusan kami', 'items' => [
            ['quote' => '[Kutipan singkat tentang pengalaman belajar di MTs Al-Hidayah.]', 'name' => '[Nama alumni]', 'role' => 'Angkatan [tahun] · kini di [MAN/SMA]'],
            ['quote' => '[Kutipan singkat tentang program tahfidz atau ekstrakurikuler.]', 'name' => '[Nama alumni]', 'role' => 'Angkatan [tahun] · kini di [pesantren]'],
            ['quote' => '[Kutipan singkat tentang guru dan pembinaan karakter.]', 'name' => '[Nama alumni]', 'role' => 'Angkatan [tahun] · kini di [universitas]']]],
    ]];

    $pages['berita'] = ['Berita', [$P('Berita & pengumuman', 'Kabar terbaru, prestasi, dan pengumuman resmi MTs Al-Hidayah Depok.'), ['type' => 'posts', 'mode' => 'full', 'limit' => '9', 'category' => '']]];

    $pages['galeri'] = ['Galeri', [
        $P('Galeri', 'Dokumentasi kegiatan, ekstrakurikuler, dan fasilitas madrasah.'),
        ['type' => 'albums', 'eyebrow' => 'ALBUM FOTO', 'title' => 'Album kegiatan', 'limit' => '0'],
        ['type' => 'videos', '_bg' => 'soft', 'eyebrow' => 'VIDEO', 'title' => 'Video kegiatan', 'intro' => 'Tambahkan tautan YouTube dari panel admin, menu Video.', 'limit' => '0'],
    ]];

    $pages['ppdb'] = ['PPDB Online', [
        ['type' => 'countdown', 'eyebrow' => 'PPDB ONLINE', 'title' => 'Penerimaan Peserta Didik Baru [2027/2028]',
            'intro' => 'Daftar dari rumah dalam empat langkah. Status pendaftaran bisa dipantau kapan saja dengan nomor pendaftaran.',
            'label' => 'KUOTA GELOMBANG 1', 'quota' => '120', 'deadline' => date('Y', strtotime('+1 year')) . '-03-31T23:59',
            'btn1_label' => 'Mulai Pendaftaran', 'btn1_url' => '#form', 'btn2_label' => 'Unduh Brosur', 'btn2_url' => 'unduhan'],
        ['type' => 'table', '_anchor' => 'jadwal', 'eyebrow' => 'JADWAL', 'title' => 'Gelombang pendaftaran', 'header' => 'Tahap|Waktu pendaftaran|Tes seleksi|Status',
            'rows' => "Gelombang 1|[Tanggal mulai] – [Tanggal selesai]|[tanggal]|Sedang berjalan\nGelombang 2|[Tanggal mulai] – [Tanggal selesai]|[tanggal]|Akan datang\nPengumuman & daftar ulang|[Tanggal pengumuman]|Daftar ulang: [tanggal]|Akan datang", 'note' => ''],
        ['type' => 'steps', '_anchor' => 'alur', '_bg' => 'soft', 'eyebrow' => 'ALUR', 'title' => 'Empat langkah mendaftar', 'items' => [
            ['title' => 'Isi formulir', 'text' => 'Isi data calon siswa, orang tua, dan sekolah asal. Nomor pendaftaran langsung muncul setelah dikirim.'],
            ['title' => 'Lengkapi berkas', 'text' => 'Serahkan berkas persyaratan ke panitia atau kirim lewat WhatsApp panitia.'],
            ['title' => 'Bayar & verifikasi', 'text' => 'Transfer biaya formulir, kirim bukti, lalu panitia memverifikasi berkas.'],
            ['title' => 'Tes & pengumuman', 'text' => 'Ikuti tes baca Al-Qur\'an dan wawancara, lalu cek hasil di halaman status.']]],
        ['type' => 'text_image', '_anchor' => 'persyaratan', 'eyebrow' => 'PERSYARATAN', 'title' => 'Persyaratan berkas', 'image' => $img('siswi'), 'image_side' => 'right',
            'body' => '<ul><li>Fotokopi akta kelahiran</li><li>Fotokopi Kartu Keluarga</li><li>Rapor SD/MI kelas 4–6 atau surat keterangan lulus</li><li>Pas foto 3×4 latar [warna]</li><li>NISN calon siswa</li><li>[Persyaratan tambahan]</li></ul>'],
        ['type' => 'table', '_anchor' => 'biaya', '_bg' => 'soft', 'eyebrow' => 'BIAYA', 'title' => 'Rincian biaya', 'header' => 'Komponen|Biaya',
            'rows' => "Formulir pendaftaran|Rp [000.000]\nUang pangkal|Rp [0.000.000]\nSeragam & perlengkapan|Rp [000.000]\nSPP per bulan|Rp [000.000]",
            'note' => 'Tersedia program beasiswa untuk siswa berprestasi dan yatim. [Sesuaikan kebijakan madrasah.]'],
        ['type' => 'ppdb_form', '_anchor' => 'form', 'title' => 'Formulir pendaftaran', 'intro' => 'Isi data dengan benar. Simpan nomor pendaftaran yang muncul setelah formulir terkirim.', 'closed' => false, 'closed_text' => 'Pendaftaran sedang ditutup.'],
        ['type' => 'ppdb_status', '_anchor' => 'status', '_bg' => 'soft', 'title' => 'Cek status pendaftaran', 'intro' => 'Masukkan nomor pendaftaran dan tanggal lahir calon siswa.', 'image' => ''],
        ['type' => 'faq', '_anchor' => 'faq', 'eyebrow' => 'FAQ', 'title' => 'Pertanyaan yang sering diajukan', 'intro' => 'Belum terjawab? Hubungi panitia PPDB di WhatsApp [0812-0000-0000].', 'items' => [
            ['q' => 'Apakah pendaftaran bisa dilakukan langsung di madrasah?', 'a' => '[Bisa, panitia membuka meja layanan pada jam kerja untuk membantu orang tua mengisi formulir online.]'],
            ['q' => 'Materi apa saja yang diujikan saat tes seleksi?', 'a' => '[Jawaban]'],
            ['q' => 'Bagaimana cara mengecek hasil seleksi?', 'a' => 'Buka bagian Cek status pendaftaran di halaman ini, lalu masukkan nomor pendaftaran dan tanggal lahir calon siswa.'],
            ['q' => 'Apakah tersedia keringanan biaya?', 'a' => '[Jawaban]']]],
    ]];

    $pages['kontak'] = ['Kontak', [
        $P('Hubungi kami', 'Kami siap membantu informasi PPDB, administrasi, dan kerja sama.'),
        ['type' => 'contact_info'],
        ['type' => 'contact_form', '_bg' => 'soft', 'title' => 'Kirim pesan', 'subjects' => 'Informasi PPDB|Administrasi & keuangan|Kerja sama|Kritik & saran', 'show_map' => true],
    ]];

    $pages['unduhan'] = ['Unduhan', [$P('Unduhan', 'Dokumen resmi madrasah yang bisa diunduh.'), ['type' => 'downloads', '_bg' => 'white', 'eyebrow' => 'DOKUMEN', 'title' => 'Semua dokumen', 'category' => '']]];

    $ids = [];
    foreach ($pages as $slug => [$title, $blocks]) {
        $ids[$slug] = insert('pages', ['title' => $title, 'slug' => $slug, 'blocks' => json_encode(sanitize_blocks(json_encode($blocks)), JSON_UNESCAPED_UNICODE), 'status' => 'published',
            'is_home' => $slug === 'beranda' ? 1 : 0, 'meta_description' => '', 'created_at' => $now, 'updated_at' => $now]);
    }

    // Menus
    $sort = 0;
    $menu = function (string $label, string $type, $target, string $loc = 'main', int $parent = 0) use (&$sort): int {
        return insert('menus', ['parent_id' => $parent ?: null, 'label' => $label, 'type' => $type, 'page_id' => $type === 'page' ? $target : null,
            'url' => $type === 'url' ? $target : '', 'sort' => ++$sort, 'visible' => 1, 'new_tab' => 0, 'location' => $loc]);
    };
    $menu('Beranda', 'page', $ids['beranda']);
    foreach ([
        'profil' => [['Sejarah', 'sejarah'], ['Visi & misi', 'visi-misi'], ['Sambutan kepala', 'sambutan'], ['Struktur organisasi', 'struktur'], ['Guru & staf', 'guru'], ['Sarana prasarana', 'sarana'], ['Akreditasi', 'akreditasi'], ['Komite', 'komite']],
        'akademik' => [['Kurikulum', 'kurikulum'], ['Program unggulan', 'program'], ['Kalender akademik', 'kalender'], ['Jadwal pelajaran', 'jadwal'], ['E-Learning & CBT', 'digital'], ['Unduhan', 'unduhan']],
        'kesiswaan' => [['OSIM', 'osim'], ['Ekstrakurikuler', 'ekstrakurikuler'], ['Prestasi', 'prestasi'], ['Tata tertib', 'tata-tertib'], ['Cek kelulusan', 'kelulusan'], ['Alumni', 'alumni']],
    ] as $slug => $subs) {
        $pid = $menu($pages[$slug][0], 'page', $ids[$slug]);
        foreach ($subs as [$l, $a]) $menu($l, 'url', $slug . '#' . $a, 'main', $pid);
    }
    $menu('Berita', 'page', $ids['berita']);
    $menu('Galeri', 'page', $ids['galeri']);
    $menu('Kontak', 'page', $ids['kontak']);
    $menu('E-Learning', 'url', 'akademik#digital', 'top');
    $menu('Ujian CBT', 'url', 'akademik#digital', 'top');
    $menu('Profil & sejarah', 'url', 'profil#sejarah', 'footer');
    $menu('Guru & staf', 'url', 'profil#guru', 'footer');
    $menu('PPDB Online', 'page', $ids['ppdb'], 'footer');
    $menu('Berita', 'page', $ids['berita'], 'footer');
    $menu('Unduhan', 'page', $ids['unduhan'], 'footer');
    $menu('Kontak', 'page', $ids['kontak'], 'footer');
}

/** Write a one-page PDF with a title line; returns its size in bytes. */
function seed_pdf(string $path, string $title): int
{
    if (!is_dir(dirname($path))) mkdir(dirname($path), 0775, true);
    $t = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], iconv('UTF-8', 'Windows-1252//TRANSLIT', $title) ?: $title);
    $stream = "BT /F1 20 Tf 60 760 Td ($t) Tj /F1 11 Tf 0 -28 Td (MTs Al-Hidayah Depok - contoh dokumen, ganti dari panel admin.) Tj ET";
    $objs = ['<< /Type /Catalog /Pages 2 0 R >>', '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
        '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>',
        '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>',
        '<< /Length ' . strlen($stream) . " >>\nstream\n$stream\nendstream"];
    $pdf = "%PDF-1.4\n";
    $off = [];
    foreach ($objs as $i => $o) {
        $off[] = strlen($pdf);
        $pdf .= ($i + 1) . " 0 obj\n$o\nendobj\n";
    }
    $xref = strlen($pdf);
    $pdf .= "xref\n0 " . (count($objs) + 1) . "\n0000000000 65535 f \n";
    foreach ($off as $o) $pdf .= sprintf("%010d 00000 n \n", $o);
    $pdf .= 'trailer << /Size ' . (count($objs) + 1) . " /Root 1 0 R >>\nstartxref\n$xref\n%%EOF\n";
    file_put_contents($path, $pdf);
    return strlen($pdf);
}
