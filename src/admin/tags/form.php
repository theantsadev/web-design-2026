<?php
/**
 * Formulaire tags (création/édition)
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../functions.php';
require_once __DIR__ . '/../../session.php';

requireLogin();

$action = 'new';
$tag = null;
$error = '';
$success = '';

// Récupérer le tag si édition
$id = $_GET['edit'] ?? null;
if ($id) {
    $action = 'edit';
    $stmt = $pdo->prepare('SELECT * FROM tag WHERE id = ?');
    $stmt->execute([$id]);
    $tag = $stmt->fetch();
    if (!$tag) die('Tag non trouvé');
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim(postParam('nom', ''));
    $slug = trim(postParam('slug', ''));

    if (empty($nom)) {
        $error = 'Le nom est obligatoire';
    } elseif (empty($slug)) {
        $slug = generateSlug($nom);
    }

    if (empty($error)) {
        try {
            if ($action === 'edit') {
                $stmt = $pdo->prepare('UPDATE tag SET nom = ?, slug = ? WHERE id = ?');
                $stmt->execute([$nom, $slug, $id]);
            } else {
                $stmt = $pdo->prepare('INSERT INTO tag (nom, slug) VALUES (?, ?)');
                $stmt->execute([$nom, $slug]);
            }
            $success = $action === 'edit' ? 'Tag modifié' : 'Tag créé';
            header('Refresh: 1; url=/admin/tags/');
        } catch (PDOException $e) {
            $error = 'Erreur: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $action === 'edit' ? 'Éditer' : 'Créer'; ?> Tag - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/admin/assets/modern-admin.css">
    <link rel="stylesheet" href="/admin/assets/forms.css">
</head>
<body>
    <div class="admin-layout">
        <nav class="navbar">
            <div class="navbar-brand"><i class="fas fa-tag"></i> <?php echo $action === 'edit' ? 'Éditer' : 'Créer'; ?> Tag</div>
            <div class="navbar-user">
                <a href="/admin/tags/" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Retour</a>
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
                    <h1 class="page-title"><i class="fas fa-<?php echo $action === 'edit' ? 'edit' : 'plus'; ?>"></i> <?php echo $action === 'edit' ? 'Modifier' : 'Nouveau'; ?> Tag</h1>
                    <p class="page-subtitle">Gérez vos mots-clés</p>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-triangle"></i> <?php echo escape($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo escape($success); ?></div>
            <?php endif; ?>

            <div class="form-card">
                <div class="form-card-header">
                    <h2 class="form-card-title"><i class="fas fa-tag"></i> Informations</h2>
                </div>
                <div class="form-card-body">
                    <form method="POST">
                        <div class="form-section">
                            <div class="form-row two-columns">
                                <div class="form-group">
                                    <label for="nom" class="form-label required">Nom</label>
                                    <input type="text" id="nom" name="nom" class="form-input" required value="<?php echo $tag ? escape($tag['nom']) : ''; ?>" placeholder="Nom du tag">
                                </div>
                                <div class="form-group">
                                    <label for="slug" class="form-label">Slug</label>
                                    <input type="text" id="slug" name="slug" class="form-input" value="<?php echo $tag ? escape($tag['slug']) : ''; ?>" placeholder="auto-genere">
                                    <div class="form-help">Auto-généré si vide</div>
                                </div>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-lg"><i class="fas fa-<?php echo $action === 'edit' ? 'save' : 'plus'; ?>"></i> <?php echo $action === 'edit' ? 'Enregistrer' : 'Créer'; ?></button>
                            <a href="/admin/tags/" class="btn btn-secondary btn-lg"><i class="fas fa-times"></i> Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
