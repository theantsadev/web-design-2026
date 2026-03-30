<?php
/**
 * Fonctions utilitaires du projet
 */

/**
 * Génère un slug à partir d'une chaîne
 * Convertit en minuscules, translittère les accents, supprime caractères spéciaux
 */
function generateSlug($text) {
    // Convertir en minuscules
    $text = mb_strtolower($text, 'UTF-8');

    // Translittération: accents → ASCII
    $transliteration = array(
        'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
        'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
        'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
        'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
        'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
        'ç' => 'c', 'ñ' => 'n',
        '&' => 'et',
        'œ' => 'oe', 'æ' => 'ae'
    );

    foreach ($transliteration as $from => $to) {
        $text = str_replace($from, $to, $text);
    }

    // Remplacer les espaces et caractères spéciaux par des tirets
    $text = preg_replace('/[^a-z0-9\s\-]/u', '', $text);
    $text = preg_replace('/[\s\-]+/', '-', $text);
    $text = trim($text, '-');

    return $text;
}

/**
 * Récupère les paramètres GET de manière sécurisée
 */
function getParam($key, $default = null) {
    return $_GET[$key] ?? $default;
}

/**
 * Récupère les paramètres POST de manière sécurisée
 */
function postParam($key, $default = null) {
    return $_POST[$key] ?? $default;
}

/**
 * Échappe une chaîne HTML pour éviter les attaques XSS
 */
function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Formate une date au format FR
 */
function formatDate($date, $format = 'd/m/Y à H:i') {
    if (!$date) return '';
    if (is_string($date)) {
        $date = new DateTime($date);
    }
    return $date->format($format);
}

/**
 * Retourne le basepath du site
 */
function basePath() {
    return '/';
}

/**
 * Génère une URL complète
 */
function url($path = '') {
    return basePath() . ltrim($path, '/');
}
