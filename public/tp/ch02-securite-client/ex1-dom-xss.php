<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="page-header">
    <h2>Ch2 — Ex1 : DOM-based XSS</h2>
    <p>Injection XSS directement via le DOM JavaScript, sans que le payload transite par le serveur.</p>
    <span class="duration">30 min</span>
</div>

<div class="card">
    <h3>Différence XSS classique vs DOM-based</h3>
    <table>
        <tr><th></th><th>XSS Réfléchi/Stocké</th><th>DOM-based XSS</th></tr>
        <tr>
            <td><strong>Payload</strong></td>
            <td>Envoyé au serveur, inclus dans la réponse HTML</td>
            <td>Reste dans le navigateur, jamais envoyé au serveur</td>
        </tr>
        <tr>
            <td><strong>Détection serveur</strong></td>
            <td>Possible (WAF, filtres)</td>
            <td>Impossible (le serveur ne voit rien)</td>
        </tr>
        <tr>
            <td><strong>Vecteur</strong></td>
            <td>Paramètres GET/POST</td>
            <td>Fragment d'URL (#), location.hash, document.referrer</td>
        </tr>
    </table>
</div>

<div class="card">
    <div class="tabs">
        <button class="tab active" data-tab="vuln">Version Vulnérable</button>
        <button class="tab secure" data-tab="secure">Version Sécurisée</button>
    </div>

    <!-- VERSION VULNÉRABLE -->
    <div id="vuln" class="tab-content active">
        <div class="alert alert-danger">
            VULNÉRABLE : Le JavaScript utilise innerHTML avec des données non fiables.
        </div>

        <h3>Scénario : Affichage du message de bienvenue</h3>
        <div class="form-group">
            <label>Entrez votre nom :</label>
            <input type="text" id="vuln-name" placeholder='Essayez : <img src=x onerror="alert(\'DOM XSS\')">'>
        </div>
        <button class="btn btn-danger" onclick="showWelcomeVuln()">Afficher (vulnérable)</button>

        <div id="vuln-output" class="result-box" style="margin-top:15px;">
            <p style="color:var(--gray);">Le résultat apparaîtra ici...</p>
        </div>

        <h4 style="margin-top:20px;">Code vulnérable</h4>
        <div class="code-block">
<span class="comment">// DANGEREUX : innerHTML interprète le HTML/JS</span>
<span class="vulnerable">function showWelcomeVuln() {
    var name = document.getElementById('vuln-name').value;
    document.getElementById('vuln-output').innerHTML
        = '&lt;h3&gt;Bienvenue, ' + name + ' !&lt;/h3&gt;';
}</span>

<span class="comment">// Si name = '&lt;img src=x onerror="alert(1)"&gt;'
// → innerHTML crée une balise &lt;img&gt; qui exécute le JS !</span>
        </div>

        <hr style="margin:20px 0;">

        <h3>Scénario 2 : DOM XSS via le hash de l'URL</h3>
        <div class="explanation">
            <h4>Le fragment d'URL (#) n'est jamais envoyé au serveur</h4>
            <p>Tout ce qui suit <code>#</code> dans l'URL reste dans le navigateur. C'est pour ça que les WAF (Web Application Firewalls) ne peuvent pas détecter ces attaques.</p>
        </div>

        <div id="hash-output" class="result-box" style="margin-top:15px;">
            <p style="color:var(--gray);">Ajoutez <code>#&lt;b&gt;test&lt;/b&gt;</code> à l'URL et rechargez...</p>
        </div>

        <div class="code-block">
<span class="comment">// Lecture du hash de l'URL et injection dans le DOM</span>
<span class="vulnerable">var userInput = decodeURIComponent(location.hash.substring(1));
document.getElementById('hash-output').innerHTML = userInput;</span>

