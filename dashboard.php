<?php
require 'functions.php';
requireLogin();
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="card">
    <h1>Halo, <?= e($user['name']) ?> 👋</h1>
    <p class="sub">Kamu berhasil login.</p>

    <div class="info">
        <p><strong>Email:</strong> <?= e($user['email']) ?></p>
        <p><strong>ID:</strong> <?= e($user['id']) ?></p>
    </div>

    <a class="btn" href="logout.php">Logout</a>
</div>
</body>
</html>