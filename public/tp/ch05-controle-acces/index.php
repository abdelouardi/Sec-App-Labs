<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="page-header">
    <h2>Chapitre 5 — Contrôle d'accès</h2>
    <p>Modèles de contrôle d'accès, OAuth 2.0, OpenID Connect et JWT.</p>
</div>

<div class="chapter-nav">
    <a href="ex1-rbac.php" class="tp-card">
        <span class="tp-number">1</span>
        <h3>Implémentation RBAC</h3>
        <p>Role-Based Access Control : contrôle d'accès basé sur les rôles.</p>
        <span class="duration">35 min</span>
    </a>
    <a href="ex2-oauth.php" class="tp-card">
        <span class="tp-number">2</span>
        <h3>Authentification OAuth 2.0</h3>
        <p>Flux d'autorisation, tokens, et intégration avec des providers externes.</p>
        <span class="duration">35 min</span>
    </a>
    <a href="ex3-jwt.php" class="tp-card">
        <span class="tp-number">3</span>
        <h3>JWT Authentication</h3>
        <p>JSON Web Tokens : structure, validation, et pièges de sécurité.</p>
        <span class="duration">20 min</span>
    </a>
</div>

<div class="card" style="margin-top:30px;">
    <h3>Cours théorique — Résumé (1h30)</h3>

    <div class="explanation">
        <h4>Authentification vs Autorisation</h4>
        <p><strong>Authentification</strong> = "Qui êtes-vous ?" (login/mot de passe)<br>
        <strong>Autorisation</strong> = "Qu'avez-vous le droit de faire ?" (permissions, rôles)</p>
    </div>

    <h4 style="margin-top:20px;">Modèles de contrôle d'accès</h4>
    <table>
        <tr><th>Modèle</th><th>Principe</th><th>Exemple</th></tr>
        <tr>
            <td><strong>DAC</strong><br><small>Discretionary</small></td>
            <td>Le propriétaire décide des permissions</td>
            <td>Partage de fichiers Google Drive</td>
        </tr>
        <tr>
            <td><strong>MAC</strong><br><small>Mandatory</small></td>
            <td>Politique centralisée imposée</td>
            <td>Classifications militaires (confidentiel, secret)</td>
        </tr>
        <tr>
            <td><strong>RBAC</strong><br><small>Role-Based</small></td>
            <td>Permissions attribuées à des rôles, utilisateurs assignés à des rôles</td>
            <td>admin, éditeur, lecteur</td>
        </tr>
        <tr>
            <td><strong>ABAC</strong><br><small>Attribute-Based</small></td>
            <td>Règles basées sur des attributs (utilisateur, ressource, contexte)</td>
            <td>"Un manager peut voir les salaires de son département"</td>
        </tr>
    </table>

    <h4 style="margin-top:20px;">Protocoles d'authentification modernes</h4>
    <table>
        <tr><th>Protocole</th><th>Rôle</th><th>Usage</th></tr>
        <tr>
            <td><strong>OAuth 2.0</strong></td>
            <td>Autorisation déléguée</td>
            <td>"Autoriser cette app à accéder à mon Google Drive"</td>
        </tr>
        <tr>
            <td><strong>OpenID Connect</strong></td>
            <td>Authentification (couche sur OAuth 2.0)</td>
            <td>"Se connecter avec Google/GitHub"</td>
        </tr>
        <tr>
            <td><strong>JWT</strong></td>
            <td>Format de token</td>
            <td>Transporter les informations d'identité entre services</td>
        </tr>
        <tr>
            <td><strong>SAML</strong></td>
            <td>SSO d'entreprise</td>
            <td>Connexion unique pour toutes les apps d'une entreprise</td>
        </tr>
    </table>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
