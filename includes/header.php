<?php require_once __DIR__ . '/../config/app.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?></title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<div class="navbar">
    <div class="container">
        <a href="/index.php" style="text-decoration:none;color:white;">
            <h1><span>Sécurité Web</span> — TP</h1>
        </a>
        <nav>
            <a href="/index.php">Accueil</a>
            <a href="/tp/ch01-bases-securite/index.php">Ch1</a>
            <a href="/tp/ch02-securite-client/index.php">Ch2</a>
            <a href="/tp/ch03-securite-reseau/index.php">Ch3</a>
            <a href="/tp/ch04-architecture-serveurs/index.php">Ch4</a>
            <a href="/tp/ch05-controle-acces/index.php">Ch5</a>
            <?php if (isLoggedIn()): ?>
                <?php $user = getCurrentUser(); ?>
                <span class="user-info">
                    <?= e($user['username']) ?> (<?= e($user['role']) ?>)
                </span>
                <a href="/logout.php">Déconnexion</a>
            <?php else: ?>
                <a href="/login.php">Connexion</a>
            <?php endif; ?>
        </nav>
    </div>
</div>
<div class="container">
