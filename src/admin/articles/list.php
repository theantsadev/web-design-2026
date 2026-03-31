<?php
/**
 * Liste des articles - BackOffice
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../functions.php';
require_once __DIR__ . '/../../session.php';

requireLogin();

$page = max(1, (int)getParam('page', 1));
$limit = 10;
$offset = ($page - 1) * $limit;

// Récupérer les articles
$stmt = $pdo->prepare('
    SELECT a.id, a.titre, a.slug, a.statut, a.date_publication,
           aut.nom as auteur_nom, cat.nom as categorie_nom
    FROM article a
    LEFT JOIN auteur aut ON a.auteur_id = aut.id
    LEFT JOIN categorie cat ON a.categorie_id = cat.id
    ORDER BY a.created_at DESC
    LIMIT ? OFFSET ?
');
$stmt->execute([$limit, $offset]);
$articles = $stmt->fetchAll();

// Compter le total
$stmt = $pdo->query('SELECT COUNT(*) as total FROM article');
$total = $stmt->fetch()['total'];
$pages = ceil($total / $limit);

// Traitement suppression
$deleted = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    requireAdmin();
    $deleteId = (int)$_POST['delete_id'];
    try {
        $pdo->beginTransaction();

        // Supprimer les images
        $pdo->prepare('DELETE FROM image WHERE article_id = ?')->execute([$deleteId]);
        // Supprimer les tags
        $pdo->prepare('DELETE FROM article_tag WHERE article_id = ?')->execute([$deleteId]);
        // Supprimer l'article
        $pdo->prepare('DELETE FROM article WHERE id = ?')->execute([$deleteId]);

        $pdo->commit();
        $deleted = true;
        // Recharger la page
        header('Location: /admin/articles/');
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = 'Erreur lors de la suppression: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Articles - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/admin/assets/modern-admin.css">
    <link rel="stylesheet" href="/admin/assets/tables.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Navbar -->
        <nav class="navbar">
            <div class="navbar-brand">
                <i class="fas fa-newspaper"></i>
                Articles
            </div>
            <div class="navbar-user">
                <a href="/admin/dashboard/" class="btn btn-secondary btn-sm">
                    <i class="fas fa-chart-pie"></i>
                    Dashboard
                </a>
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
                        <a href="/admin/dashboard/" class="nav-link">
                            <span class="nav-icon"><i class="fas fa-chart-pie"></i></span>
                            <span class="nav-text">Dashboard</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="/admin/articles/" class="nav-link active">
                            <span class="nav-icon"><i class="fas fa-newspaper"></i></span>
                            <span class="nav-text">Articles</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="/admin/categories/" class="nav-link">
                            <span class="nav-icon"><i class="fas fa-folder"></i></span>
                            <span class="nav-text">Catégories</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="/admin/tags/" class="nav-link">
                            <span class="nav-icon"><i class="fas fa-tags"></i></span>
                            <span class="nav-text">Tags</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="/admin/auteurs/" class="nav-link">
                            <span class="nav-icon"><i class="fas fa-users"></i></span>
                            <span class="nav-text">Auteurs</span>
                        </a>
                    </div>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <div>
                    <h1 class="page-title"><i class="fas fa-newspaper"></i> Articles</h1>
                    <p class="page-subtitle">Gérez vos articles de presse</p>
                </div>
                <a href="/admin/articles/new" class="btn">
                    <i class="fas fa-plus"></i>
                    Nouvel Article
                </a>
            </div>

            <?php if (!empty($articles)): ?>
                <div class="data-table">
                    <div class="data-table-header">
                        <div class="data-table-title">
                            <i class="fas fa-list"></i>
                            Liste des Articles
                            <span class="badge badge-primary"><?php echo $total; ?></span>
                        </div>
                        <div class="data-table-search">
                            <i class="fas fa-search"></i>
                            <input type="text" placeholder="Rechercher..." id="searchInput">
                        </div>
                    </div>

                    <div class="table-wrapper">
                        <table class="enhanced-table">
                            <thead>
                                <tr>
                                    <th>Article</th>
                                    <th>Catégorie</th>
                                    <th>Auteur</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($articles as $article): ?>
                                    <tr>
                                        <td data-label="Article">
                                            <div class="table-cell-title"><?php echo escape($article['titre']); ?></div>
                                            <div class="table-cell-subtitle">
                                                <i class="fas fa-link"></i>
                                                <?php echo escape($article['slug']); ?>
                                            </div>
                                        </td>
                                        <td data-label="Catégorie">
                                            <?php if ($article['categorie_nom']): ?>
                                                <span class="badge badge-secondary"><?php echo escape($article['categorie_nom']); ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td data-label="Auteur">
                                            <?php if ($article['auteur_nom']): ?>
                                                <div class="table-cell-avatar">
                                                    <div class="table-avatar"><?php echo strtoupper(substr($article['auteur_nom'], 0, 2)); ?></div>
                                                    <span><?php echo escape($article['auteur_nom']); ?></span>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td data-label="Statut">
                                            <span class="badge badge-<?php echo $article['statut'] === 'publié' ? 'success' : ($article['statut'] === 'brouillon' ? 'warning' : 'danger'); ?>">
                                                <?php echo ucfirst($article['statut']); ?>
                                            </span>
                                        </td>
                                        <td data-label="Date">
                                            <?php echo formatDate($article['date_publication'], 'd/m/Y'); ?>
                                        </td>
                                        <td data-label="Actions">
                                            <div class="table-cell-actions">
                                                <a href="/admin/articles/edit/<?php echo $article['id']; ?>" class="action-btn edit" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr?')">
                                                    <input type="hidden" name="delete_id" value="<?php echo $article['id']; ?>">
                                                    <button type="submit" class="action-btn delete" title="Supprimer">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($pages > 1): ?>
                        <div class="table-pagination">
                            <div class="pagination-info">
                                Page <?php echo $page; ?> sur <?php echo $pages; ?> (<?php echo $total; ?> articles)
                            </div>
                            <div class="pagination-controls">
                                <?php if ($page > 1): ?>
                                    <a href="/admin/articles/?page=<?php echo $page - 1; ?>" class="pagination-btn">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                <?php endif; ?>

                                <?php for ($p = max(1, $page - 2); $p <= min($pages, $page + 2); $p++): ?>
                                    <a href="/admin/articles/?page=<?php echo $p; ?>" class="pagination-btn <?php echo $p === $page ? 'active' : ''; ?>">
                                        <?php echo $p; ?>
                                    </a>
                                <?php endfor; ?>

                                <?php if ($page < $pages): ?>
                                    <a href="/admin/articles/?page=<?php echo $page + 1; ?>" class="pagination-btn">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="data-table">
                    <div class="table-empty">
                        <div class="table-empty-icon"><i class="fas fa-newspaper"></i></div>
                        <div class="table-empty-title">Aucun article</div>
                        <div class="table-empty-description">Créez votre premier article pour commencer.</div>
                        <a href="/admin/articles/new" class="btn">
                            <i class="fas fa-plus"></i>
                            Créer un article
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        // Simple recherche
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const filter = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('.enhanced-table tbody tr');
            rows.forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
            });
        });
    </script>
</body>
</html>
