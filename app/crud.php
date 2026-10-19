<?php
declare(strict_types=1);

/** Simple list + form screens for small tables. */
function crud_defs(): array
{
    return [
        'agenda' => ['title' => 'Agenda', 'one' => 'agenda', 'table' => 'agenda', 'order' => 'event_date DESC', 'fields' => [
            ['title', 'Nama kegiatan', 'text', true], ['event_date', 'Tanggal', 'date', true], ['time_text', 'Waktu (contoh: 07.30 – 10.00)', 'text'],
            ['place', 'Tempat', 'text'], ['description', 'Keterangan', 'textarea']],
            'cols' => ['title' => 'Kegiatan', 'event_date' => 'Tanggal', 'place' => 'Tempat']],
        'videos' => ['title' => 'Video', 'one' => 'video', 'table' => 'videos', 'order' => 'sort, id DESC', 'fields' => [
            ['title', 'Judul video', 'text', true], ['youtube', 'Tautan YouTube', 'text', true], ['sort', 'Urutan', 'number']],
            'cols' => ['title' => 'Judul', 'youtube' => 'YouTube']],
        'downloads' => ['title' => 'Unduhan', 'one' => 'dokumen', 'table' => 'downloads', 'order' => 'created_at DESC', 'fields' => [
            ['title', 'Nama dokumen', 'text', true], ['category', 'Kategori (contoh: Akademik, PPDB)', 'text'], ['file', 'File', 'file', true]],
            'cols' => ['title' => 'Dokumen', 'category' => 'Kategori', 'file' => 'File']],
        'staff' => ['title' => 'Guru & staf', 'one' => 'guru/staf', 'table' => 'staff', 'order' => 'sort, name', 'fields' => [
            ['name', 'Nama lengkap', 'text', true], ['position', 'Jabatan (contoh: Guru, Wali kelas VII-A)', 'text'], ['subject', 'Mata pelajaran', 'text'],
            ['group_name', 'Kelompok (contoh: Guru PAI, Guru umum, Tenaga kependidikan)', 'text'], ['photo', 'Foto', 'image'], ['sort', 'Urutan', 'number']],
            'cols' => ['photo' => 'Foto', 'name' => 'Nama', 'position' => 'Jabatan', 'subject' => 'Mapel', 'group_name' => 'Kelompok']],
        'categories' => ['title' => 'Kategori berita', 'one' => 'kategori', 'table' => 'categories', 'order' => 'name', 'fields' => [
            ['name', 'Nama kategori', 'text', true]],
            'cols' => ['name' => 'Nama', 'slug' => 'Alamat']],
    ];
}

function admin_crud(string $key, string $action, int $id, string $method): void
{
    $d = crud_defs()[$key];
    $t = $d['table'];
    if ($method === 'POST' && $action === 'delete') {
        $r = row("SELECT * FROM $t WHERE id = ?", [$id]);
        if ($r && $key === 'downloads') delete_upload($r['file']);
        if ($key === 'categories') q('UPDATE posts SET category_id = NULL WHERE category_id = ?', [$id]);
        q("DELETE FROM $t WHERE id = ?", [$id]);
        flash(ucfirst($d['one']) . ' dihapus.');
        redirect('admin/' . $key);
    }
    $rec = $id ? row("SELECT * FROM $t WHERE id = ?", [$id]) : null;
    if ($method === 'POST' && $action === 'edit') {
        $data = [];
        foreach ($d['fields'] as $f) {
            [$name, $label, $type] = $f;
            if ($type === 'file') {
                $up = $_FILES[$name] ?? null;
                if ($up && $up['error'] !== UPLOAD_ERR_NO_FILE) {
                    try {
                        $r = store_upload($up);
                        if ($rec) delete_upload($rec[$name]);
                        $data[$name] = $r['path'];
                        $data['size'] = $r['size'];
                    } catch (RuntimeException $ex) {
                        flash($ex->getMessage(), 'error');
                        redirect('admin/' . $key . '/edit/' . $id);
                    }
                } elseif (!$rec) {
                    flash('Pilih file untuk diunggah.', 'error');
                    redirect('admin/' . $key . '/edit/0');
                }
                continue;
            }
            $v = post($name);
            if (!empty($f[3]) && $v === '') {
                flash($label . ' wajib diisi.', 'error');
                redirect('admin/' . $key . '/edit/' . $id);
            }
            $data[$name] = $type === 'number' ? (int)$v : mb_substr((string)$v, 0, 2000);
        }
        if ($key === 'categories') $data['slug'] = unique_slug('categories', slugify($data['name']), $id);
        if ($key === 'downloads' && !$rec) $data['created_at'] = now();
        if ($key === 'videos' && !$rec) $data['created_at'] = now();
        if ($key === 'videos' && !youtube_id($data['youtube'])) {
            flash('Tautan YouTube tidak dikenali.', 'error');
            redirect('admin/videos/edit/' . $id);
        }
        if ($rec) update($t, $id, $data);
        else insert($t, $data);
        flash(ucfirst($d['one']) . ' disimpan.');
        redirect('admin/' . $key);
    }
    if ($action === 'edit') {
        admin_layout(($rec ? 'Ubah ' : 'Tambah ') . $d['one'], render_to_string('admin/crud_form', ['d' => $d, 'rec' => $rec, 'key' => $key]), $key,
            ['actions' => '<a class="btn ghost" href="' . e(url('admin/' . $key)) . '">← Kembali</a>']);
        return;
    }
    $list = rows("SELECT * FROM $t ORDER BY {$d['order']}");
    admin_layout($d['title'], render_to_string('admin/crud_list', ['d' => $d, 'list' => $list, 'key' => $key]), $key,
        ['actions' => '<a class="btn" href="' . e(url('admin/' . $key . '/edit/0')) . '">+ Tambah ' . e($d['one']) . '</a>']);
}
