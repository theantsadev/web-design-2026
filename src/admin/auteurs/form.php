<?php
/**
 * Formulaire auteurs (création/édition)
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../functions.php';
require_once __DIR__ . '/../../session.php';

requireLogin();

$action = 'new';
$auteur = null;
$error = '';
$success = '';

// Récupérer l'auteur si édition
$id = $_GET['edit'] ?? null;
if ($id) {
    $action = 'edit';
    $stmt = $pdo->prepare('SELECT * FROM auteur WHERE id = ?');
    $stmt->execute([$id]);
    $auteur = $stmt->fetch();
    if (!$auteur) die('Auteur non trouvé');
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim(postParam('nom', ''));
    $email = trim(postParam('email', ''));
    $bio = trim(postParam('bio', ''));
    $photo_url = trim(postParam('photo_url', ''));

    if (empty($nom)) {
        $error = 'Le nom est obligatoire';
    } elseif (empty($email)) {
        $error = 'L\'email est obligatoire';
    }

    if (empty($error)) {
        try {
            if ($action === 'edit') {
                $stmt = $pdo->prepare('
                    UPDATE auteur
                    SET nom = ?, email = ?, bio = ?, photo_url = ?
                    WHERE id = ?
                ');
                $stmt->execute([$nom, $email, $bio, $photo_url, $id]);
            } else {
                $stmt = $pdo->prepare('
                    INSERT INTO auteur (nom, email, bio, photo_url)
                    VALUES (?, ?, ?, ?)
                ');
                $stmt->execute([$nom, $email, $bio, $photo_url]);
            }
            $success = $action === 'edit' ? 'Auteur modifié' : 'Auteur créé';
            header('Refresh: 1; url=/admin/auteurs/');
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
    <title><?php echo $action === 'edit' ? 'Éditer' : 'Créer'; ?> Auteur</title>
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
            max-width: 500px;
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

        input, textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            font-family: inherit;
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        input:focus, textarea:focus {
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
        <h1><?php echo $action === 'edit' ? 'Éditer' : 'Créer'; ?> Auteur</h1>
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
                       value="<?php echo $auteur ? escape($auteur['nom']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" required
                       value="<?php echo $auteur ? escape($auteur['email']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="bio">Bio</label>
                <textarea id="bio" name="bio"><?php echo $auteur ? escape($auteur['bio'] ?? '') : ''; ?></textarea>
            </div>

            <div class="form-group">
                <label for="photo_url">URL Photo</label>
                <input type="text" id="photo_url" name="photo_url"
                       value="<?php echo $auteur ? escape($auteur['photo_url'] ?? '') : ''; ?>">
            </div>

            <button type="submit" class="btn"><?php echo $action === 'edit' ? '✏️ Modifier' : '✚ Créer'; ?></button>
            <a href="/admin/auteurs/" style="text-decoration: none;"><button type="button" class="btn" style="background: #95a5a6;">Annuler</button></a>
        </form>
    </div>
</body>
</html>
