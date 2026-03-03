<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="page-header">
    <h2>Chapitre 2 — Sécurité du client</h2>
    <p>Failles applicatives côté client : DOM XSS, Prototype Pollution, configuration cookies, CORS.</p>
</div>

<div class="chapter-nav">
    <a href="ex1-dom-xss.php" class="tp-card">
        <span class="tp-number">1</span>
        <h3>DOM-based XSS</h3>
        <p>Injection XSS via manipulation du DOM JavaScript, sans passer par le serveur.</p>
        <span class="duration">30 min</span>
    </a>
    <a href="ex2-cookies.php" class="tp-card">
        <span class="tp-number">2</span>
        <h3>Configuration cookies sécurisée</h3>
        <p>Attributs HttpOnly, Secure, SameSite et leur impact sur la sécurité.</p>
        <span class="duration">25 min</span>
    </a>
    <a href="ex3-cors.php" class="tp-card">
        <span class="tp-number">3</span>
        <h3>CORS Attack</h3>
        <p>Cross-Origin Resource Sharing : mauvaise configuration et exploitation.</p>
        <span class="duration">35 min</span>
    </a>
</div>

<div class="card" style="margin-top:30px;">
    <h3>Cours théorique — Résumé (1h30)</h3>

    <div class="explanation">
        <h4>Failles applicatives côté client</h4>
        <p>Contrairement au XSS classique (où le payload transite par le serveur), certaines failles se produisent <strong>entièrement dans le navigateur</strong> : DOM-based XSS, Prototype Pollution, etc.</p>
    </div>

    <h4 style="margin-top:20px;">Failles de configuration</h4>
    <table>
        <tr><th>Élément</th><th>Risque si mal configuré</th><th>Protection</th></tr>
        <tr>
            <td><strong>Cookies</strong></td>
            <td>Vol de session par XSS, envoi cross-site</td>
            <td>HttpOnly, Secure, SameSite</td>
        </tr>
        <tr>
            <td><strong>localStorage</strong></td>
            <td>Accessible par tout script JavaScript sur la page</td>
            <td>Ne pas stocker de données sensibles</td>
        </tr>
        <tr>
            <td><strong>CORS</strong></td>
            <td>Un site externe peut lire vos données API</td>
            <td>Whitelist stricte des origines</td>
        </tr>
    </table>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
