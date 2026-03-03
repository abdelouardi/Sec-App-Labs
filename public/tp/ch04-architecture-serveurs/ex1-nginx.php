<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="page-header">
    <h2>Ch4 — Ex1 : Configuration Nginx sécurisée</h2>
    <p>Reverse proxy, en-têtes de sécurité, rate limiting, et hardening du serveur web.</p>
    <span class="duration">40 min</span>
</div>

<div class="card">
    <div class="tabs">
        <button class="tab active" data-tab="vuln">Configuration vulnérable</button>
        <button class="tab secure" data-tab="secure">Configuration sécurisée</button>
    </div>

    <!-- VULNÉRABLE -->
    <div id="vuln" class="tab-content active">
        <div class="alert alert-danger">
            VULNÉRABLE : Configuration Nginx par défaut, sans protection.
        </div>

        <h3>Configuration minimale (dangereuse en production)</h3>
        <div class="code-block">
<span class="comment"># /etc/nginx/sites-available/default</span>
<span class="vulnerable">server {
    listen 80;
    server_name _;

    root /var/www/html;
    index index.php index.html;

    location / {
        try_files $uri $uri/ =404;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}</span>
        </div>

        <h4 style="margin-top:20px;">Problèmes identifiés</h4>
        <table>
            <tr><th>#</th><th>Problème</th><th>Risque</th></tr>
            <tr><td>1</td><td>Pas de HTTPS</td><td>Trafic interceptable</td></tr>
            <tr><td>2</td><td>Version Nginx exposée</td><td>Identification des CVE</td></tr>
            <tr><td>3</td><td>Pas d'en-têtes de sécurité</td><td>XSS, Clickjacking</td></tr>
            <tr><td>4</td><td>Pas de rate limiting</td><td>Brute-force, DDoS</td></tr>
            <tr><td>5</td><td>Accès aux fichiers cachés (.git, .env)</td><td>Fuite de code source et secrets</td></tr>
            <tr><td>6</td><td>Pas de limitation de taille</td><td>Upload de fichiers énormes</td></tr>
            <tr><td>7</td><td>Directory listing actif</td><td>Exposition de la structure</td></tr>
        </table>
    </div>

    <!-- SÉCURISÉE -->
    <div id="secure" class="tab-content">
        <div class="alert alert-success">
            SÉCURISÉ : Configuration Nginx hardened avec toutes les protections.
        </div>

        <h3>Configuration sécurisée complète</h3>
        <div class="code-block">
<span class="comment"># /etc/nginx/nginx.conf — Configuration globale</span>
<span class="secure">http {
    # Masquer la version de Nginx
    server_tokens off;

    # Limiter la taille des requêtes (anti-upload malveillant)
    client_max_body_size 10M;
    client_body_timeout 12;
    client_header_timeout 12;
    send_timeout 10;

    # Rate limiting (anti brute-force)
    limit_req_zone $binary_remote_addr zone=login:10m rate=5r/m;
    limit_req_zone $binary_remote_addr zone=api:10m rate=30r/m;
    limit_conn_zone $binary_remote_addr zone=addr:10m;
}</span>
        </div>

        <div class="code-block" style="margin-top:15px;">
<span class="comment"># /etc/nginx/sites-available/monsite.conf</span>
<span class="secure">server {
    listen 443 ssl http2;
    server_name monsite.com;

    # === SSL/TLS ===
    ssl_certificate     /etc/letsencrypt/live/monsite.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/monsite.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_prefer_server_ciphers on;

    # === EN-TÊTES DE SÉCURITÉ ===
    add_header X-Frame-Options "DENY" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Content-Security-Policy "default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header Permissions-Policy "camera=(), microphone=(), geolocation=()" always;

    # Supprimer les en-têtes révélateurs
    proxy_hide_header X-Powered-By;
    fastcgi_hide_header X-Powered-By;

    root /var/www/monsite/public;
    index index.php;

    # === BLOQUER LES FICHIERS SENSIBLES ===
    location ~ /\.(git|env|htaccess|htpasswd) {
        deny all;
        return 404;
    }

    location ~ \.(bak|sql|log|ini|conf)$ {
        deny all;
        return 404;
    }

    # === RATE LIMITING SUR LE LOGIN ===
    location /login {
        limit_req zone=login burst=3 nodelay;
        limit_conn addr 5;
        try_files $uri $uri/ /index.php?$query_string;
    }

    # === PHP-FPM ===
    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_intercept_errors on;
    }

    # Désactiver le directory listing
    autoindex off;

    # Empêcher l'exécution de PHP dans les dossiers uploads
    location /uploads {
        location ~ \.php$ { deny all; }
    }
}</span>
        </div>

        <h4 style="margin-top:20px;">Checklist de sécurisation Nginx</h4>
        <ul style="margin:10px 0 0 20px;">
            <li><code>server_tokens off</code> — Masquer la version</li>
            <li>HTTPS avec TLS 1.2+ uniquement</li>
            <li>En-têtes de sécurité (HSTS, CSP, X-Frame-Options...)</li>
            <li>Rate limiting sur login et API</li>
            <li>Bloquer l'accès aux fichiers sensibles (.git, .env, .bak)</li>
            <li>Limiter la taille des uploads</li>
            <li>Désactiver le directory listing</li>
            <li>Pas d'exécution PHP dans /uploads</li>
        </ul>
    </div>
</div>

<div class="card">
    <h3>Exercice pratique</h3>
    <ol style="margin:10px 0 0 20px;">
        <li>Ouvrez DevTools → Network → Cliquez sur la requête principale</li>
        <li>Cherchez l'en-tête <code>Server</code> dans les Response Headers</li>
        <li>Si vous voyez <code>Server: nginx/1.x.x</code> ou <code>X-Powered-By: PHP/8.x</code>, c'est une fuite d'information</li>
        <li>Testez l'accès aux fichiers sensibles : <code>http://localhost:8080/.env</code>, <code>http://localhost:8080/.git/config</code></li>
        <li><strong>Défi :</strong> Écrivez une configuration Nginx qui bloque plus de 10 requêtes par seconde sur l'endpoint <code>/api</code></li>
    </ol>
</div>

<a href="index.php" class="btn btn-primary">← Retour au chapitre 4</a>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
