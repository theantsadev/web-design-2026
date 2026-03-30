<?php
/**
 * Configuration de la base de données
 * Fichier de connexion PDO centralisé
 */

$db_host = getenv('MYSQL_HOST') ?: 'db';
$db_name = getenv('MYSQL_DATABASE') ?: 'guerre_iran';
$db_user = getenv('MYSQL_USER') ?: 'user';
$db_password = getenv('MYSQL_PASSWORD') ?: 'password';

try {
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die('Erreur de connexion à la base de données: ' . $e->getMessage());
}