<span class="comment">// URL malveillante :
// http://localhost:8080/...ex1-dom-xss.php#&lt;img src=x onerror=alert(1)&gt;
// → Le payload n'est JAMAIS envoyé au serveur
// → Impossible à détecter côté serveur</span>
        </div>

        <div class="explanation" style="margin-top:15px;">
            <h4>Sources dangereuses (Sources) → Puits dangereux (Sinks)</h4>
            <table style="margin-top:10px;">
                <tr><th>Sources (données non fiables)</th><th>Sinks (insertion dans le DOM)</th></tr>
                <tr>
                    <td>
                        <code>location.hash</code><br>
                        <code>location.search</code><br>
                        <code>document.referrer</code><br>
                        <code>window.name</code><br>
                        <code>postMessage data</code>
                    </td>
                    <td>
                        <code>innerHTML</code><br>
                        <code>outerHTML</code><br>
                        <code>document.write()</code><br>
                        <code>eval()</code><br>
                        <code>setTimeout(string)</code>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- VERSION SÉCURISÉE -->
    <div id="secure" class="tab-content">
        <div class="alert alert-success">
            SÉCURISÉ : Utilisation de textContent au lieu de innerHTML.
        </div>

        <h3>Version corrigée</h3>
        <div class="form-group">
            <label>Entrez votre nom :</label>
            <input type="text" id="safe-name" placeholder="Tapez n'importe quoi, y compris du HTML">
        </div>
        <button class="btn btn-success" onclick="showWelcomeSafe()">Afficher (sécurisé)</button>

        <div id="safe-output" class="result-box" style="margin-top:15px;">
            <p style="color:var(--gray);">Le résultat apparaîtra ici...</p>
        </div>

        <div class="code-block" style="margin-top:20px;">
<span class="comment">// SÛR : textContent n'interprète PAS le HTML</span>
<span class="secure">function showWelcomeSafe() {
    var name = document.getElementById('safe-name').value;
    var output = document.getElementById('safe-output');
    output.textContent = 'Bienvenue, ' + name + ' !';
}</span>

<span class="comment">// Si name = '&lt;img src=x onerror="alert(1)"&gt;'
// → textContent affiche le texte littéral, pas de balise HTML créée</span>
        </div>

        <hr style="margin:20px 0;">

        <h3>Alternatives sécurisées</h3>
        <div class="code-block">
<span class="comment">// 1. textContent (le plus simple et sûr)</span>
<span class="secure">element.textContent = userInput;</span>

<span class="comment">// 2. Création d'éléments DOM</span>
<span class="secure">var p = document.createElement('p');
p.textContent = userInput;
container.appendChild(p);</span>

<span class="comment">// 3. Bibliothèque de sanitization (si HTML nécessaire)</span>
<span class="secure">import DOMPurify from 'dompurify';
element.innerHTML = DOMPurify.sanitize(userInput);</span>

<span class="comment">// 4. Template literals avec échappement</span>
<span class="secure">function escapeHTML(str) {
    var div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}
element.innerHTML = '&lt;b&gt;' + escapeHTML(name) + '&lt;/b&gt;';</span>
        </div>
    </div>
</div>

<div class="card">
    <h3>Exercices</h3>
    <ol style="margin:10px 0 0 20px;">
        <li>Dans l'onglet vulnérable, tapez <code>&lt;img src=x onerror="alert('DOM XSS')"&gt;</code> et cliquez "Afficher"</li>
        <li>Retapez le même payload dans la version sécurisée → le texte s'affiche littéralement</li>
        <li>Ajoutez <code>#&lt;b&gt;Bonjour&lt;/b&gt;</code> à l'URL et rechargez la page</li>
        <li><strong>Défi :</strong> Trouvez un payload DOM XSS qui utilise <code>document.referrer</code></li>
    </ol>
</div>

<script>
function showWelcomeVuln() {
    var name = document.getElementById('vuln-name').value;
    document.getElementById('vuln-output').innerHTML = '<h3>Bienvenue, ' + name + ' !</h3>';
}

function showWelcomeSafe() {
    var name = document.getElementById('safe-name').value;
    document.getElementById('safe-output').textContent = 'Bienvenue, ' + name + ' !';
}

// DOM XSS via hash (démonstration)
window.addEventListener('load', function() {
    if (location.hash) {
        var hashContent = decodeURIComponent(location.hash.substring(1));
        var el = document.getElementById('hash-output');
        if (el) el.innerHTML = '<p>Contenu du hash : ' + hashContent + '</p>';
    }
});
</script>

<a href="index.php" class="btn btn-primary">← Retour au chapitre 2</a>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
