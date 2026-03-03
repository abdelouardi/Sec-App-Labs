<?php include __DIR__ . '/../../../includes/header.php';

$jwtResult = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $secret = 'super-secret-key-for-demo-only';

    if ($_POST['action'] === 'create') {
        // Créer un JWT manuellement (sans bibliothèque)
        $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
        $payload = json_encode([
            'sub'  => $_POST['user_id'] ?? '1',
            'name' => $_POST['username'] ?? 'admin',
            'role' => $_POST['role'] ?? 'admin',
            'iat'  => time(),
            'exp'  => time() + 3600,
        ]);

        $base64Header = rtrim(strtr(base64_encode($header), '+/', '-_'), '=');
        $base64Payload = rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');
        $signature = rtrim(strtr(base64_encode(
            hash_hmac('sha256', "$base64Header.$base64Payload", $secret, true)
        ), '+/', '-_'), '=');

        $jwtResult = [
            'token' => "$base64Header.$base64Payload.$signature",
            'header' => $header,
            'payload' => $payload,
            'header_b64' => $base64Header,
            'payload_b64' => $base64Payload,
        ];
    }

    if ($_POST['action'] === 'decode') {
        $token = $_POST['token'] ?? '';
        $parts = explode('.', $token);
        if (count($parts) === 3) {
            $jwtResult = [
                'token' => $token,
                'header' => base64_decode(strtr($parts[0], '-_', '+/')),
                'payload' => base64_decode(strtr($parts[1], '-_', '+/')),
                'signature' => $parts[2],
            ];
        }
    }
}
?>

<div class="page-header">
    <h2>Ch5 — Ex3 : JWT Authentication</h2>
    <p>JSON Web Tokens : structure, création, validation et pièges de sécurité.</p>
    <span class="duration">20 min</span>
</div>

<div class="card">
    <h3>Structure d'un JWT</h3>
    <div class="explanation">
        <h4>3 parties séparées par des points</h4>
        <p>Un JWT est composé de : <code style="color:var(--danger);">HEADER</code>.<code style="color:var(--primary);">PAYLOAD</code>.<code style="color:var(--success);">SIGNATURE</code></p>
    </div>

    <div class="code-block">
<span class="comment">// JWT = 3 parties encodées en Base64URL, séparées par des points</span>
<span class="vulnerable">eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9</span>.<span class="comment">eyJzdWIiOiIxIiwibmFtZSI6ImFkbWluIiwicm9sZSI6ImFkbWluIiwiaWF0IjoxNzA4MDAwMDAwfQ</span>.<span class="secure">SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c</span>

<span class="comment">// Décodé :</span>
<span class="vulnerable">// HEADER (algorithme de signature)
{
  "alg": "HS256",
  "typ": "JWT"
}</span>

<span class="comment">// PAYLOAD (données = "claims")</span>
{
  "sub": "1",
  "name": "admin",
  "role": "admin",
  "iat": 1708000000,
  "exp": 1708003600
}

<span class="secure">// SIGNATURE = HMAC-SHA256(header.payload, secret)</span>
    </div>
</div>

<div class="card">
    <div class="tabs">
        <button class="tab active" data-tab="vuln">Vulnérabilités JWT</button>
        <button class="tab secure" data-tab="secure">JWT sécurisé</button>
    </div>

    <!-- VULNÉRABLE -->
    <div id="vuln" class="tab-content active">
        <div class="alert alert-danger">
            VULNÉRABLE : Plusieurs attaques classiques sur les JWT.
        </div>

        <h3>Attaque 1 : Algorithm None</h3>
        <div class="code-block">
<span class="comment">// L'attaquant change l'algorithme à "none" (pas de signature)</span>
<span class="vulnerable">// Header modifié :
{"alg": "none", "typ": "JWT"}

// Si le serveur accepte alg=none,
// l'attaquant peut forger n'importe quel token sans clé !</span>

<span class="vulnerable">// Token forgé (pas de signature) :
eyJhbGciOiJub25lIiwidHlwIjoiSldUIn0.
eyJzdWIiOiIxIiwicm9sZSI6ImFkbWluIn0.</span>
        </div>

        <h3 style="margin-top:20px;">Attaque 2 : Confusion HS256/RS256</h3>
        <div class="code-block">
<span class="comment">// Le serveur utilise RS256 (asymétrique : clé publique + privée)
// L'attaquant change l'algorithme à HS256 (symétrique)
// et signe avec la CLÉ PUBLIQUE (qui est connue)</span>

<span class="vulnerable">// Header modifié :
{"alg": "HS256"}  // au lieu de RS256

// Le serveur vérifie avec la clé publique en mode HS256
// → la signature est valide ! L'attaquant a forgé le token.</span>
        </div>

        <h3 style="margin-top:20px;">Attaque 3 : Escalade via le payload</h3>
        <div class="code-block">
<span class="comment">// Le serveur fait confiance au payload sans vérifier la signature</span>
<span class="vulnerable">$payload = json_decode(base64_decode($parts[1]));
$role = $payload->role;  // PAS DE VÉRIFICATION DE SIGNATURE !

// L'attaquant modifie juste le payload :
{"sub": "1", "role": "admin"}  // était "user"</span>
        </div>

        <h3 style="margin-top:20px;">Attaque 4 : Pas d'expiration</h3>
        <div class="code-block">
