<?php
session_start();

define('USERS_FILE', __DIR__ . '/data/users.json');

/** Ambil semua user dari JSON */
function loadUsers(): array
{
    if (!file_exists(USERS_FILE)) {
        return [];
    }
    $data = json_decode(file_get_contents(USERS_FILE), true);
    return is_array($data) ? $data : [];
}

/** Simpan array user ke JSON (dengan file lock) */
function saveUsers(array $users): bool
{
    $json = json_encode(array_values($users), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents(USERS_FILE, $json, LOCK_EX) !== false;
}

/** Cari user berdasarkan email (case-insensitive) */
function findUserByEmail(string $email): ?array
{
    foreach (loadUsers() as $user) {
        if (strtolower($user['email']) === strtolower($email)) {
            return $user;
        }
    }
    return null;
}

/** Escape output untuk mencegah XSS */
function e(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function isLoggedIn(): bool
{
    return isset($_SESSION['user']);
}

/** Proteksi halaman: wajib login */
function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/** Jika sudah login, jangan tampilkan login/register lagi */
function redirectIfLoggedIn(): void
{
    if (isLoggedIn()) {
        header('Location: dashboard.php');
        exit;
    }
}

/** CSRF token */
function csrfToken(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function verifyCsrf(?string $token): bool
{
    return isset($_SESSION['csrf']) && is_string($token)
        && hash_equals($_SESSION['csrf'], $token);
}

/** Flash message (pesan sekali tampil) */
function setFlash(string $type, string $msg): void
{
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function getFlash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}