<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="page-header">
    <h2>Chapitre 1 — Bases de la sécurité</h2>
    <p>Architecture client, Same-Origin Policy, attaques côté client et défense en profondeur.</p>
</div>

<div class="chapter-nav">
    <a href="ex1-sop.php" class="tp-card">
        <span class="tp-number">1</span>
        <h3>Same-Origin Policy</h3>
        <p>Comprendre la politique de même origine du navigateur et ses implications.</p>
        <span class="duration">20 min</span>
    </a>
    <a href="ex2-xss.php" class="tp-card">
        <span class="tp-number">2</span>
        <h3>Exploitation XSS</h3>
        <p>Injection de scripts malveillants : XSS réfléchi et stocké.</p>
        <span class="duration">30 min</span>
    </a>
    <a href="ex3-clickjacking.php" class="tp-card">
        <span class="tp-number">3</span>
        <h3>Protection Clickjacking</h3>
        <p>Détourner les clics utilisateur via des iframes invisibles.</p>
        <span class="duration">20 min</span>
    </a>
    <a href="ex4-csp.php" class="tp-card">
        <span class="tp-number">4</span>
        <h3>Configuration CSP</h3>
        <p>Content Security Policy : restreindre les sources de contenu autorisées.</p>
        <span class="duration">20 min</span>
    </a>
</div>

<div class="card" style="margin-top:30px;">
    <h3>Cours théorique — Résumé (1h30)</h3>
    <div class="explanation">
        <h4>Architecture du client (navigateur)</h4>
        <p>Le navigateur est le point d'entrée de l'utilisateur vers le web. Il interprète HTML, CSS et JavaScript, et applique des politiques de sécurité comme la <strong>Same-Origin Policy</strong>.</p>
    </div>

    <h4 style="margin-top:20px;">Zones de sécurité et défense en profondeur</h4>
    <div class="code-block">
<span class="comment">// Défense en profondeur = plusieurs couches de protection</span>

┌──────────────────────────────────────────────┐
│  Internet (zone non fiable)                  │
│  ┌────────────────────────────────────────┐  │
│  │  Pare-feu / WAF                       │  │
│  │  ┌──────────────────────────────────┐  │  │
│  │  │  Serveur Web (DMZ)              │  │  │
│  │  │  ┌────────────────────────────┐  │  │  │
│  │  │  │  Application              │  │  │  │
│  │  │  │  ┌──────────────────────┐  │  │  │  │
│  │  │  │  │  Base de données    │  │  │  │  │
│  │  │  │  └──────────────────────┘  │  │  │  │
│  │  │  └────────────────────────────┘  │  │  │
│  │  └──────────────────────────────────┘  │  │
│  └────────────────────────────────────────┘  │
└──────────────────────────────────────────────┘
    </div>

    <h4 style="margin-top:20px;">Attaques côté client</h4>
    <table>
        <tr><th>Attaque</th><th>Principe</th><th>Cible</th></tr>
        <tr>
            <td><strong>XSS</strong></td>
            <td>Injection de JavaScript dans les pages web</td>
            <td>Cookies, sessions, données utilisateur</td>
        </tr>
        <tr>
            <td><strong>Clickjacking</strong></td>
            <td>Superposition d'une iframe invisible sur un bouton</td>
            <td>Actions non consenties (like, achat, suppression)</td>
        </tr>
        <tr>
            <td><strong>Man-in-the-Browser</strong></td>
            <td>Extension/malware modifiant le contenu de la page</td>
            <td>Transactions bancaires, formulaires</td>
        </tr>
    </table>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
