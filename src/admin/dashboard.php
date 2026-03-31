<?php
/**
 * Dashboard admin
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../session.php';

requireLogin();

$user = getCurrentUser();

// Récupérer les statistiques
$stats = [];

// Nombre d'articles
$stmt = $pdo->query('SELECT COUNT(*) as total FROM article');
$stats['articles'] = $stmt->fetch()['total'];

// Nombre de catégories
$stmt = $pdo->query('SELECT COUNT(*) as total FROM categorie');
$stats['categories'] = $stmt->fetch()['total'];

// Nombre de tags
$stmt = $pdo->query('SELECT COUNT(*) as total FROM tag');
$stats['tags'] = $stmt->fetch()['total'];

// Nombre d'auteurs
$stmt = $pdo->query('SELECT COUNT(*) as total FROM auteur');
$stats['auteurs'] = $stmt->fetch()['total'];

// Derniers articles
$stmt = $pdo->query('
    SELECT a.id, a.titre, a.statut, a.date_publication, a.created_at, aut.nom
    FROM article a
    LEFT JOIN auteur aut ON a.auteur_id = aut.id
    ORDER BY a.created_at DESC
    LIMIT 5
');
$recentArticles = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/admin/assets/modern-admin.css">
    <link rel="stylesheet" href="/admin/assets/dashboard.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Navbar -->
        <nav class="navbar">
            <div class="navbar-brand">
                <i class="fas fa-tachometer-alt"></i>
                Dashboard
            </div>
            <div class="navbar-user">
                <div class="user-info">
                    <div class="name">Bienvenue, <?php echo escape($user['login']); ?></div>
                    <div class="role">Administrateur</div>
                </div>
                <a href="/admin/logout" class="btn btn-secondary btn-sm">
                    <i class="fas fa-sign-out-alt"></i>
                    Déconnexion
                </a>
            </div>
        </nav>

        <!-- Sidebar -->
        <aside class="sidebar">
            <nav class="sidebar-nav">
                <div class="nav-group">
                    <div class="nav-group-title">Menu</div>
                    <div class="nav-item">
                        <a href="/admin/dashboard/" class="nav-link active">
                            <span class="nav-icon"><i class="fas fa-chart-pie"></i></span>
                            <span class="nav-text">Dashboard</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="/admin/articles/" class="nav-link">
                            <span class="nav-icon"><i class="fas fa-newspaper"></i></span>
                            <span class="nav-text">Articles</span>
                            <span class="nav-badge"><?php echo $stats['articles']; ?></span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="/admin/categories/" class="nav-link">
                            <span class="nav-icon"><i class="fas fa-folder"></i></span>
                            <span class="nav-text">Catégories</span>
                            <span class="nav-badge"><?php echo $stats['categories']; ?></span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="/admin/tags/" class="nav-link">
                            <span class="nav-icon"><i class="fas fa-tags"></i></span>
                            <span class="nav-text">Tags</span>
                            <span class="nav-badge"><?php echo $stats['tags']; ?></span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="/admin/auteurs/" class="nav-link">
                            <span class="nav-icon"><i class="fas fa-users"></i></span>
                            <span class="nav-text">Auteurs</span>
                            <span class="nav-badge"><?php echo $stats['auteurs']; ?></span>
                        </a>
                    </div>
                </div>
                <div class="nav-group">
                    <div class="nav-group-title">Site</div>
                    <div class="nav-item">
                        <a href="/" target="_blank" class="nav-link">
                            <span class="nav-icon"><i class="fas fa-external-link-alt"></i></span>
                            <span class="nav-text">Voir le site</span>
                        </a>
                    </div>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <div>
                    <h1 class="page-title"><i class="fas fa-chart-line"></i> Tableau de bord</h1>
                    <p class="page-subtitle">Vue d'ensemble de votre site</p>
                </div>
            </div>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon articles"><i class="fas fa-newspaper"></i></div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['articles']; ?></div>
                        <div class="stat-label">Articles</div>
                        <div class="stat-info">Total des articles</div>
                    </div>
                    <a href="/admin/articles/" class="stat-action"><i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="stat-card">
                    <div class="stat-icon categories"><i class="fas fa-folder"></i></div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['categories']; ?></div>
                        <div class="stat-label">Catégories</div>
                        <div class="stat-info">Thématiques</div>
                    </div>
                    <a href="/admin/categories/" class="stat-action"><i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="stat-card">
                    <div class="stat-icon tags"><i class="fas fa-tags"></i></div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['tags']; ?></div>
                        <div class="stat-label">Tags</div>
                        <div class="stat-info">Mots-clés</div>
                    </div>
                    <a href="/admin/tags/" class="stat-action"><i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="stat-card">
                    <div class="stat-icon auteurs"><i class="fas fa-users"></i></div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['auteurs']; ?></div>
                        <div class="stat-label">Auteurs</div>
                        <div class="stat-info">Rédacteurs</div>
                    </div>
                    <a href="/admin/auteurs/" class="stat-action"><i class="fas fa-arrow-right"></i></a>
                </div>
            </div>

            <!-- Sections -->
            <div class="dashboard-sections">
                <div class="section">
                    <div class="section-header">
                        <h2><i class="fas fa-bolt"></i> Actions rapides</h2>
                    </div>
                    <div class="quick-actions">
                        <a href="/admin/articles/new" class="quick-action-btn primary">
                            <i class="fas fa-plus"></i>
                            <span>Nouvel article</span>
                        </a>
                        <a href="/admin/categories/new" class="quick-action-btn">
                            <i class="fas fa-folder-plus"></i>
                            <span>Catégorie</span>
                        </a>
                        <a href="/admin/tags/new" class="quick-action-btn">
                            <i class="fas fa-tag"></i>
                            <span>Tag</span>
                        </a>
                        <a href="/admin/auteurs/new" class="quick-action-btn">
                            <i class="fas fa-user-plus"></i>
                            <span>Auteur</span>
                        </a>
                    </div>
                </div>

                <div class="section">
                    <div class="section-header">
                        <h2><i class="fas fa-clock"></i> Articles récents</h2>
                    </div>
                    <div class="recent-articles">
                        <?php if (!empty($recentArticles)): ?>
                            <?php foreach ($recentArticles as $article): ?>
                                <div class="recent-article">
                                    <div class="article-info">
                                        <h4><?php echo escape($article['titre']); ?></h4>
                                        <div class="article-meta">
                                            <span class="badge badge-<?php echo $article['statut'] === 'publié' ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($article['statut']); ?>
                                            </span>
                                            <span class="article-date"><?php echo formatDate($article['created_at'], 'd/m/Y'); ?></span>
                                        </div>
                                    </div>
                                    <a href="/admin/articles/edit/<?php echo $article['id']; ?>" class="article-action">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-newspaper"></i>
                                <p>Aucun article récent</p>
                                <a href="/admin/articles/new" class="btn btn-primary btn-sm">Créer un article</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
