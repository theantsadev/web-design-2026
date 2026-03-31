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
    <title>Le Monde Iran - Actualités et analyses sur l'Iran</title>
    <meta name="description" content="Suivez les dernières actualités, analyses et informations sur la situation politique, économique et internationale de l'Iran. Actualités fiables et vérifiées.">

    <!-- Open Graph -->
    <meta property="og:title" content="Le Monde Iran - Actualités et analyses sur l'Iran">
    <meta property="og:description" content="Suivez les dernières actualités et analyses approfondies sur l'Iran - Politique, économie, société et international">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="fr_FR">

    <!-- CSS externe -->
    <link rel="stylesheet" href="pages/style.css">
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
        <h1 class="section-title">À la une</h1>
        <p class="section-subtitle">Les dernières actualités et analyses sur l'Iran</p>

        <?php if (!empty($articles)): ?>
            <div class="articles-layout">
                <!-- Article principal (premier article) -->
                <div class="main-content">
                    <?php $mainArticle = array_shift($articles); ?>
                    <article class="main-article">
                        <a href="/article/<?php echo escape($mainArticle['slug']); ?>" class="main-article-link">
                            <?php if ($mainArticle['image_url']): ?>
                                <img src="<?php echo escape($mainArticle['image_url']); ?>"
                                     alt="<?php echo escape($mainArticle['image_alt'] ?: $mainArticle['titre']); ?>"
                                     class="main-article-image">
                            <?php else: ?>
                                <div class="main-article-image"
                                     style="background: linear-gradient(135deg, #c41e3a 0%, #a01729 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 24px;">
                                    📰
                                </div>
                            <?php endif; ?>

                            <?php if ($mainArticle['categorie_nom']): ?>
                                <div class="main-article-category">
                                    <?php echo escape($mainArticle['categorie_nom']); ?>
                                </div>
                            <?php endif; ?>

                            <h2 class="main-article-title">
                                <?php echo escape($mainArticle['titre']); ?>
                            </h2>

                            <p class="main-article-excerpt">
                                <?php echo escape(substr($mainArticle['chapeau'] ?: '', 0, 200)); ?>...
                            </p>

                            <div class="article-meta">
                                Publié le <?php echo formatDate($mainArticle['date_publication'], 'd/m/Y à H:i'); ?>
                                <?php if ($mainArticle['auteur_nom']): ?>
                                    • Par <span class="article-author"><?php echo escape($mainArticle['auteur_nom']); ?></span>
                                <?php endif; ?>
                            </div>
                        </a>
                    </article>
                </div>

                <!-- Sidebar avec articles secondaires -->
                <aside class="sidebar">
                    <h3 class="sidebar-title">Autres actualités</h3>

                    <?php foreach ($articles as $article): ?>
                        <article class="sidebar-article">
                            <a href="/article/<?php echo escape($article['slug']); ?>" class="sidebar-article-link">
                                <?php if ($article['image_url']): ?>
                                    <img src="<?php echo escape($article['image_url']); ?>"
                                         alt="<?php echo escape($article['image_alt'] ?: $article['titre']); ?>"
                                         class="sidebar-article-image">
                                <?php else: ?>
                                    <div class="sidebar-article-image"
                                         style="background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%); display: flex; align-items: center; justify-content: center; color: #adb5bd; font-size: 16px;">
                                        📄
                                    </div>
                                <?php endif; ?>

                                <?php if ($article['categorie_nom']): ?>
                                    <div class="sidebar-article-category">
                                        <?php echo escape($article['categorie_nom']); ?>
                                    </div>
                                <?php endif; ?>

                                <h4 class="sidebar-article-title">
                                    <?php echo escape($article['titre']); ?>
                                </h4>

                                <p class="sidebar-article-excerpt">
                                    <?php echo escape(substr($article['chapeau'] ?: '', 0, 120)); ?>...
                                </p>

                                <div class="article-meta">
                                    <?php echo formatDate($article['date_publication'], 'd/m/Y'); ?>
                                    <?php if ($article['auteur_nom']): ?>
                                        • <?php echo escape($article['auteur_nom']); ?>
                                    <?php endif; ?>
                                </div>
                            </a>
                        </article>
                    <?php endforeach; ?>
                </aside>
            </div>
        <?php else: ?>
            <div class="no-articles">
                <p>Aucun article publié pour le moment. L'équipe rédactionnelle travaille sur de nouveaux contenus.</p>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="footer-content">
            <p>&copy; 2026 Le Monde Iran. Tous droits réservés. | Mentions légales | Politique de confidentialité</p>
        </div>
    </footer>
</body>
</html>
