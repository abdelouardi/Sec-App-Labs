<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="page-header">
    <h2>TP7 - Mauvaise configuration de sécurité</h2>
    <p>Configurations par défaut non sécurisées, messages d'erreur verbeux, en-têtes de sécurité manquants.</p>
</div>

<div class="card">
    <div class="tabs">
        <button class="tab active" data-tab="vuln">Version Vulnérable</button>
        <button class="tab secure" data-tab="secure">Version Sécurisée</button>
    </div>

    <!-- VERSION VULNÉRABLE -->
    <div id="vuln" class="tab-content active">
        <div class="alert alert-danger">
            VULNÉRABLE : Erreurs détaillées, informations serveur exposées, en-têtes manquants.
        </div>

        <!-- 1. Messages d'erreur verbeux -->
        <h3>1. Messages d'erreur verbeux</h3>
        <p>Cliquez pour déclencher une erreur qui révèle des informations sensibles :</p>
        <a href="?mode=vulnerable&action=error" class="btn btn-sm btn-danger">Déclencher une erreur</a>

        <?php if (($_GET['action'] ?? '') === 'error' && ($_GET['mode'] ?? '') === 'vulnerable'): ?>
        <div class="code-block" style="margin-top:10px;">
<span class="vulnerable">Fatal error: Uncaught PDOException: SQLSTATE[42S02]:
Base table or view not found: 1146
Table 'owasp_tp.nonexistent_table' doesn't exist
in /var/www/html/tp/tp07-misconfig/index.php on line 42

Stack trace:
#0 /var/www/html/tp/tp07-misconfig/index.php(42):
   PDO->query('SELECT * FROM n...')
#1 /var/www/html/includes/router.php(15):
   include('/var/www/html/t...')
#2 {main}

Server: Apache/2.4.52 (Ubuntu)
PHP Version: 8.1.2
MySQL: 8.0.32
Document Root: /var/www/html
Server IP: 192.168.1.100</span>
        </div>
        <div class="alert alert-danger">
            Cette erreur révèle : le chemin du serveur, la version PHP, la version MySQL, la structure du code !
        </div>
        <?php endif; ?>

        <hr style="margin:20px 0;">

        <!-- 2. Directory listing -->
        <h3>2. Directory Listing activé</h3>
        <div class="code-block">
<span class="vulnerable">Index of /uploads/

