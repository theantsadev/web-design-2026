<?php
/**
 * Page article détail
 * URL: /article/{slug}
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

$slug = getParam('slug', '');

if (empty($slug)) {
    header('Location: /');
    exit;
}

// Récupérer l'article
$stmt = $pdo->prepare('
    SELECT a.*, aut.nom as auteur_nom, aut.email, cat.nom as categorie_nom, cat.slug as categorie_slug
    FROM article a
    LEFT JOIN auteur aut ON a.auteur_id = aut.id
    LEFT JOIN categorie cat ON a.categorie_id = cat.id
    WHERE a.slug = ? AND a.statut = "publié"
');
$stmt->execute([$slug]);
$article = $stmt->fetch();

if (!$article) {
    header('HTTP/1.0 404 Not Found');
    die('<h1>Article non trouvé</h1>');
}

// Incrémenter les vues
$pdo->prepare('UPDATE article SET nb_vues = nb_vues + 1 WHERE id = ?')->execute([$article['id']]);

// Récupérer les images
$stmt = $pdo->prepare('SELECT * FROM image WHERE article_id = ? ORDER BY ordre');
$stmt->execute([$article['id']]);
$images = $stmt->fetchAll();

// Récupérer les tags
$stmt = $pdo->prepare('
    SELECT t.nom, t.slug
    FROM tag t
    JOIN article_tag at ON t.id = at.tag_id
    WHERE at.article_id = ?
');
$stmt->execute([$article['id']]);
$tags = $stmt->fetchAll();

// Récupérer les articles connexes (même catégorie, excluant celui-ci)
$connexesQuery = '
    SELECT a.id, a.titre, a.slug, a.chapeau, a.date_publication
    FROM article a
    WHERE a.statut = "publié"
    '. ($article['categorie_id'] ? 'AND a.categorie_id = ' . (int)$article['categorie_id'] : '') . '
    AND a.id != ' . (int)$article['id'] . '
    ORDER BY a.date_publication DESC
    LIMIT 3
';
$stmt = $pdo->query($connexesQuery);
$connexes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo escape($article['seo_title'] ?: $article['titre']); ?></title>
    <meta name="description" content="<?php echo escape($article['seo_meta_description'] ?: substr(strip_tags($article['contenu_html']), 0, 160)); ?>">

    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo escape($article['titre']); ?>">
    <meta property="og:description" content="<?php echo escape($article['chapeau'] ?? ''); ?>">
    <meta property="og:type" content="article">
    <meta property="og:locale" content="fr_FR">
    <?php if (!empty($images) && $images[0]['url']): ?>
        <meta property="og:image" content="<?php echo escape($images[0]['url']); ?>">
    <?php endif; ?>

    <!-- Schema.org NewsArticle -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "NewsArticle",
        "headline": "<?php echo escape($article['titre']); ?>",
        "description": "<?php echo escape($article['chapeau'] ?? ''); ?>",
        "datePublished": "<?php echo date('c', strtotime($article['date_publication'])); ?>",
        "dateModified": "<?php echo date('c', strtotime($article['date_modification'])); ?>",
        "author": {
            "@type": "Person",
            "name": "<?php echo escape($article['auteur_nom'] ?? 'Admin'); ?>"
        }
        <?php if (!empty($images) && $images[0]['url']): ?>
        ,"image": "<?php echo escape($images[0]['url']); ?>"
        <?php endif; ?>
    }
    </script>

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
            line-height: 1.8;
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
            max-width: 900px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .article-header {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .article-header h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #2c3e50;
        }

        .article-meta {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
            font-size: 0.95rem;
            color: #666;
            margin-bottom: 1rem;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .category-badge {
            display: inline-block;
            background: #2a5298;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .category-badge:hover {
            background: #1e3c72;
        }

        .article-body {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .article-body h2 {
            font-size: 1.8rem;
            margin: 2rem 0 1rem 0;
            color: #2c3e50;
        }

        .article-body h3 {
            font-size: 1.4rem;
            margin: 1.5rem 0 0.8rem 0;
            color: #34495e;
        }

        .article-body p {
            margin-bottom: 1rem;
            text-align: justify;
        }

        .article-body img {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
            margin: 1.5rem 0;
        }

        .article-body ul, .article-body ol {
            margin: 1rem 0 1rem 2rem;
        }

        .article-body li {
            margin-bottom: 0.5rem;
        }

        .articles-images {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin: 2rem 0;
        }

        .image-item {
            border-radius: 4px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .image-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }

        .article-tags {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #eee;
        }

        .article-tags h3 {
            margin-bottom: 1rem;
        }

        .tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .tag {
            background: #ecf0f1;
            color: #2c3e50;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: background 0.3s;
        }

        .tag:hover {
            background: #bdc3c7;
        }

        .related-articles {
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid #eee;
        }

        .related-articles h3 {
            margin-bottom: 1.5rem;
        }

        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .related-card {
            background: #f9f9f9;
            border-radius: 4px;
            padding: 1rem;
            text-decoration: none;
            color: inherit;
            transition: transform 0.3s;
        }

        .related-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .related-card h4 {
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .related-card small {
            color: #999;
        }

        footer {
            background: #333;
            color: white;
            text-align: center;
            padding: 2rem;
            margin-top: 3rem;
        }

        @media (max-width: 768px) {
            .article-header h1 {
                font-size: 1.8rem;
            }

            .article-meta {
                flex-direction: column;
                gap: 0.5rem;
            }

            nav a {
                margin: 0 0.5rem;
                font-size: 0.9rem;
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
        <article class="article-header">
            <h1><?php echo escape($article['titre']); ?></h1>

            <div class="article-meta">
                <div class="meta-item">
                    📅 Publié le <?php echo formatDate($article['date_publication'], 'd/m/Y à H:i'); ?>
                </div>
                <?php if ($article['auteur_nom']): ?>
                    <div class="meta-item">
                        ✍️ Par <strong><?php echo escape($article['auteur_nom']); ?></strong>
                    </div>
                <?php endif; ?>
                <?php if ($article['categorie_nom']): ?>
                    <div class="meta-item">
                        <a href="/categorie/<?php echo escape($article['categorie_slug']); ?>" class="category-badge">
                            <?php echo escape($article['categorie_nom']); ?>
                        </a>
                    </div>
                <?php endif; ?>
                <div class="meta-item">
                    👁️ <?php echo number_format($article['nb_vues']); ?> vues
                </div>
            </div>

            <?php if ($article['chapeau']): ?>
                <p style="font-size: 1.2rem; color: #666; font-style: italic;">
                    <?php echo escape($article['chapeau']); ?>
                </p>
            <?php endif; ?>
        </article>

        <?php if (!empty($images)): ?>
            <div class="articles-images">
                <?php foreach ($images as $img): ?>
                    <div class="image-item" title="<?php echo escape($img['legende'] ?? $img['alt'] ?? ''); ?>">
                        <img src="<?php echo escape($img['url']); ?>" alt="<?php echo escape($img['alt'] ?? $img['legende'] ?? $article['titre']); ?>">
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <article class="article-body">
            <?php echo $article['contenu_html']; ?>

            <?php if (!empty($tags)): ?>
                <div class="article-tags">
                    <h3>Tags</h3>
                    <div class="tags">
                        <?php foreach ($tags as $tag): ?>
                            <a href="/tag/<?php echo escape($tag['slug']); ?>" class="tag">
                                #<?php echo escape($tag['nom']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($connexes)): ?>
                <div class="related-articles">
                    <h3>Articles connexes</h3>
                    <div class="related-grid">
                        <?php foreach ($connexes as $rel): ?>
                            <a href="/article/<?php echo escape($rel['slug']); ?>" class="related-card">
                                <h4><?php echo escape($rel['titre']); ?></h4>
                                <p><?php echo escape(substr($rel['chapeau'] ?? '', 0, 80)); ?>...</p>
                                <small><?php echo formatDate($rel['date_publication'], 'd/m/Y'); ?></small>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </article>
    </main>

    <footer>
        <p>&copy; 2026 Actualités Iran. Tous droits réservés.</p>
    </footer>
</body>
</html>
