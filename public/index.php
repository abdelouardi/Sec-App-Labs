<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="page-header">
    <h2>Cours de Sécurité du Développement Web</h2>
    <p>5 chapitres — 15h de cours et TP pratiques avec versions vulnérables et sécurisées</p>
</div>

<?php if (!isLoggedIn()): ?>
<div class="alert alert-warning">
    <a href="/login.php">Connectez-vous</a> pour accéder à toutes les fonctionnalités.
    Comptes : admin / alice / bob (mot de passe : password123)
</div>
<?php endif; ?>

<!-- CHAPITRES -->
<div class="chapter-grid">

    <a href="/tp/ch01-bases-securite/index.php" class="chapter-card ch1">
        <div class="chapter-number">1</div>
        <div class="chapter-content">
            <h3>Bases de la sécurité</h3>
            <p>Architecture client, Same-Origin Policy, attaques XSS, Clickjacking et CSP.</p>
            <div class="chapter-meta">
                <span class="duration">3h</span>
                <span>4 exercices</span>
            </div>
        </div>
    </a>

    <a href="/tp/ch02-securite-client/index.php" class="chapter-card ch2">
        <div class="chapter-number">2</div>
        <div class="chapter-content">
            <h3>Sécurité du client</h3>
            <p>DOM-based XSS, Prototype Pollution, configuration cookies, localStorage et CORS.</p>
            <div class="chapter-meta">
                <span class="duration">3h</span>
                <span>3 exercices</span>
            </div>
        </div>
    </a>

    <a href="/tp/ch03-securite-reseau/index.php" class="chapter-card ch3">
        <div class="chapter-number">3</div>
        <div class="chapter-content">
            <h3>Sécurité du réseau</h3>
            <p>TLS/HTTPS, handshake, certificats Let's Encrypt et cryptographie AES/RSA.</p>
            <div class="chapter-meta">
                <span class="duration">3h</span>
                <span>3 exercices</span>
            </div>
        </div>
    </a>

    <a href="/tp/ch04-architecture-serveurs/index.php" class="chapter-card ch4">
        <div class="chapter-number">4</div>
        <div class="chapter-content">
            <h3>Architecture des serveurs Web</h3>
            <p>Configuration Nginx, isolation Docker, reverse proxy et protection DDoS.</p>
            <div class="chapter-meta">
                <span class="duration">3h</span>
                <span>3 exercices</span>
            </div>
        </div>
    </a>

    <a href="/tp/ch05-controle-acces/index.php" class="chapter-card ch5">
        <div class="chapter-number">5</div>
        <div class="chapter-content">
            <h3>Contrôle d'accès</h3>
            <p>RBAC, ABAC, OAuth 2.0, OpenID Connect, JWT et gestion des sessions.</p>
            <div class="chapter-meta">
                <span class="duration">3h</span>
                <span>3 exercices</span>
            </div>
        </div>
    </a>

</div>

<!-- FAILLES APPLICATIVES (anciens TPs) -->
<div class="page-header" style="margin-top:40px;">
    <h2>Failles applicatives — Démonstrations OWASP</h2>
    <p>Explorations pratiques des vulnérabilités web courantes (injection SQL, CSRF, SSRF, etc.)</p>
</div>

<div class="tp-grid">
    <a href="/tp/tp01-sqli/index.php" class="tp-card">
        <span class="tp-number">1</span>
        <h3>Injection SQL</h3>
        <p>Manipuler les requêtes SQL via les champs de saisie pour extraire ou modifier les données.</p>
        <span class="severity critical">Critique</span>
    </a>

    <a href="/login.php?mode=vulnerable" class="tp-card">
        <span class="tp-number">3</span>
        <h3>Broken Authentication</h3>
        <p>Messages d'erreur détaillés, mots de passe en clair, session fixation.</p>
        <span class="severity critical">Critique</span>
    </a>

    <a href="/tp/tp05-sensitive-data/index.php" class="tp-card">
        <span class="tp-number">5</span>
        <h3>Exposition de données sensibles</h3>
        <p>Données stockées sans chiffrement : mots de passe en clair, SSN visibles.</p>
        <span class="severity high">Élevée</span>
    </a>

    <a href="/tp/tp06-csrf/index.php" class="tp-card">
        <span class="tp-number">6</span>
        <h3>Cross-Site Request Forgery</h3>
        <p>Forcer un utilisateur authentifié à exécuter des actions non désirées.</p>
        <span class="severity high">Élevée</span>
    </a>

    <a href="/tp/tp07-misconfig/index.php" class="tp-card">
        <span class="tp-number">7</span>
        <h3>Mauvaise configuration</h3>
        <p>Configurations par défaut, erreurs verbeux, en-têtes manquants.</p>
        <span class="severity high">Élevée</span>
    </a>

    <a href="/tp/tp08-vulnerable-components/index.php" class="tp-card">
        <span class="tp-number">8</span>
        <h3>Composants vulnérables</h3>
        <p>Bibliothèques avec des failles connues non corrigées.</p>
        <span class="severity high">Élevée</span>
    </a>

    <a href="/tp/tp09-logging/index.php" class="tp-card">
        <span class="tp-number">9</span>
        <h3>Logging insuffisant</h3>
        <p>Absence de journalisation rendant impossible la détection d'intrusions.</p>
        <span class="severity medium">Moyenne</span>
    </a>

    <a href="/tp/tp10-ssrf/index.php" class="tp-card">
        <span class="tp-number">10</span>
        <h3>Server-Side Request Forgery</h3>
        <p>Requêtes vers des ressources internes non autorisées.</p>
        <span class="severity critical">Critique</span>
    </a>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
