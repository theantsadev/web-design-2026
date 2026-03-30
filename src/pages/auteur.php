<?php
/**
 * Page auteur - affiche infos auteur et ses articles
 * URL: /auteur/{slug} -> utilise le nom comme slug
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

$slug = getParam('slug', '');

if (empty($slug)) {
    header('Location: /');
    exit;
}

// Récupérer l'auteur par slug (nom transformé en slug)
$authorsStmt = $pdo->query('SELECT * FROM auteur');
$auteur = null;

foreach ($authorsStmt->fetchAll() as $a) {
    if (generateSlug($a['nom']) === $slug) {
        $auteur = $a;
        break;
    }
}

if (!$auteur) {
    header('HTTP/1.0 404 Not Found');
    die('<h1>Auteur non trouvé</h1>');
}

// Récupérer les articles de cet auteur
$stmt = $pdo->prepare('
    SELECT a.id, a.titre, a.slug, a.chapeau, a.date_publication,
           cat.nom as categorie_nom, img.url as image_url, img.alt as image_alt
    FROM article a
    LEFT JOIN categorie cat ON a.categorie_id = cat.id
    LEFT JOIN image img ON a.id = img.article_id AND img.est_principale = 1
    WHERE a.auteur_id = ? AND a.statut = "publié"
    ORDER BY a.date_publication DESC
');
$stmt->execute([$auteur['id']]);
$articles = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auteur: <?php echo escape($auteur['nom']); ?></title>
    <meta name="description" content="Articles de <?php echo escape($auteur['nom']); ?>">

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

        .author-intro {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .author-photo {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: #e0e0e0;
            flex-shrink: 0;
            overflow: hidden;
        }

        .author-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .author-info h1 {
            font-size: 2rem;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .author-info p {
            color: #666;
            margin: 0.5rem 0;
        }

        .author-info a {
            color: #2a5298;
            text-decoration: none;
        }

        .author-info a:hover {
            text-decoration: underline;
        }

        .articles-section h2 {
            font-size: 1.8rem;
            color: #2c3e50;
            margin: 2rem 0 1.5rem 0;
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

            .author-intro {
                flex-direction: column;
                text-align: center;
            }

            .author-photo {
                width: 120px;
                height: 120px;
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
        <div class="author-intro">
            <div class="author-photo">
                <?php if ($auteur['photo_url']): ?>
                    <img src="<?php echo escape($auteur['photo_url']); ?>" alt="<?php echo escape($auteur['nom']); ?>">
                <?php else: ?>
                    <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem;">
                        ✍️
                    </div>
                <?php endif; ?>
            </div>

            <div class="author-info">
                <h1><?php echo escape($auteur['nom']); ?></h1>
                <?php if ($auteur['bio']): ?>
                    <p><?php echo escape($auteur['bio']); ?></p>
                <?php endif; ?>
                <?php if ($auteur['email']): ?>
                    <p>✉️ <a href="mailto:<?php echo escape($auteur['email']); ?>"><?php echo escape($auteur['email']); ?></a></p>
                <?php endif; ?>
                <p style="margin-top: 1rem; font-weight: bold;">
                    📝 <?php echo count($articles); ?> article<?php echo count($articles) > 1 ? 's' : ''; ?> publié<?php echo count($articles) > 1 ? 's' : ''; ?>
                </p>
            </div>
        </div>

        <div class="articles-section">
            <h2>Articles de <?php echo escape($auteur['nom']); ?></h2>

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
                                        <span style="background: #2a5298; color: white; padding: 0.25rem 0.5rem; border-radius: 3px;">
                                            <?php echo escape($article['categorie_nom']); ?>
                                        </span>
                                    <?php endif; ?>
                                    Publié le <?php echo formatDate($article['date_publication'], 'd/m/Y'); ?>
                                </div>

                                <h3 class="article-title"><?php echo escape($article['titre']); ?></h3>

                                <p class="article-excerpt">
                                    <?php echo escape(substr($article['chapeau'] ?: '', 0, 150)); ?>...
                                </p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-articles">
                    <p>Aucun article publié de cet auteur pour le moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>&copy; 2026 Actualités Iran. Tous droits réservés.</p>
    </footer>
</body>
</html>
