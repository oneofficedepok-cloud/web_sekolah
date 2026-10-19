<?php
declare(strict_types=1);

function current_user(): ?array
{
    static $u = false;
    if ($u === false) {
        $u = !empty($_SESSION['uid']) ? row('SELECT id, name, username, role FROM users WHERE id = ?', [$_SESSION['uid']]) : null;
    }
    return $u;
}

function is_admin(): bool
{
    return (current_user()['role'] ?? '') === 'admin';
}

function require_login(): void
{
    if (!current_user()) redirect('admin/login');
}

function require_admin(): void
{
    require_login();
    if (!is_admin()) {
        flash('Hanya admin yang boleh membuka bagian itu.', 'error');
        redirect('admin');
    }
}

function too_many_attempts(): bool
{
    $since = date('Y-m-d H:i:s', time() - 15 * 60);
    return (int)val('SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND attempted_at > ?', [client_ip(), $since]) >= 8;
}

function attempt_login(string $username, string $password): bool
{
    $u = row('SELECT * FROM users WHERE username = ?', [$username]);
    if ($u && password_verify($password, $u['password'])) {
        session_regenerate_id(true);
        $_SESSION['uid'] = (int)$u['id'];
        q('DELETE FROM login_attempts WHERE ip = ?', [client_ip()]);
        q('UPDATE users SET last_login = ? WHERE id = ?', [now(), $u['id']]);
        if (password_needs_rehash($u['password'], PASSWORD_DEFAULT)) {
            q('UPDATE users SET password = ? WHERE id = ?', [password_hash($password, PASSWORD_DEFAULT), $u['id']]);
        }
        return true;
    }
    insert('login_attempts', ['ip' => client_ip(), 'username' => mb_substr($username, 0, 100), 'attempted_at' => now()]);
    return false;
}

function logout(): void
{
    $_SESSION = [];
    session_regenerate_id(true);
}
