<?php include __DIR__ . '/../../../includes/header.php';

$message = '';
$messageType = '';

// Simuler des actions pour démontrer le logging
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $mode = $_POST['mode'] ?? 'vulnerable';

    if ($mode === 'secure') {
        // VERSION SÉCURISÉE : logger les événements
        $db = getDB();
        $userId = $_SESSION['user_id'] ?? null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

        switch ($action) {
            case 'failed_login':
                $stmt = $db->prepare("INSERT INTO security_logs (event_type, user_id, ip_address, user_agent, details, severity) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute(['failed_login', null, $ip, $ua, 'Tentative de connexion échouée pour: test_user', 'warning']);
                $message = "Tentative de connexion échouée - LOGUÉE avec IP, user-agent, timestamp.";
                break;
            case 'access_denied':
                $stmt = $db->prepare("INSERT INTO security_logs (event_type, user_id, ip_address, user_agent, details, severity) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute(['access_denied', $userId, $ip, $ua, 'Tentative d\'accès à /admin sans autorisation', 'warning']);
                $message = "Accès refusé - LOGUÉ avec détails complets.";
                break;
            case 'sqli_attempt':
                $stmt = $db->prepare("INSERT INTO security_logs (event_type, user_id, ip_address, user_agent, details, severity) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute(['sqli_attempt', $userId, $ip, $ua, "Tentative d'injection SQL détectée: ' OR 1=1 --", 'critical']);
                $message = "Tentative SQLi détectée - ALERTE CRITIQUE loguée !";
                break;
            case 'data_export':
                $stmt = $db->prepare("INSERT INTO security_logs (event_type, user_id, ip_address, user_agent, details, severity) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute(['data_export', $userId, $ip, $ua, 'Export de 500 enregistrements employés', 'info']);
                $message = "Export de données - LOGUÉ pour traçabilité.";
                break;
        }
        $messageType = 'success';
    } else {
        // VERSION VULNÉRABLE : aucun logging
        switch ($action) {
            case 'failed_login':
                $message = "Tentative de connexion échouée - AUCUN LOG enregistré.";
                break;
            case 'access_denied':
                $message = "Accès refusé - AUCUN LOG enregistré.";
                break;
            case 'sqli_attempt':
                $message = "Tentative SQLi - AUCUN LOG enregistré. L'attaque passe inaperçue !";
                break;
            case 'data_export':
                $message = "Export massif de données - AUCUN LOG enregistré. Impossible de savoir qui a fait quoi.";
                break;
        }
        $messageType = 'danger';
    }
}
?>

<div class="page-header">
    <h2>TP9 - Logging et Monitoring insuffisants</h2>
    <p>Absence de journalisation rendant difficile la détection et la réponse aux incidents.</p>
</div>

<?php if ($message): ?>
<div class="alert alert-<?= $messageType ?>"><?= e($message) ?></div>
<?php endif; ?>

<div class="card">
    <div class="tabs">
        <button class="tab active" data-tab="vuln">Version Vulnérable</button>
        <button class="tab secure" data-tab="secure">Version Sécurisée</button>
    </div>

    <!-- VERSION VULNÉRABLE -->
    <div id="vuln" class="tab-content active">
        <div class="alert alert-danger">
            VULNÉRABLE : Aucun événement de sécurité n'est enregistré.
        </div>

        <h3>Simuler des événements de sécurité</h3>
        <p>Cliquez sur chaque bouton - rien ne sera enregistré :</p>
        <div style="display:flex;flex-wrap:wrap;gap:10px;margin:15px 0;">
            <form method="POST" style="display:inline;">
                <input type="hidden" name="mode" value="vulnerable">
                <input type="hidden" name="action" value="failed_login">
                <button class="btn btn-sm btn-danger">Connexion échouée</button>
            </form>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="mode" value="vulnerable">
                <input type="hidden" name="action" value="access_denied">
                <button class="btn btn-sm btn-danger">Accès non autorisé</button>
            </form>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="mode" value="vulnerable">
                <input type="hidden" name="action" value="sqli_attempt">
                <button class="btn btn-sm btn-danger">Tentative SQLi</button>
            </form>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="mode" value="vulnerable">
                <input type="hidden" name="action" value="data_export">
                <button class="btn btn-sm btn-danger">Export massif</button>
            </form>
        </div>

        <div class="code-block">
<span class="comment">// Code vulnérable - aucun logging</span>
<span class="vulnerable">if (!$user || !password_verify($pw, $user['password'])) {
    echo "Erreur de connexion";
    // Aucun log ! L'attaquant peut faire du brute-force
    // sans jamais être détecté
}</span>
        </div>

        <div class="explanation" style="margin-top:15px;">
            <h4>Conséquences :</h4>
            <ul style="margin:10px 0 0 20px;">
                <li>Impossible de détecter les attaques en cours</li>
                <li>Pas de traçabilité des actions (qui a fait quoi ?)</li>
                <li>Pas de preuves en cas d'incident</li>
                <li>Pas d'alerte en cas de comportement suspect</li>
                <li>Non-conformité RGPD (obligation de traçabilité)</li>
            </ul>
        </div>
    </div>

    <!-- VERSION SÉCURISÉE -->
    <div id="secure" class="tab-content">
        <div class="alert alert-success">
            SÉCURISÉ : Tous les événements de sécurité sont journalisés avec détails.
        </div>

        <h3>Simuler des événements de sécurité (avec logging)</h3>
        <div style="display:flex;flex-wrap:wrap;gap:10px;margin:15px 0;">
            <form method="POST" style="display:inline;">
                <input type="hidden" name="mode" value="secure">
                <input type="hidden" name="action" value="failed_login">
                <button class="btn btn-sm btn-success">Connexion échouée</button>
            </form>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="mode" value="secure">
                <input type="hidden" name="action" value="access_denied">
                <button class="btn btn-sm btn-success">Accès non autorisé</button>
            </form>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="mode" value="secure">
                <input type="hidden" name="action" value="sqli_attempt">
                <button class="btn btn-sm btn-success">Tentative SQLi</button>
            </form>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="mode" value="secure">
                <input type="hidden" name="action" value="data_export">
                <button class="btn btn-sm btn-success">Export massif</button>
            </form>
        </div>

        <h3 style="margin-top:20px;">Journal de sécurité</h3>
        <?php
        $db = getDB();
        $logs = $db->query("SELECT * FROM security_logs ORDER BY created_at DESC LIMIT 20")->fetchAll();
        ?>
        <?php if (empty($logs)): ?>
            <p style="color:var(--gray);">Aucun log enregistré. Cliquez sur les boutons ci-dessus pour générer des logs.</p>
        <?php else: ?>
            <table>
                <tr><th>Date</th><th>Type</th><th>Sévérité</th><th>IP</th><th>Détails</th></tr>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td style="font-size:0.85rem;"><?= e($log['created_at']) ?></td>
                    <td><code><?= e($log['event_type']) ?></code></td>
                    <td>
                        <?php
                        $sevClass = match($log['severity']) {
                            'critical' => 'badge-vuln',
                            'warning' => 'badge-vuln',
                            default => 'badge-secure'
                        };
                        ?>
                        <span class="badge <?= $sevClass ?>"><?= e($log['severity']) ?></span>
                    </td>
                    <td><code><?= e($log['ip_address']) ?></code></td>
                    <td style="font-size:0.85rem;"><?= e($log['details']) ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

        <div class="code-block" style="margin-top:15px;">
<span class="comment">// Code sécurisé - logging complet</span>
<span class="secure">if (!$user || !password_verify($pw, $hash)) {
    // Logger la tentative échouée
    $db->prepare("INSERT INTO security_logs
      (event_type, ip_address, user_agent, details, severity)
      VALUES (?, ?, ?, ?, ?)")
    ->execute(['failed_login', $ip, $ua,
      "Échec pour: $username", 'warning']);

    // Alerter si trop de tentatives
    $count = countRecentFailures($ip, 15);
    if ($count > 5) {
      alertAdmin("Brute-force détecté: $ip");
    }
}</span>
        </div>

        <h3 style="margin-top:20px;">Événements à logger</h3>
        <ul style="margin:10px 0 0 20px;">
            <li>Connexions réussies et échouées</li>
            <li>Tentatives d'accès non autorisé</li>
            <li>Modifications de données sensibles</li>
            <li>Erreurs applicatives et exceptions</li>
            <li>Exports et téléchargements de données</li>
            <li>Changements de configuration</li>
            <li>Patterns suspects (SQLi, XSS, etc.)</li>
        </ul>
    </div>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
