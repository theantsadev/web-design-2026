<?php
/**
 * Sitemap XML dynamique
 * URL: /sitemap.xml
 */

require_once __DIR__ . '/src/config.php';

header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <!-- Page d'accueil -->
    <url>
        <loc>http://localhost/</loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <priority>1.0</priority>
        <changefreq>daily</changefreq>
    </url>

    <!-- Articles publiés -->
    <?php
    try {
        $stmt = $pdo->query('
            SELECT slug, date_modification
            FROM article
            WHERE statut = "publié"
            ORDER BY date_modification DESC
        ');

        foreach ($stmt->fetchAll() as $article) {
            $lastmod = date('Y-m-d', strtotime($article['date_modification']));
            echo '    <url>' . "\n";
            echo '        <loc>http://localhost/article/' . htmlspecialchars($article['slug']) . '</loc>' . "\n";
            echo '        <lastmod>' . $lastmod . '</lastmod>' . "\n";
            echo '        <priority>0.8</priority>' . "\n";
            echo '        <changefreq>weekly</changefreq>' . "\n";
            echo '    </url>' . "\n";
        }
    } catch (Exception $e) {
        // Erreur de connexion
    }
    ?>

    <!-- Catégories -->
    <?php
    try {
        $stmt = $pdo->query('SELECT slug FROM categorie');

        foreach ($stmt->fetchAll() as $cat) {
            echo '    <url>' . "\n";
            echo '        <loc>http://localhost/categorie/' . htmlspecialchars($cat['slug']) . '</loc>' . "\n";
            echo '        <lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";
            echo '        <priority>0.7</priority>' . "\n";
            echo '        <changefreq>weekly</changefreq>' . "\n";
            echo '    </url>' . "\n";
        }
    } catch (Exception $e) {
        // Erreur de connexion
    }
    ?>

    <!-- Tags -->
    <?php
    try {
        $stmt = $pdo->query('SELECT slug FROM tag');

        foreach ($stmt->fetchAll() as $tag) {
            echo '    <url>' . "\n";
            echo '        <loc>http://localhost/tag/' . htmlspecialchars($tag['slug']) . '</loc>' . "\n";
            echo '        <lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";
            echo '        <priority>0.6</priority>' . "\n";
            echo '        <changefreq>monthly</changefreq>' . "\n";
            echo '    </url>' . "\n";
        }
    } catch (Exception $e) {
        // Erreur de connexion
    }
    ?>
</urlset>
