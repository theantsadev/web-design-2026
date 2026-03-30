<?php
/**
 * Page filtrage par catégorie
 * URL: /categorie/{slug}
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

$slug = getParam('slug', '');

if (empty($slug)) {
    header('Location: /');
    exit;
}

// Récupérer la catégorie
$stmt = $pdo->prepare('SELECT * FROM categorie WHERE slug = ?');
$stmt->execute([$slug]);
$categorie = $stmt->fetch();

if (!$categorie) {
    header('HTTP/1.0 404 Not Found');
    die('<h1>Catégorie non trouvée</h1>');
}

// Récupérer les articles de cette catégorie
$stmt = $pdo->prepare('
    SELECT a.id, a.titre, a.slug, a.chapeau, a.date_publication,
           aut.nom as auteur_nom, img.url as image_url, img.alt as image_alt
    FROM article a
    LEFT JOIN auteur aut ON a.auteur_id = aut.id
    LEFT JOIN image img ON a.id = img.article_id AND img.est_principale = 1
    WHERE a.categorie_id = ? AND a.statut = "publié"
    ORDER BY a.date_publication DESC
');
$stmt->execute([$categorie['id']]);
$articles = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catégorie: <?php echo escape($categorie['nom']); ?></title>
    <meta name="description" content="<?php echo escape($categorie['meta_description'] ?? $categorie['description'] ?? ''); ?>">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }

        header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 1.5rem;
            text-align: center;
        }

        header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
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
        }

        nav a:hover {
            color: #4CAF50;
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .category-intro {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .category-intro h1 {
            font-size: 2.5rem;
            color: #2c3e50;
            margin-bottom: 1rem;
        }

        .category-intro p {
            font-size: 1.1rem;
            color: #666;
        }

        .articles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
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

        .article-title {
            font-size: 1.4rem;
            margin: 0.5rem 0;
            font-weight: bold;
        }

        .article-excerpt {
            color: #666;
            font-size: 0.95rem;
            margin: 0.5rem 0;
        }

        .no-articles {
            text-align: center;
            padding: 3rem;
            color: #666;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
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
    </header>

    <nav>
        <a href="/">Accueil</a>
        <a href="/categorie/politique">Politique</a>
        <a href="/categorie/economie">Économie</a>
        <a href="/categorie/societe">Société</a>
        <a href="/categorie/militaire">Militaire</a>
    </nav>

    <main class="container">
        <div class="category-intro">
            <h1><?php echo escape($categorie['nom']); ?></h1>
            <?php if ($categorie['description']): ?>
                <p><?php echo escape($categorie['description']); ?></p>
            <?php endif; ?>
        </div>

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
                                Publié le <?php echo formatDate($article['date_publication'], 'd/m/Y'); ?>
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
                <p>Aucun article dans cette catégorie pour le moment.</p>
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2026 Actualités Iran. Tous droits réservés.</p>
    </footer>
</body>
</html>
