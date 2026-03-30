<?php
/**
 * Routeur du BackOffice - Admin
 * Gère les URLs /admin/*
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../session.php';

// Parse l'URL admin
$pathInfo = $_SERVER['PATH_INFO'] ?? '';
$path = trim($pathInfo, '/');
$parts = explode('/', $path);

// Récupère la section (login, dashboard, articles, etc.)
$section = $parts[1] ?? '';
$action = $parts[2] ?? '';
$id = $parts[3] ?? '';

// Pages publiques (login, etc.)
if ($section === 'login') {
    include 'login.php';
    exit;
}

// Les pages suivantes requièrent une connexion
requireLogin();

// Dashboard
if ($section === 'dashboard' || empty($section)) {
    include 'dashboard.php';
    exit;
}

// Articles
if ($section === 'articles') {
    if ($action === 'new') {
        include 'articles/form.php';
    } elseif ($action === 'edit' && $id) {
        include 'articles/form.php';
    } else {
        include 'articles/list.php';
    }
    exit;
}

// Catégories
if ($section === 'categories') {
    if ($action === 'new') {
        include 'categories/form.php';
    } elseif ($action === 'edit' && $id) {
        include 'categories/form.php';
    } else {
        include 'categories/list.php';
    }
    exit;
}

// Tags
if ($section === 'tags') {
    if ($action === 'new') {
        include 'tags/form.php';
    } elseif ($action === 'edit' && $id) {
        include 'tags/form.php';
    } else {
        include 'tags/list.php';
    }
    exit;
}

// Auteurs
if ($section === 'auteurs') {
    if ($action === 'new') {
        include 'auteurs/form.php';
    } elseif ($action === 'edit' && $id) {
        include 'auteurs/form.php';
    } else {
        include 'auteurs/list.php';
    }
    exit;
}

// Page non trouvée
header('HTTP/1.0 404 Not Found');
echo '<h1>Page non trouvée</h1>';
