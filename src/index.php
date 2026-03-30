<?php
/**
 * Page d'accueil - Affiche les derniers articles publiés
 */

require_once 'config.php';
require_once 'functions.php';

// Récupérer les derniers articles publiés
$query = "
    SELECT
        a.id, a.titre, a.slug, a.chapeau, a.date_publication,
        a.seo_title, a.seo_meta_description,
        aut.nom as auteur_nom,
        cat.nom as categorie_nom, cat.slug as categorie_slug,
        img.url as image_url, img.alt as image_alt
    FROM article a
    LEFT JOIN auteur aut ON a.auteur_id = aut.id
    LEFT JOIN categorie cat ON a.categorie_id = cat.id
    LEFT JOIN image img ON a.id = img.article_id AND img.est_principale = 1
    WHERE a.statut = 'publié'
    ORDER BY a.date_publication DESC
    LIMIT 10
";

$stmt = $pdo->query($query);
$articles = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualités Iran - Informations sur la situation en Iran</title>
    <meta name="description" content="Seguez les dernières actualités, analyses et informations sur la situation politique, économique et militaire en Iran.">

    <!-- Open Graph -->
    <meta property="og:title" content="Actualités Iran">
    <meta property="og:description" content="Suivez les dernières actualités sur l'Iran">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="fr_FR">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }

        header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        nav {
            background: #333;
            padding: 1rem;
            text-align: center;
        }

        nav a {
            color: white;
            text-decoration: none;
            margin: 0 1.5rem;
            font-weight: bold;
            transition: color 0.3s;
        }

        nav a:hover {
            color: #4CAF50;
        }

        .admin-link {
            float: right;
            background: #e74c3c;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            font-size: 0.9rem;
        }

        .admin-link:hover {
            background: #c0392b;
            color: white;
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .articles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
        }

        .article-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .article-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        }

        .article-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: #e0e0e0;
        }

        .article-content {
            padding: 1.5rem;
        }

        .article-meta {
            font-size: 0.85rem;
            color: #888;
            margin-bottom: 0.5rem;
        }

        .article-category {
            display: inline-block;
            background: #2a5298;
            color: white;
            padding: 0.25rem 0.7rem;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-right: 0.5rem;
        }

        .article-title {
            font-size: 1.4rem;
            margin: 0.5rem 0;
            font-weight: bold;
        }

        .article-excerpt {
            color: #666;
            font-size: 0.95rem;
            margin: 0.5rem 0;
            line-height: 1.5;
        }

        .no-articles {
            text-align: center;
            padding: 3rem;
            color: #666;
        }

        footer {
            background: #333;
            color: white;
            text-align: center;
            padding: 2rem;
            margin-top: 3rem;
        }

        @media (max-width: 768px) {
            header h1 {
                font-size: 1.8rem;
            }

            nav a {
                margin: 0 0.5rem;
                font-size: 0.9rem;
            }

            .articles-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>🌍 Actualités Iran</h1>
        <p>Informations fiables et à jour sur la situation en Iran</p>
    </header>

    <nav>
        <a href="/">Accueil</a>
        <a href="/categorie/politique">Politique</a>
        <a href="/categorie/economie">Économie</a>
        <a href="/categorie/societe">Société</a>
        <a href="/categorie/militaire">Militaire</a>
        <a href="/admin/login/" class="admin-link">Admin →</a>
    </nav>

    <main class="container">
        <h2 style="margin-bottom: 2rem; text-align: center; font-size: 1.8rem;">Derniers articles</h2>

        <?php if (!empty($articles)): ?>
            <div class="articles-grid">
                <?php foreach ($articles as $article): ?>
                    <a href="/article/<?php echo escape($article['slug']); ?>" class="article-card">
                        <?php if ($article['image_url']): ?>
                            <img src="<?php echo escape($article['image_url']); ?>" alt="<?php echo escape($article['image_alt'] ?: $article['titre']); ?>" class="article-image">
                        <?php else: ?>
                            <div class="article-image" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"></div>
                        <?php endif; ?>

                        <div class="article-content">
                            <div class="article-meta">
                                <?php if ($article['categorie_nom']): ?>
                                    <span class="article-category"><?php echo escape($article['categorie_nom']); ?></span>
                                <?php endif; ?>
                                <span>publié le <?php echo formatDate($article['date_publication'], 'd/m/Y'); ?></span>
                            </div>

                            <h3 class="article-title"><?php echo escape($article['titre']); ?></h3>

                            <p class="article-excerpt">
                                <?php echo escape(substr($article['chapeau'] ?: '', 0, 150)); ?>...
                            </p>

                            <?php if ($article['auteur_nom']): ?>
                                <div class="article-meta" style="margin-top: 1rem;">
                                    Par <strong><?php echo escape($article['auteur_nom']); ?></strong>
                                </div>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-articles">
                <p>Aucun article publié pour le moment. Revenez bientôt!</p>
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2026 Actualités Iran. Tous droits réservés.</p>
    </footer>
</body>
</html>
