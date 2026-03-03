# Sécurité Web — TP

Plateforme éducative pour apprendre la sécurité du développement web.
5 chapitres de 3h (15h total) avec cours théorique et TP pratiques.
Chaque exercice présente une **version vulnérable** et une **version sécurisée** côte à côte.

> **ATTENTION** : Ce projet est conçu uniquement à des fins éducatives. Ne JAMAIS déployer en production.

## Prérequis

- PHP 8.0+
- MySQL 5.7+ / MariaDB 10+
- Un navigateur web

## Installation

### 1. Créer la base de données

```bash
mysql -P 3306 -u root -p < sql/init.sql
```

### 2. Configurer la connexion

Modifier `config/database.php` si nécessaire (host, port, user, password).

### 3. Lancer le serveur

```bash
php -S localhost:8080 -t public
```

### 4. Accéder à l'application

Ouvrir http://localhost:8080 dans le navigateur.

**Comptes de test :**
| Utilisateur | Mot de passe | Rôle |
|-------------|-------------|------|
| admin | password123 | admin |
| alice | password123 | user |
| bob | password123 | user |

## Programme du cours

| Chapitre | Thème | Durée | Exercices |
|----------|-------|-------|-----------|
| 1 | Bases de la sécurité | 3h | SOP, XSS, Clickjacking, CSP |
| 2 | Sécurité du client | 3h | DOM XSS, Cookies, CORS |
| 3 | Sécurité du réseau | 3h | TLS/Wireshark, HTTPS, Cryptographie |
| 4 | Architecture des serveurs Web | 3h | Nginx, Docker, DDoS/fail2ban |
| 5 | Contrôle d'accès | 3h | RBAC, OAuth 2.0, JWT |

## Détail des exercices

### Chapitre 1 — Bases de la sécurité

| Exercice | Thème | Durée | Chemin |
|----------|-------|-------|--------|
| Ex1 | Same-Origin Policy | 20 min | `/tp/ch01-bases-securite/ex1-sop.php` |
| Ex2 | Exploitation XSS | 30 min | `/tp/ch01-bases-securite/ex2-xss.php` |
| Ex3 | Protection Clickjacking | 20 min | `/tp/ch01-bases-securite/ex3-clickjacking.php` |
| Ex4 | Configuration CSP | 20 min | `/tp/ch01-bases-securite/ex4-csp.php` |

### Chapitre 2 — Sécurité du client

| Exercice | Thème | Durée | Chemin |
|----------|-------|-------|--------|
| Ex1 | DOM-based XSS | 30 min | `/tp/ch02-securite-client/ex1-dom-xss.php` |
| Ex2 | Cookies sécurisés | 25 min | `/tp/ch02-securite-client/ex2-cookies.php` |
| Ex3 | CORS Attack | 35 min | `/tp/ch02-securite-client/ex3-cors.php` |

### Chapitre 3 — Sécurité du réseau

| Exercice | Thème | Durée | Chemin |
|----------|-------|-------|--------|
| Ex1 | Analyse TLS / Wireshark | 30 min | `/tp/ch03-securite-reseau/ex1-tls.php` |
| Ex2 | HTTPS / Let's Encrypt | 35 min | `/tp/ch03-securite-reseau/ex2-https.php` |
| Ex3 | Cryptographie AES/RSA | 25 min | `/tp/ch03-securite-reseau/ex3-crypto.php` |

### Chapitre 4 — Architecture des serveurs Web

| Exercice | Thème | Durée | Chemin |
|----------|-------|-------|--------|
| Ex1 | Configuration Nginx | 40 min | `/tp/ch04-architecture-serveurs/ex1-nginx.php` |
| Ex2 | Isolation Docker | 30 min | `/tp/ch04-architecture-serveurs/ex2-docker.php` |
| Ex3 | Protection DDoS / fail2ban | 20 min | `/tp/ch04-architecture-serveurs/ex3-ddos.php` |

### Chapitre 5 — Contrôle d'accès

| Exercice | Thème | Durée | Chemin |
|----------|-------|-------|--------|
| Ex1 | RBAC / IDOR | 35 min | `/tp/ch05-controle-acces/ex1-rbac.php` |
| Ex2 | OAuth 2.0 | 35 min | `/tp/ch05-controle-acces/ex2-oauth.php` |
| Ex3 | JWT Authentication | 20 min | `/tp/ch05-controle-acces/ex3-jwt.php` |

## Failles applicatives — Démonstrations OWASP

| # | Faille | Sévérité | Chemin |
|---|--------|----------|--------|
| 1 | Injection SQL (SQLi) | Critique | `/tp/tp01-sqli/` |
| 3 | Broken Authentication | Critique | `/login.php?mode=vulnerable` |
| 5 | Exposition de données sensibles | Elevée | `/tp/tp05-sensitive-data/` |
| 6 | Cross-Site Request Forgery (CSRF) | Elevée | `/tp/tp06-csrf/` |
| 7 | Mauvaise configuration de sécurité | Elevée | `/tp/tp07-misconfig/` |
| 8 | Composants vulnérables | Elevée | `/tp/tp08-vulnerable-components/` |
| 9 | Logging et monitoring insuffisants | Moyenne | `/tp/tp09-logging/` |
| 10 | Server-Side Request Forgery (SSRF) | Critique | `/tp/tp10-ssrf/` |

## Structure du projet

```
owasp-tp/
├── config/
│   ├── app.php                # Configuration et helpers (e(), isLoggedIn(), etc.)
│   └── database.php           # Connexion BDD (host, port, user, password)
├── includes/
│   ├── header.php             # En-tête HTML + navigation
│   └── footer.php             # Pied de page + scripts
├── public/                    # Racine du serveur web (-t public)
│   ├── index.php              # Page d'accueil (chapitres + failles)
│   ├── login.php              # Authentification (+ TP Broken Auth)
│   ├── logout.php
│   ├── css/style.css
│   ├── js/app.js
│   └── tp/
│       ├── ch01-bases-securite/       # Chapitre 1 (SOP, XSS, Clickjacking, CSP)
│       ├── ch02-securite-client/      # Chapitre 2 (DOM XSS, Cookies, CORS)
│       ├── ch03-securite-reseau/      # Chapitre 3 (TLS, HTTPS, Crypto)
│       ├── ch04-architecture-serveurs/# Chapitre 4 (Nginx, Docker, DDoS)
│       ├── ch05-controle-acces/       # Chapitre 5 (RBAC, OAuth, JWT)
│       ├── tp01-sqli/                 # Injection SQL
│       ├── tp05-sensitive-data/       # Données sensibles
│       ├── tp06-csrf/                 # CSRF
│       ├── tp07-misconfig/            # Mauvaise configuration
│       ├── tp08-vulnerable-components/# Composants vulnérables
│       ├── tp09-logging/              # Logging insuffisant
│       └── tp10-ssrf/                 # SSRF
├── sql/
│   └── init.sql               # Script d'initialisation BDD
├── COURS_SECURITE_WEB.md      # Document de cours complet (5 chapitres)
└── README.md
```
