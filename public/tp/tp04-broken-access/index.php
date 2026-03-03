<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="page-header">
    <h2>TP4 - Broken Access Control</h2>
    <p>Les utilisateurs accèdent à des ressources auxquelles ils ne devraient pas avoir accès (IDOR, escalade de privilèges).</p>
</div>

<div class="card">
    <div class="tabs">
        <button class="tab active" data-tab="vuln">Version Vulnérable</button>
        <button class="tab secure" data-tab="secure">Version Sécurisée</button>
    </div>

    <!-- VERSION VULNÉRABLE -->
    <div id="vuln" class="tab-content active">
        <div class="alert alert-danger">
            VULNÉRABLE : Pas de vérification d'autorisation. L'ID dans l'URL suffit pour accéder au profil.
        </div>

        <h3>IDOR - Insecure Direct Object Reference</h3>
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

            // VULNÉRABLE : pas de vérification d'autorisation
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
                echo '</table>';
                echo '</div>';

                echo '<div class="alert alert-danger">Un utilisateur non autorisé peut voir les profils privés en manipulant l\'URL !</div>';
            }
        }
        ?>

        <hr style="margin:20px 0;">

        <h3>Escalade de privilèges</h3>
        <p>Page d'administration accessible sans vérification de rôle :</p>

        <a href="admin-vulnerable.php" class="btn btn-danger">Accéder au panel admin (vulnérable)</a>

        <div class="explanation" style="margin-top:20px;">
            <h4>Attaques à tester :</h4>
            <ul style="margin:10px 0 0 20px;">
                <li>Changer <code>user_id=1</code> à <code>user_id=2</code> ou <code>user_id=3</code></li>
                <li>Accéder à la page admin sans être admin</li>
                <li>Modifier les paramètres d'URL pour voir les profils privés</li>
            </ul>
        </div>
    </div>

    <!-- VERSION SÉCURISÉE -->
    <div id="secure" class="tab-content">
        <div class="alert alert-success">
            SÉCURISÉ : Vérification des autorisations côté serveur avant chaque accès.
        </div>

        <h3>Accès contrôlé aux profils</h3>

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
                echo '<div class="alert alert-danger">Vous devez être connecté.</div>';
            } else {
                $db = getDB();
                $stmt = $db->prepare("SELECT u.id, u.username, u.email, u.role, p.bio, p.address, p.phone as profile_phone, p.is_private
                                      FROM users u LEFT JOIN profiles p ON u.id = p.user_id WHERE u.id = ?");
                $stmt->execute([$requestedId]);
                $profile = $stmt->fetch();

                if ($profile) {
                    // SÉCURISÉ : vérification des droits
                    $canView = ($currentUser['id'] == $requestedId) || ($currentUser['role'] === 'admin') || (!$profile['is_private']);

                    if ($canView) {
                        echo '<div class="card" style="border-color:var(--success);">';
                        echo '<h3>Profil de ' . e($profile['username']) . '</h3>';
                        echo '<table>';
                        echo '<tr><td><strong>Email</strong></td><td>' . e($profile['email']) . '</td></tr>';
                        echo '<tr><td><strong>Bio</strong></td><td>' . e($profile['bio'] ?? 'N/A') . '</td></tr>';
                        // On masque les infos sensibles si ce n'est pas le propre profil
                        if ($currentUser['id'] == $requestedId || $currentUser['role'] === 'admin') {
                            echo '<tr><td><strong>Adresse</strong></td><td>' . e($profile['address'] ?? 'N/A') . '</td></tr>';
                            echo '<tr><td><strong>Téléphone</strong></td><td>' . e($profile['profile_phone'] ?? 'N/A') . '</td></tr>';
                        }
                        echo '</table></div>';
                    } else {
                        echo '<div class="alert alert-danger">Accès refusé : ce profil est privé.</div>';
                    }
                }
            }
        }
        ?>

        <hr style="margin:20px 0;">
        <h3>Panel admin sécurisé</h3>
        <a href="admin-secure.php" class="btn btn-success">Accéder au panel admin (sécurisé)</a>
    </div>
</div>

<div class="two-cols">
    <div class="card">
        <h3>Code Vulnérable</h3>
        <div class="code-block">
<span class="comment">// DANGEREUX : aucune vérification</span>
<span class="vulnerable">$userId = $_GET['user_id'];
$stmt = $db->prepare("SELECT * FROM profiles
  WHERE user_id = ?");
$stmt->execute([$userId]);
// Affichage direct, pas de contrôle !</span>
        </div>
    </div>
    <div class="card">
        <h3>Code Sécurisé</h3>
        <div class="code-block">
<span class="comment">// SÛR : vérification des droits</span>
<span class="secure">$canView = ($currentUser['id'] == $id)
  || ($currentUser['role'] === 'admin')
  || (!$profile['is_private']);

if (!$canView) {
  echo "Accès refusé";
  exit;
}</span>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
