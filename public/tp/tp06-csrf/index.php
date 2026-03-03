<?php include __DIR__ . '/../../../includes/header.php';

// Générer un token CSRF pour la version sécurisée
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['mode'])) {
        if ($_POST['mode'] === 'vulnerable') {
            // VULNÉRABLE : pas de vérification de token CSRF
            $db = getDB();
            $userId = $_SESSION['user_id'] ?? 1;
            $toAccount = $_POST['to_account'] ?? '';
            $amount = $_POST['amount'] ?? 0;
            $stmt = $db->prepare("INSERT INTO transfers (from_user_id, to_account, amount, description) VALUES (?, ?, ?, 'Transfert sans protection CSRF')");
            $stmt->execute([$userId, $toAccount, $amount]);
            $message = "Transfert de {$amount} EUR vers {$toAccount} effectué (SANS vérification CSRF) !";
            $messageType = 'danger';
        } elseif ($_POST['mode'] === 'secure') {
            // SÉCURISÉ : vérification du token CSRF
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $message = "Erreur CSRF : token invalide ! La requête a été bloquée.";
                $messageType = 'danger';
            } else {
                $db = getDB();
                $userId = $_SESSION['user_id'] ?? 1;
                $toAccount = $_POST['to_account'] ?? '';
                $amount = $_POST['amount'] ?? 0;
                $stmt = $db->prepare("INSERT INTO transfers (from_user_id, to_account, amount, description) VALUES (?, ?, ?, 'Transfert avec protection CSRF')");
                $stmt->execute([$userId, $toAccount, $amount]);
                $message = "Transfert de {$amount} EUR vers {$toAccount} effectué (avec token CSRF valide).";
                $messageType = 'success';
                // Regénérer le token après usage
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }
        }
    }
}
?>

<div class="page-header">
    <h2>TP6 - Cross-Site Request Forgery (CSRF)</h2>
    <p>Forcer un utilisateur authentifié à exécuter des actions non désirées.</p>
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
            VULNÉRABLE : Aucun token CSRF. Un site malveillant peut soumettre ce formulaire à votre place.
        </div>

        <h3>Transfert d'argent (sans protection)</h3>
        <form method="POST">
            <input type="hidden" name="mode" value="vulnerable">
            <div class="form-group">
                <label>Compte destinataire :</label>
                <input type="text" name="to_account" value="FR76 1234 5678 9012" required>
            </div>
            <div class="form-group">
                <label>Montant (EUR) :</label>
                <input type="number" name="amount" value="1000" required>
            </div>
            <button type="submit" class="btn btn-danger">Transférer (vulnérable)</button>
        </form>

        <hr style="margin:20px 0;">

        <h3>Page malveillante simulée</h3>
        <div class="alert alert-warning">
            Un attaquant pourrait créer une page contenant ce code HTML, qui soumet automatiquement un transfert
            lorsque la victime visite la page :
        </div>
        <div class="code-block">
<span class="comment">&lt;!-- Page malveillante de l'attaquant --&gt;</span>
<span class="vulnerable">&lt;html&gt;
&lt;body onload="document.forms[0].submit()"&gt;
  &lt;form method="POST"
    action="http://localhost:8080/tp/tp06-csrf/index.php"&gt;
    &lt;input type="hidden" name="mode" value="vulnerable"&gt;
    &lt;input type="hidden" name="to_account"
      value="COMPTE_ATTAQUANT"&gt;
    &lt;input type="hidden" name="amount" value="5000"&gt;
  &lt;/form&gt;
&lt;/body&gt;
&lt;/html&gt;</span>
        </div>
    </div>

    <!-- VERSION SÉCURISÉE -->
    <div id="secure" class="tab-content">
        <div class="alert alert-success">
            SÉCURISÉ : Token CSRF unique vérifié à chaque soumission.
        </div>

        <h3>Transfert d'argent (avec protection CSRF)</h3>
        <form method="POST">
            <input type="hidden" name="mode" value="secure">
            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
            <div class="form-group">
                <label>Compte destinataire :</label>
                <input type="text" name="to_account" value="FR76 1234 5678 9012" required>
            </div>
            <div class="form-group">
                <label>Montant (EUR) :</label>
                <input type="number" name="amount" value="100" required>
            </div>
            <button type="submit" class="btn btn-success">Transférer (sécurisé)</button>
        </form>

        <div class="explanation" style="margin-top:20px;">
            <h4>Token CSRF actuel :</h4>
            <p style="font-family:monospace;font-size:0.8rem;word-break:break-all;"><?= e($_SESSION['csrf_token']) ?></p>
            <p style="margin-top:10px;">Ce token est unique par session et vérifié côté serveur. Une requête externe ne peut pas le deviner.</p>
        </div>
    </div>
</div>

<div class="card">
    <h3>Historique des transferts</h3>
    <?php
    $db = getDB();
    $transfers = $db->query("SELECT t.*, u.username FROM transfers t JOIN users u ON t.from_user_id = u.id ORDER BY t.created_at DESC LIMIT 10")->fetchAll();
    ?>
    <?php if (empty($transfers)): ?>
        <p style="color:var(--gray);">Aucun transfert effectué.</p>
    <?php else: ?>
        <table>
            <tr><th>De</th><th>Vers</th><th>Montant</th><th>Description</th><th>Date</th></tr>
            <?php foreach ($transfers as $t): ?>
            <tr>
                <td><?= e($t['username']) ?></td>
                <td><?= e($t['to_account']) ?></td>
                <td><?= e($t['amount']) ?> EUR</td>
                <td><?= e($t['description']) ?></td>
                <td><?= e($t['created_at']) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>

<div class="two-cols">
    <div class="card">
        <h3>Code Vulnérable</h3>
        <div class="code-block">
<span class="comment">// Pas de vérification CSRF</span>
<span class="vulnerable">&lt;form method="POST"&gt;
  &lt;input name="to_account"&gt;
  &lt;input name="amount"&gt;
  &lt;button&gt;Transférer&lt;/button&gt;
&lt;/form&gt;</span>

<span class="comment">// Traitement direct</span>
<span class="vulnerable">$db->execute([$to, $amount]);</span>
        </div>
    </div>
    <div class="card">
        <h3>Code Sécurisé</h3>
        <div class="code-block">
<span class="comment">// Token CSRF dans le formulaire</span>
<span class="secure">&lt;input type="hidden" name="csrf_token"
  value="&lt;?= $_SESSION['csrf_token'] ?&gt;"&gt;</span>

<span class="comment">// Vérification côté serveur</span>
<span class="secure">if ($_POST['csrf_token']
    !== $_SESSION['csrf_token']) {
  die("Token CSRF invalide !");
}</span>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
