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

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Source+Sans+Pro:wght@300;400;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Source Sans Pro', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #fdfdfd;
            color: #1a1a1a;
            line-height: 1.6;
            font-size: 16px;
        }

        /* Header style Le Monde */
        .site-header {
            background: #ffffff;
            border-bottom: 3px solid #c41e3a;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 4px rgba(0,0,0,0.06);
        }

        .header-top {
            background: #f8f9fa;
            padding: 8px 0;
            font-size: 12px;
            color: #6c757d;
            text-align: center;
            border-bottom: 1px solid #dee2e6;
        }

        .header-main {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 24px;
        }

        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 36px;
            font-weight: 700;
            color: #c41e3a;
            text-decoration: none;
            letter-spacing: -1px;
        }

        .logo:hover {
            color: #a01729;
        }

        .header-subtitle {
            font-size: 13px;
            color: #6c757d;
            font-weight: 400;
            margin-top: 4px;
        }

        /* Navigation principale */
        .main-nav {
            background: #ffffff;
            border-bottom: 1px solid #dee2e6;
            padding: 0;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
        }

        .nav-links {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .nav-links li {
            margin: 0;
        }

        .nav-links a {
            display: block;
            padding: 16px 20px;
            color: #1a1a1a;
            text-decoration: none;
            font-weight: 400;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.2s ease;
            border-bottom: 2px solid transparent;
        }

        .nav-links a:hover {
            color: #c41e3a;
            border-bottom-color: #c41e3a;
            background: #f8f9fa;
        }

        .admin-link {
            background: #c41e3a;
            color: white !important;
            padding: 8px 16px !important;
            border-radius: 3px;
            font-size: 12px !important;
            text-transform: none !important;
            letter-spacing: 0 !important;
            border: none !important;
        }

        .admin-link:hover {
            background: #a01729 !important;
        }

        /* Container principal */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 32px 24px;
        }

        /* Section titre */
        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 8px;
            text-align: left;
        }

        .section-subtitle {
            color: #6c757d;
            font-size: 16px;
            margin-bottom: 40px;
            font-weight: 300;
        }

        /* Layout articles style Le Monde */
        .articles-layout {
            display: grid;
            grid-template-columns: 2fr 1fr;
            grid-gap: 48px;
            margin-top: 32px;
        }

        /* Article principal (Une) */
        .main-article {
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 32px;
        }

        .main-article-link {
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .main-article-image {
            width: 100%;
            height: 320px;
            object-fit: cover;
            margin-bottom: 20px;
        }

        .main-article-category {
            display: inline-block;
            background: #c41e3a;
            color: white;
            padding: 4px 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 16px;
        }

        .main-article-title {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            line-height: 1.3;
            font-weight: 700;
            margin-bottom: 16px;
            color: #1a1a1a;
        }

        .main-article-title:hover {
            color: #c41e3a;
        }

        .main-article-excerpt {
            font-size: 16px;
            line-height: 1.6;
            color: #495057;
            margin-bottom: 16px;
        }

        .article-meta {
            font-size: 13px;
            color: #868e96;
            font-weight: 400;
        }

        .article-author {
            font-weight: 600;
            color: #1a1a1a;
        }

        /* Sidebar articles */
        .sidebar {
            border-left: 1px solid #dee2e6;
            padding-left: 32px;
        }

        .sidebar-title {
            font-family: 'Playfair Display', serif;
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 24px;
            color: #1a1a1a;
            padding-bottom: 8px;
            border-bottom: 2px solid #c41e3a;
        }

        .sidebar-article {
            margin-bottom: 32px;
            padding-bottom: 24px;
            border-bottom: 1px solid #e9ecef;
        }

        .sidebar-article:last-child {
            border-bottom: none;
        }

        .sidebar-article-link {
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .sidebar-article-image {
            width: 100%;
            height: 140px;
            object-fit: cover;
            margin-bottom: 12px;
        }

        .sidebar-article-category {
            display: inline-block;
            background: #e9ecef;
            color: #6c757d;
            padding: 3px 8px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .sidebar-article-title {
            font-family: 'Playfair Display', serif;
            font-size: 16px;
            line-height: 1.4;
            font-weight: 700;
            margin-bottom: 8px;
            color: #1a1a1a;
        }

        .sidebar-article-title:hover {
            color: #c41e3a;
        }

        .sidebar-article-excerpt {
            font-size: 14px;
            line-height: 1.5;
            color: #6c757d;
            margin-bottom: 8px;
        }

        /* Message vide */
        .no-articles {
            grid-column: 1 / -1;
            text-align: center;
            padding: 80px 32px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
        }

        .no-articles p {
            font-size: 18px;
            color: #6c757d;
            font-weight: 300;
        }

        /* Footer style Le Monde */
        .site-footer {
            background: #1a1a1a;
            color: #adb5bd;
            margin-top: 64px;
            padding: 32px 0;
            text-align: center;
            font-size: 14px;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .articles-layout {
                grid-template-columns: 1fr;
                grid-gap: 32px;
            }

            .sidebar {
                border-left: none;
                border-top: 1px solid #dee2e6;
                padding-left: 0;
                padding-top: 32px;
            }
        }

        @media (max-width: 768px) {
            .header-main {
                padding: 16px 20px;
            }

            .logo {
                font-size: 28px;
            }

            .nav-links {
                flex-wrap: wrap;
            }

            .nav-links a {
                padding: 12px 12px;
                font-size: 12px;
            }

            .container {
                padding: 24px 20px;
            }

            .section-title {
                font-size: 24px;
            }

            .main-article-title {
                font-size: 22px;
            }

            .main-article-image {
                height: 240px;
            }

            .articles-layout {
                grid-gap: 24px;
            }
        }

        @media (max-width: 480px) {
            .header-main {
                flex-direction: column;
                text-align: center;
            }

            .logo {
                margin-bottom: 8px;
            }

            .nav-container {
                flex-direction: column;
            }

            .nav-links {
                justify-content: center;
                margin-bottom: 16px;
            }

            .container {
                padding: 16px;
            }
        }
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
