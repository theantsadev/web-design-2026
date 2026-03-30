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
    SELECT a.id, a.titre, a.statut, a.date_publication, aut.nom
    FROM article a
    LEFT JOIN auteur aut ON a.auteur_id = aut.id
    ORDER BY a.created_at DESC
    LIMIT 5
');
$lastArticles = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin</title>
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

        .sidebar a:hover {
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

        .page-title {
            font-size: 2rem;
            margin-bottom: 2rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-card h3 {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
        }

        .stat-card .number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #2c3e50;
        }

        .recent-articles {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .recent-articles h2 {
            padding: 1.5rem;
            background: #f9f9f9;
            border-bottom: 1px solid #eee;
            font-size: 1.2rem;
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

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .navbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .navbar a {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>📊 Admin</h1>
        <div>
            <span>Bienvenue, <strong><?php echo escape($user['login']); ?></strong></span>
            <a href="/admin/logout">Déconnexion</a>
        </div>
    </div>

    <div class="sidebar">
        <div class="sidebar-title">● Gestion</div>
        <a href="/admin/dashboard/">Dashboard</a>
        <a href="/admin/articles/">Articles</a>
        <a href="/admin/categories/">Catégories</a>
        <a href="/admin/tags/">Tags</a>
        <a href="/admin/auteurs/">Auteurs</a>
    </div>

    <div class="main-content">
        <h1 class="page-title">Dashboard</h1>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Articles</h3>
                <div class="number"><?php echo $stats['articles']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Catégories</h3>
                <div class="number"><?php echo $stats['categories']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Tags</h3>
                <div class="number"><?php echo $stats['tags']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Auteurs</h3>
                <div class="number"><?php echo $stats['auteurs']; ?></div>
            </div>
        </div>

        <?php if (!empty($lastArticles)): ?>
            <div class="recent-articles">
                <h2>Derniers articles</h2>
                <table class="articles-table">
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Auteur</th>
                            <th>Statut</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lastArticles as $article): ?>
                            <tr>
                                <td><strong><?php echo escape($article['titre']); ?></strong></td>
                                <td><?php echo escape($article['nom'] ?? 'N/A'); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $article['statut']; ?>">
                                        <?php echo ucfirst($article['statut']); ?>
                                    </span>
                                </td>
                                <td><?php echo formatDate($article['date_publication'], 'd/m/Y'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
