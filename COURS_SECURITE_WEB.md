# Cours de Sécurité du Développement Web

## Programme complet — 5 chapitres de 3h (15h total)

**Plateforme TP :** Application PHP/MySQL "Sécurité Web — TP"
**Lancement :** 
---

## Comment lancer l'environnement

```bash
# 1. Démarrer MySQL (MAMP ou autre)
# 2. Initialiser la base de données
mysql -P3306 -u root -p < sql/init.sql

# 3. Lancer le serveur PHP
php -S localhost:8080 -t public

# 4. Ouvrir dans le navigateur
open http://localhost:8080

# Comptes de test :
# admin / password123 (rôle admin)
# alice / password123 (rôle user)
# bob   / password123 (rôle user)
```


---

## Structure du cours

| Chapitre | Thème | Durée | Exercices |
|----------|-------|-------|-----------|
| 1 | Bases de la sécurité | 3h | SOP, XSS, Clickjacking, CSP |
| 2 | Sécurité du client | 3h | DOM XSS, Cookies, CORS |
| 3 | Sécurité du réseau | 3h | TLS/Wireshark, HTTPS, Cryptographie |
| 4 | Architecture des serveurs Web | 3h | Nginx, Docker, DDoS/fail2ban |
| 5 | Contrôle d'accès | 3h | RBAC, OAuth 2.0, JWT |

---

## Chapitre 1 — Bases de la sécurité (3h)

### Cours théorique (1h30)

#### Architecture du client (navigateur)
Le navigateur est le point d'entrée vers le web. Il interprète HTML, CSS et JavaScript, et applique des politiques de sécurité comme la **Same-Origin Policy (SOP)**.

#### Same-Origin Policy
Règle fondamentale : un script d'une origine ne peut accéder qu'aux ressources de la **même origine** (protocole + domaine + port).

| URL A | URL B | Même origine ? | Raison |
|-------|-------|---------------|--------|
| `http://site.com/page1` | `http://site.com/page2` | OUI | Même tout |
| `http://site.com` | `https://site.com` | NON | Protocole |
| `http://site.com` | `http://api.site.com` | NON | Sous-domaine |

#### Défense en profondeur
Plusieurs couches de protection indépendantes. Si une couche est compromise, les autres protègent encore.

#### Attaques côté client
- **XSS** : Injection de JavaScript dans les pages web (vol de cookies, redirection)
- **Clickjacking** : iframe transparente superposée sur un bouton légitime
- **Man-in-the-Browser** : extension/malware qui modifie le contenu des pages

### TP pratiques (1h30)

#### Ex1 : Same-Origin Policy (20 min)
- Tester un `fetch` vers la même origine (autorisé)
- Tester un `fetch` vers google.com (bloqué par SOP)
- Observer le message d'erreur CORS dans la console

#### Ex2 : Exploitation XSS (30 min)
- **XSS Réfléchi** : `<script>alert('XSS')</script>` dans un champ de recherche
- **XSS Stocké** : `<img src=x onerror="alert(document.cookie)">` dans un commentaire
- **Protection** : `htmlspecialchars()` / fonction `e()` pour échapper les sorties

**Code vulnérable :**
```php
echo $_GET['q'];              // Affichage direct → XSS !
echo $comment['content'];     // Commentaire non échappé
```

**Code sécurisé :**
```php
echo htmlspecialchars($_GET['q'], ENT_QUOTES, 'UTF-8');
echo e($comment['content']);
```

#### Ex3 : Protection Clickjacking (20 min)
- Démonstration d'une iframe invisible superposée sur un bouton
- Protection : `X-Frame-Options: DENY` et `Content-Security-Policy: frame-ancestors 'none'`

