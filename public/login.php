<?php
require_once __DIR__ . '/../config/app.php';

$error = '';
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'secure';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($mode === 'vulnerable') {
        // ============================================
        // VERSION VULNÉRABLE - Broken Authentication
        // - Pas de limite de tentatives
        // - Messages d'erreur trop détaillés
        // - Session fixation possible
        // ============================================
        $conn = getMysqli();
        $query = "SELECT * FROM users WHERE username = '" . $conn->real_escape_string($username) . "'";
        $result = $conn->query($query);

        if ($result->num_rows === 0) {
            $error = "L'utilisateur '$username' n'existe pas dans la base de données."; // Trop détaillé !
        } else {
            $user = $result->fetch_assoc();
            // Comparaison en clair au lieu de password_verify
            if ($password === $user['password_plain']) {
                // Pas de regeneration d'ID de session = session fixation
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                redirect('/index.php');
            } else {
                $error = "Mot de passe incorrect pour l'utilisateur '$username'."; // Trop détaillé !
            }
        }
        $conn->close();
    } else {
        // ============================================
        // VERSION SÉCURISÉE
        // - Messages d'erreur génériques
        // - password_verify avec hash bcrypt
        // - Régénération de l'ID de session
        // ============================================
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true); // Anti session fixation
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            redirect('/index.php');
        } else {
            $error = "Identifiants incorrects."; // Message générique
        }
    }
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="page-header">
    <h2>Connexion</h2>
    <p>TP3 - Broken Authentication : Observez les différences entre les deux modes</p>
</div>

<div class="tabs">
    <a href="?mode=vulnerable" class="tab <?= $mode === 'vulnerable' ? 'active' : '' ?>">Version Vulnérable</a>
    <a href="?mode=secure" class="tab secure <?= $mode === 'secure' ? 'active' : '' ?>">Version Sécurisée</a>
</div>

<?php if ($mode === 'vulnerable'): ?>
<div class="alert alert-danger">
    MODE VULNÉRABLE : Messages d'erreur détaillés, pas de protection brute-force, comparaison mot de passe en clair
</div>
<?php else: ?>
<div class="alert alert-success">
    MODE SÉCURISÉ : Messages génériques, password_verify(), régénération de session
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<div class="two-cols">
    <div class="card">
        <h3>Se connecter</h3>
        <form method="POST" action="?mode=<?= e($mode) ?>">
            <div class="form-group">
                <label>Nom d'utilisateur</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Connexion</button>
        </form>
        <p style="margin-top:15px;color:var(--gray);font-size:0.85rem;">
            Comptes test : admin / alice / bob (mot de passe : password123)
        </p>
    </div>

    <div class="card">
        <h3>Explication</h3>
        <div class="explanation">
            <h4>Broken Authentication</h4>
            <p>Les failles d'authentification permettent aux attaquants de compromettre les comptes utilisateurs.</p>
        </div>

        <h4>Vulnérabilités démontrées :</h4>
        <ul style="margin:10px 0 10px 20px;">
            <li>Messages d'erreur révélant si un utilisateur existe</li>
            <li>Mots de passe stockés et comparés en clair</li>
            <li>Pas de régénération de l'ID de session</li>
            <li>Aucune limite de tentatives de connexion</li>
        </ul>

        <h4 style="margin-top:15px;">Code vulnérable :</h4>
        <div class="code-block">
<span class="comment">// Message trop détaillé</span>
<span class="vulnerable">$error = "L'utilisateur '$username' n'existe pas";</span>

<span class="comment">// Comparaison en clair</span>
<span class="vulnerable">if ($password === $user['password_plain'])</span>

<span class="comment">// Pas de régénération de session</span>
<span class="vulnerable">$_SESSION['user_id'] = $user['id'];</span></div>

        <h4 style="margin-top:15px;">Code sécurisé :</h4>
        <div class="code-block">
<span class="comment">// Message générique</span>
<span class="secure">$error = "Identifiants incorrects.";</span>

<span class="comment">// Hash bcrypt</span>
<span class="secure">password_verify($password, $user['password'])</span>

<span class="comment">// Régénération de session</span>
<span class="secure">session_regenerate_id(true);</span></div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
