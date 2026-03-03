<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="page-header">
    <h2>Ch5 — Ex1 : Implémentation RBAC</h2>
    <p>Role-Based Access Control : contrôle d'accès basé sur les rôles avec IDOR et escalade de privilèges.</p>
    <span class="duration">35 min</span>
</div>

<div class="card">
    <div class="tabs">
        <button class="tab active" data-tab="vuln">Version Vulnérable</button>
        <button class="tab secure" data-tab="secure">Version Sécurisée</button>
    </div>

    <!-- VULNÉRABLE -->
    <div id="vuln" class="tab-content active">
        <div class="alert alert-danger">
            VULNÉRABLE : Pas de vérification des rôles ni de contrôle d'accès.
        </div>

        <h3>IDOR — Insecure Direct Object Reference</h3>
        <p>Accédez aux profils en changeant l'ID dans l'URL :</p>
        <div style="margin:15px 0;">
            <a href="?mode=vulnerable&user_id=1" class="btn btn-sm btn-danger">Profil #1 (admin)</a>
            <a href="?mode=vulnerable&user_id=2" class="btn btn-sm btn-danger">Profil #2 (alice)</a>
            <a href="?mode=vulnerable&user_id=3" class="btn btn-sm btn-danger">Profil #3 (bob)</a>
        </div>

        <?php
        if (isset($_GET['user_id']) && ($_GET['mode'] ?? '') === 'vulnerable') {
            $userId = $_GET['user_id'];
            $db = getDB();
            $stmt = $db->prepare("SELECT u.*, p.bio, p.address, p.phone as profile_phone, p.is_private
                                  FROM users u LEFT JOIN profiles p ON u.id = p.user_id WHERE u.id = ?");
            $stmt->execute([$userId]);
            $profile = $stmt->fetch();

            if ($profile) {
                echo '<div class="card" style="border-color:var(--danger);">';
                echo '<h3>Profil de ' . e($profile['username']) . '</h3>';
                echo '<table>';
                echo '<tr><td><strong>Email</strong></td><td>' . e($profile['email']) . '</td></tr>';
                echo '<tr><td><strong>Rôle</strong></td><td>' . e($profile['role']) . '</td></tr>';
                echo '<tr><td><strong>Bio</strong></td><td>' . e($profile['bio'] ?? 'N/A') . '</td></tr>';
                echo '<tr><td><strong>Adresse</strong></td><td>' . e($profile['address'] ?? 'N/A') . '</td></tr>';
                echo '<tr><td><strong>Téléphone</strong></td><td>' . e($profile['profile_phone'] ?? 'N/A') . '</td></tr>';
                echo '<tr><td><strong>Privé</strong></td><td>' . ($profile['is_private'] ? 'Oui' : 'Non') . '</td></tr>';
                echo '</table></div>';
                echo '<div class="alert alert-danger">Aucune vérification d\'autorisation ! N\'importe qui peut voir n\'importe quel profil.</div>';
            }
        }
        ?>

        <hr style="margin:20px 0;">

        <h3>Escalade de privilèges — Panel admin sans vérification</h3>
        <?php
        $db = getDB();
        $users = $db->query("SELECT id, username, email, role, password_plain, created_at FROM users")->fetchAll();
        ?>
        <div class="alert alert-danger">N'importe qui accède à cette page — même sans être connecté !</div>
        <table>
            <tr><th>ID</th><th>Username</th><th>Email</th><th>Rôle</th><th>Mot de passe</th></tr>
            <?php foreach ($users as $u): ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td><?= $u['username'] ?></td>
                <td><?= $u['email'] ?></td>
                <td><?= $u['role'] ?></td>
                <td><span class="badge badge-vuln"><?= $u['password_plain'] ?></span></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <div class="code-block" style="margin-top:15px;">
<span class="comment">// DANGEREUX : aucune vérification de rôle</span>
<span class="vulnerable">$userId = $_GET['user_id'];
$stmt = $db->prepare("SELECT * FROM profiles WHERE user_id = ?");
$stmt->execute([$userId]);
// Affichage direct, pas de contrôle !</span>

<span class="comment">// Page admin sans vérification</span>
<span class="vulnerable">$users = $db->query("SELECT * FROM users")->fetchAll();
// N'importe qui peut voir les mots de passe !</span>
        </div>
    </div>

    <!-- SÉCURISÉE -->
    <div id="secure" class="tab-content">
        <div class="alert alert-success">
            SÉCURISÉ : Contrôle d'accès basé sur les rôles (RBAC) avec vérification systématique.
        </div>

        <h3>Modèle RBAC implémenté</h3>
        <div class="code-block">
<span class="comment">// Définition des permissions par rôle</span>
<span class="secure">$rbac = [
    'admin' => [
        'view_any_profile',
        'view_private_profiles',
        'edit_any_profile',
        'view_users_list',
        'view_salaries',
        'export_data',
        'manage_roles',
    ],
    'manager' => [
        'view_any_profile',
        'view_department_salaries',
        'export_department_data',
    ],
    'user' => [
        'view_own_profile',
        'edit_own_profile',
        'view_public_profiles',
    ],
];</span>
        </div>

        <h3 style="margin-top:20px;">Profils avec RBAC</h3>
        <div style="margin:15px 0;">
            <a href="?mode=secure&user_id=1" class="btn btn-sm btn-success">Profil #1</a>
            <a href="?mode=secure&user_id=2" class="btn btn-sm btn-success">Profil #2</a>
            <a href="?mode=secure&user_id=3" class="btn btn-sm btn-success">Profil #3</a>
        </div>

        <?php
        if (isset($_GET['user_id']) && ($_GET['mode'] ?? '') === 'secure') {
            $requestedId = (int)$_GET['user_id'];
            $currentUser = getCurrentUser();

            if (!$currentUser) {
                echo '<div class="alert alert-danger">Vous devez être connecté pour accéder aux profils.</div>';
            } else {
                $db = getDB();
                $stmt = $db->prepare("SELECT u.id, u.username, u.email, u.role, p.bio, p.address, p.phone as profile_phone, p.is_private
                                      FROM users u LEFT JOIN profiles p ON u.id = p.user_id WHERE u.id = ?");
                $stmt->execute([$requestedId]);
                $profile = $stmt->fetch();

                if ($profile) {
                    $canView = ($currentUser['id'] == $requestedId)
                            || ($currentUser['role'] === 'admin')
                            || (!$profile['is_private']);

                    if ($canView) {
                        echo '<div class="card" style="border-color:var(--success);">';
                        echo '<h3>Profil de ' . e($profile['username']) . '</h3>';
                        echo '<table>';
                        echo '<tr><td><strong>Email</strong></td><td>' . e($profile['email']) . '</td></tr>';
                        echo '<tr><td><strong>Bio</strong></td><td>' . e($profile['bio'] ?? 'N/A') . '</td></tr>';
                        if ($currentUser['id'] == $requestedId || $currentUser['role'] === 'admin') {
                            echo '<tr><td><strong>Adresse</strong></td><td>' . e($profile['address'] ?? 'N/A') . '</td></tr>';
                            echo '<tr><td><strong>Téléphone</strong></td><td>' . e($profile['profile_phone'] ?? 'N/A') . '</td></tr>';
                        } else {
                            echo '<tr><td><strong>Adresse</strong></td><td><span style="color:var(--gray);">***masqué***</span></td></tr>';
                        }
                        echo '</table></div>';
                    } else {
                        echo '<div class="alert alert-danger">Accès refusé : ce profil est privé et vous n\'avez pas la permission <code>view_private_profiles</code>.</div>';
                    }
                }
            }
        }
        ?>

        <hr style="margin:20px 0;">

        <h3>Panel admin sécurisé</h3>
        <?php
        $currentUser = getCurrentUser();
        if (!$currentUser || $currentUser['role'] !== 'admin') {
            echo '<div class="alert alert-danger">Accès réservé aux utilisateurs ayant le rôle <code>admin</code>.</div>';
            if ($currentUser) {
                echo '<p>Votre rôle actuel : <code>' . e($currentUser['role']) . '</code></p>';
            }
        } else {
            echo '<div class="alert alert-success">Connecté en tant qu\'admin — accès autorisé.</div>';
            $users = $db->query("SELECT id, username, email, role, created_at FROM users")->fetchAll();
            echo '<table><tr><th>ID</th><th>Username</th><th>Email</th><th>Rôle</th><th>Créé le</th></tr>';
            foreach ($users as $u) {
                echo '<tr><td>' . e($u['id']) . '</td><td>' . e($u['username']) . '</td><td>' . e($u['email']) . '</td>';
                echo '<td>' . e($u['role']) . '</td><td>' . e($u['created_at']) . '</td></tr>';
            }
            echo '</table>';
            echo '<p style="color:var(--gray);margin-top:10px;"><em>Note : les mots de passe ne sont PAS affichés.</em></p>';
        }
        ?>

        <div class="code-block" style="margin-top:15px;">
<span class="comment">// Fonction de vérification RBAC</span>
<span class="secure">function hasPermission($user, $permission) {
    $rbac = [
        'admin'   => ['view_any_profile', 'manage_roles', ...],
        'manager' => ['view_any_profile', ...],
        'user'    => ['view_own_profile', ...],
    ];
    $role = $user['role'] ?? 'user';
    return in_array($permission, $rbac[$role] ?? []);
}

// Utilisation
if (!hasPermission($currentUser, 'view_users_list')) {
    http_response_code(403);
    die('Accès refusé');
}</span>
        </div>
    </div>
</div>

<div class="card">
    <h3>Exercices</h3>
    <ol style="margin:10px 0 0 20px;">
        <li><strong>IDOR</strong> : Sans être connecté, changez <code>user_id</code> dans l'onglet vulnérable → accédez à tous les profils</li>
        <li><strong>Escalade</strong> : Observez les mots de passe en clair dans la version vulnérable</li>
        <li><strong>RBAC</strong> : Connectez-vous en tant qu'alice (user) et essayez d'accéder au profil privé de bob</li>
        <li><strong>Admin</strong> : Connectez-vous en admin → l'accès est autorisé, mais les mots de passe sont cachés</li>
        <li><strong>Défi</strong> : Implémentez un rôle <code>manager</code> qui peut voir les profils de son département uniquement</li>
    </ol>
</div>

<a href="index.php" class="btn btn-primary">← Retour au chapitre 5</a>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
