<?php include __DIR__ . '/../../../includes/header.php';

$results = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $plaintext = $_POST['plaintext'] ?? 'Données sensibles à protéger';

    switch ($action) {
        case 'hash':
            $results['md5'] = md5($plaintext);
            $results['sha256'] = hash('sha256', $plaintext);
            $results['bcrypt'] = password_hash($plaintext, PASSWORD_BCRYPT);
            $results['bcrypt2'] = password_hash($plaintext, PASSWORD_BCRYPT);
            break;

        case 'aes':
            $key = random_bytes(32); // AES-256
            $iv = random_bytes(16);  // Vecteur d'initialisation
            $encrypted = openssl_encrypt($plaintext, 'aes-256-cbc', $key, 0, $iv);
            $decrypted = openssl_decrypt($encrypted, 'aes-256-cbc', $key, 0, $iv);
            $results['aes_key'] = bin2hex($key);
            $results['aes_iv'] = bin2hex($iv);
            $results['aes_encrypted'] = $encrypted;
            $results['aes_decrypted'] = $decrypted;
            break;

        case 'rsa':
            $config = ['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA];
            $keypair = openssl_pkey_new($config);
            openssl_pkey_export($keypair, $privateKey);
            $pubKeyDetails = openssl_pkey_get_details($keypair);
            $publicKey = $pubKeyDetails['key'];

            openssl_public_encrypt($plaintext, $encrypted, $publicKey);
            openssl_private_decrypt($encrypted, $decrypted, $keypair);

            $results['rsa_public'] = substr($publicKey, 0, 120) . '...';
            $results['rsa_encrypted'] = base64_encode($encrypted);
            $results['rsa_decrypted'] = $decrypted;
            break;
    }
}
?>

<div class="page-header">
    <h2>Ch3 — Ex3 : Cryptographie pratique</h2>
    <p>Hachage, chiffrement symétrique (AES) et asymétrique (RSA) en PHP.</p>
    <span class="duration">25 min</span>
</div>

<div class="card">
    <div class="tabs">
        <button class="tab active" data-tab="hash">Hachage</button>
        <button class="tab" data-tab="sym">AES (Symétrique)</button>
        <button class="tab secure" data-tab="asym">RSA (Asymétrique)</button>
    </div>

    <!-- HACHAGE -->
    <div id="hash" class="tab-content active">
        <div class="alert alert-info">
            Le hachage est une transformation <strong>irréversible</strong>. On ne peut pas retrouver le texte original à partir du hash.
        </div>

        <form method="POST">
            <input type="hidden" name="action" value="hash">
            <div class="form-group">
                <label>Texte à hacher :</label>
                <input type="text" name="plaintext" value="<?= e($_POST['plaintext'] ?? 'password123') ?>">
            </div>
            <button type="submit" class="btn btn-primary">Calculer les hashs</button>
        </form>

        <?php if (isset($results['md5'])): ?>
        <table style="margin-top:15px;">
            <tr><th>Algorithme</th><th>Résultat</th><th>Sécurité</th></tr>
            <tr>
                <td><code>MD5</code></td>
                <td style="font-size:0.8rem; word-break:break-all;"><code><?= e($results['md5']) ?></code></td>
                <td><span class="badge badge-vuln">Cassé</span> — collisions possibles</td>
            </tr>
            <tr>
                <td><code>SHA-256</code></td>
                <td style="font-size:0.8rem; word-break:break-all;"><code><?= e($results['sha256']) ?></code></td>
                <td><span class="badge badge-secure">OK</span> — mais rapide (vulnérable au brute-force pour les mots de passe)</td>
            </tr>
            <tr>
                <td><code>bcrypt</code> (1)</td>
                <td style="font-size:0.8rem; word-break:break-all;"><code><?= e($results['bcrypt']) ?></code></td>
                <td><span class="badge badge-secure">Recommandé</span> — lent + salé</td>
            </tr>
            <tr>
                <td><code>bcrypt</code> (2)</td>
                <td style="font-size:0.8rem; word-break:break-all;"><code><?= e($results['bcrypt2']) ?></code></td>
                <td><span class="badge badge-secure">Différent !</span> — le sel change</td>
            </tr>
        </table>

        <div class="explanation" style="margin-top:15px;">
            <h4>Pourquoi bcrypt est différent à chaque fois ?</h4>
            <p>Bcrypt ajoute un <strong>sel</strong> (valeur aléatoire) intégré dans le hash. Le même mot de passe produit un hash différent à chaque fois, ce qui empêche les attaques par rainbow table.</p>
        </div>
        <?php endif; ?>

        <div class="code-block" style="margin-top:15px;">
<span class="comment">// PHP — Hachage de mots de passe</span>

<span class="vulnerable">// MAUVAIS : MD5 (cassé, pas de sel)
$hash = md5($password);</span>

<span class="vulnerable">// MAUVAIS : SHA-256 (trop rapide, pas de sel)
$hash = hash('sha256', $password);</span>

<span class="secure">// BON : bcrypt (lent, salé automatiquement)
$hash = password_hash($password, PASSWORD_BCRYPT);
// Vérification :
$valid = password_verify($password, $hash);</span>

