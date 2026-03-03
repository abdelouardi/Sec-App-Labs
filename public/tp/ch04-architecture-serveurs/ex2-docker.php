<?php include __DIR__ . '/../../../includes/header.php'; ?>

<div class="page-header">
    <h2>Ch4 — Ex2 : Isolation avec Docker</h2>
    <p>Conteneurisation des services, isolation et bonnes pratiques de sécurité Docker.</p>
    <span class="duration">30 min</span>
</div>

<div class="card">
    <div class="tabs">
        <button class="tab active" data-tab="vuln">Sans isolation</button>
        <button class="tab secure" data-tab="secure">Avec Docker</button>
    </div>

    <!-- SANS ISOLATION -->
    <div id="vuln" class="tab-content active">
        <div class="alert alert-danger">
            VULNÉRABLE : Tous les services sur la même machine, mêmes permissions.
        </div>

        <h3>Architecture monolithique (non isolée)</h3>
        <div class="code-block">
<span class="vulnerable">┌─────────────────────────────────────────┐
│           Serveur unique                │
│                                         │
│  ┌─────────┐ ┌────────┐ ┌───────────┐  │
│  │ Nginx   │ │  PHP   │ │  MySQL    │  │
│  │ (root)  │ │ (root) │ │  (root)   │  │
│  └─────────┘ └────────┘ └───────────┘  │
│                                         │
│  Tous partagent :                       │
│  - Le même système de fichiers          │
│  - Les mêmes utilisateurs              │
│  - Le même réseau                       │
│  - Les mêmes variables d'environnement  │
└─────────────────────────────────────────┘</span>

<span class="comment">// Problème : si PHP est compromis, l'attaquant a accès à :
// - La configuration MySQL (mots de passe)
// - Les fichiers de tous les autres sites
// - Le système entier si PHP tourne en root</span>
        </div>

        <h4 style="margin-top:20px;">Risques</h4>
        <table>
            <tr><th>Risque</th><th>Conséquence</th></tr>
            <tr><td>RCE dans l'application</td><td>Accès à toute la machine</td></tr>
            <tr><td>SQLi → lecture de fichiers</td><td>Peut lire /etc/passwd, les clés SSH</td></tr>
            <tr><td>Services en root</td><td>Compromission totale immédiate</td></tr>
            <tr><td>Réseau partagé</td><td>Tous les services sont accessibles</td></tr>
        </table>
    </div>

    <!-- AVEC DOCKER -->
    <div id="secure" class="tab-content">
        <div class="alert alert-success">
            SÉCURISÉ : Chaque service isolé dans son propre conteneur.
        </div>

        <h3>Architecture Docker (isolée)</h3>
        <div class="code-block">
<span class="secure">┌──────────────────────────────────────────────┐
│                    Docker                     │
│                                               │
│  ┌──────────┐  ┌──────────┐  ┌────────────┐  │
│  │  Nginx   │  │   PHP    │  │   MySQL    │  │
│  │ (nobody) │  │(www-data)│  │  (mysql)   │  │
│  │ Port 443 │  │ Port 9000│  │ Port 3306  │  │
│  │          │  │          │  │            │  │
│  │ Réseau:  │  │ Réseau:  │  │ Réseau:    │  │
│  │ frontend │  │ frontend │  │ backend    │  │
│  │          │  │ backend  │  │ (interne)  │  │
│  └──────────┘  └──────────┘  └────────────┘  │
│                                               │
│  Isolation :                                  │
│  ✅ Systèmes de fichiers séparés              │
│  ✅ Utilisateurs non-root                     │
│  ✅ Réseaux séparés (Nginx ↛ MySQL)          │
│  ✅ Ressources limitées (CPU, RAM)            │
└──────────────────────────────────────────────┘</span>
        </div>

        <hr style="margin:20px 0;">

        <h3>docker-compose.yml sécurisé</h3>
        <div class="code-block">
<span class="secure">version: '3.8'