<span class="vulnerable">// Token sans claim "exp" → valide indéfiniment
{
  "sub": "1",
  "role": "admin"
  // Pas de "exp" !
}
// Si le token est volé, l'attaquant l'utilise pour toujours</span>
        </div>
    </div>

    <!-- SÉCURISÉ -->
    <div id="secure" class="tab-content">
        <div class="alert alert-success">
            SÉCURISÉ : Validation stricte de l'algorithme, de la signature et de l'expiration.
        </div>

        <h3>Validation JWT sécurisée</h3>
        <div class="code-block">
<span class="secure">function validateJWT($token, $secret) {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return false;

    [$headerB64, $payloadB64, $signatureB64] = $parts;

    // 1. Décoder le header
    $header = json_decode(base64_decode(strtr($headerB64, '-_', '+/')));

    // 2. VÉRIFIER L'ALGORITHME (whitelist !)
    if ($header->alg !== 'HS256') {
        return false; // Rejeter alg=none, RS256, etc.
    }

    // 3. VÉRIFIER LA SIGNATURE
    $expectedSig = rtrim(strtr(base64_encode(
        hash_hmac('sha256', "$headerB64.$payloadB64", $secret, true)
    ), '+/', '-_'), '=');

    if (!hash_equals($expectedSig, $signatureB64)) {
        return false; // Signature invalide !
    }

    // 4. Décoder et vérifier le payload
    $payload = json_decode(base64_decode(strtr($payloadB64, '-_', '+/')));

    // 5. VÉRIFIER L'EXPIRATION
    if (!isset($payload->exp) || $payload->exp < time()) {
        return false; // Token expiré !
    }

    return $payload;
}</span>
        </div>

        <h4 style="margin-top:20px;">Bonnes pratiques JWT</h4>
        <ul style="margin:10px 0 0 20px;">
            <li><strong>Whitelist d'algorithmes</strong> : n'accepter que <code>HS256</code> ou <code>RS256</code></li>
            <li><strong>Toujours vérifier la signature</strong> avant de lire le payload</li>
            <li><strong>Toujours vérifier l'expiration</strong> (claim <code>exp</code>)</li>
            <li><strong>Durée de vie courte</strong> (15 min pour access token, 7j pour refresh token)</li>
            <li><strong>Stocker en cookie HttpOnly</strong>, pas en localStorage</li>
            <li><strong>Utiliser une bibliothèque</strong> (firebase/php-jwt, lcobucci/jwt) plutôt que du code maison</li>
            <li><strong>Rotation des secrets</strong> régulière</li>
        </ul>
    </div>
</div>

<div class="card">
    <h3>Outil interactif : Créer un JWT</h3>
    <form method="POST">
        <input type="hidden" name="action" value="create">
        <div class="two-cols">
            <div class="form-group">
                <label>User ID :</label>
                <input type="text" name="user_id" value="1">
            </div>
            <div class="form-group">
                <label>Username :</label>
                <input type="text" name="username" value="admin">
            </div>
        </div>
        <div class="form-group">
            <label>Rôle :</label>
            <select name="role">
                <option value="admin">admin</option>
                <option value="user">user</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Générer le JWT</button>
    </form>

    <?php if ($jwtResult && isset($jwtResult['header_b64'])): ?>
    <div style="margin-top:15px;">
        <h4>JWT généré :</h4>
        <div class="code-block" style="word-break:break-all;">
<span class="vulnerable"><?= e($jwtResult['header_b64']) ?></span>.<span class="comment"><?= e($jwtResult['payload_b64']) ?></span>.<span class="secure"><?= substr(e($jwtResult['token']), strrpos(e($jwtResult['token']), '.') + 1) ?></span>
        </div>

        <div class="two-cols" style="margin-top:15px;">
            <div>
                <h4>Header :</h4>
                <div class="code-block"><span class="vulnerable"><?= e($jwtResult['header']) ?></span></div>
            </div>
            <div>
                <h4>Payload :</h4>
                <div class="code-block"><span class="comment"><?= e($jwtResult['payload']) ?></span></div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <hr style="margin:20px 0;">

    <h3>Décoder un JWT</h3>
    <form method="POST">
        <input type="hidden" name="action" value="decode">
        <div class="form-group">
            <label>Collez un JWT :</label>
            <input type="text" name="token" value="<?= e($jwtResult['token'] ?? '') ?>" placeholder="eyJhbGci...">
        </div>
        <button type="submit" class="btn btn-warning">Décoder</button>
    </form>

    <?php if ($jwtResult && isset($jwtResult['signature'])): ?>
    <div style="margin-top:15px;">
        <h4>Header :</h4>
        <div class="code-block"><?= e($jwtResult['header']) ?></div>
        <h4>Payload :</h4>
        <div class="code-block"><?= e($jwtResult['payload']) ?></div>
        <div class="alert alert-warning" style="margin-top:10px;">
            Le payload est visible par tout le monde (juste encodé en Base64, PAS chiffré). Ne mettez jamais de données sensibles dans un JWT !
        </div>
    </div>
    <?php endif; ?>
</div>

<a href="index.php" class="btn btn-primary">← Retour au chapitre 5</a>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
