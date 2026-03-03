<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="page-header">
    <h2>Ch1 — Ex3 : Protection Clickjacking</h2>
    <p>Détourner les clics de l'utilisateur via des iframes transparentes superposées.</p>
    <span class="duration">20 min</span>
</div>

<div class="card">
    <div class="tabs">
        <button class="tab active" data-tab="vuln">Version Vulnérable</button>
        <button class="tab secure" data-tab="secure">Version Sécurisée</button>
    </div>

    <!-- VERSION VULNÉRABLE -->
    <div id="vuln" class="tab-content active">
        <div class="alert alert-danger">
            VULNÉRABLE : La page peut être intégrée dans une iframe sur un site malveillant.
        </div>

        <h3>Qu'est-ce que le Clickjacking ?</h3>
        <div class="explanation">
            <h4>Principe de l'attaque</h4>
            <p>L'attaquant crée une page avec un bouton visible ("Gagner un iPhone !") et superpose dessus une <strong>iframe transparente</strong> de votre site (ex: bouton "Supprimer mon compte"). L'utilisateur croit cliquer sur le bouton visible, mais clique en réalité sur l'action cachée.</p>
        </div>

        <h4 style="margin-top:20px;">Démonstration : page de l'attaquant</h4>
        <div style="position:relative; border:2px solid var(--danger); border-radius:8px; padding:20px; margin:15px 0; min-height:200px; background:#fff;">
            <div style="text-align:center; padding:20px;">
                <h3 style="color:var(--danger);">🎁 Vous avez gagné un iPhone 16 !</h3>
                <p style="margin:15px 0;">Cliquez sur le bouton ci-dessous pour réclamer votre prix :</p>
                <button class="btn btn-success" style="font-size:1.2rem; padding:15px 40px;" onclick="alert('Clickjacking ! En réalité vous auriez cliqué sur un bouton caché (ex: Supprimer mon compte, Confirmer un transfert...)')">
                    Réclamer mon prix !
                </button>
            </div>

            <div style="position:absolute; top:0; left:0; right:0; bottom:0; display:flex; align-items:center; justify-content:center; pointer-events:none;">
                <div style="border:2px dashed var(--danger); padding:10px; border-radius:5px; background:rgba(220,38,38,0.05);">
                    <small style="color:var(--danger);">↑ Zone de l'iframe invisible<br>(ici rendue visible pour la démo)</small>
                </div>
            </div>
        </div>

        <h4 style="margin-top:20px;">Code de l'attaque</h4>
        <div class="code-block">
<span class="comment">&lt;!-- Page de l'attaquant (evil.com) --&gt;</span>
<span class="vulnerable">&lt;style&gt;
  .target-iframe {
    position: absolute;
    top: 0; left: 0;
    width: 500px; height: 300px;
    opacity: 0;        /* INVISIBLE ! */
    z-index: 10;       /* Au-dessus du bouton visible */
    pointer-events: auto;
  }
  .fake-button {
    position: absolute;
    top: 120px; left: 150px;
  }
&lt;/style&gt;

&lt;div class="fake-button"&gt;
  &lt;button&gt;Gagner un iPhone !&lt;/button&gt;
&lt;/div&gt;

&lt;!-- L'iframe de VOTRE site, invisible --&gt;
&lt;iframe class="target-iframe"
  src="http://localhost:8080/tp/ch01-bases-securite/ex3-clickjacking.php?action=delete"&gt;
&lt;/iframe&gt;</span>
        </div>

        <hr style="margin:20px 0;">

        <h3>Action simulée (sans protection)</h3>
        <?php if (isset($_GET['action']) && $_GET['action'] === 'delete'): ?>
        <div class="alert alert-danger">
            Action "<?= e($_GET['action']) ?>" exécutée ! Sans protection, un attaquant pourrait déclencher cette action via une iframe invisible.
        </div>
        <?php endif; ?>

        <form method="GET">
            <input type="hidden" name="action" value="delete">
            <button type="submit" class="btn btn-danger">Supprimer mon compte (simulé)</button>
        </form>
    </div>

    <!-- VERSION SÉCURISÉE -->
    <div id="secure" class="tab-content">
        <div class="alert alert-success">
            SÉCURISÉ : En-têtes HTTP empêchant l'intégration dans une iframe.
        </div>

        <h3>Protection 1 : X-Frame-Options</h3>
        <div class="code-block">
<span class="comment">// En-tête HTTP côté serveur (PHP)</span>
<span class="secure">header('X-Frame-Options: DENY');           // Interdit TOUT iframe</span>
<span class="secure">header('X-Frame-Options: SAMEORIGIN');     // Autorise seulement la même origine</span>
        </div>

        <table style="margin-top:15px;">
            <tr><th>Valeur</th><th>Effet</th></tr>
            <tr>
                <td><code>DENY</code></td>
                <td>La page ne peut JAMAIS être dans une iframe</td>
            </tr>
            <tr>
                <td><code>SAMEORIGIN</code></td>
                <td>Seul le même site peut l'intégrer en iframe</td>
            </tr>
        </table>

        <hr style="margin:20px 0;">

        <h3>Protection 2 : Content-Security-Policy (frame-ancestors)</h3>
        <div class="code-block">
<span class="comment">// Méthode moderne (remplace X-Frame-Options)</span>
<span class="secure">header("Content-Security-Policy: frame-ancestors 'none'");</span>
<span class="comment">// Ou pour autoriser seulement la même origine :</span>
<span class="secure">header("Content-Security-Policy: frame-ancestors 'self'");</span>
        </div>

        <hr style="margin:20px 0;">

        <h3>Protection 3 : JavaScript (fallback)</h3>
        <div class="code-block">
<span class="comment">// Script anti-iframe (moins fiable, mais utile en fallback)</span>
<span class="secure">&lt;script&gt;
  // Si la page est dans une iframe, on la fait sortir
  if (window.self !== window.top) {
    window.top.location = window.self.location;
  }
&lt;/script&gt;</span>
        </div>

        <hr style="margin:20px 0;">

        <h3>Test : iframe bloquée</h3>
        <p>Si votre serveur envoie <code>X-Frame-Options: DENY</code>, l'iframe ci-dessous sera vide/bloquée :</p>
        <div style="border:2px dashed var(--success); border-radius:6px; padding:10px; margin:10px 0;">
            <iframe src="about:blank" style="width:100%; height:80px; border:1px solid var(--border);" sandbox></iframe>
            <small style="color:var(--gray);">↑ L'iframe serait bloquée par le navigateur si X-Frame-Options est configuré</small>
        </div>
    </div>
</div>

<div class="two-cols">
    <div class="card">
        <h3>Vulnérable</h3>
        <div class="code-block">
<span class="comment">// Aucun en-tête de protection</span>
<span class="vulnerable">// La page peut être intégrée
// dans n'importe quelle iframe
// → Clickjacking possible !</span>
        </div>
    </div>
    <div class="card">
        <h3>Sécurisé</h3>
        <div class="code-block">
<span class="comment">// En-têtes anti-clickjacking</span>
<span class="secure">header('X-Frame-Options: DENY');
header("Content-Security-Policy:
  frame-ancestors 'none'");</span>
        </div>
    </div>
</div>

<a href="index.php" class="btn btn-primary">← Retour au chapitre 1</a>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