Name                Last modified      Size
─────────────────────────────────────────────
backup_db.sql       2024-01-15 10:30   2.4M
config.php.bak      2024-01-10 08:15   1.2K
.env                2024-01-08 14:20   0.5K
id_rsa.pem          2024-01-05 09:00   1.7K</span>
        </div>
        <div class="alert alert-danger">Le listing de répertoire expose des fichiers sensibles !</div>

        <hr style="margin:20px 0;">

        <!-- 3. En-têtes HTTP manquants -->
        <h3>3. En-têtes de sécurité HTTP</h3>
        <?php
        $headers = [
            'X-Frame-Options' => ['status' => 'absent', 'risk' => 'Clickjacking possible'],
            'X-Content-Type-Options' => ['status' => 'absent', 'risk' => 'MIME sniffing possible'],
            'X-XSS-Protection' => ['status' => 'absent', 'risk' => 'Protection XSS du navigateur désactivée'],
            'Strict-Transport-Security' => ['status' => 'absent', 'risk' => 'Pas de forçage HTTPS'],
            'Content-Security-Policy' => ['status' => 'absent', 'risk' => 'Pas de restriction des sources de contenu'],
            'Referrer-Policy' => ['status' => 'absent', 'risk' => 'Le referrer peut fuiter des informations'],
        ];
        ?>
        <table>
            <tr><th>En-tête</th><th>Statut</th><th>Risque</th></tr>
            <?php foreach ($headers as $name => $info): ?>
            <tr>
                <td><code><?= $name ?></code></td>
                <td><span class="badge badge-vuln">Absent</span></td>
                <td><?= $info['risk'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <hr style="margin:20px 0;">

        <!-- 4. phpinfo exposé -->
        <h3>4. phpinfo() exposé publiquement</h3>
        <div class="code-block">
<span class="comment">// fichier phpinfo.php accessible publiquement</span>
<span class="vulnerable">&lt;?php phpinfo(); ?&gt;</span>
<span class="comment">// Révèle : version PHP, extensions, variables d'environnement,
// chemins serveur, configuration, etc.</span>
        </div>

        <hr style="margin:20px 0;">

        <!-- 5. Credentials par défaut -->
        <h3>5. Identifiants par défaut</h3>
        <div class="code-block">
<span class="vulnerable">// Config non modifiée après installation
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=owasp_tp

// Panel d'admin
ADMIN_USER=admin
ADMIN_PASS=admin123</span>
        </div>
    </div>

    <!-- VERSION SÉCURISÉE -->
    <div id="secure" class="tab-content">
        <div class="alert alert-success">
            SÉCURISÉ : Erreurs génériques, en-têtes de sécurité configurés, informations serveur masquées.
        </div>

        <h3>1. Gestion d'erreurs sécurisée</h3>
        <a href="?mode=secure&action=error" class="btn btn-sm btn-success">Déclencher une erreur</a>

        <?php if (($_GET['action'] ?? '') === 'error' && ($_GET['mode'] ?? '') === 'secure'): ?>
        <div class="alert alert-warning" style="margin-top:10px;">
            Une erreur est survenue. Veuillez réessayer plus tard. (Réf: ERR-2024-001542)
        </div>
        <p style="color:var(--gray);font-size:0.9rem;">L'erreur détaillée est loguée côté serveur, pas exposée au client.</p>
        <?php endif; ?>

        <hr style="margin:20px 0;">

        <h3>2. Directory Listing désactivé</h3>
        <div class="code-block">
<span class="comment"># .htaccess - désactiver le listing</span>
<span class="secure">Options -Indexes</span>

<span class="comment"># Ou dans httpd.conf / nginx.conf</span>
<span class="secure">autoindex off;</span>
        </div>

        <hr style="margin:20px 0;">

        <h3>3. En-têtes de sécurité HTTP configurés</h3>
        <table>
            <tr><th>En-tête</th><th>Valeur</th><th>Protection</th></tr>
            <tr>
                <td><code>X-Frame-Options</code></td>
                <td><span class="badge badge-secure">DENY</span></td>
                <td>Bloque le clickjacking</td>
            </tr>
            <tr>
                <td><code>X-Content-Type-Options</code></td>
                <td><span class="badge badge-secure">nosniff</span></td>
                <td>Empêche le MIME sniffing</td>
            </tr>
            <tr>
                <td><code>Strict-Transport-Security</code></td>
                <td><span class="badge badge-secure">max-age=31536000</span></td>
                <td>Force HTTPS</td>
            </tr>
            <tr>
                <td><code>Content-Security-Policy</code></td>
                <td><span class="badge badge-secure">default-src 'self'</span></td>
                <td>Restreint les sources</td>
            </tr>
            <tr>
                <td><code>Referrer-Policy</code></td>
                <td><span class="badge badge-secure">no-referrer</span></td>
                <td>Protège le referrer</td>
            </tr>
        </table>

        <div class="code-block" style="margin-top:15px;">
<span class="comment">// PHP - Ajouter les en-têtes</span>
<span class="secure">header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Strict-Transport-Security: max-age=31536000');
header("Content-Security-Policy: default-src 'self'");
header('Referrer-Policy: no-referrer');</span>

<span class="comment">// PHP - Gestion d'erreurs en production</span>
<span class="secure">ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/php/errors.log');</span>
        </div>

        <hr style="margin:20px 0;">

        <h3>4. Checklist de configuration sécurisée</h3>
        <ul style="margin:10px 0 0 20px;">
            <li>Désactiver <code>display_errors</code> en production</li>
            <li>Supprimer <code>phpinfo.php</code> et fichiers de test</li>
            <li>Changer tous les identifiants par défaut</li>
            <li>Désactiver le directory listing</li>
            <li>Supprimer les en-têtes <code>Server</code> et <code>X-Powered-By</code></li>
            <li>Configurer les en-têtes de sécurité HTTP</li>
            <li>Désactiver les méthodes HTTP inutiles (TRACE, OPTIONS)</li>
        </ul>
    </div>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
