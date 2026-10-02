<?php
require 'functions.php';
redirectIfLoggedIn();

$errors = [];
$old = ['name' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf'] ?? null)) {
        die('Token tidak valid.');
    }

    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    $old = ['name' => $name, 'email' => $email];

    // Validasi nama
    if ($name === '') {
        $errors[] = 'Nama wajib diisi.';
    } elseif (mb_strlen($name) < 3) {
        $errors[] = 'Nama minimal 3 karakter.';
    }

    // Validasi email
    if ($email === '') {
        $errors[] = 'Email wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    } elseif (findUserByEmail($email) !== null) {
        $errors[] = 'Email sudah terdaftar.'; // cek duplikat
    }

    // Validasi password
    if ($password === '') {
        $errors[] = 'Password wajib diisi.';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password minimal 8 karakter.';
    } elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password harus mengandung huruf dan angka.';
    }

    if ($password !== $confirm) {
        $errors[] = 'Konfirmasi password tidak cocok.';
    }

    // Simpan jika lolos semua validasi
    if (empty($errors)) {
        $users = loadUsers();
        $users[] = [
            'id'         => uniqid('usr_'),
            'name'       => $name,
            'email'      => strtolower($email),
            'password'   => password_hash($password, PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        if (saveUsers($users)) {
            setFlash('success', 'Registrasi berhasil! Silakan login.');
            header('Location: login.php');
            exit;
        }
        $errors[] = 'Gagal menyimpan data. Coba lagi.';
    }
}

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="card">
    <h1>Buat Akun</h1>
    <p class="sub">Daftar untuk mulai menggunakan aplikasi</p>

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

        <label for="name">Nama Lengkap</label>
        <input type="text" id="name" name="name" value="<?= e($old['name']) ?>" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= e($old['email']) ?>" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <label for="confirm">Konfirmasi Password</label>
        <input type="password" id="confirm" name="confirm" required>

        <button type="submit">Daftar</button>
    </form>

    <p class="link">Sudah punya akun? <a href="login.php">Login</a></p>
</div>
</body>
</html>