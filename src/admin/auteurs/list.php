<?php
/**
 * Liste des auteurs
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../functions.php';
require_once __DIR__ . '/../../session.php';

requireLogin();

$stmt = $pdo->query('SELECT id, nom, email, bio FROM auteur ORDER BY nom');
$auteurs = $stmt->fetchAll();
$total = count($auteurs);

// Suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    requireAdmin();
    $deleteId = (int)$_POST['delete_id'];
    try {
        $pdo->prepare('DELETE FROM auteur WHERE id = ?')->execute([$deleteId]);
        header('Location: /admin/auteurs/');
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
    <title>Auteurs - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/admin/assets/modern-admin.css">
    <link rel="stylesheet" href="/admin/assets/tables.css">
</head>
<body>
    <div class="admin-layout">
        <nav class="navbar">
            <div class="navbar-brand"><i class="fas fa-users"></i> Auteurs</div>
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
                    <div class="nav-item"><a href="/admin/tags/" class="nav-link"><span class="nav-icon"><i class="fas fa-tags"></i></span><span class="nav-text">Tags</span></a></div>
                    <div class="nav-item"><a href="/admin/auteurs/" class="nav-link active"><span class="nav-icon"><i class="fas fa-users"></i></span><span class="nav-text">Auteurs</span></a></div>
                </div>
            </nav>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <div>
                    <h1 class="page-title"><i class="fas fa-users"></i> Auteurs</h1>
                    <p class="page-subtitle">Gérez les rédacteurs de vos articles</p>
                </div>
                <a href="/admin/auteurs/new" class="btn"><i class="fas fa-plus"></i> Nouvel Auteur</a>
            </div>

            <?php if (!empty($auteurs)): ?>
                <div class="data-table">
                    <div class="data-table-header">
                        <div class="data-table-title"><i class="fas fa-list"></i> Liste <span class="badge badge-primary"><?php echo $total; ?></span></div>
                    </div>
                    <div class="table-wrapper">
                        <table class="enhanced-table">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th>Bio</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($auteurs as $a): ?>
                                    <tr>
                                        <td data-label="Nom">
                                            <div class="table-cell-avatar">
                                                <div class="table-avatar"><?php echo strtoupper(substr($a['nom'], 0, 2)); ?></div>
                                                <span><?php echo escape($a['nom']); ?></span>
                                            </div>
                                        </td>
                                        <td data-label="Email"><div class="table-cell-subtitle"><i class="fas fa-envelope"></i> <?php echo escape($a['email']); ?></div></td>
                                        <td data-label="Bio"><?php echo escape(substr($a['bio'] ?? '', 0, 40)); ?><?php echo strlen($a['bio'] ?? '') > 40 ? '...' : ''; ?></td>
                                        <td data-label="Actions">
                                            <div class="table-cell-actions">
                                                <a href="/admin/auteurs/edit/<?php echo $a['id']; ?>" class="action-btn edit" title="Modifier"><i class="fas fa-edit"></i></a>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Confirmez?')">
                                                    <input type="hidden" name="delete_id" value="<?php echo $a['id']; ?>">
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
                        <div class="table-empty-icon"><i class="fas fa-user-plus"></i></div>
                        <div class="table-empty-title">Aucun auteur</div>
                        <div class="table-empty-description">Créez votre premier auteur.</div>
                        <a href="/admin/auteurs/new" class="btn"><i class="fas fa-plus"></i> Créer</a>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
