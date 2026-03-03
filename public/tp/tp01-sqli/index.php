<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="page-header">
    <h2>TP1 - Injection SQL (SQLi)</h2>
    <p>L'attaquant insère du code SQL malveillant dans les champs de saisie pour manipuler la base de données.</p>
</div>

<div class="card">
    <div class="tabs">
        <button class="tab active" data-tab="vuln">Version Vulnérable</button>
        <button class="tab secure" data-tab="secure">Version Sécurisée</button>
    </div>

    <!-- VERSION VULNÉRABLE -->
    <div id="vuln" class="tab-content active">
        <div class="alert alert-danger">
            VULNÉRABLE : La saisie utilisateur est directement concaténée dans la requête SQL.
        </div>

        <h3>Recherche d'employés</h3>
        <form method="GET" action="">
            <input type="hidden" name="mode" value="vulnerable">
            <div class="form-group">
                <label>Rechercher par nom :</label>
                <input type="text" name="search" value="<?= $_GET['search'] ?? '' ?>"
                       placeholder="Essayez : ' OR 1=1 --">
            </div>
            <button type="submit" class="btn btn-danger">Rechercher (vulnérable)</button>
        </form>

        <?php
        if (isset($_GET['search']) && isset($_GET['mode']) && $_GET['mode'] === 'vulnerable') {
            $search = $_GET['search'];
            $conn = getMysqli();

            // VULNÉRABLE : concaténation directe !
            $query = "SELECT id, first_name, last_name, email, department FROM employees WHERE last_name LIKE '%$search%' OR first_name LIKE '%$search%'";

            echo '<div class="code-block"><span class="comment">// Requête exécutée :</span>' . "\n";
            echo '<span class="vulnerable">' . htmlspecialchars($query) . '</span></div>';

            $result = $conn->query($query);
            if ($result && $result->num_rows > 0) {
                echo '<table><tr><th>ID</th><th>Prénom</th><th>Nom</th><th>Email</th><th>Département</th></tr>';
                while ($row = $result->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>' . $row['id'] . '</td>';
                    echo '<td>' . $row['first_name'] . '</td>';
                    echo '<td>' . $row['last_name'] . '</td>';
                    echo '<td>' . $row['email'] . '</td>';
                    echo '<td>' . $row['department'] . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            } else {
                echo '<div class="alert alert-warning">Aucun résultat ou erreur SQL.</div>';
                if ($conn->error) {
                    echo '<div class="alert alert-danger">Erreur MySQL : ' . $conn->error . '</div>';
                }
            }
            $conn->close();
        }
        ?>

        <div class="explanation" style="margin-top:20px;">
            <h4>Payloads à tester :</h4>
            <ul style="margin:10px 0 0 20px;">
                <li><code>' OR 1=1 --</code> — Affiche tous les employés</li>
                <li><code>' UNION SELECT 1,username,password,email,role FROM users --</code> — Extrait les utilisateurs</li>
                <li><code>' UNION SELECT 1,table_name,2,3,4 FROM information_schema.tables WHERE table_schema=database() --</code> — Liste les tables</li>
            </ul>
        </div>
    </div>

    <!-- VERSION SÉCURISÉE -->
    <div id="secure" class="tab-content">
        <div class="alert alert-success">
            SÉCURISÉ : Utilisation de requêtes préparées (prepared statements) avec PDO.
        </div>

        <h3>Recherche d'employés</h3>
        <form method="GET" action="">
            <input type="hidden" name="mode" value="secure">
            <div class="form-group">
                <label>Rechercher par nom :</label>
                <input type="text" name="search_safe" value="<?= e($_GET['search_safe'] ?? '') ?>"
                       placeholder="Essayez les mêmes payloads...">
            </div>
            <button type="submit" class="btn btn-success">Rechercher (sécurisé)</button>
        </form>

        <?php
        if (isset($_GET['search_safe']) && isset($_GET['mode']) && $_GET['mode'] === 'secure') {
            $search = $_GET['search_safe'];
            $db = getDB();

            // SÉCURISÉ : requête préparée
            $stmt = $db->prepare("SELECT id, first_name, last_name, email, department FROM employees WHERE last_name LIKE ? OR first_name LIKE ?");
            $param = "%$search%";
            $stmt->execute([$param, $param]);
            $results = $stmt->fetchAll();

            echo '<div class="code-block"><span class="comment">// Requête préparée :</span>' . "\n";
            echo '<span class="secure">$stmt = $db->prepare("SELECT ... WHERE last_name LIKE ? OR first_name LIKE ?");</span>' . "\n";
            echo '<span class="secure">$stmt->execute([$param, $param]);</span></div>';

            if (count($results) > 0) {
                echo '<table><tr><th>ID</th><th>Prénom</th><th>Nom</th><th>Email</th><th>Département</th></tr>';
                foreach ($results as $row) {
                    echo '<tr>';
                    echo '<td>' . e($row['id']) . '</td>';
                    echo '<td>' . e($row['first_name']) . '</td>';
                    echo '<td>' . e($row['last_name']) . '</td>';
                    echo '<td>' . e($row['email']) . '</td>';
                    echo '<td>' . e($row['department']) . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            } else {
                echo '<div class="alert alert-warning">Aucun résultat.</div>';
            }
        }
        ?>
    </div>
</div>

<div class="two-cols">
    <div class="card">
        <h3>Code Vulnérable</h3>
        <div class="code-block">
<span class="comment">// DANGEREUX : concaténation directe</span>
<span class="vulnerable">$query = "SELECT * FROM employees
  WHERE last_name LIKE '%$search%'";</span>
<span class="vulnerable">$result = $conn->query($query);</span>
        </div>
    </div>
    <div class="card">
        <h3>Code Sécurisé</h3>
        <div class="code-block">
<span class="comment">// SÛR : requête préparée PDO</span>
<span class="secure">$stmt = $db->prepare("SELECT * FROM employees
  WHERE last_name LIKE ?");</span>
<span class="secure">$stmt->execute(["%$search%"]);</span>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
