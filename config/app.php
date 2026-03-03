<?php
// =============================================
// Configuration de l'application
// =============================================

session_start();

define('APP_NAME', 'OWASP TP - Portail Employés');
define('APP_ROOT', dirname(__DIR__));
define('APP_URL', 'http://localhost:8080');

require_once APP_ROOT . '/config/database.php';

// Fonction pour vérifier si l'utilisateur est connecté
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Fonction pour obtenir l'utilisateur courant
function getCurrentUser() {
    if (!isLoggedIn()) return null;
    $db = getDB();
    $stmt = $db->prepare("SELECT id, username, email, role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Fonction pour vérifier le rôle admin
function isAdmin() {
    $user = getCurrentUser();
    return $user && $user['role'] === 'admin';
}

// Redirection
function redirect($url) {
    header("Location: $url");
    exit;
}

// Échappement HTML
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
