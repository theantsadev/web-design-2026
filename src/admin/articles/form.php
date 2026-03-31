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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/admin/assets/modern-admin.css">
    <link rel="stylesheet" href="/admin/assets/forms.css">
    <script src="https://cdn.tiny.cloud/1/0pvlc2xj6msvkrovd2uddkx3r42zwgre63ut8jh054wavvq2/tinymce/8/tinymce.min.js" referrerpolicy="origin" crossorigin="anonymous"></script>
</head>
<body>
    <div class="admin-layout">
        <!-- Navbar -->
        <nav class="navbar">
            <div class="navbar-brand">
                <i class="fas fa-edit"></i>
                <?php echo $action === 'edit' ? 'Éditer' : 'Créer'; ?> Article
            </div>
            <div class="navbar-user">
                <a href="/admin/articles/" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i>
                    Retour
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
                    <h1 class="page-title">
                        <i class="fas fa-<?php echo $action === 'edit' ? 'edit' : 'plus'; ?>"></i>
                        <?php echo $action === 'edit' ? 'Modifier' : 'Nouvel'; ?> Article
                    </h1>
                    <p class="page-subtitle">Rédigez votre article de presse</p>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo escape($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo escape($success); ?>
                </div>
            <?php endif; ?>

            <div class="form-card">
                <div class="form-card-header">
                    <h2 class="form-card-title">
                        <i class="fas fa-file-alt"></i>
                        Informations de l'article
                    </h2>
                </div>
                <div class="form-card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <!-- Titre et Slug -->
                        <div class="form-section">
                            <div class="form-section-title"><i class="fas fa-heading"></i> Titre</div>
                            <div class="form-row two-columns">
                                <div class="form-group">
                                    <label for="titre" class="form-label required">Titre</label>
                                    <input type="text" id="titre" name="titre" class="form-input" required
                                           value="<?php echo $article ? escape($article['titre']) : ''; ?>"
                                           onchange="if(this.form.slug.value === '') this.form.slug.value = slugify(this.value)"
                                           placeholder="Titre de l'article">
                                </div>
                                <div class="form-group">
                                    <label for="slug" class="form-label">Slug (URL)</label>
                                    <input type="text" id="slug" name="slug" class="form-input"
                                           value="<?php echo $article ? escape($article['slug']) : ''; ?>"
                                           placeholder="mon-article">
                                    <div class="form-help">Auto-généré si vide</div>
                                </div>
                            </div>
                        </div>

                        <!-- Chapeau -->
                        <div class="form-section">
                            <div class="form-section-title"><i class="fas fa-align-left"></i> Chapeau</div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="chapeau" class="form-label">Résumé court</label>
                                    <textarea id="chapeau" name="chapeau" class="form-textarea" rows="3"
                                              placeholder="Introduction de l'article..."><?php echo $article ? escape($article['chapeau']) : ''; ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Contenu -->
                        <div class="form-section">
                            <div class="form-section-title"><i class="fas fa-edit"></i> Contenu</div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="contenu" class="form-label required">Contenu principal</label>
                                    <div class="editor-wrapper">
                                        <textarea id="contenu" name="contenu" required><?php echo $article ? $article['contenu_html'] : ''; ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Classification -->
                        <div class="form-section">
                            <div class="form-section-title"><i class="fas fa-tags"></i> Classification</div>
                            <div class="form-row two-columns">
                                <div class="form-group">
                                    <label for="categorie_id" class="form-label"><i class="fas fa-folder"></i> Catégorie</label>
                                    <select id="categorie_id" name="categorie_id" class="form-select">
                                        <option value="">-- Sélectionner --</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo $cat['id']; ?>"
                                                    <?php echo $article && $article['categorie_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                                <?php echo escape($cat['nom']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="auteur_id" class="form-label"><i class="fas fa-user"></i> Auteur</label>
                                    <select id="auteur_id" name="auteur_id" class="form-select">
                                        <option value="">-- Sélectionner --</option>
                                        <?php foreach ($auteurs as $aut): ?>
                                            <option value="<?php echo $aut['id']; ?>"
                                                    <?php echo $article && $article['auteur_id'] == $aut['id'] ? 'selected' : ''; ?>>
                                                <?php echo escape($aut['nom']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label"><i class="fas fa-hashtag"></i> Tags</label>
                                    <div class="checkbox-grid">
                                        <?php foreach ($allTags as $t): ?>
                                            <label class="checkbox-item">
                                                <input type="checkbox" name="tags[]" value="<?php echo $t['id']; ?>"
                                                       <?php echo in_array($t['id'], $selectedTagIds) ? 'checked' : ''; ?>>
                                                <?php echo escape($t['nom']); ?>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SEO -->
                        <div class="form-section">
                            <div class="form-section-title"><i class="fas fa-search"></i> SEO</div>
                            <div class="form-row two-columns">
                                <div class="form-group">
                                    <label for="seo_title" class="form-label">Titre SEO (60 car.)</label>
                                    <input type="text" id="seo_title" name="seo_title" class="form-input" maxlength="60"
                                           value="<?php echo $article ? escape($article['seo_title']) : ''; ?>"
                                           placeholder="Titre optimisé pour Google">
                                </div>
                                <div class="form-group">
                                    <label for="seo_meta_description" class="form-label">Meta Description (160 car.)</label>
                                    <textarea id="seo_meta_description" name="seo_meta_description" class="form-textarea" maxlength="160" rows="2"
                                              placeholder="Description pour les moteurs de recherche"><?php echo $article ? escape($article['seo_meta_description']) : ''; ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Statut -->
                        <div class="form-section">
                            <div class="form-section-title"><i class="fas fa-flag"></i> Publication</div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="statut" class="form-label">Statut</label>
                                    <select id="statut" name="statut" class="form-select">
                                        <option value="brouillon" <?php echo (!$article || $article['statut'] === 'brouillon') ? 'selected' : ''; ?>>Brouillon</option>
                                        <option value="publié" <?php echo ($article && $article['statut'] === 'publié') ? 'selected' : ''; ?>>Publié</option>
                                        <option value="archivé" <?php echo ($article && $article['statut'] === 'archivé') ? 'selected' : ''; ?>>Archivé</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="form-actions">
                            <button type="submit" class="btn btn-lg">
                                <i class="fas fa-<?php echo $action === 'edit' ? 'save' : 'plus'; ?>"></i>
                                <?php echo $action === 'edit' ? 'Enregistrer' : 'Créer'; ?>
                            </button>
                            <a href="/admin/articles/" class="btn btn-secondary btn-lg">
                                <i class="fas fa-times"></i>
                                Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        // TinyMCE
        tinymce.init({
            selector: '#contenu',
            plugins: ['anchor', 'autolink', 'charmap', 'link', 'lists', 'media', 'searchreplace', 'table', 'visualblocks', 'wordcount'],
            toolbar: 'undo redo | bold italic underline | link | bullist numlist | removeformat',
            height: 400,
            menubar: false,
            branding: false
        });

        // Slugify
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
