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

    <!-- CSS externe -->
    <link rel="stylesheet" href="/pages/style.css">

    <style>
        /* Quelques styles spécifiques à la page article si nécessaire */
    </style>
</head>
<body>
    <!-- Header style Le Monde -->
    <header class="site-header">
        <div class="header-top">
            Suivez les dernières actualités sur l'Iran - Mise à jour continue
        </div>
        <div class="header-main">
            <div>
                <a href="/" class="logo">Le Monde Iran</a>
                <div class="header-subtitle">Informations fiables et analyses approfondies</div>
            </div>
        </div>
    </header>

    <!-- Navigation principale -->
    <nav class="main-nav">
        <div class="nav-container">
            <ul class="nav-links">
                <li><a href="/">À la une</a></li>
                <li><a href="/categorie/politique">Politique</a></li>
                <li><a href="/categorie/economie">Économie</a></li>
                <li><a href="/categorie/societe">Société</a></li>
                <li><a href="/categorie/militaire">International</a></li>
            </ul>
            <a href="/admin/login/" class="admin-link">Administration</a>
        </div>
    </nav>

    <!-- Contenu principal -->
    <main class="container">
        <!-- Breadcrumbs -->
        <nav class="breadcrumbs">
            <a href="/">Accueil</a>
            <span>></span>
            <?php if ($article['categorie_nom']): ?>
                <a href="/categorie/<?php echo escape($article['categorie_slug']); ?>"><?php echo escape($article['categorie_nom']); ?></a>
                <span>></span>
            <?php endif; ?>
            <span><?php echo escape($article['titre']); ?></span>
        </nav>

        <div class="article-page">
            <!-- Article principal -->
            <article class="article-content">
                <header class="article-header">
                    <?php if ($article['categorie_nom']): ?>
                        <a href="/categorie/<?php echo escape($article['categorie_slug']); ?>" class="article-category">
                            <?php echo escape($article['categorie_nom']); ?>
                        </a>
                    <?php endif; ?>

                    <h1 class="article-title"><?php echo escape($article['titre']); ?></h1>

                    <?php if ($article['chapeau']): ?>
                        <p class="article-subtitle"><?php echo escape($article['chapeau']); ?></p>
                    <?php endif; ?>

                    <div class="article-meta-full">
                        <div class="article-author-info">
                            Publié le <time datetime="<?php echo $article['date_publication']; ?>" class="article-date">
                                <?php echo formatDate($article['date_publication'], 'd/m/Y à H:i'); ?>
                            </time>
                            <?php if ($article['auteur_nom']): ?>
                                • Par <span class="article-author-name"><?php echo escape($article['auteur_nom']); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="article-meta">
                            👁️ <?php echo number_format($article['nb_vues']); ?> vues
                        </div>
                    </div>
                </header>

                <?php if (!empty($images) && $images[0]['url']): ?>
                    <figure>
                        <img src="<?php echo escape($images[0]['url']); ?>"
                             alt="<?php echo escape($images[0]['alt'] ?: $article['titre']); ?>"
                             loading="lazy"
                             class="article-image-main">
                        <?php if ($images[0]['legende']): ?>
                            <figcaption class="article-image-caption">
                                <?php echo escape($images[0]['legende']); ?>
                            </figcaption>
                        <?php endif; ?>
                    </figure>
                <?php endif; ?>

                <div class="article-body">
                    <?php echo $article['contenu_html']; ?>
                </div>

                <!-- Images supplémentaires -->
                <?php if (!empty($images) && count($images) > 1): ?>
                    <div class="articles-images">
                        <?php for ($i = 1; $i < count($images); $i++): ?>
                            <div class="image-item" title="<?php echo escape($images[$i]['legende'] ?? $images[$i]['alt'] ?? ''); ?>">
                                <img src="<?php echo escape($images[$i]['url']); ?>"
                                     alt="<?php echo escape($images[$i]['alt'] ?? $images[$i]['legende'] ?? $article['titre']); ?>"
                                     loading="lazy">
                            </div>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>

                <!-- Tags -->
                <?php if (!empty($tags)): ?>
                    <div class="article-tags">
                        <div class="tags-title">Mots-clés :</div>
                        <?php foreach ($tags as $tag): ?>
                            <a href="/tag/<?php echo escape($tag['slug']); ?>" class="tag-link">
                                <?php echo escape($tag['nom']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Articles liés -->
                <?php if (!empty($connexes)): ?>
                    <aside class="related-articles">
                        <h2 class="related-title">Articles connexes</h2>
                        <?php foreach ($connexes as $related): ?>
                            <article class="related-article">
                                <div class="related-article-content">
                                    <h3 class="related-article-title">
                                        <a href="/article/<?php echo escape($related['slug']); ?>">
                                            <?php echo escape($related['titre']); ?>
                                        </a>
                                    </h3>
                                    <div class="related-article-date">
                                        <?php echo formatDate($related['date_publication'], 'd/m/Y'); ?>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </aside>
                <?php endif; ?>
            </article>

            <!-- Sidebar vide pour respecter le layout -->
            <aside class="sidebar">
                <h3 class="sidebar-title">À découvrir aussi</h3>
                <p style="color: #6c757d; font-style: italic;">Autres articles à venir...</p>
            </aside>
        </div>
    </main>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="footer-content">
            <p>&copy; 2026 Le Monde Iran. Tous droits réservés. | Mentions légales | Politique de confidentialité</p>
        </div>
    </footer>
</body>
</html>
