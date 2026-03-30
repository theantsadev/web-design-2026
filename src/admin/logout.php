<?php
/**
 * Logout - Déconnexion utilisateur
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../session.php';

logout();
header('Location: /admin/login/');
exit;
