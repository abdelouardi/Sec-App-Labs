<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="page-header">
    <h2>Chapitre 3 — Sécurité du réseau</h2>
    <p>Protocoles TLS/HTTPS, WS-Security, et cryptographie appliquée.</p>
</div>

<div class="chapter-nav">
    <a href="ex1-tls.php" class="tp-card">
        <span class="tp-number">1</span>
        <h3>Analyse TLS avec Wireshark</h3>
        <p>Observer le handshake TLS, les versions de protocole et les certificats.</p>
        <span class="duration">30 min</span>
    </a>
    <a href="ex2-https.php" class="tp-card">
        <span class="tp-number">2</span>
        <h3>Configuration HTTPS</h3>
        <p>Mise en place de HTTPS avec Let's Encrypt et configuration sécurisée.</p>
        <span class="duration">35 min</span>
    </a>
    <a href="ex3-crypto.php" class="tp-card">
        <span class="tp-number">3</span>
        <h3>Cryptographie pratique</h3>
        <p>Chiffrement symétrique (AES), asymétrique (RSA) et hachage en PHP.</p>
        <span class="duration">25 min</span>
    </a>
</div>

<div class="card" style="margin-top:30px;">
    <h3>Cours théorique — Résumé (1h30)</h3>

    <div class="explanation">
        <h4>Pourquoi chiffrer les communications ?</h4>
        <p>Sans chiffrement (HTTP), n'importe qui sur le même réseau peut <strong>lire</strong> et <strong>modifier</strong> le trafic. C'est comme envoyer une carte postale : tout le monde peut la lire en route.</p>
    </div>

    <h4 style="margin-top:20px;">Comparaison HTTP vs HTTPS</h4>
    <table>
        <tr><th></th><th>HTTP</th><th>HTTPS (TLS)</th></tr>
        <tr><td><strong>Confidentialité</strong></td><td><span class="badge badge-vuln">Non</span></td><td><span class="badge badge-secure">Oui</span></td></tr>
        <tr><td><strong>Intégrité</strong></td><td><span class="badge badge-vuln">Non</span></td><td><span class="badge badge-secure">Oui</span></td></tr>
        <tr><td><strong>Authentification serveur</strong></td><td><span class="badge badge-vuln">Non</span></td><td><span class="badge badge-secure">Oui (certificat)</span></td></tr>
        <tr><td><strong>Port par défaut</strong></td><td>80</td><td>443</td></tr>
    </table>

    <h4 style="margin-top:20px;">Types de cryptographie</h4>
    <table>
        <tr><th>Type</th><th>Principe</th><th>Algorithmes</th><th>Usage</th></tr>
        <tr>
            <td><strong>Symétrique</strong></td>
            <td>Même clé pour chiffrer et déchiffrer</td>
            <td>AES-256, ChaCha20</td>
            <td>Chiffrement des données</td>
        </tr>
        <tr>
            <td><strong>Asymétrique</strong></td>
            <td>Clé publique + clé privée</td>
            <td>RSA, Ed25519, ECDSA</td>
            <td>Échange de clés, signatures</td>
        </tr>
        <tr>
            <td><strong>Hachage</strong></td>
            <td>Empreinte irréversible</td>
            <td>SHA-256, bcrypt, Argon2</td>
            <td>Mots de passe, intégrité</td>
        </tr>
    </table>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
