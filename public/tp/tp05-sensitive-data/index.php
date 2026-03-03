<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="page-header">
    <h2>TP5 - Exposition de données sensibles</h2>
    <p>Transmission ou stockage de données sensibles sans chiffrement approprié.</p>
</div>

<div class="card">
    <div class="tabs">
        <button class="tab active" data-tab="vuln">Version Vulnérable</button>
        <button class="tab secure" data-tab="secure">Version Sécurisée</button>
    </div>

    <!-- VERSION VULNÉRABLE -->
    <div id="vuln" class="tab-content active">
        <div class="alert alert-danger">
            VULNÉRABLE : Données sensibles stockées en clair et exposées sans protection.
        </div>

        <h3>Base de données des employés (toutes les données exposées)</h3>
        <?php
        $db = getDB();
        $employees = $db->query("SELECT * FROM employees")->fetchAll();
        ?>
        <table>
            <tr><th>Nom</th><th>Email</th><th>Téléphone</th><th>Salaire</th><th>N° Sécu. Sociale</th></tr>
            <?php foreach ($employees as $emp): ?>
            <tr>
                <td><?= $emp['first_name'] . ' ' . $emp['last_name'] ?></td>
                <td><?= $emp['email'] ?></td>
                <td><?= $emp['phone'] ?></td>
                <td><?= number_format($emp['salary'], 2) ?> EUR</td>
                <td><span class="badge badge-vuln"><?= $emp['ssn'] ?></span></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <hr style="margin:20px 0;">

        <h3>Mots de passe stockés en clair</h3>
        <?php
        $users = $db->query("SELECT username, email, password_plain FROM users")->fetchAll();
        ?>
        <table>
            <tr><th>Utilisateur</th><th>Email</th><th>Mot de passe (en clair !)</th></tr>
            <?php foreach ($users as $u): ?>
            <tr>
                <td><?= $u['username'] ?></td>
                <td><?= $u['email'] ?></td>
                <td><span class="badge badge-vuln"><?= $u['password_plain'] ?></span></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <hr style="margin:20px 0;">

        <h3>Formulaire sans HTTPS</h3>
        <form method="POST" action="http://example.com/login">
            <div class="form-group">
                <label>Carte bancaire :</label>
                <input type="text" name="credit_card" placeholder="1234 5678 9012 3456">
            </div>
            <div class="alert alert-danger">Ce formulaire enverrait les données en HTTP non chiffré !</div>
        </form>

        <div class="explanation" style="margin-top:20px;">
            <h4>Problèmes identifiés :</h4>
            <ul style="margin:10px 0 0 20px;">
                <li>Numéros de sécurité sociale affichés en clair</li>
                <li>Salaires visibles par tous</li>
                <li>Mots de passe stockés en texte brut</li>
                <li>Données transmises sans chiffrement (HTTP)</li>
                <li>Aucun masquage des données sensibles</li>
            </ul>
        </div>
    </div>

    <!-- VERSION SÉCURISÉE -->
    <div id="secure" class="tab-content">
        <div class="alert alert-success">
            SÉCURISÉ : Données sensibles masquées, chiffrées, et accès contrôlé.
        </div>

        <h3>Base de données des employés (données protégées)</h3>
        <?php
        $currentUser = getCurrentUser();
        $isAdminUser = $currentUser && $currentUser['role'] === 'admin';
        ?>
        <table>
            <tr><th>Nom</th><th>Email</th><th>Département</th><th>Salaire</th><th>N° Sécu. Sociale</th></tr>
            <?php foreach ($employees as $emp): ?>
            <tr>
                <td><?= e($emp['first_name'] . ' ' . $emp['last_name']) ?></td>
                <td><?= e($emp['email']) ?></td>
                <td><?= e($emp['department']) ?></td>
                <td>
                    <?php if ($isAdminUser): ?>
                        <?= number_format($emp['salary'], 2) ?> EUR
                    <?php else: ?>
                        <span style="color:var(--gray);">***masqué***</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php
                    // Masquer le SSN : afficher seulement les 2 derniers chiffres
                    $maskedSSN = str_repeat('*', strlen($emp['ssn']) - 2) . substr($emp['ssn'], -2);
                    ?>
                    <span class="badge badge-secure"><?= e($maskedSSN) ?></span>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>

        <hr style="margin:20px 0;">

        <h3>Mots de passe hashés (bcrypt)</h3>
        <?php
        $users = $db->query("SELECT username, email, password FROM users")->fetchAll();
        ?>
        <table>
            <tr><th>Utilisateur</th><th>Email</th><th>Hash du mot de passe</th></tr>
            <?php foreach ($users as $u): ?>
            <tr>
                <td><?= e($u['username']) ?></td>
                <td><?= e($u['email']) ?></td>
                <td><span class="badge badge-secure" style="font-size:0.7rem;"><?= e(substr($u['password'], 0, 30)) ?>...</span></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

<div class="two-cols">
    <div class="card">
        <h3>Code Vulnérable</h3>
        <div class="code-block">
<span class="comment">// Mot de passe en clair</span>
<span class="vulnerable">$password_plain = $_POST['password'];
INSERT INTO users (password_plain) VALUES ('$pw');</span>

<span class="comment">// SSN affiché en clair</span>
<span class="vulnerable">echo $employee['ssn'];</span>

<span class="comment">// HTTP au lieu de HTTPS</span>
<span class="vulnerable">&lt;form action="http://..."&gt;</span>
        </div>
    </div>
    <div class="card">
        <h3>Code Sécurisé</h3>
        <div class="code-block">
<span class="comment">// Hash bcrypt</span>
<span class="secure">$hash = password_hash($pw, PASSWORD_BCRYPT);
INSERT INTO users (password) VALUES ($hash);</span>

<span class="comment">// SSN masqué</span>
<span class="secure">$masked = str_repeat('*', strlen($ssn)-2)
  . substr($ssn, -2);</span>

<span class="comment">// HTTPS obligatoire</span>
<span class="secure">&lt;form action="https://..."&gt;</span>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
