<?php
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;
    if ($pdo) return $pdo;
    global $CONFIG;
    if (($CONFIG['db_driver'] ?? 'sqlite') === 'mysql') {
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $CONFIG['mysql_host'], (int)($CONFIG['mysql_port'] ?? 3306), $CONFIG['mysql_database']);
        $pdo = new PDO($dsn, $CONFIG['mysql_user'], $CONFIG['mysql_password']);
    } else {
        $path = $CONFIG['sqlite_path'] ?? ROOT . '/storage/database.sqlite';
        if (!is_dir(dirname($path))) mkdir(dirname($path), 0775, true);
        $pdo = new PDO('sqlite:' . $path);
        $pdo->exec('PRAGMA foreign_keys = ON; PRAGMA journal_mode = WAL;');
    }
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
}

function is_mysql(): bool
{
    return db()->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql';
}

function q(string $sql, array $params = []): PDOStatement
{
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st;
}

function row(string $sql, array $params = []): ?array
{
    $r = q($sql, $params)->fetch();
    return $r ?: null;
}

function rows(string $sql, array $params = []): array
{
    return q($sql, $params)->fetchAll();
}

function val(string $sql, array $params = [])
{
    $v = q($sql, $params)->fetchColumn();
    return $v === false ? null : $v;
}

function now(): string
{
    return date('Y-m-d H:i:s');
}

/** Insert an associative array, return new id. */
function insert(string $table, array $data): int
{
    $cols = array_keys($data);
    $sql = 'INSERT INTO ' . $table . ' (' . implode(',', $cols) . ') VALUES (' . implode(',', array_fill(0, count($cols), '?')) . ')';
    q($sql, array_values($data));
    return (int)db()->lastInsertId();
}

function update(string $table, int $id, array $data): void
{
    $set = implode(',', array_map(fn($c) => "$c = ?", array_keys($data)));
    q("UPDATE $table SET $set WHERE id = ?", [...array_values($data), $id]);
}

function set_setting(string $k, string $v): void
{
    if (is_mysql()) {
        q('INSERT INTO settings (k, v) VALUES (?, ?) ON DUPLICATE KEY UPDATE v = VALUES(v)', [$k, $v]);
    } else {
        q('INSERT INTO settings (k, v) VALUES (?, ?) ON CONFLICT(k) DO UPDATE SET v = excluded.v', [$k, $v]);
    }
}

/** Make a slug unique within a table. */
function unique_slug(string $table, string $slug, int $exceptId = 0): string
{
    $base = $slug;
    $i = 2;
    while (val("SELECT COUNT(*) FROM $table WHERE slug = ? AND id <> ?", [$slug, $exceptId])) {
        $slug = $base . '-' . $i++;
    }
    return $slug;
}

function db_migrate(): void
{
    $pdo = db();
    $my = is_mysql();
    $pk = $my ? 'INT AUTO_INCREMENT PRIMARY KEY' : 'INTEGER PRIMARY KEY AUTOINCREMENT';
    $long = $my ? 'LONGTEXT' : 'TEXT';
    $str = 'VARCHAR(255)';
    $eng = $my ? ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4' : '';

    $tables = [
        "settings (k VARCHAR(100) PRIMARY KEY, v $long)",
        "users (id $pk, name $str NOT NULL, username VARCHAR(100) NOT NULL UNIQUE, password $str NOT NULL, role VARCHAR(20) NOT NULL DEFAULT 'editor', created_at VARCHAR(19), last_login VARCHAR(19))",
        "pages (id $pk, title $str NOT NULL, slug VARCHAR(191) NOT NULL UNIQUE, blocks $long, status VARCHAR(20) NOT NULL DEFAULT 'published', is_home INT NOT NULL DEFAULT 0, meta_description TEXT, created_at VARCHAR(19), updated_at VARCHAR(19))",
        "menus (id $pk, parent_id INT NULL, label $str NOT NULL, type VARCHAR(20) NOT NULL DEFAULT 'page', page_id INT NULL, url $str, sort INT NOT NULL DEFAULT 0, visible INT NOT NULL DEFAULT 1, new_tab INT NOT NULL DEFAULT 0, location VARCHAR(20) NOT NULL DEFAULT 'main')",
        "categories (id $pk, name $str NOT NULL, slug VARCHAR(191) NOT NULL UNIQUE)",
        "posts (id $pk, title $str NOT NULL, slug VARCHAR(191) NOT NULL UNIQUE, category_id INT NULL, cover $str, excerpt TEXT, body $long, status VARCHAR(20) NOT NULL DEFAULT 'published', featured INT NOT NULL DEFAULT 0, views INT NOT NULL DEFAULT 0, author_id INT NULL, published_at VARCHAR(19), created_at VARCHAR(19), updated_at VARCHAR(19))",
        "albums (id $pk, title $str NOT NULL, slug VARCHAR(191) NOT NULL UNIQUE, description TEXT, cover $str, event_date VARCHAR(10), created_at VARCHAR(19))",
        "photos (id $pk, album_id INT NOT NULL, file $str NOT NULL, caption $str, sort INT NOT NULL DEFAULT 0)",
        "videos (id $pk, title $str NOT NULL, youtube $str NOT NULL, sort INT NOT NULL DEFAULT 0, created_at VARCHAR(19))",
        "agenda (id $pk, title $str NOT NULL, event_date VARCHAR(10) NOT NULL, time_text $str, place $str, description TEXT)",
        "downloads (id $pk, title $str NOT NULL, file $str NOT NULL, category $str, size INT NOT NULL DEFAULT 0, created_at VARCHAR(19))",
        "staff (id $pk, name $str NOT NULL, position $str, subject $str, group_name $str, photo $str, sort INT NOT NULL DEFAULT 0)",
        "messages (id $pk, name $str NOT NULL, phone $str, email $str, subject $str, message TEXT, is_read INT NOT NULL DEFAULT 0, ip VARCHAR(45), created_at VARCHAR(19))",
        "ppdb (id $pk, reg_no VARCHAR(30) NOT NULL, name $str NOT NULL, nisn VARCHAR(20), birth_place $str, birth_date VARCHAR(10), gender VARCHAR(10), school_origin $str, parent_name $str, phone VARCHAR(30), email $str, address TEXT, status VARCHAR(20) NOT NULL DEFAULT 'baru', notes TEXT, created_at VARCHAR(19))",
        "media (id $pk, file $str NOT NULL, original $str, mime VARCHAR(100), size INT NOT NULL DEFAULT 0, width INT NULL, height INT NULL, created_at VARCHAR(19))",
        "login_attempts (id $pk, ip VARCHAR(45) NOT NULL, username $str, attempted_at VARCHAR(19) NOT NULL)",
    ];
    foreach ($tables as $t) {
        $pdo->exec('CREATE TABLE IF NOT EXISTS ' . $t . $eng);
    }
}
