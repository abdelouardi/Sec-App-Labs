<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="page-header">
    <h2>Panel Administration</h2>
    <p>TP4 - Version VULNÉRABLE : aucune vérification de rôle</p>
</div>

<div class="alert alert-danger">
    VULNÉRABLE : N'importe quel utilisateur (ou visiteur non connecté) peut accéder à cette page !
</div>

<div class="card">
    <h3>Liste de tous les utilisateurs</h3>
    <?php
    $db = getDB();
    $users = $db->query("SELECT id, username, email, role, password_plain, created_at FROM users")->fetchAll();
    ?>
    <table>
        <tr><th>ID</th><th>Username</th><th>Email</th><th>Rôle</th><th>Mot de passe</th><th>Créé le</th></tr>
        <?php foreach ($users as $u): ?>
        <tr>
            <td><?= $u['id'] ?></td>
            <td><?= $u['username'] ?></td>
            <td><?= $u['email'] ?></td>
            <td><?= $u['role'] ?></td>
            <td><span class="badge badge-vuln"><?= $u['password_plain'] ?></span></td>
            <td><?= $u['created_at'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<a href="index.php" class="btn btn-primary">Retour au TP4</a>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
