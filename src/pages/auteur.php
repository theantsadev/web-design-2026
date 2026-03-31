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
    <title>Articles de <?php echo escape($auteur['nom']); ?> - Le Monde Iran</title>
    <meta name="description" content="Découvrez tous les articles rédigés par <?php echo escape($auteur['nom']); ?> sur Le Monde Iran. Analyses et actualités sur l'Iran.">

    <!-- Open Graph -->
    <meta property="og:title" content="Articles de <?php echo escape($auteur['nom']); ?> - Le Monde Iran">
    <meta property="og:description" content="Découvrez tous les articles de <?php echo escape($auteur['nom']); ?>">
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
            <span>Auteurs</span>
            <span>></span>
            <span><?php echo escape($auteur['nom']); ?></span>
        </nav>

        <div class="article-page">
            <!-- Contenu principal -->
            <div class="article-content">
                <!-- Header de la page auteur avec photo -->
                <header class="page-header" style="display: flex; align-items: center; gap: 32px; margin-bottom: 48px;">
                    <div style="width: 120px; height: 120px; border-radius: 50%; overflow: hidden; flex-shrink: 0; background: linear-gradient(135deg, #c41e3a 0%, #a01729 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 48px;">
                        <?php if ($auteur['photo_url']): ?>
                            <img src="<?php echo escape($auteur['photo_url']); ?>" alt="<?php echo escape($auteur['nom']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            ✍️
                        <?php endif; ?>
                    </div>
                    <div style="flex: 1;">
                        <h1 class="page-title"><?php echo escape($auteur['nom']); ?></h1>
                        <?php if ($auteur['bio']): ?>
                            <p class="page-description"><?php echo escape($auteur['bio']); ?></p>
                        <?php endif; ?>
                        <div style="display: flex; gap: 24px; margin-top: 16px; font-size: 14px; color: #6c757d;">
                            <?php if ($auteur['email']): ?>
                                <span>✉️ <a href="mailto:<?php echo escape($auteur['email']); ?>" style="color: #c41e3a; text-decoration: none;"><?php echo escape($auteur['email']); ?></a></span>
                            <?php endif; ?>
                            <span><strong><?php echo count($articles); ?></strong> articles publiés</span>
                        </div>
                    </div>
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
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-articles">
                        <p>Aucun article publié par cet auteur pour le moment.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <aside class="sidebar">
                <h3 class="sidebar-title">À propos de <?php echo escape($auteur['nom']); ?></h3>
                <div style="padding: 24px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px;">
                    <?php if ($auteur['bio']): ?>
                        <p style="color: #495057; line-height: 1.6; margin-bottom: 16px;"><?php echo escape($auteur['bio']); ?></p>
                    <?php endif; ?>
                    <p style="font-size: 14px; color: #6c757d;">
                        <strong><?php echo count($articles); ?></strong> articles publiés sur Le Monde Iran
                    </p>
                    <?php if ($auteur['email']): ?>
                        <p style="margin-top: 16px;">
                            <a href="mailto:<?php echo escape($auteur['email']); ?>" style="color: #c41e3a; text-decoration: none; font-size: 14px;">
                                ✉️ Contacter <?php echo escape($auteur['nom']); ?>
                            </a>
                        </p>
                    <?php endif; ?>
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