<span class="secure">// MEILLEUR : Argon2id (plus récent, résistant au GPU)
$hash = password_hash($password, PASSWORD_ARGON2ID);</span>
        </div>
    </div>

    <!-- AES SYMÉTRIQUE -->
    <div id="sym" class="tab-content">
        <div class="alert alert-info">
            Chiffrement <strong>symétrique</strong> : la même clé sert à chiffrer ET déchiffrer. Comme un cadenas à code unique.
        </div>

        <form method="POST">
            <input type="hidden" name="action" value="aes">
            <div class="form-group">
                <label>Texte à chiffrer :</label>
                <input type="text" name="plaintext" value="<?= e($_POST['plaintext'] ?? 'Numéro de carte : 4532 1234 5678 9012') ?>">
            </div>
            <button type="submit" class="btn btn-primary">Chiffrer avec AES-256</button>
        </form>

        <?php if (isset($results['aes_encrypted'])): ?>
        <table style="margin-top:15px;">
            <tr><th>Élément</th><th>Valeur</th></tr>
            <tr><td>Clé AES (256 bits)</td><td style="font-size:0.75rem; word-break:break-all;"><code><?= e($results['aes_key']) ?></code></td></tr>
            <tr><td>IV (vecteur d'init.)</td><td style="font-size:0.75rem;"><code><?= e($results['aes_iv']) ?></code></td></tr>
            <tr><td>Texte chiffré</td><td><code><?= e($results['aes_encrypted']) ?></code></td></tr>
            <tr><td>Texte déchiffré</td><td><span class="badge badge-secure"><?= e($results['aes_decrypted']) ?></span></td></tr>
        </table>
        <?php endif; ?>

        <div class="code-block" style="margin-top:15px;">
<span class="comment">// PHP — Chiffrement AES-256-CBC</span>
<span class="secure">// Générer une clé aléatoire de 256 bits
$key = random_bytes(32);
$iv  = random_bytes(16); // Vecteur d'initialisation

// Chiffrer
$encrypted = openssl_encrypt($plaintext, 'aes-256-cbc', $key, 0, $iv);

// Déchiffrer
$decrypted = openssl_decrypt($encrypted, 'aes-256-cbc', $key, 0, $iv);

// IMPORTANT : stocker la clé de manière sécurisée !
// Jamais dans le code source ou la base de données</span>
        </div>
    </div>

    <!-- RSA ASYMÉTRIQUE -->
    <div id="asym" class="tab-content">
        <div class="alert alert-info">
            Chiffrement <strong>asymétrique</strong> : une clé publique pour chiffrer, une clé privée pour déchiffrer. Comme une boîte aux lettres : tout le monde peut déposer (clé publique), seul le propriétaire peut ouvrir (clé privée).
        </div>

        <form method="POST">
            <input type="hidden" name="action" value="rsa">
            <div class="form-group">
                <label>Texte à chiffrer :</label>
                <input type="text" name="plaintext" value="<?= e($_POST['plaintext'] ?? 'Message secret pour le destinataire') ?>">
            </div>
            <button type="submit" class="btn btn-success">Chiffrer avec RSA-2048</button>
        </form>

        <?php if (isset($results['rsa_encrypted'])): ?>
        <table style="margin-top:15px;">
            <tr><th>Élément</th><th>Valeur</th></tr>
            <tr><td>Clé publique (début)</td><td style="font-size:0.7rem; word-break:break-all;"><code><?= e($results['rsa_public']) ?></code></td></tr>
            <tr><td>Texte chiffré (base64)</td><td style="font-size:0.75rem; word-break:break-all;"><code><?= e(substr($results['rsa_encrypted'], 0, 80)) ?>...</code></td></tr>
            <tr><td>Texte déchiffré</td><td><span class="badge badge-secure"><?= e($results['rsa_decrypted']) ?></span></td></tr>
        </table>
        <?php endif; ?>

        <div class="code-block" style="margin-top:15px;">
<span class="comment">// PHP — Chiffrement RSA</span>
<span class="secure">// Générer une paire de clés
$config = ['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA];
$keypair = openssl_pkey_new($config);

// Extraire les clés
openssl_pkey_export($keypair, $privateKey);
$publicKey = openssl_pkey_get_details($keypair)['key'];

// Chiffrer avec la clé publique
openssl_public_encrypt($message, $encrypted, $publicKey);

// Déchiffrer avec la clé privée
openssl_private_decrypt($encrypted, $decrypted, $keypair);</span>
        </div>

        <div class="explanation" style="margin-top:15px;">
            <h4>Symétrique vs Asymétrique</h4>
            <table>
                <tr><th></th><th>Symétrique (AES)</th><th>Asymétrique (RSA)</th></tr>
                <tr><td>Vitesse</td><td><span class="badge badge-secure">Rapide</span></td><td><span class="badge badge-vuln">Lent</span></td></tr>
                <tr><td>Clés</td><td>1 clé partagée</td><td>2 clés (publique/privée)</td></tr>
                <tr><td>Usage</td><td>Chiffrement de données</td><td>Échange de clés, signatures</td></tr>
                <tr><td>TLS utilise</td><td colspan="2">Les deux ! RSA pour échanger la clé AES, puis AES pour les données</td></tr>
            </table>
        </div>
    </div>
</div>

<a href="index.php" class="btn btn-primary">← Retour au chapitre 3</a>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
