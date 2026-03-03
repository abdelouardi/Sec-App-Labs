<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="page-header">
    <h2>TP10 - Server-Side Request Forgery (SSRF)</h2>
    <p>Manipuler l'application pour effectuer des requêtes vers des ressources internes non autorisées.</p>
</div>

<div class="card">
    <div class="tabs">
        <button class="tab active" data-tab="vuln">Version Vulnérable</button>
        <button class="tab secure" data-tab="secure">Version Sécurisée</button>
    </div>

    <!-- VERSION VULNÉRABLE -->
    <div id="vuln" class="tab-content active">
        <div class="alert alert-danger">
            VULNÉRABLE : L'URL fournie par l'utilisateur est utilisée directement sans validation.
        </div>

        <h3>Prévisualisation d'URL (fetch côté serveur)</h3>
        <form method="POST">
            <input type="hidden" name="mode" value="vulnerable">
            <div class="form-group">
                <label>URL à prévisualiser :</label>
                <input type="text" name="url" value="<?= e($_POST['url'] ?? '') ?>"
                       placeholder="https://example.com">
            </div>
            <button type="submit" class="btn btn-danger">Charger (vulnérable)</button>
        </form>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['mode'] ?? '') === 'vulnerable' && !empty($_POST['url'])) {
            $url = $_POST['url'];

            echo '<div class="code-block" style="margin-top:15px;">';
            echo '<span class="comment">// Requête effectuée par le serveur :</span>' . "\n";
            echo '<span class="vulnerable">file_get_contents("' . htmlspecialchars($url) . '")</span>';
            echo '</div>';

            // Simulation du résultat (on ne fait pas vraiment la requête pour la sécurité du TP)
            $dangerousUrls = [
                'http://localhost' => true,
                'http://127.0.0.1' => true,
                'http://169.254.169.254' => true,
                'http://0.0.0.0' => true,
                'file:///etc/passwd' => true,
            ];

            $isDangerous = false;
            foreach ($dangerousUrls as $dangerous => $v) {
                if (str_starts_with($url, $dangerous)) {
                    $isDangerous = true;
                    break;
                }
            }

            if ($isDangerous) {
                echo '<div class="alert alert-danger" style="margin-top:10px;">';
                echo '<strong>SSRF détectée !</strong> Cette URL cible une ressource interne/sensible.<br>';

                if (str_contains($url, '169.254.169.254')) {
                    echo '<br>Résultat simulé (métadonnées cloud AWS) :';
                    echo '<div class="code-block"><span class="vulnerable">{
  "instanceId": "i-1234567890abcdef0",
  "region": "eu-west-1",
  "securityCredentials": {
    "AccessKeyId": "AKIAIOSFODNN7EXAMPLE",
    "SecretAccessKey": "wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY",
    "Token": "AQoDYXdzEJr..."
  }
}</span></div>';
                } elseif (str_contains($url, 'etc/passwd')) {
                    echo '<br>Résultat simulé (fichier système) :';
                    echo '<div class="code-block"><span class="vulnerable">root:x:0:0:root:/root:/bin/bash
daemon:x:1:1:daemon:/usr/sbin:/usr/sbin/nologin
www-data:x:33:33:www-data:/var/www:/usr/sbin/nologin
mysql:x:27:27:MySQL Server:/var/lib/mysql:/bin/false</span></div>';
                } else {
                    echo '<br>Le serveur accède à des ressources internes qui ne devraient pas être accessibles !';
                }
                echo '</div>';
            } else {
                echo '<div class="result-box" style="margin-top:10px;">';
                echo '<p>Contenu récupéré depuis : <code>' . e($url) . '</code></p>';
                echo '<p style="color:var(--gray);">(Simulation - le contenu serait affiché ici)</p>';
                echo '</div>';
            }
        }
        ?>

        <div class="explanation" style="margin-top:20px;">
            <h4>Payloads SSRF à tester :</h4>
            <ul style="margin:10px 0 0 20px;">
                <li><code>http://localhost/admin</code> — Accès aux services internes</li>
                <li><code>http://127.0.0.1:3306</code> — Scanner les ports internes</li>
                <li><code>http://169.254.169.254/latest/meta-data/</code> — Métadonnées cloud AWS</li>
                <li><code>file:///etc/passwd</code> — Lire des fichiers locaux</li>
                <li><code>http://192.168.1.1</code> — Accéder au réseau interne</li>
            </ul>
        </div>
    </div>

    <!-- VERSION SÉCURISÉE -->
    <div id="secure" class="tab-content">
        <div class="alert alert-success">
            SÉCURISÉ : Validation stricte des URL avec whitelist et blocage des adresses internes.
        </div>

        <h3>Prévisualisation d'URL (sécurisée)</h3>
        <form method="POST">
            <input type="hidden" name="mode" value="secure">
            <div class="form-group">
                <label>URL à prévisualiser :</label>
                <input type="text" name="url_safe" value="<?= e($_POST['url_safe'] ?? '') ?>"
                       placeholder="https://example.com">
            </div>
            <button type="submit" class="btn btn-success">Charger (sécurisé)</button>
        </form>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['mode'] ?? '') === 'secure' && !empty($_POST['url_safe'])) {
            $url = $_POST['url_safe'];
            $errors = [];

            // 1. Valider le format de l'URL
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                $errors[] = "URL invalide.";
            }

            // 2. Vérifier le protocole (HTTPS uniquement)
            $scheme = parse_url($url, PHP_URL_SCHEME);
            if (!in_array($scheme, ['http', 'https'])) {
                $errors[] = "Protocole non autorisé : seuls HTTP et HTTPS sont acceptés.";
            }

            // 3. Résoudre le nom d'hôte et vérifier l'IP
            $host = parse_url($url, PHP_URL_HOST);
            if ($host) {
                $ip = gethostbyname($host);
                $privateRanges = [
                    '127.0.0.0/8',      // Loopback
                    '10.0.0.0/8',       // Privé classe A
                    '172.16.0.0/12',    // Privé classe B
                    '192.168.0.0/16',   // Privé classe C
                    '169.254.0.0/16',   // Link-local (métadonnées cloud)
                    '0.0.0.0/8',        // Réseau actuel
                ];

                foreach ($privateRanges as $range) {
                    list($subnet, $mask) = explode('/', $range);
                    if ((ip2long($ip) & ~((1 << (32 - $mask)) - 1)) === ip2long($subnet)) {
                        $errors[] = "Adresse IP privée/interne bloquée : $ip ($range)";
                        break;
                    }
                }
            }

            // 4. Whitelist de domaines autorisés (optionnel)
            $allowedDomains = ['example.com', 'httpbin.org', 'jsonplaceholder.typicode.com'];

            if (!empty($errors)) {
                echo '<div class="alert alert-danger" style="margin-top:15px;">';
                echo '<strong>Requête bloquée !</strong><ul style="margin:5px 0 0 20px;">';
                foreach ($errors as $err) {
                    echo '<li>' . e($err) . '</li>';
                }
                echo '</ul></div>';
            } else {
                echo '<div class="alert alert-success" style="margin-top:15px;">';
                echo 'URL validée : <code>' . e($url) . '</code> → IP: <code>' . e($ip) . '</code> (externe, autorisée)';
                echo '</div>';
            }
        }
        ?>

        <div class="code-block" style="margin-top:15px;">
