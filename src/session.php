<?php
/**
 * Gestion des sessions utilisateur
 */

session_start();

/**
 * Connecte un utilisateur
 */
function login($userId, $login, $role) {
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_login'] = $login;
    $_SESSION['user_role'] = $role;
}

/**
 * Déconnecte l'utilisateur
 */
function logout() {
    $_SESSION = [];
    session_destroy();
}

/**
 * Vérifie si l'utilisateur est connecté
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Récupère l'utilisateur actuel ou redirection
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /admin/login/');
        exit;
    }
}

/**
 * Récupère l'utilisateur actuel
 */
function getCurrentUser() {
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'login' => $_SESSION['user_login'] ?? null,
        'role' => $_SESSION['user_role'] ?? null,
    ];
}

/**
 * Vérifie qu'on est admin
 */
function requireAdmin() {
    requireLogin();
    if ($_SESSION['user_role'] !== 'admin') {
        header('HTTP/1.0 403 Forbidden');
        die('Accès refusé');
    }
}

/**
 * Hash un mot de passe avec bcrypt
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
}

/**
 * Vérifie un mot de passe contre un hash
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}
