<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="page-header">
    <h2>TP8 - Utilisation de composants vulnérables</h2>
    <p>Bibliothèques et frameworks avec des vulnérabilités connues non corrigées.</p>
</div>

<div class="card">
    <div class="tabs">
        <button class="tab active" data-tab="vuln">Version Vulnérable</button>
        <button class="tab secure" data-tab="secure">Version Sécurisée</button>
    </div>

    <!-- VERSION VULNÉRABLE -->
    <div id="vuln" class="tab-content active">
        <div class="alert alert-danger">
            VULNÉRABLE : Utilisation de bibliothèques obsolètes avec des CVE connues.
        </div>

        <h3>Audit des dépendances du projet</h3>
        <table>
            <tr><th>Composant</th><th>Version utilisée</th><th>Dernière version</th><th>CVE connues</th><th>Sévérité</th></tr>
            <tr>
                <td>jQuery</td>
                <td><span class="badge badge-vuln">1.6.1</span></td>
                <td>3.7.1</td>
                <td>CVE-2020-11022, CVE-2020-11023, CVE-2015-9251</td>
                <td><span class="severity critical">Critique</span></td>
            </tr>
            <tr>
                <td>Bootstrap</td>
                <td><span class="badge badge-vuln">3.3.6</span></td>
                <td>5.3.2</td>
                <td>CVE-2019-8331, CVE-2018-14041</td>
                <td><span class="severity high">Élevée</span></td>
            </tr>
            <tr>
                <td>PHPMailer</td>
                <td><span class="badge badge-vuln">5.2.18</span></td>
                <td>6.9.1</td>
                <td>CVE-2016-10033 (RCE !)</td>
                <td><span class="severity critical">Critique</span></td>
            </tr>
            <tr>
                <td>Twig</td>
                <td><span class="badge badge-vuln">1.35.0</span></td>
                <td>3.8.0</td>
                <td>CVE-2022-39261 (Path traversal)</td>
                <td><span class="severity high">Élevée</span></td>
            </tr>
            <tr>
                <td>PHP</td>
                <td><span class="badge badge-vuln">7.2.0</span></td>
                <td>8.3.x</td>
                <td>Multiples CVE, fin de support</td>
                <td><span class="severity critical">Critique</span></td>
            </tr>
        </table>

        <hr style="margin:20px 0;">

        <h3>Exemple : CVE-2020-11022 (jQuery XSS)</h3>
        <div class="explanation">
            <h4>Description</h4>
            <p>jQuery &lt; 3.5.0 est vulnérable aux attaques XSS via la méthode <code>.html()</code> lorsqu'elle reçoit du HTML non fiable.</p>
        </div>
        <div class="code-block">
<span class="comment">// jQuery 1.x - Vulnérable à XSS</span>
<span class="vulnerable">$('#result').html(userInput);
// Si userInput = '&lt;img src=x onerror=alert(1)&gt;'
// → le script s'exécute !</span>
        </div>

        <hr style="margin:20px 0;">

        <h3>Exemple : CVE-2016-10033 (PHPMailer RCE)</h3>
        <div class="code-block">
<span class="comment">// PHPMailer 5.2.18 - Exécution de code à distance</span>
<span class="vulnerable">$mail->setFrom('attacker" -X/var/www/shell.php @example.com');
// Permet d'écrire un fichier PHP arbitraire sur le serveur !</span>
        </div>

        <div class="alert alert-danger" style="margin-top:15px;">
            <strong>Risques :</strong> Exécution de code à distance (RCE), vol de données, déni de service, prise de contrôle totale du serveur.
        </div>
    </div>

    <!-- VERSION SÉCURISÉE -->
    <div id="secure" class="tab-content">
        <div class="alert alert-success">
            SÉCURISÉ : Composants à jour, audits réguliers, processus de mise à jour.
        </div>

        <h3>Audit des dépendances (après mise à jour)</h3>
        <table>
            <tr><th>Composant</th><th>Version</th><th>Statut</th><th>CVE connues</th></tr>
            <tr>
                <td>jQuery</td>
                <td><span class="badge badge-secure">3.7.1</span></td>
                <td><span class="badge badge-secure">À jour</span></td>
                <td>Aucune</td>
            </tr>
            <tr>
                <td>Bootstrap</td>
                <td><span class="badge badge-secure">5.3.2</span></td>
                <td><span class="badge badge-secure">À jour</span></td>
                <td>Aucune</td>
            </tr>
            <tr>
                <td>PHPMailer</td>
                <td><span class="badge badge-secure">6.9.1</span></td>
                <td><span class="badge badge-secure">À jour</span></td>
                <td>Aucune</td>
            </tr>
            <tr>
                <td>PHP</td>
                <td><span class="badge badge-secure">8.3.x</span></td>
                <td><span class="badge badge-secure">À jour</span></td>
                <td>Aucune</td>
            </tr>
        </table>

        <hr style="margin:20px 0;">

        <h3>Bonnes pratiques</h3>
        <div class="code-block">
<span class="comment"># Vérifier les vulnérabilités avec Composer</span>
<span class="secure">composer audit</span>

<span class="comment"># Mettre à jour les dépendances</span>
<span class="secure">composer update --with-dependencies</span>

<span class="comment"># Vérifier les CVE avec npm (pour le JS)</span>
<span class="secure">npm audit
npm audit fix</span>

<span class="comment"># Outils de scanning</span>
<span class="secure">- Snyk (snyk.io)
- OWASP Dependency-Check
- Retire.js (pour JavaScript)
- Roave Security Advisories (PHP)</span>
        </div>

        <hr style="margin:20px 0;">

        <h3>Checklist de sécurité des composants</h3>
        <ul style="margin:10px 0 0 20px;">
            <li>Inventorier toutes les dépendances (directes et transitives)</li>
            <li>Vérifier régulièrement les CVE avec <code>composer audit</code></li>
            <li>Automatiser les mises à jour avec Dependabot ou Renovate</li>
            <li>Supprimer les dépendances inutilisées</li>
            <li>Utiliser des versions LTS (Long Term Support)</li>
            <li>Configurer des alertes de sécurité sur GitHub/GitLab</li>
            <li>Tester après chaque mise à jour</li>
        </ul>
    </div>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
