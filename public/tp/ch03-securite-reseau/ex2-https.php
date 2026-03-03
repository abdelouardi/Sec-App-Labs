<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="page-header">
    <h2>Ch3 — Ex2 : Configuration HTTPS avec Let's Encrypt</h2>
    <p>Mise en place de HTTPS et configuration sécurisée du serveur web.</p>
    <span class="duration">35 min</span>
</div>

<div class="card">
    <div class="tabs">
        <button class="tab active" data-tab="vuln">Sans HTTPS</button>
        <button class="tab secure" data-tab="secure">Avec HTTPS</button>
    </div>

    <!-- SANS HTTPS -->
    <div id="vuln" class="tab-content active">
        <div class="alert alert-danger">
            VULNÉRABLE : Trafic en clair, interception possible sur tout le réseau.
        </div>

        <h3>Scénario d'attaque : Man-in-the-Middle</h3>
        <div class="code-block">
<span class="comment">// Sur un Wi-Fi public (café, aéroport, hôtel)...</span>

Utilisateur ──<span class="vulnerable"> HTTP (clair) </span>──→ Wi-Fi ──→ Internet ──→ Serveur
                    ↑
               <span class="vulnerable">Attaquant</span>
           (même réseau Wi-Fi)
           Lit : mots de passe, cookies,
                 données personnelles, emails

<span class="comment">// Outils utilisés : Wireshark, mitmproxy, Bettercap</span>
        </div>

        <h4 style="margin-top:20px;">Ce que l'attaquant voit</h4>
        <div class="code-block">
<span class="vulnerable">POST /login.php HTTP/1.1
Host: site.com
Content-Type: application/x-www-form-urlencoded

username=alice&password=MonMotDePasse2024!
Cookie: PHPSESSID=5e8a3f2b1c9d</span>

<span class="comment">// Tout est lisible : identifiants, cookies, données...</span>
        </div>

        <h4 style="margin-top:20px;">Attaques possibles</h4>
        <table>
            <tr><th>Attaque</th><th>Description</th></tr>
            <tr><td><strong>Écoute passive</strong></td><td>Lire le trafic (mots de passe, cookies)</td></tr>
            <tr><td><strong>Injection</strong></td><td>Modifier les réponses HTTP (injecter du JS malveillant)</td></tr>
            <tr><td><strong>SSL Stripping</strong></td><td>Forcer la connexion en HTTP au lieu de HTTPS</td></tr>
            <tr><td><strong>Session Hijacking</strong></td><td>Voler le cookie de session pour usurper l'identité</td></tr>
        </table>
    </div>

    <!-- AVEC HTTPS -->
    <div id="secure" class="tab-content">
        <div class="alert alert-success">
            SÉCURISÉ : Trafic chiffré, certificat vérifié, intégrité garantie.
        </div>

        <h3>Étape 1 : Obtenir un certificat avec Let's Encrypt</h3>
        <div class="code-block">
<span class="comment"># Installer Certbot</span>
<span class="secure">sudo apt install certbot python3-certbot-nginx</span>

<span class="comment"># Obtenir un certificat (automatique)</span>
<span class="secure">sudo certbot --nginx -d monsite.com -d www.monsite.com</span>

<span class="comment"># Renouvellement automatique (Let's Encrypt expire tous les 90 jours)</span>
<span class="secure">sudo certbot renew --dry-run</span>

<span class="comment"># Cron pour renouvellement automatique</span>
<span class="secure">0 0 1 * * certbot renew --post-hook "systemctl reload nginx"</span>
        </div>

        <hr style="margin:20px 0;">

        <h3>Étape 2 : Configuration Nginx sécurisée</h3>
        <div class="code-block">
<span class="comment"># /etc/nginx/sites-available/monsite.conf</span>

<span class="comment"># Redirection HTTP → HTTPS</span>
<span class="secure">server {
    listen 80;
    server_name monsite.com www.monsite.com;
    return 301 https://$server_name$request_uri;
}</span>

<span class="secure">server {
    listen 443 ssl http2;
    server_name monsite.com www.monsite.com;

    # Certificat Let's Encrypt
    ssl_certificate     /etc/letsencrypt/live/monsite.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/monsite.com/privkey.pem;

    # Protocoles TLS (désactiver les anciens)
    ssl_protocols TLSv1.2 TLSv1.3;

    # Cipher suites sécurisées
    ssl_ciphers 'ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384';
    ssl_prefer_server_ciphers on;

    # HSTS (force HTTPS pendant 1 an)
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    # OCSP Stapling (vérification du certificat)
    ssl_stapling on;
    ssl_stapling_verify on;
    resolver 1.1.1.1 8.8.8.8;

    root /var/www/monsite/public;
    index index.php;
}</span>
        </div>

        <hr style="margin:20px 0;">

        <h3>Étape 3 : Configuration Apache sécurisée</h3>
        <div class="code-block">
<span class="comment"># /etc/apache2/sites-available/monsite-ssl.conf</span>
<span class="secure">&lt;VirtualHost *:443&gt;
    ServerName monsite.com

    SSLEngine on
    SSLCertificateFile    /etc/letsencrypt/live/monsite.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/monsite.com/privkey.pem

    SSLProtocol           all -SSLv3 -TLSv1 -TLSv1.1
    SSLHonorCipherOrder   on

    Header always set Strict-Transport-Security "max-age=31536000"
&lt;/VirtualHost&gt;</span>
        </div>

        <hr style="margin:20px 0;">

        <h3>Vérification</h3>
        <div class="code-block">
<span class="comment"># Tester la configuration SSL de votre site</span>
<span class="secure"># → https://www.ssllabs.com/ssltest/</span>

<span class="comment"># Ou en ligne de commande :</span>
<span class="secure">curl -I https://monsite.com</span>
<span class="comment"># Vérifier que Strict-Transport-Security est présent</span>

<span class="secure">openssl s_client -connect monsite.com:443 -tls1_3</span>
<span class="comment"># Vérifier que TLS 1.3 fonctionne</span>
        </div>
    </div>
</div>

<div class="card">
    <h3>Exercices</h3>
    <ol style="margin:10px 0 0 20px;">
        <li>Allez sur <a href="https://www.ssllabs.com/ssltest/" target="_blank">SSL Labs</a> et testez un site (ex: google.com)</li>
        <li>Observez le grade obtenu (A, B, C...) et les protocoles TLS supportés</li>
        <li>En terminal : <code>openssl s_client -connect google.com:443</code> → identifiez la version TLS</li>
        <li><strong>Défi :</strong> Vérifiez si un site utilise HSTS en inspectant ses en-têtes de réponse (DevTools → Network)</li>
    </ol>
</div>

<a href="index.php" class="btn btn-primary">← Retour au chapitre 3</a>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
