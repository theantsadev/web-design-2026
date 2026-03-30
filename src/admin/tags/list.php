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
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #2c3e50;
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

        .btn {
            padding: 0.75rem 1.5rem;
            background: #3498db;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f9f9f9;
            padding: 1rem;
            text-align: left;
            border-bottom: 2px solid #eee;
        }

        td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }

        .action-btn {
            padding: 0.5rem 0.8rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8rem;
            background: #95a5a6;
            color: white;
            text-decoration: none;
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>🏷️ Tags</h1>
        <a href="/admin/logout" style="color: white;">Déconnexion</a>
    </div>

    <div class="sidebar">
        <a href="/admin/dashboard/">Dashboard</a>
        <a href="/admin/articles/">Articles</a>
        <a href="/admin/categories/">Catégories</a>
        <a href="/admin/tags/" class="active">Tags</a>
        <a href="/admin/auteurs/">Auteurs</a>
    </div>

    <div class="main-content">
        <div class="page-header">
            <h1>Tags</h1>
            <a href="/admin/tags/new" class="btn">+ Créer</a>
        </div>

        <div class="table-container">
            <table>
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
                            <td><strong><?php echo escape($tag['nom']); ?></strong></td>
                            <td><small><?php echo escape($tag['slug']); ?></small></td>
                            <td>
                                <a href="/admin/tags/edit/<?php echo $tag['id']; ?>" class="action-btn">Éditer</a>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Confirmez?')">
                                    <input type="hidden" name="delete_id" value="<?php echo $tag['id']; ?>">
                                    <button class="action-btn" style="background: #e74c3c;">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
