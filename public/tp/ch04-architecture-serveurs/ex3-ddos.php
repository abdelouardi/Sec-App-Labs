<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="page-header">
    <h2>Ch4 — Ex3 : Protection DDoS avec fail2ban</h2>
    <p>Détection et blocage automatique des attaques par déni de service.</p>
    <span class="duration">20 min</span>
</div>

<div class="card">
    <h3>Qu'est-ce qu'une attaque DDoS ?</h3>
    <div class="explanation">
        <h4>DDoS = Distributed Denial of Service</h4>
        <p>L'attaquant envoie un <strong>nombre massif de requêtes</strong> depuis de multiples sources pour saturer le serveur et le rendre inaccessible aux utilisateurs légitimes.</p>
    </div>

    <table>
        <tr><th>Type</th><th>Couche</th><th>Méthode</th><th>Protection</th></tr>
        <tr>
            <td><strong>Volumétrique</strong></td>
            <td>Réseau (L3/L4)</td>
            <td>Saturation de bande passante (UDP flood, ICMP)</td>
            <td>CDN, FAI, Cloudflare</td>
        </tr>
        <tr>
            <td><strong>Protocolaire</strong></td>
            <td>Transport (L4)</td>
            <td>SYN flood, Ping of Death</td>
            <td>Pare-feu, sysctl tuning</td>
        </tr>
        <tr>
            <td><strong>Applicatif</strong></td>
            <td>Application (L7)</td>
            <td>HTTP flood, Slowloris</td>
            <td>Rate limiting, fail2ban, WAF</td>
        </tr>
    </table>
</div>

<div class="card">
    <div class="tabs">
        <button class="tab active" data-tab="vuln">Sans protection</button>
        <button class="tab secure" data-tab="secure">Avec fail2ban</button>
    </div>

    <!-- SANS PROTECTION -->
    <div id="vuln" class="tab-content active">
        <div class="alert alert-danger">
            VULNÉRABLE : Le serveur accepte toutes les requêtes sans limite.
        </div>

        <h3>Simulation : flood de requêtes</h3>
        <div class="code-block">
<span class="comment"># Un attaquant peut envoyer des milliers de requêtes :</span>
<span class="vulnerable"># Attaque simple avec curl en boucle
for i in $(seq 1 10000); do
    curl -s http://cible.com/login.php \
         -d "username=admin&password=test$i" &
done</span>

<span class="vulnerable"># Attaque avec ab (Apache Benchmark)
ab -n 100000 -c 1000 http://cible.com/</span>

<span class="vulnerable"># Attaque Slowloris (connexions lentes)
# Ouvre des milliers de connexions et les maintient ouvertes
# sans jamais les terminer → épuise les slots du serveur</span>
        </div>

        <h4 style="margin-top:20px;">Conséquences sans protection</h4>
        <ul style="margin:10px 0 0 20px;">
            <li>Le serveur est <strong>saturé</strong> et ne répond plus</li>
            <li>Les utilisateurs légitimes ne peuvent plus accéder au site</li>
            <li>Brute-force des mots de passe possible sans limite</li>
            <li>Pas de détection ni d'alerte</li>
        </ul>
    </div>

    <!-- AVEC FAIL2BAN -->
    <div id="secure" class="tab-content">
        <div class="alert alert-success">
            SÉCURISÉ : fail2ban détecte les comportements suspects et bloque les IP automatiquement.
        </div>

        <h3>Installation et configuration de fail2ban</h3>
        <div class="code-block">
<span class="comment"># Installation</span>
<span class="secure">sudo apt install fail2ban</span>

<span class="comment"># Copier la config par défaut</span>
<span class="secure">sudo cp /etc/fail2ban/jail.conf /etc/fail2ban/jail.local</span>
        </div>

        <hr style="margin:20px 0;">

        <h3>Configuration : protection SSH</h3>
        <div class="code-block">
<span class="comment"># /etc/fail2ban/jail.local</span>
<span class="secure">[DEFAULT]
bantime  = 3600      # Durée du ban : 1 heure
findtime = 600       # Fenêtre d'observation : 10 minutes
maxretry = 5         # Nombre de tentatives avant ban
banaction = iptables-multiport