services:
  nginx:
    image: nginx:alpine
    ports:
      - "443:443"
      - "80:80"
    volumes:
      - ./nginx.conf:/etc/nginx/conf.d/default.conf:ro
      - ./public:/var/www/html/public:ro  # Read-only !
    networks:
      - frontend
    deploy:
      resources:
        limits:
          memory: 256M
          cpus: '0.5'
    security_opt:
      - no-new-privileges:true
    read_only: true  # Filesystem en lecture seule

  php:
    build: ./docker/php
    volumes:
      - ./:/var/www/html
    networks:
      - frontend
      - backend
    user: "1000:1000"  # Non-root !
    deploy:
      resources:
        limits:
          memory: 512M
          cpus: '1.0'
    security_opt:
      - no-new-privileges:true
    environment:
      - DB_HOST=mysql
      - DB_PORT=3306

  mysql:
    image: mysql:8.0
    volumes:
      - db_data:/var/lib/mysql
    networks:
      - backend  # Pas de réseau frontend !
    environment:
      MYSQL_ROOT_PASSWORD_FILE: /run/secrets/db_root_password
      MYSQL_DATABASE: owasp_tp
    deploy:
      resources:
        limits:
          memory: 1G
    secrets:
      - db_root_password

networks:
  frontend:  # Nginx ↔ PHP
  backend:   # PHP ↔ MySQL (Nginx n'y a PAS accès)

volumes:
  db_data:

secrets:
  db_root_password:
    file: ./secrets/db_password.txt</span>
        </div>

        <hr style="margin:20px 0;">

        <h3>Dockerfile sécurisé pour PHP</h3>
        <div class="code-block">
<span class="secure">FROM php:8.3-fpm-alpine

# Installer les extensions nécessaires uniquement
RUN docker-php-ext-install pdo pdo_mysql

# Créer un utilisateur non-root
RUN addgroup -S appgroup && adduser -S appuser -G appgroup

# Copier le code
COPY --chown=appuser:appgroup . /var/www/html

# Utiliser l'utilisateur non-root
USER appuser

# Désactiver les fonctions PHP dangereuses
RUN echo "disable_functions = exec, system, shell_exec, passthru, proc_open, popen" \
    >> /usr/local/etc/php/conf.d/security.ini</span>
        </div>

        <h4 style="margin-top:20px;">Bonnes pratiques Docker</h4>
        <ul style="margin:10px 0 0 20px;">
            <li><strong>Non-root</strong> : Toujours utiliser <code>USER</code> non-root dans le Dockerfile</li>
            <li><strong>Read-only</strong> : <code>read_only: true</code> quand possible</li>
            <li><strong>Réseaux séparés</strong> : Le serveur web ne doit pas accéder directement à la BDD</li>
            <li><strong>Secrets</strong> : Utiliser Docker secrets, pas des variables d'environnement</li>
            <li><strong>Images minimales</strong> : Utiliser Alpine, pas Ubuntu/Debian</li>
            <li><strong>Limiter les ressources</strong> : CPU et mémoire pour chaque conteneur</li>
            <li><strong>Scanner les images</strong> : <code>docker scout cves</code> ou Trivy</li>
        </ul>
    </div>
</div>

<div class="card">
    <h3>Exercice pratique</h3>
    <ol style="margin:10px 0 0 20px;">
        <li>Analysez le <code>docker-compose.yml</code> ci-dessus et identifiez chaque mesure de sécurité</li>
        <li>Pourquoi MySQL n'est-il pas sur le réseau <code>frontend</code> ?</li>
        <li>Que se passe-t-il si un attaquant compromet le conteneur PHP ? Peut-il accéder aux fichiers de Nginx ?</li>
        <li><strong>Défi :</strong> Créez un <code>docker-compose.yml</code> pour cette application OWASP TP avec 3 conteneurs isolés</li>
    </ol>
</div>

<a href="index.php" class="btn btn-primary">← Retour au chapitre 4</a>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>
