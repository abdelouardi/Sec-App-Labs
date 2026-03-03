<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="page-header">
    <h2>Chapitre 4 — Architecture des serveurs Web</h2>
    <p>Architecture web moderne, configuration sécurisée, isolation et protection DDoS.</p>
</div>

<div class="chapter-nav">
    <a href="ex1-nginx.php" class="tp-card">
        <span class="tp-number">1</span>
        <h3>Configuration Nginx sécurisée</h3>
        <p>Reverse proxy, en-têtes de sécurité, rate limiting, et hardening.</p>
        <span class="duration">40 min</span>
    </a>
    <a href="ex2-docker.php" class="tp-card">
        <span class="tp-number">2</span>
        <h3>Isolation avec Docker</h3>
        <p>Conteneurisation, isolation des services, et bonnes pratiques de sécurité.</p>
        <span class="duration">30 min</span>
    </a>
    <a href="ex3-ddos.php" class="tp-card">
        <span class="tp-number">3</span>
        <h3>Protection DDoS avec fail2ban</h3>
        <p>Détection et blocage automatique des attaques par déni de service.</p>
        <span class="duration">20 min</span>
    </a>
</div>

<div class="card" style="margin-top:30px;">
    <h3>Cours théorique — Résumé (1h30)</h3>

    <div class="explanation">
        <h4>Architecture web moderne</h4>
        <p>Une application web en production est rarement un simple serveur. Elle utilise plusieurs couches de protection et d'optimisation.</p>
    </div>

    <div class="code-block">
<span class="comment">// Architecture typique en production</span>

Internet
    │
    ▼
┌──────────────┐
│   CDN/WAF    │  Cloudflare, AWS CloudFront
│  (DDoS, cache)│
└──────┬───────┘
       │
┌──────▼───────┐
│ Load Balancer │  HAProxy, Nginx, AWS ALB
│  (répartition) │
└──┬────────┬──┘
   │        │
┌──▼──┐  ┌──▼──┐
│ App1│  │ App2│   Serveurs d'application (PHP, Node.js)
└──┬──┘  └──┬──┘
   │        │
┌──▼────────▼──┐
│   Base de    │   MySQL, PostgreSQL (réseau privé)
│   données    │
└──────────────┘
    </div>

    <h4 style="margin-top:20px;">Principes clés</h4>
    <table>
        <tr><th>Principe</th><th>Description</th></tr>
        <tr><td><strong>Défense en profondeur</strong></td><td>Plusieurs couches de protection (pas qu'un pare-feu)</td></tr>
        <tr><td><strong>Moindre privilège</strong></td><td>Chaque composant n'a que les droits nécessaires</td></tr>
        <tr><td><strong>Isolation</strong></td><td>Les services sont séparés (Docker, VMs)</td></tr>
        <tr><td><strong>Fail-safe</strong></td><td>En cas de doute, bloquer plutôt qu'autoriser</td></tr>
    </table>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