[sshd]
enabled  = true
port     = ssh
filter   = sshd
logpath  = /var/log/auth.log
maxretry = 3         # Ban après 3 échecs SSH</span>
        </div>

        <hr style="margin:20px 0;">

        <h3>Configuration : protection Nginx</h3>
        <div class="code-block">
<span class="comment"># /etc/fail2ban/jail.local — Protection HTTP</span>
<span class="secure">[nginx-http-auth]
enabled  = true
port     = http,https
filter   = nginx-http-auth
logpath  = /var/log/nginx/error.log

[nginx-botsearch]
enabled  = true
port     = http,https
filter   = nginx-botsearch
logpath  = /var/log/nginx/access.log
maxretry = 2

[nginx-limit-req]
enabled  = true
port     = http,https
filter   = nginx-limit-req
logpath  = /var/log/nginx/error.log
maxretry = 5</span>
        </div>

        <hr style="margin:20px 0;">

        <h3>Filtre personnalisé : brute-force login PHP</h3>
        <div class="code-block">
<span class="comment"># /etc/fail2ban/filter.d/php-login.conf</span>
<span class="secure">[Definition]
failregex = ^&lt;HOST&gt; .* "POST /login\.php.*" (401|403)
            ^&lt;HOST&gt; .* "POST /login\.php.*" 200.*"failed_login"</span>

<span class="comment"># /etc/fail2ban/jail.local</span>
<span class="secure">[php-login]
enabled  = true
port     = http,https
filter   = php-login
logpath  = /var/log/nginx/access.log
maxretry = 5         # 5 tentatives en 10 min → ban 1h
findtime = 600
bantime  = 3600</span>
        </div>

        <hr style="margin:20px 0;">

        <h3>Commandes fail2ban utiles</h3>
        <div class="code-block">
<span class="comment"># Voir le statut de tous les jails</span>
<span class="secure">sudo fail2ban-client status</span>

<span class="comment"># Voir les IP bannies d'un jail</span>
<span class="secure">sudo fail2ban-client status nginx-limit-req</span>

<span class="comment"># Débannir une IP manuellement</span>
<span class="secure">sudo fail2ban-client set nginx-limit-req unbanip 192.168.1.100</span>

<span class="comment"># Voir les logs de fail2ban</span>
<span class="secure">sudo tail -f /var/log/fail2ban.log</span>
        </div>

        <hr style="margin:20px 0;">

        <h3>Protection Nginx complémentaire</h3>
        <div class="code-block">
<span class="comment"># nginx.conf — Rate limiting natif</span>
<span class="secure"># Définir les zones de limitation
limit_req_zone $binary_remote_addr zone=login:10m rate=5r/m;
limit_req_zone $binary_remote_addr zone=general:10m rate=30r/s;
limit_conn_zone $binary_remote_addr zone=perip:10m;

server {
    # Limiter les connexions simultanées par IP
    limit_conn perip 20;

    # Rate limit sur le login (5 req/min max)
    location /login {
        limit_req zone=login burst=3 nodelay;
        limit_req_status 429;  # Too Many Requests
    }

    # Rate limit général
    location / {
        limit_req zone=general burst=50;
    }

    # Protection Slowloris
    client_body_timeout 12;
    client_header_timeout 12;
    keepalive_timeout 15;
    send_timeout 10;
}</span>
        </div>
    </div>
</div>

<div class="card">
    <h3>Exercices</h3>
    <ol style="margin:10px 0 0 20px;">
        <li>Analysez la configuration fail2ban ci-dessus : combien de tentatives de login avant d'être banni ?</li>
        <li>Quel est l'avantage de fail2ban par rapport au rate limiting Nginx seul ?</li>
        <li>Pourquoi la protection DDoS volumétrique ne peut pas être gérée au niveau du serveur ?</li>
        <li><strong>Défi :</strong> Écrivez un filtre fail2ban qui détecte les tentatives d'injection SQL dans les logs d'accès Nginx</li>
    </ol>
</div>

<a href="index.php" class="btn btn-primary">← Retour au chapitre 4</a>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
