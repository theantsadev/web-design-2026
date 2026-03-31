<?php
/**
 * Liste des tags
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../functions.php';
require_once __DIR__ . '/../../session.php';

requireLogin();

$stmt = $pdo->query('SELECT id, nom, slug FROM tag ORDER BY nom');
$tags = $stmt->fetchAll();
$total = count($tags);

// Suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = (int)$_POST['delete_id'];
    try {
        $pdo->prepare('DELETE FROM tag WHERE id = ?')->execute([$deleteId]);
        header('Location: /admin/tags/');
        exit;
    } catch (PDOException $e) {
        $error = 'Erreur: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tags - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/admin/assets/modern-admin.css">
    <link rel="stylesheet" href="/admin/assets/tables.css">
</head>
<body>
    <div class="admin-layout">
        <nav class="navbar">
            <div class="navbar-brand"><i class="fas fa-tags"></i> Tags</div>
            <div class="navbar-user">
                <a href="/admin/dashboard/" class="btn btn-secondary btn-sm"><i class="fas fa-chart-pie"></i> Dashboard</a>
                <a href="/admin/logout" class="btn btn-secondary btn-sm"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
            </div>
        </nav>

        <aside class="sidebar">
            <nav class="sidebar-nav">
                <div class="nav-group">
                    <div class="nav-group-title">Menu</div>
                    <div class="nav-item"><a href="/admin/dashboard/" class="nav-link"><span class="nav-icon"><i class="fas fa-chart-pie"></i></span><span class="nav-text">Dashboard</span></a></div>
                    <div class="nav-item"><a href="/admin/articles/" class="nav-link"><span class="nav-icon"><i class="fas fa-newspaper"></i></span><span class="nav-text">Articles</span></a></div>
                    <div class="nav-item"><a href="/admin/categories/" class="nav-link"><span class="nav-icon"><i class="fas fa-folder"></i></span><span class="nav-text">Catégories</span></a></div>
                    <div class="nav-item"><a href="/admin/tags/" class="nav-link active"><span class="nav-icon"><i class="fas fa-tags"></i></span><span class="nav-text">Tags</span></a></div>
                    <div class="nav-item"><a href="/admin/auteurs/" class="nav-link"><span class="nav-icon"><i class="fas fa-users"></i></span><span class="nav-text">Auteurs</span></a></div>
                </div>
            </nav>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <div>
                    <h1 class="page-title"><i class="fas fa-tags"></i> Tags</h1>
                    <p class="page-subtitle">Gérez les mots-clés de vos articles</p>
                </div>
                <a href="/admin/tags/new" class="btn"><i class="fas fa-plus"></i> Nouveau Tag</a>
            </div>

            <?php if (!empty($tags)): ?>
                <div class="data-table">
                    <div class="data-table-header">
                        <div class="data-table-title"><i class="fas fa-list"></i> Liste <span class="badge badge-primary"><?php echo $total; ?></span></div>
                    </div>
                    <div class="table-wrapper">
                        <table class="enhanced-table">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Slug</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tags as $tag): ?>
                                    <tr>
                                        <td data-label="Nom"><div class="table-cell-title"><i class="fas fa-hashtag"></i> <?php echo escape($tag['nom']); ?></div></td>
                                        <td data-label="Slug"><div class="table-cell-subtitle"><i class="fas fa-link"></i> <?php echo escape($tag['slug']); ?></div></td>
                                        <td data-label="Actions">
                                            <div class="table-cell-actions">
                                                <a href="/admin/tags/edit/<?php echo $tag['id']; ?>" class="action-btn edit" title="Modifier"><i class="fas fa-edit"></i></a>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Confirmez?')">
                                                    <input type="hidden" name="delete_id" value="<?php echo $tag['id']; ?>">
                                                    <button class="action-btn delete" title="Supprimer"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <div class="data-table">
                    <div class="table-empty">
                        <div class="table-empty-icon"><i class="fas fa-tag"></i></div>
                        <div class="table-empty-title">Aucun tag</div>
                        <div class="table-empty-description">Créez votre premier tag.</div>
                        <a href="/admin/tags/new" class="btn"><i class="fas fa-plus"></i> Créer</a>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
