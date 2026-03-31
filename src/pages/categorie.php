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
    <title><?php echo escape($categorie['nom']); ?> - Le Monde Iran</title>
    <meta name="description" content="<?php echo escape($categorie['description'] ?: 'Actualités et analyses dans la rubrique ' . $categorie['nom']); ?>">

    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo escape($categorie['nom']); ?> - Le Monde Iran">
    <meta property="og:description" content="<?php echo escape($categorie['description'] ?: 'Actualités et analyses dans la rubrique ' . $categorie['nom']); ?>">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="fr_FR">

    <!-- CSS externe -->
    <link rel="stylesheet" href="/pages/style.css">
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
                <li><a href="/" <?php echo ($slug === 'accueil') ? 'style="color: #c41e3a; border-bottom-color: #c41e3a;"' : ''; ?>>À la une</a></li>
                <li><a href="/categorie/politique" <?php echo ($slug === 'politique') ? 'style="color: #c41e3a; border-bottom-color: #c41e3a;"' : ''; ?>>Politique</a></li>
                <li><a href="/categorie/economie" <?php echo ($slug === 'economie') ? 'style="color: #c41e3a; border-bottom-color: #c41e3a;"' : ''; ?>>Économie</a></li>
                <li><a href="/categorie/societe" <?php echo ($slug === 'societe') ? 'style="color: #c41e3a; border-bottom-color: #c41e3a;"' : ''; ?>>Société</a></li>
                <li><a href="/categorie/militaire" <?php echo ($slug === 'militaire') ? 'style="color: #c41e3a; border-bottom-color: #c41e3a;"' : ''; ?>>International</a></li>
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
            <span><?php echo escape($categorie['nom']); ?></span>
        </nav>

        <div class="article-page">
            <!-- Contenu principal -->
            <div class="article-content">
                <!-- Header de la page -->
                <header class="page-header">
                    <h1 class="page-title"><?php echo escape($categorie['nom']); ?></h1>
                    <?php if ($categorie['description']): ?>
                        <p class="page-description"><?php echo escape($categorie['description']); ?></p>
                    <?php endif; ?>
                    <p class="page-description">
                        <strong><?php echo count($articles); ?></strong> articles publiés
                    </p>
                </header>

                <!-- Articles avec layout spécial si il y en a -->
                <?php if (!empty($articles)): ?>
                    <!-- Article principal (premier article) -->
                    <?php $mainArticle = array_shift($articles); ?>
                    <article class="main-article" style="margin-bottom: 48px;">
                        <a href="/article/<?php echo escape($mainArticle['slug']); ?>" class="main-article-link">
                            <?php if ($mainArticle['image_url']): ?>
                                <img src="<?php echo escape($mainArticle['image_url']); ?>"
                                     alt="<?php echo escape($mainArticle['image_alt'] ?: $mainArticle['titre']); ?>"
                                     loading="lazy"
                                     class="main-article-image">
                            <?php else: ?>
                                <div class="main-article-image"
                                     style="background: linear-gradient(135deg, #c41e3a 0%, #a01729 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 24px;">
                                    📰
                                </div>
                            <?php endif; ?>

                            <div class="main-article-category">
                                <?php echo escape($categorie['nom']); ?>
                            </div>

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

                    <!-- Liste des autres articles -->
                    <?php if (!empty($articles)): ?>
                        <div class="articles-list">
                            <?php foreach ($articles as $article): ?>
                                <article class="article-card">
                                    <?php if ($article['image_url']): ?>
                                        <img src="<?php echo escape($article['image_url']); ?>"
                                             alt="<?php echo escape($article['image_alt'] ?: $article['titre']); ?>"
                                             loading="lazy"
                                             class="article-card-image">
                                    <?php else: ?>
                                        <div class="article-card-image"
                                             style="background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%); display: flex; align-items: center; justify-content: center; color: #adb5bd; font-size: 24px;">
                                            📄
                                        </div>
                                    <?php endif; ?>

                                    <div class="article-card-content">
                                        <div class="article-card-category">
                                            <?php echo escape($categorie['nom']); ?>
                                        </div>

                                        <h2 class="article-card-title">
                                            <a href="/article/<?php echo escape($article['slug']); ?>">
                                                <?php echo escape($article['titre']); ?>
                                            </a>
                                        </h2>

                                        <p class="article-card-excerpt">
                                            <?php echo escape(substr($article['chapeau'] ?: '', 0, 200)); ?>...
                                        </p>

                                        <div class="article-card-meta">
                                            <span><?php echo formatDate($article['date_publication'], 'd/m/Y'); ?></span>
                                            <?php if ($article['auteur_nom']): ?>
                                                <span>Par <?php echo escape($article['auteur_nom']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="no-articles">
                        <p>Aucun article publié dans cette catégorie pour le moment.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <aside class="sidebar">
                <h3 class="sidebar-title"><?php echo escape($categorie['nom']); ?></h3>

                <div style="padding: 24px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px; margin-bottom: 32px;">
                    <?php if ($categorie['description']): ?>
                        <p style="color: #495057; line-height: 1.6; margin-bottom: 16px;"><?php echo escape($categorie['description']); ?></p>
                    <?php endif; ?>
                    <p style="font-size: 14px; color: #6c757d;">
                        <strong><?php echo count($articles) + (isset($mainArticle) ? 1 : 0); ?></strong> articles dans cette rubrique
                    </p>
                </div>

                <h4 class="sidebar-title" style="font-size: 16px;">Autres rubriques</h4>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <a href="/categorie/politique" style="color: #1a1a1a; text-decoration: none; padding: 8px; border-radius: 3px; transition: background 0.2s;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='transparent'">Politique</a>
                    <a href="/categorie/economie" style="color: #1a1a1a; text-decoration: none; padding: 8px; border-radius: 3px; transition: background 0.2s;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='transparent'">Économie</a>
                    <a href="/categorie/societe" style="color: #1a1a1a; text-decoration: none; padding: 8px; border-radius: 3px; transition: background 0.2s;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='transparent'">Société</a>
                    <a href="/categorie/militaire" style="color: #1a1a1a; text-decoration: none; padding: 8px; border-radius: 3px; transition: background 0.2s;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='transparent'">International</a>
                </div>
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