<span class="comment">// Validation sécurisée d'URL</span>
<span class="secure">function isUrlSafe($url) {
    // 1. Valider le format
    if (!filter_var($url, FILTER_VALIDATE_URL))
        return false;

    // 2. Protocole autorisé uniquement
    $scheme = parse_url($url, PHP_URL_SCHEME);
    if (!in_array($scheme, ['http', 'https']))
        return false;

    // 3. Résoudre et vérifier l'IP
    $host = parse_url($url, PHP_URL_HOST);
    $ip = gethostbyname($host);

    // 4. Bloquer les IP privées/internes
    if (isPrivateIP($ip)) return false;

    return true;
}</span>
        </div>

        <h3 style="margin-top:20px;">Mesures de protection SSRF</h3>
        <ul style="margin:10px 0 0 20px;">
            <li>Valider et assainir toutes les URL fournies par l'utilisateur</li>
            <li>Utiliser une whitelist de domaines/IP autorisés</li>
            <li>Bloquer les adresses IP privées et de loopback</li>
            <li>Bloquer les protocoles dangereux (file://, gopher://, dict://)</li>
            <li>Utiliser un proxy dédié pour les requêtes sortantes</li>
            <li>Limiter les ports autorisés (80, 443)</li>
            <li>Implémenter un timeout et une limite de taille de réponse</li>
        </ul>
    </div>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
