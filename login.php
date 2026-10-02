<?php
require 'functions.php';
redirectIfLoggedIn();

$errors = [];
// Bonus cookie: ingat email terakhir
$email = $_COOKIE['remember_email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf'] ?? null)) {
        die('Token tidak valid.');
    }

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = 'Email dan password wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    } else {
        $user = findUserByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true); // cegah session fixation

            $_SESSION['user'] = [
                'id'    => $user['id'],
                'name'  => $user['name'],
                'email' => $user['email'],
            ];

            // Bonus cookie: simpan email 7 hari jika dicentang
            if (!empty($_POST['remember'])) {
                setcookie('remember_email', $user['email'], [
                    'expires'  => time() + 7 * 24 * 3600,
                    'path'     => '/',
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]);
            } else {
                setcookie('remember_email', '', time() - 3600, '/');
            }

            header('Location: dashboard.php');
            exit;
        }
        // Pesan sengaja dibuat umum agar tidak membocorkan email mana yang terdaftar
        $errors[] = 'Email atau password salah.';
    }
}

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="card">
    <h1>Selamat Datang</h1>
    <p class="sub">Login ke akun kamu</p>

    <?php if ($flash): ?>
        <div class="alert <?= e($flash['type']) ?>"><?= e($flash['msg']) ?></div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="alert error">
            <ul>
                <?php foreach ($errors as $err): ?>
                    <li><?= e($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" novalidate>
        <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= e($email) ?>" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <label class="check">
            <input type="checkbox" name="remember" <?= $email ? 'checked' : '' ?>>
            Ingat email saya
        </label>

        <button type="submit">Login</button>
    </form>

    <p class="link">Belum punya akun? <a href="register.php">Daftar</a></p>
</div>
</body>
</html>