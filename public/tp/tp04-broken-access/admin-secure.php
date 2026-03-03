<?php include __DIR__ . '/../../../includes/header.php';

// SÉCURISÉ : vérification d'authentification ET de rôle
if (!isLoggedIn()) {
    echo '<div class="alert alert-danger">Vous devez être connecté.</div>';
    echo '<a href="/login.php" class="btn btn-primary">Se connecter</a>';
    include __DIR__ . '/../../../includes/footer.php';
    exit;
}

if (!isAdmin()) {
    echo '<div class="alert alert-danger">Accès refusé : vous n\'êtes pas administrateur.</div>';
    echo '<div class="alert alert-success">La vérification de rôle fonctionne correctement !</div>';
    echo '<a href="index.php" class="btn btn-primary">Retour au TP4</a>';
    include __DIR__ . '/../../../includes/footer.php';
    exit;
}
?>

<div class="page-header">
    <h2>Panel Administration</h2>
    <p>TP4 - Version SÉCURISÉE : accès réservé aux administrateurs</p>
</div>

<div class="alert alert-success">
    SÉCURISÉ : Seuls les administrateurs authentifiés peuvent voir cette page.
</div>

<div class="card">
    <h3>Liste des utilisateurs (données sensibles masquées)</h3>
    <?php
    $db = getDB();
    $users = $db->query("SELECT id, username, email, role, created_at FROM users")->fetchAll();
    ?>
    <table>
        <tr><th>ID</th><th>Username</th><th>Email</th><th>Rôle</th><th>Créé le</th></tr>
        <?php foreach ($users as $u): ?>
        <tr>
            <td><?= e($u['id']) ?></td>
            <td><?= e($u['username']) ?></td>
            <td><?= e($u['email']) ?></td>
            <td><?= e($u['role']) ?></td>
            <td><?= e($u['created_at']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<a href="index.php" class="btn btn-primary">Retour au TP4</a>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
