<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="page-header">
    <h2>TP2 - Cross-Site Scripting (XSS)</h2>
    <p>Injection de scripts JavaScript malveillants dans les pages web vues par d'autres utilisateurs.</p>
</div>

<div class="card">
    <div class="tabs">
        <button class="tab active" data-tab="vuln">Version Vulnérable</button>
        <button class="tab secure" data-tab="secure">Version Sécurisée</button>
    </div>

    <!-- VERSION VULNÉRABLE -->
    <div id="vuln" class="tab-content active">
        <div class="alert alert-danger">
            VULNÉRABLE : Les données utilisateur sont affichées sans échappement HTML.
        </div>

        <!-- XSS Réfléchi -->
        <h3>XSS Réfléchi (Reflected)</h3>
        <form method="GET" action="">
            <input type="hidden" name="mode" value="vulnerable">
            <div class="form-group">
                <label>Rechercher :</label>
                <input type="text" name="q" value="<?= $_GET['q'] ?? '' ?>"
                       placeholder='Essayez : <script>alert("XSS")</script>'>
            </div>
            <button type="submit" class="btn btn-danger">Rechercher</button>
        </form>

        <?php if (isset($_GET['q']) && ($_GET['mode'] ?? '') === 'vulnerable'): ?>
        <div class="result-box">
            <p>Résultats pour : <?= $_GET['q'] ?></p>
        </div>
        <?php endif; ?>

        <hr style="margin:20px 0;">

        <!-- XSS Stocké -->
        <h3>XSS Stocké (Stored)</h3>
        <form method="POST" action="">
            <input type="hidden" name="mode" value="vulnerable">
            <div class="form-group">
                <label>Ajouter un commentaire :</label>
                <textarea name="comment" rows="3" placeholder='Essayez : <img src=x onerror="alert(document.cookie)">'></textarea>
            </div>
            <button type="submit" class="btn btn-danger">Publier (vulnérable)</button>
        </form>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['mode'] ?? '') === 'vulnerable' && isset($_POST['comment'])) {
            $db = getDB();
            $userId = $_SESSION['user_id'] ?? 1;
            $stmt = $db->prepare("INSERT INTO comments (user_id, content, page) VALUES (?, ?, 'xss-vuln')");
            $stmt->execute([$userId, $_POST['comment']]);
        }

        $db = getDB();
        $comments = $db->query("SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.page = 'xss-vuln' ORDER BY c.created_at DESC LIMIT 10")->fetchAll();
        ?>

        <div style="margin-top:15px;">
            <?php foreach ($comments as $c): ?>
            <div class="card" style="padding:12px;">
                <strong><?= $c['username'] ?></strong>
                <small style="color:var(--gray);"> - <?= $c['created_at'] ?></small>
                <p style="margin-top:5px;"><?= $c['content'] ?></p>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="explanation" style="margin-top:20px;">
            <h4>Payloads à tester :</h4>
            <ul style="margin:10px 0 0 20px;">
                <li><code>&lt;script&gt;alert('XSS')&lt;/script&gt;</code></li>
                <li><code>&lt;img src=x onerror="alert(document.cookie)"&gt;</code></li>
                <li><code>&lt;a href="javascript:alert('XSS')"&gt;Cliquez ici&lt;/a&gt;</code></li>
                <li><code>&lt;svg onload="alert('XSS')"&gt;</code></li>
            </ul>
        </div>
    </div>

    <!-- VERSION SÉCURISÉE -->
    <div id="secure" class="tab-content">
        <div class="alert alert-success">
            SÉCURISÉ : Toutes les sorties sont échappées avec htmlspecialchars().
        </div>

        <h3>XSS Réfléchi - Sécurisé</h3>
        <form method="GET" action="">
            <input type="hidden" name="mode" value="secure">
            <div class="form-group">
                <label>Rechercher :</label>
                <input type="text" name="q_safe" value="<?= e($_GET['q_safe'] ?? '') ?>"
                       placeholder="Les mêmes payloads seront neutralisés">
            </div>
            <button type="submit" class="btn btn-success">Rechercher</button>
        </form>

        <?php if (isset($_GET['q_safe']) && ($_GET['mode'] ?? '') === 'secure'): ?>
        <div class="result-box">
            <p>Résultats pour : <?= e($_GET['q_safe']) ?></p>
        </div>
        <?php endif; ?>

        <hr style="margin:20px 0;">

        <h3>XSS Stocké - Sécurisé</h3>
        <form method="POST" action="">
            <input type="hidden" name="mode" value="secure">
            <div class="form-group">
                <label>Ajouter un commentaire :</label>
                <textarea name="comment_safe" rows="3" placeholder="Le contenu sera échappé à l'affichage"></textarea>
            </div>
            <button type="submit" class="btn btn-success">Publier (sécurisé)</button>
        </form>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['mode'] ?? '') === 'secure' && isset($_POST['comment_safe'])) {
            $db = getDB();
            $userId = $_SESSION['user_id'] ?? 1;
            $stmt = $db->prepare("INSERT INTO comments (user_id, content, page) VALUES (?, ?, 'xss-safe')");
            $stmt->execute([$userId, $_POST['comment_safe']]);
        }

        $db = getDB();
        $comments = $db->query("SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.page = 'xss-safe' ORDER BY c.created_at DESC LIMIT 10")->fetchAll();
        ?>

        <div style="margin-top:15px;">
            <?php foreach ($comments as $c): ?>
            <div class="card" style="padding:12px;">
                <strong><?= e($c['username']) ?></strong>
                <small style="color:var(--gray);"> - <?= e($c['created_at']) ?></small>
                <p style="margin-top:5px;"><?= e($c['content']) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="two-cols">
    <div class="card">
        <h3>Code Vulnérable</h3>
        <div class="code-block">
<span class="comment">// DANGEREUX : affichage direct</span>
<span class="vulnerable">echo $_GET['q'];</span>
<span class="vulnerable">echo $comment['content'];</span>
        </div>
    </div>
    <div class="card">
        <h3>Code Sécurisé</h3>
        <div class="code-block">
<span class="comment">// SÛR : échappement HTML</span>
<span class="secure">echo htmlspecialchars($_GET['q'],
  ENT_QUOTES, 'UTF-8');</span>
<span class="secure">echo e($comment['content']);</span>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
