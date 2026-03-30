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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            color: #333;
        }

        .navbar {
            background: #2c3e50;
            color: white;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar h1 {
            font-size: 1.5rem;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            margin-left: 2rem;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: background 0.3s;
        }

        .navbar a:hover {
            background: #34495e;
        }

        .sidebar {
            width: 250px;
            background: #34495e;
            color: white;
            position: fixed;
            top: 60px;
            left: 0;
            height: calc(100vh - 60px);
            overflow-y: auto;
        }

        .sidebar a {
            display: block;
            color: white;
            padding: 1rem;
            text-decoration: none;
            transition: background 0.3s;
            font-size: 0.95rem;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #2c3e50;
        }

        .sidebar-title {
            padding: 1rem;
            font-weight: bold;
            background: #2c3e50;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 1px;
        }

        .main-content {
            margin-left: 250px;
            margin-top: 60px;
            padding: 2rem;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-header h1 {
            font-size: 2rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            background: #3498db;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
            font-size: 0.95rem;
        }

        .btn:hover {
            background: #2980b9;
        }

        .btn-danger {
            background: #e74c3c;
        }

        .btn-danger:hover {
            background: #c0392b;
        }

        .table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .articles-table {
            width: 100%;
            border-collapse: collapse;
        }

        .articles-table th {
            background: #f9f9f9;
            padding: 1rem;
            text-align: left;
            font-weight: bold;
            border-bottom: 2px solid #eee;
        }

        .articles-table td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }

        .articles-table tr:hover {
            background: #f9f9f9;
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.7rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .badge-publié {
            background: #d4edda;
            color: #155724;
        }

        .badge-brouillon {
            background: #fff3cd;
            color: #856404;
        }

        .badge-archivé {
            background: #f8d7da;
            color: #721c24;
        }

        .actions {
            display: flex;
            gap: 0.5rem;
        }

        .action-btn {
            padding: 0.5rem 0.8rem;
            text-decoration: none;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8rem;
            background: #95a5a6;
            color: white;
        }

        .action-btn:hover {
            background: #7f8c8d;
        }

        .action-btn-danger {
            background: #e74c3c;
        }

        .action-btn-danger:hover {
            background: #c0392b;
        }

        .no-data {
            text-align: center;
            padding: 3rem;
            color: #999;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: static;
                height: auto;
            }

            .main-content {
                margin-left: 0;
                margin-top: 0;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .articles-table {
                font-size: 0.9rem;
            }

            .articles-table td, .articles-table th {
                padding: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>📄 Articles</h1>
        <div>
            <a href="/admin/dashboard/">Dashboard</a>
            <a href="/admin/logout">Déconnexion</a>
        </div>
    </div>

    <div class="sidebar">
        <div class="sidebar-title">● Gestion</div>
        <a href="/admin/dashboard/">Dashboard</a>
        <a href="/admin/articles/" class="active">Articles</a>
        <a href="/admin/categories/">Catégories</a>
        <a href="/admin/tags/">Tags</a>
        <a href="/admin/auteurs/">Auteurs</a>
    </div>

    <div class="main-content">
        <div class="page-header">
            <h1>Articles</h1>
            <a href="/admin/articles/new" class="btn">+ Créer un article</a>
        </div>

        <div class="table-container">
            <?php if (!empty($articles)): ?>
                <table class="articles-table">
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Catégorie</th>
                            <th>Auteur</th>
                            <th>Statut</th>
                            <th>Date publication</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($articles as $article): ?>
                            <tr>
                                <td>
                                    <strong><?php echo escape($article['titre']); ?></strong><br>
                                    <small style="color: #999;">/<span style="color: #666;"><?php echo escape($article['slug']); ?></span></small>
                                </td>
                                <td><?php echo escape($article['categorie_nom'] ?? '-'); ?></td>
                                <td><?php echo escape($article['auteur_nom'] ?? '-'); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $article['statut']; ?>">
                                        <?php echo ucfirst($article['statut']); ?>
                                    </span>
                                </td>
                                <td><?php echo formatDate($article['date_publication'], 'd/m/Y'); ?></td>
                                <td>
                                    <div class="actions">
                                        <a href="/admin/articles/edit/<?php echo $article['id']; ?>" class="action-btn">Éditer</a>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr?')">
                                            <input type="hidden" name="delete_id" value="<?php echo $article['id']; ?>">
                                            <button type="submit" class="action-btn action-btn-danger">Supprimer</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if ($pages > 1): ?>
                    <div style="padding: 1.5rem; text-align: center; border-top: 1px solid #eee;">
                        <?php for ($p = 1; $p <= $pages; $p++): ?>
                            <?php if ($p === $page): ?>
                                <strong><?php echo $p; ?></strong>
                            <?php else: ?>
                                <a href="/admin/articles/?page=<?php echo $p; ?>"><?php echo $p; ?></a>
                            <?php endif; ?>
                            <?php if ($p < $pages) echo ' | '; ?>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="no-data">
                    <p>Aucun article pour le moment.</p>
                    <p><a href="/admin/articles/new" class="btn" style="display: inline-block; margin-top: 1rem;">+ Créer un article</a></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
