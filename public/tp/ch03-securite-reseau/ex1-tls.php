<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="page-header">
    <h2>Ch3 — Ex1 : Analyse TLS avec Wireshark</h2>
    <p>Observer le handshake TLS, comprendre les versions et vérifier les certificats.</p>
    <span class="duration">30 min</span>
</div>

<div class="card">
    <h3>Le Handshake TLS en détail</h3>
    <div class="code-block">
<span class="comment">// Le handshake TLS 1.3 (simplifié)</span>

Client                                    Serveur
  │                                          │
  │──── <span class="secure">ClientHello</span> ──────────────────────→│  Versions TLS, cipher suites
  │                                          │
  │←─── <span class="secure">ServerHello</span> ──────────────────────│  Version choisie, cipher suite
  │←─── <span class="secure">Certificate</span> ──────────────────────│  Certificat du serveur
  │←─── <span class="secure">ServerKeyExchange</span> ────────────────│  Clé publique éphémère
  │←─── <span class="secure">ServerHelloDone</span> ──────────────────│
  │                                          │
  │──── <span class="secure">ClientKeyExchange</span> ────────────────→│  Clé publique éphémère client
  │──── <span class="secure">ChangeCipherSpec</span> ─────────────────→│  "On passe au chiffré"
  │──── <span class="secure">Finished</span> ─────────────────────────→│
  │                                          │
  │←─── <span class="secure">ChangeCipherSpec</span> ─────────────────│
  │←─── <span class="secure">Finished</span> ─────────────────────────│
  │                                          │
  │◄═══ <span class="secure">Trafic chiffré (AES-256-GCM)</span> ═══►│
    </div>
</div>

<div class="card">
    <h3>Exercice pratique : capturer du trafic avec Wireshark</h3>

    <div class="alert alert-info">
        <strong>Prérequis :</strong> Installer <a href="https://www.wireshark.org/download.html" target="_blank">Wireshark</a> (gratuit)
    </div>

    <h4>Étape 1 : Capturer du trafic HTTP (non chiffré)</h4>
    <ol style="margin:10px 0 0 20px;">
        <li>Lancez Wireshark et sélectionnez l'interface <strong>Loopback (lo0)</strong></li>
        <li>Filtrez avec : <code>tcp.port == 8080</code></li>
        <li>Naviguez sur <code>http://localhost:8080</code></li>
        <li>Observez : les requêtes et réponses HTTP sont en <strong>clair</strong></li>
    </ol>

    <div class="code-block">
<span class="comment">// Ce que Wireshark montre en HTTP (tout est lisible)</span>
<span class="vulnerable">GET /login.php HTTP/1.1
Host: localhost:8080
Cookie: PHPSESSID=abc123def456

POST /login.php HTTP/1.1
Content-Type: application/x-www-form-urlencoded

username=admin&password=password123</span>
<span class="comment">// ↑ Le mot de passe est visible en clair !</span>
    </div>

    <hr style="margin:20px 0;">

    <h4>Étape 2 : Observer du trafic HTTPS (chiffré)</h4>
    <ol style="margin:10px 0 0 20px;">
        <li>Naviguez sur un site HTTPS (ex: <code>https://www.google.com</code>)</li>
        <li>Filtrez avec : <code>tls</code></li>
        <li>Observez le handshake TLS : ClientHello, ServerHello, Certificate...</li>
        <li>Après le handshake, les données sont <strong>illisibles</strong> (Application Data)</li>
    </ol>

    <div class="code-block">
<span class="comment">// Ce que Wireshark montre en HTTPS</span>
<span class="secure">TLSv1.3  ClientHello
    Version: TLS 1.3
    Cipher Suites:
      TLS_AES_256_GCM_SHA384
      TLS_CHACHA20_POLY1305_SHA256
      TLS_AES_128_GCM_SHA256

TLSv1.3  ServerHello
    Cipher Suite: TLS_AES_256_GCM_SHA384

TLSv1.3  Application Data
    Encrypted Application Data: 7a3f9b2c8e1d... (illisible !)</span>
    </div>
</div>

<div class="card">
    <h3>Versions TLS et sécurité</h3>
    <table>
        <tr><th>Version</th><th>Année</th><th>Statut</th><th>Vulnérabilités</th></tr>
        <tr>
            <td>SSL 2.0</td><td>1995</td>
            <td><span class="badge badge-vuln">Obsolète</span></td>
            <td>Multiples failles critiques</td>
        </tr>
        <tr>
            <td>SSL 3.0</td><td>1996</td>
            <td><span class="badge badge-vuln">Obsolète</span></td>
            <td>POODLE (CVE-2014-3566)</td>
        </tr>
        <tr>
            <td>TLS 1.0</td><td>1999</td>
            <td><span class="badge badge-vuln">Déprécié</span></td>
            <td>BEAST, CRIME</td>
        </tr>
        <tr>
            <td>TLS 1.1</td><td>2006</td>
            <td><span class="badge badge-vuln">Déprécié</span></td>
            <td>Cipher suites faibles</td>
        </tr>
        <tr>
            <td>TLS 1.2</td><td>2008</td>
            <td><span class="badge badge-secure">OK (si bien configuré)</span></td>
            <td>Dépend de la configuration</td>
        </tr>
        <tr>
            <td>TLS 1.3</td><td>2018</td>
            <td><span class="badge badge-secure">Recommandé</span></td>
            <td>Aucune connue, handshake plus rapide</td>
        </tr>
    </table>
</div>

<div class="card">
    <h3>Vérifier le certificat TLS d'un site</h3>
    <div class="code-block">
<span class="comment"># Commande terminal pour inspecter un certificat</span>
<span class="secure">openssl s_client -connect www.google.com:443 -servername www.google.com 2>/dev/null | openssl x509 -noout -text</span>

<span class="comment"># Voir la date d'expiration</span>
<span class="secure">echo | openssl s_client -connect www.google.com:443 2>/dev/null | openssl x509 -noout -dates</span>

<span class="comment"># Tester les protocoles TLS supportés</span>
<span class="secure">nmap --script ssl-enum-ciphers -p 443 www.google.com</span>
    </div>

    <h4 style="margin-top:15px;">Exercices</h4>
    <ol style="margin:10px 0 0 20px;">
        <li>Lancez Wireshark et capturez le trafic pendant que vous naviguez sur <code>http://localhost:8080</code></li>
        <li>Identifiez les requêtes HTTP et lisez les cookies/données en clair</li>
        <li>Capturez du trafic HTTPS vers un site externe et observez le handshake TLS</li>
        <li>Utilisez <code>openssl s_client</code> pour inspecter le certificat d'un site</li>
    </ol>
</div>

<a href="index.php" class="btn btn-primary">← Retour au chapitre 3</a>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