#### Ex4 : Configuration CSP (20 min)
- Sans CSP : le navigateur exécute n'importe quel script
- Avec CSP `script-src 'self'` : les scripts inline et externes sont bloqués
- CSP = filet de sécurité supplémentaire (ne remplace pas l'échappement)

---

## Chapitre 2 — Sécurité du client (3h)

### Cours théorique (1h30)

#### DOM-based XSS
Contrairement au XSS classique, le payload ne transite jamais par le serveur. L'injection se fait via le DOM JavaScript (location.hash, innerHTML, document.write).

**Sources dangereuses :** `location.hash`, `location.search`, `document.referrer`, `window.name`
**Sinks dangereux :** `innerHTML`, `outerHTML`, `document.write()`, `eval()`

#### Cookies et localStorage
- **Cookies** : envoyés automatiquement avec chaque requête. Attributs de sécurité : `HttpOnly`, `Secure`, `SameSite`
- **localStorage** : accessible par tout script JavaScript sur la page, pas d'attribut HttpOnly

#### CORS (Cross-Origin Resource Sharing)
Mécanisme qui permet au serveur d'autoriser des origines spécifiques à lire ses réponses. `Access-Control-Allow-Origin: *` est dangereux avec des données sensibles.

### TP pratiques (1h30)

#### Ex1 : DOM-based XSS (30 min)
- `innerHTML` avec données non fiables → exécution de code
- `textContent` comme alternative sécurisée
- DOM XSS via `location.hash` (invisible pour le serveur)

#### Ex2 : Configuration cookies sécurisée (25 min)

| Attribut | Valeur | Protection |
|----------|--------|-----------|
| HttpOnly | true | JavaScript ne peut pas lire le cookie (anti-XSS) |
| Secure | true | Envoyé uniquement en HTTPS (anti-interception) |
| SameSite | Strict | Jamais envoyé cross-site (anti-CSRF) |

```php
setcookie('session_id', $id, [
    'httponly'  => true,
    'secure'   => true,
    'samesite' => 'Strict',
]);
```

#### Ex3 : CORS Attack (35 min)
- API avec `Access-Control-Allow-Origin: *` → tout site peut lire les données
- Whitelist stricte d'origines autorisées
- Requête preflight OPTIONS

---

## Chapitre 3 — Sécurité du réseau (3h)

### Cours théorique (1h30)

#### TLS/HTTPS
- **Handshake TLS** : ClientHello → ServerHello → Certificate → KeyExchange → Finished
- **Versions** : TLS 1.3 (recommandé), TLS 1.2 (acceptable), SSL/TLS 1.0/1.1 (obsolètes)
- **Certificats** : délivrés par des autorités de certification (CA), Let's Encrypt gratuit

#### Cryptographie
| Type | Principe | Algorithmes | Usage |
|------|----------|-------------|-------|
| Symétrique | Même clé chiffrer/déchiffrer | AES-256, ChaCha20 | Données |
| Asymétrique | Clé publique + privée | RSA, Ed25519 | Échange de clés, signatures |
| Hachage | Empreinte irréversible | SHA-256, bcrypt, Argon2 | Mots de passe |

### TP pratiques (1h30)

#### Ex1 : Analyse TLS avec Wireshark (30 min)
- Capturer du trafic HTTP (tout est visible en clair)
- Capturer du trafic HTTPS (handshake visible, données chiffrées)
- `openssl s_client` pour inspecter les certificats

#### Ex2 : Configuration HTTPS avec Let's Encrypt (35 min)
- `certbot --nginx -d monsite.com`
- Configuration Nginx : `ssl_protocols TLSv1.2 TLSv1.3`
- HSTS : `Strict-Transport-Security: max-age=31536000`
- Test sur SSL Labs

#### Ex3 : Cryptographie pratique AES/RSA (25 min)
- **Hachage** : MD5 (cassé) vs SHA-256 vs bcrypt (recommandé)
- **AES-256** : chiffrement symétrique interactif en PHP
- **RSA-2048** : chiffrement asymétrique (clé publique/privée)

```php
// Hachage sécurisé
$hash = password_hash($password, PASSWORD_BCRYPT);
$valid = password_verify($password, $hash);

// AES-256
$encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
$decrypted = openssl_decrypt($encrypted, 'aes-256-cbc', $key, 0, $iv);
```

---

## Chapitre 4 — Architecture des serveurs Web (3h)

### Cours théorique (1h30)

#### Architecture web moderne
CDN/WAF → Load Balancer → Serveurs d'application → Base de données

#### Principes clés
- **Défense en profondeur** : plusieurs couches de protection
- **Moindre privilège** : chaque composant n'a que les droits nécessaires
- **Isolation** : services séparés (Docker, VMs)

### TP pratiques (1h30)

#### Ex1 : Configuration Nginx sécurisée (40 min)
- `server_tokens off` (masquer la version)
- En-têtes de sécurité (HSTS, CSP, X-Frame-Options)
- Rate limiting : `limit_req_zone` pour le login
- Bloquer les fichiers sensibles (.git, .env, .bak)
- Désactiver le directory listing

#### Ex2 : Isolation avec Docker (30 min)
- docker-compose.yml avec 3 conteneurs isolés (Nginx, PHP, MySQL)
- Réseaux séparés : Nginx ↔ PHP (frontend), PHP ↔ MySQL (backend)
- Utilisateurs non-root, filesystem read-only, limites de ressources
- Docker secrets pour les mots de passe

#### Ex3 : Protection DDoS avec fail2ban (20 min)
- Types de DDoS : volumétrique, protocolaire, applicatif
- fail2ban : détection et blocage automatique par IP
- Configuration pour SSH, Nginx et login PHP
- Rate limiting Nginx natif (burst, nodelay)

---

## Chapitre 5 — Contrôle d'accès (3h)

### Cours théorique (1h30)

#### Modèles de contrôle d'accès
| Modèle | Principe | Exemple |
|--------|----------|---------|
| DAC | Le propriétaire décide | Google Drive |
| MAC | Politique centralisée | Classifications militaires |
| RBAC | Permissions par rôle | admin, éditeur, lecteur |
| ABAC | Règles par attributs | "Manager voit les salaires de son département" |

#### OAuth 2.0 et OpenID Connect
- OAuth 2.0 = autorisation déléguée ("autoriser cette app à accéder à mes données")
- OpenID Connect = authentification ("se connecter avec Google")
- Authorization Code Flow avec PKCE (recommandé)

#### JWT (JSON Web Tokens)
- Structure : Header.Payload.Signature (Base64URL)
- Validation : algorithme, signature, expiration

### TP pratiques (1h30)

#### Ex1 : Implémentation RBAC (35 min)
- **IDOR** : accès à des profils en changeant `user_id` dans l'URL
- **Escalade de privilèges** : page admin sans vérification de rôle
- **Correction** : vérification des droits côté serveur avant chaque accès

```php
$canView = ($currentUser['id'] == $requestedId)
        || ($currentUser['role'] === 'admin')
        || (!$profile['is_private']);

if (!$canView) {
    http_response_code(403);
    die('Accès refusé');
}
```

#### Ex2 : Authentification OAuth 2.0 (35 min)
- Erreurs courantes : Implicit Flow, pas de state, redirect_uri non validé
- Authorization Code Flow avec PKCE
- Paramètre `state` anti-CSRF obligatoire
- Stocker les tokens en cookie HttpOnly

#### Ex3 : JWT Authentication (20 min)
- Création et décodage interactif de JWT
- Attaques : alg=none, confusion HS256/RS256, payload non vérifié
- Validation sécurisée : whitelist d'algorithmes, vérification signature + expiration

---

## Failles applicatives — Démonstrations OWASP

Ces TPs pratiques supplémentaires couvrent les vulnérabilités OWASP classiques :

| TP | Faille | Fichier |
|----|--------|---------|
| 1 | Injection SQL (SQLi) | `tp01-sqli/` |
| 3 | Broken Authentication | `login.php` |
| 5 | Exposition de données sensibles | `tp05-sensitive-data/` |
| 6 | Cross-Site Request Forgery (CSRF) | `tp06-csrf/` |
| 7 | Mauvaise configuration de sécurité | `tp07-misconfig/` |
| 8 | Composants vulnérables | `tp08-vulnerable-components/` |
| 9 | Logging et monitoring insuffisants | `tp09-logging/` |
| 10 | Server-Side Request Forgery (SSRF) | `tp10-ssrf/` |

