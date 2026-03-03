-- =============================================
-- OWASP TP - Base de données
-- Portail Employés - À des fins éducatives
-- =============================================

CREATE DATABASE IF NOT EXISTS owasp_tp;
USE owasp_tp;

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    password_plain VARCHAR(255) DEFAULT NULL,  -- TP5: stockage en clair (vulnérable)
    role ENUM('user', 'admin') DEFAULT 'user',
    session_token VARCHAR(255) DEFAULT NULL,
    reset_token VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des employés
CREATE TABLE IF NOT EXISTS employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    department VARCHAR(50),
    salary DECIMAL(10,2),
    ssn VARCHAR(30),  -- Numéro de sécurité sociale (donnée sensible)
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Table des commentaires (pour XSS)
CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    content TEXT NOT NULL,
    page VARCHAR(100) DEFAULT 'general',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Table des profils (pour Broken Access Control)
CREATE TABLE IF NOT EXISTS profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    bio TEXT,
    address VARCHAR(255),
    phone VARCHAR(20),
    is_private TINYINT(1) DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Table des transferts (pour CSRF)
CREATE TABLE IF NOT EXISTS transfers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    from_user_id INT NOT NULL,
    to_account VARCHAR(50) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (from_user_id) REFERENCES users(id)
);

-- Table de logs (pour TP9)
CREATE TABLE IF NOT EXISTS security_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_type VARCHAR(50) NOT NULL,
    user_id INT DEFAULT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    details TEXT,
    severity ENUM('info', 'warning', 'critical') DEFAULT 'info',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =============================================
-- Données de test
-- =============================================

-- Utilisateurs (mot de passe: "password123" pour tous)
INSERT INTO users (username, email, password, password_plain, role) VALUES
('admin', 'admin@company.com', '$2y$10$8tGbOWTH3l0kFQ5JtPQUqOYf3GJVZ1oGii0Z3.JOvXMkFxTqBdOKS', 'password123', 'admin'),
('alice', 'alice@company.com', '$2y$10$8tGbOWTH3l0kFQ5JtPQUqOYf3GJVZ1oGii0Z3.JOvXMkFxTqBdOKS', 'password123', 'user'),
('bob', 'bob@company.com', '$2y$10$8tGbOWTH3l0kFQ5JtPQUqOYf3GJVZ1oGii0Z3.JOvXMkFxTqBdOKS', 'password123', 'user');

-- Employés
INSERT INTO employees (first_name, last_name, email, phone, department, salary, ssn, notes, created_by) VALUES
('Jean', 'Dupont', 'jean.dupont@company.com', '01 23 45 67 89', 'IT', 45000.00, '1 85 12 75 108 234 56', 'Développeur senior', 1),
('Marie', 'Martin', 'marie.martin@company.com', '01 98 76 54 32', 'RH', 42000.00, '2 90 05 13 055 123 45', 'Responsable recrutement', 1),
('Pierre', 'Bernard', 'pierre.bernard@company.com', '01 11 22 33 44', 'Finance', 55000.00, '1 78 11 69 388 456 78', 'Directeur financier', 1),
('Sophie', 'Petit', 'sophie.petit@company.com', '01 55 66 77 88', 'IT', 48000.00, '2 95 03 33 100 789 01', 'Cheffe de projet', 1),
('Luc', 'Moreau', 'luc.moreau@company.com', '01 44 33 22 11', 'Marketing', 38000.00, '1 88 07 44 200 234 56', 'Community manager', 1);

-- Profils
INSERT INTO profiles (user_id, bio, address, phone, is_private) VALUES
(1, 'Administrateur système', '10 Rue de la Paix, Paris', '06 00 00 00 01', 0),
(2, 'Développeuse passionnée', '25 Avenue des Champs, Lyon', '06 00 00 00 02', 1),
(3, 'Analyste données', '5 Boulevard Victor Hugo, Marseille', '06 00 00 00 03', 1);

-- Commentaires
INSERT INTO comments (user_id, content, page) VALUES
(1, 'Bienvenue sur le portail employés !', 'general'),
(2, 'Pensez à mettre à jour vos coordonnées.', 'general'),
(3, 'La réunion de vendredi est reportée.', 'general');
