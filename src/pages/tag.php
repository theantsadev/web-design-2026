<?php
/**
 * Page filtrage par tag
 * URL: /tag/{slug}
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

$slug = getParam('slug', '');

if (empty($slug)) {
    header('Location: /');
    exit;
}

// Récupérer le tag
$stmt = $pdo->prepare('SELECT * FROM tag WHERE slug = ?');
$stmt->execute([$slug]);
$tag = $stmt->fetch();

if (!$tag) {
    header('HTTP/1.0 404 Not Found');
    die('<h1>Tag non trouvé</h1>');
}

// Récupérer les articles ayant ce tag
$stmt = $pdo->prepare('
    SELECT a.id, a.titre, a.slug, a.chapeau, a.date_publication,
           aut.nom as auteur_nom, cat.nom as categorie_nom, img.url as image_url, img.alt as image_alt
    FROM article a
    JOIN article_tag at ON a.id = at.article_id
    LEFT JOIN auteur aut ON a.auteur_id = aut.id
    LEFT JOIN categorie cat ON a.categorie_id = cat.id
    LEFT JOIN image img ON a.id = img.article_id AND img.est_principale = 1
    WHERE at.tag_id = ? AND a.statut = "publié"
    ORDER BY a.date_publication DESC
');
$stmt->execute([$tag['id']]);
$articles = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo escape($tag['nom']); ?> - Le Monde Iran</title>
    <meta name="description" content="Articles liés au tag <?php echo escape($tag['nom']); ?>">

    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo escape($tag['nom']); ?> - Le Monde Iran">
    <meta property="og:description" content="Articles liés au tag <?php echo escape($tag['nom']); ?>">
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
            <span>Tags</span>
            <span>></span>
            <span><?php echo escape($tag['nom']); ?></span>
        </nav>

        <div class="article-page">
            <!-- Contenu principal -->
            <div class="article-content">
                <!-- Header de la page -->
                <header class="page-header">
                    <h1 class="page-title"># <?php echo escape($tag['nom']); ?></h1>
                    <p class="page-description">
                        <strong><?php echo count($articles); ?></strong> articles trouvés
                    </p>
                </header>

                <!-- Liste des articles -->
                <?php if (!empty($articles)): ?>
                    <div class="articles-list">
                        <?php foreach ($articles as $article): ?>
                            <article class="article-card">
                                <?php if ($article['image_url']): ?>
                                    <img src="<?php echo escape($article['image_url']); ?>"
                                         alt="<?php echo escape($article['image_alt'] ?: $article['titre']); ?>"
                                         class="article-card-image">
                                <?php else: ?>
                                    <div class="article-card-image"
                                         style="background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%); display: flex; align-items: center; justify-content: center; color: #adb5bd; font-size: 24px;">
                                        📄
                                    </div>
                                <?php endif; ?>

                                <div class="article-card-content">
                                    <?php if ($article['categorie_nom']): ?>
                                        <div class="article-card-category">
                                            <?php echo escape($article['categorie_nom']); ?>
                                        </div>
                                    <?php endif; ?>

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
                <?php else: ?>
                    <div class="no-articles">
                        <p>Aucun article trouvé pour ce mot-clé.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <aside class="sidebar">
                <h3 class="sidebar-title">À propos du tag</h3>

                <div style="padding: 24px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px; margin-bottom: 32px;">
                    <div style="display: inline-block; background: #c41e3a; color: white; padding: 8px 16px; border-radius: 20px; font-size: 14px; font-weight: 600; margin-bottom: 16px;">
                        # <?php echo escape($tag['nom']); ?>
                    </div>
                    <p style="color: #495057; line-height: 1.6; margin-bottom: 16px;">
                        Ce tag regroupe tous les articles traitant de <strong><?php echo escape($tag['nom']); ?></strong>.
                    </p>
                    <p style="font-size: 14px; color: #6c757d;">
                        <strong><?php echo count($articles); ?></strong> articles trouvés
                    </p>
                </div>

                <h4 class="sidebar-title" style="font-size: 16px;">Navigation</h4>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <a href="/" style="color: #1a1a1a; text-decoration: none; padding: 8px; border-radius: 3px; transition: background 0.2s;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='transparent'">← Retour à l'accueil</a>
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
