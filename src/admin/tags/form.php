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
    <title><?php echo $action === 'edit' ? 'Éditer' : 'Créer'; ?> Tag</title>
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
        }

        .main-content {
            max-width: 400px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        input:focus {
            outline: none;
            border-color: #3498db;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 1rem;
        }

        .btn:hover {
            background: #2980b9;
        }

        .alert {
            padding: 0.75rem;
            margin-bottom: 1.5rem;
            border-radius: 4px;
        }

        .alert-error {
            background: #fee;
            color: #c33;
        }

        .alert-success {
            background: #efe;
            color: #3c3;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1><?php echo $action === 'edit' ? 'Éditer' : 'Créer'; ?> Tag</h1>
    </div>

    <div class="main-content">
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="nom">Nom *</label>
                <input type="text" id="nom" name="nom" required
                       value="<?php echo $tag ? escape($tag['nom']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="slug">Slug</label>
                <input type="text" id="slug" name="slug"
                       value="<?php echo $tag ? escape($tag['slug']) : ''; ?>">
                <small style="color: #999;">Auto-généré si vide</small>
            </div>

            <button type="submit" class="btn"><?php echo $action === 'edit' ? '✏️ Modifier' : '✚ Créer'; ?></button>
            <a href="/admin/tags/" style="text-decoration: none;"><button type="button" class="btn" style="background: #95a5a6;">Annuler</button></a>
        </form>
    </div>
</body>
</html>
