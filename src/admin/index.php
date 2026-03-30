<?php
/**
 * Routeur du BackOffice - Admin
 * Gère les URLs /admin/*
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../session.php';

// Récupère la page et action depuis les paramètres GET (définis par .htaccess)
$page = getParam('page', '');
$action = getParam('action', '');
$id = (int)getParam('id', 0);

// Pages publiques (login, logout)
if ($page === 'login') {
    include 'login.php';
    exit;
}

if ($page === 'logout') {
    include 'logout.php';
    exit;
}

// Les pages suivantes requièrent une connexion
requireLogin();

// Dashboard
if ($page === 'dashboard' || empty($page)) {
    include 'dashboard.php';
    exit;
}

// Articles
if ($page === 'articles') {
    if ($action === 'new') {
        $_GET['edit'] = null;
        include 'articles/form.php';
    } elseif ($action === 'edit' && $id) {
        $_GET['edit'] = $id;
        include 'articles/form.php';
    } else {
        include 'articles/list.php';
    }
    exit;
}

// Catégories
if ($page === 'categories') {
    if ($action === 'new') {
        $_GET['edit'] = null;
        include 'categories/form.php';
    } elseif ($action === 'edit' && $id) {
        $_GET['edit'] = $id;
        include 'categories/form.php';
    } else {
        include 'categories/list.php';
    }
    exit;
}

// Tags
if ($page === 'tags') {
    if ($action === 'new') {
        $_GET['edit'] = null;
        include 'tags/form.php';
    } elseif ($action === 'edit' && $id) {
        $_GET['edit'] = $id;
        include 'tags/form.php';
    } else {
        include 'tags/list.php';
    }
    exit;
}

// Auteurs
if ($page === 'auteurs') {
    if ($action === 'new') {
        $_GET['edit'] = null;
        include 'auteurs/form.php';
    } elseif ($action === 'edit' && $id) {
        $_GET['edit'] = $id;
        include 'auteurs/form.php';
    } else {
        include 'auteurs/list.php';
    }
    exit;
}

// Page non trouvée
header('HTTP/1.0 404 Not Found');
echo '<h1>Page non trouvée</h1>';
