<?php
/**
 * Formulaire création/édition d'article - BackOffice
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../functions.php';
require_once __DIR__ . '/../../session.php';

requireLogin();

$article = null;
$tags = [];
$images = [];
$error = '';
$success = '';

// Récupérer l'ID de l'article si édition
$action = isset($_GET['edit']) ? 'edit' : 'new';
$articleId = $action === 'edit' ? (int)$_GET['edit'] : null;

// Récupérer l'article s'il existe
if ($articleId) {
    $stmt = $pdo->prepare('SELECT * FROM article WHERE id = ?');
    $stmt->execute([$articleId]);
    $article = $stmt->fetch();

    if (!$article) {
        die('Article non trouvé');
    }

    // Récupérer les tags de l'article
    $stmt = $pdo->prepare('
        SELECT t.id, t.nom
        FROM tag t
        JOIN article_tag at ON t.id = at.tag_id
        WHERE at.article_id = ?
    ');
    $stmt->execute([$articleId]);
    $tags = $stmt->fetchAll();

    // Récupérer les images de l'article
    $stmt = $pdo->prepare('
        SELECT * FROM image
        WHERE article_id = ?
        ORDER BY ordre ASC
    ');
    $stmt->execute([$articleId]);
    $images = $stmt->fetchAll();
}

// Récupérer les catégories
$stmt = $pdo->query('SELECT id, nom FROM categorie ORDER BY nom');
$categories = $stmt->fetchAll();

// Récupérer tous les tags
$stmt = $pdo->query('SELECT id, nom FROM tag ORDER BY nom');
$allTags = $stmt->fetchAll();

// Récupérer les auteurs
$stmt = $pdo->query('SELECT id, nom FROM auteur ORDER BY nom');
$auteurs = $stmt->fetchAll();

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim(postParam('titre', ''));
    $slug = trim(postParam('slug', ''));
    $chapeau = trim(postParam('chapeau', ''));
    $contenu = postParam('contenu', '');
    $categorie_id = (int)postParam('categorie_id', 0);
    $auteur_id = (int)postParam('auteur_id', 0);
    $statut = postParam('statut', 'brouillon');
    $seo_title = trim(postParam('seo_title', ''));
    $seo_meta_description = trim(postParam('seo_meta_description', ''));
    $selectedTags = postParam('tags', []);

    if (empty($titre)) {
        $error = 'Le titre est obligatoire';
    } elseif (empty($slug)) {
        $slug = generateSlug($titre);
    }

    if (empty($error)) {
        try {
            $pdo->beginTransaction();

            $now = date('Y-m-d H:i:s');

            if ($articleId) {
                // Mise à jour
                $stmt = $pdo->prepare('
                    UPDATE article
                    SET titre = ?, slug = ?, chapeau = ?, contenu_html = ?,
                        categorie_id = ?, auteur_id = ?, statut = ?,
                        seo_title = ?, seo_meta_description = ?,
                        date_modification = ?
                    WHERE id = ?
                ');
                $stmt->execute([
                    $titre, $slug, $chapeau, $contenu,
                    $categorie_id ?: null, $auteur_id ?: null, $statut,
                    $seo_title, $seo_meta_description,
                    $now, $articleId
                ]);
            } else {
                // Création
                $stmt = $pdo->prepare('
                    INSERT INTO article
                    (titre, slug, chapeau, contenu_html, categorie_id, auteur_id, statut,
                     seo_title, seo_meta_description, date_publication)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ');
                $stmt->execute([
                    $titre, $slug, $chapeau, $contenu,
                    $categorie_id ?: null, $auteur_id ?: null, $statut,
                    $seo_title, $seo_meta_description,
                    $now
                ]);
                $articleId = $pdo->lastInsertId();
            }

            // Mettre à jour les tags
            $pdo->prepare('DELETE FROM article_tag WHERE article_id = ?')->execute([$articleId]);
            if (!empty($selectedTags)) {
                $stmt = $pdo->prepare('INSERT INTO article_tag (article_id, tag_id) VALUES (?, ?)');
                foreach ((array)$selectedTags as $tagId) {
                    $stmt->execute([$articleId, (int)$tagId]);
                }
            }

            $pdo->commit();
            $success = $action === 'edit' ? 'Article modifié' : 'Article créé';

            // Redirection après 1s
            header('Refresh: 1; url=/admin/articles/');
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Erreur: ' . $e->getMessage();
        }
    }
}

// Récupérer les tags sélectionnés pour PRE-remplir le formulaire
$selectedTagIds = [];
if ($article) {
    foreach ($tags as $t) {
        $selectedTagIds[] = $t['id'];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $action === 'edit' ? 'Éditer' : 'Créer'; ?> Article - Admin</title>
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js"></script>
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
        }

        .main-content {
            margin-top: 60px;
            padding: 2rem;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
        }

        .page-title {
            font-size: 2rem;
            margin-bottom: 2rem;
        }

        .form-container {
            background: white;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .form-row.full {
            grid-template-columns: 1fr;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group textarea,
        .form-group select {
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            font-family: inherit;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.1);
        }

        .tinymce-container {
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 0.5rem;
        }

        .checkbox-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .checkbox-group label {
            display: flex;
            align-items: center;
            font-weight: normal;
            margin: 0;
        }

        .checkbox-group input[type="checkbox"] {
            margin-right: 0.5rem;
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .btn-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            background: #3498db;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: bold;
            transition: background 0.3s;
        }

        .btn:hover {
            background: #2980b9;
        }

        .btn-secondary {
            background: #95a5a6;
        }

        .btn-secondary:hover {
            background: #7f8c8d;
        }

        .alert {
            padding: 0.75rem;
            border-radius: 4px;
            margin-bottom: 1.5rem;
        }

        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }

        .alert-success {
            background: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }

        .help-text {
            font-size: 0.85rem;
            color: #999;
            margin-top: 0.25rem;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .btn-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>📝 <?php echo $action === 'edit' ? 'Éditer' : 'Créer'; ?> Article</h1>
        <div>
            <a href="/admin/articles/">← Articles</a>
        </div>
    </div>

    <div class="main-content">
        <div class="form-container">
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <!-- Titre et Slug -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="titre">Titre *</label>
                        <input type="text" id="titre" name="titre" required
                               value="<?php echo $article ? escape($article['titre']) : ''; ?>"
                               onchange="if(this.form.slug.value === '') this.form.slug.value = slugify(this.value)">
                    </div>
                    <div class="form-group">
                        <label for="slug">Slug</label>
                        <input type="text" id="slug" name="slug"
                               value="<?php echo $article ? escape($article['slug']) : ''; ?>">
                        <div class="help-text">Auto-généré à partir du titre si vide</div>
                    </div>
                </div>

                <!-- Chapeau -->
                <div class="form-row full">
                    <div class="form-group">
                        <label for="chapeau">Chapeau (résumé court)</label>
                        <textarea id="chapeau" name="chapeau" rows="3"><?php echo $article ? escape($article['chapeau']) : ''; ?></textarea>
                    </div>
                </div>

                <!-- Contenu HTML (TinyMCE) -->
                <div class="form-row full">
                    <div class="form-group">
                        <label for="contenu">Contenu *</label>
                        <textarea id="contenu" name="contenu" required><?php echo $article ? $article['contenu_html'] : ''; ?></textarea>
                    </div>
                </div>

                <!-- Catégorie et Auteur -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="categorie_id">Catégorie</label>
                        <select id="categorie_id" name="categorie_id">
                            <option value="">-- Sélectionner une catégorie --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"
                                        <?php echo $article && $article['categorie_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo escape($cat['nom']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="auteur_id">Auteur</label>
                        <select id="auteur_id" name="auteur_id">
                            <option value="">-- Sélectionner un auteur --</option>
                            <?php foreach ($auteurs as $aut): ?>
                                <option value="<?php echo $aut['id']; ?>"
                                        <?php echo $article && $article['auteur_id'] == $aut['id'] ? 'selected' : ''; ?>>
                                    <?php echo escape($aut['nom']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Tags -->
                <div class="form-row full">
                    <div class="form-group">
                        <label>Tags</label>
                        <div class="checkbox-group">
                            <?php foreach ($allTags as $t): ?>
                                <label>
                                    <input type="checkbox" name="tags[]" value="<?php echo $t['id']; ?>"
                                           <?php echo in_array($t['id'], $selectedTagIds) ? 'checked' : ''; ?>>
                                    <?php echo escape($t['nom']); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- SEO -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="seo_title">SEO Title (max 60 caractères)</label>
                        <input type="text" id="seo_title" name="seo_title" maxlength="60"
                               value="<?php echo $article ? escape($article['seo_title']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="seo_meta_description">Meta Description (max 160 caractères)</label>
                        <textarea id="seo_meta_description" name="seo_meta_description" maxlength="160" rows="2"><?php echo $article ? escape($article['seo_meta_description']) : ''; ?></textarea>
                    </div>
                </div>

                <!-- Statut -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="statut">Statut</label>
                        <select id="statut" name="statut">
                            <option value="brouillon" <?php echo (!$article || $article['statut'] === 'brouillon') ? 'selected' : ''; ?>>Brouillon</option>
                            <option value="publié" <?php echo ($article && $article['statut'] === 'publié') ? 'selected' : ''; ?>>Publié</option>
                            <option value="archivé" <?php echo ($article && $article['statut'] === 'archivé') ? 'selected' : ''; ?>>Archivé</option>
                        </select>
                    </div>
                </div>

                <!-- Boutons -->
                <div class="btn-group">
                    <button type="submit" class="btn">
                        <?php echo $action === 'edit' ? '✏️ Modifier' : '✚ Créer'; ?> l'article
                    </button>
                    <a href="/admin/articles/" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Initialiser TinyMCE
        tinymce.init({
            selector: '#contenu',
            plugins: 'lists link image table code',
            toolbar: 'formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image code',
            height: 400,
            menubar: false,
            branding: false
        });

        // Fonction pour générer un slug
        function slugify(text) {
            return text
                .toLowerCase()
                .replace(/[éè]/g, 'e')
                .replace(/[àâ]/g, 'a')
                .replace(/[ôö]/g, 'o')
                .replace(/[ûù]/g, 'u')
                .replace(/[ç]/g, 'c')
                .replace(/[^a-z0-9\s\-]/g, '')
                .trim()
                .replace(/[\s]+/g, '-')
                .replace(/[\-]+/g, '-');
        }
    </script>
</body>
</html>
