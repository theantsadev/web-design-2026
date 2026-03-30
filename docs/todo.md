# Document Technique — Mini-projet Web Design 2026

## 1. Informations générales

| Champ | Valeur |
|---|---|
| **Numéro ETU** | [À compléter] |
| **Binôme** | [Nom 1] / [Nom 2] |
| **Sujet** | Site d'informations sur la guerre en Iran |
| **Délai de livraison** | Mardi 31 mars 2026 à 14h00 |
| **Technologies** | PHP 8, MySQL 8, Apache, Docker, TinyMCE |
| **Dépôt** | [Lien GitHub / GitLab à compléter] |

---

## 2. Modélisation de la base de données

> Contenu riche stocké en HTML brut via TinyMCE. Les images sont gérées dans une table dédiée (relation 1,n). Pas de découpage en blocs — incompatible avec TinyMCE sans parser supplémentaire.

### 2.1 Tables principales

#### `article`
| Colonne | Type | Contrainte | Description |
|---|---|---|---|
| `id` | INT | PK, AUTO_INCREMENT | Identifiant unique |
| `titre` | VARCHAR(255) | NOT NULL | Titre de l'article |
| `slug` | VARCHAR(255) | UNIQUE, NOT NULL | URL normalisée (ex: `conflit-iran-2024`) |
| `chapeau` | TEXT | | Résumé court affiché en Une |
| `contenu_html` | LONGTEXT | | Corps de l'article (HTML TinyMCE) |
| `date_publication` | DATETIME | NOT NULL | Date de mise en ligne |
| `date_modification` | DATETIME | | Dernière mise à jour |
| `statut` | ENUM('brouillon','publié','archivé') | DEFAULT 'brouillon' | État éditorial |
| `auteur_id` | INT | FK → auteur.id | Référence auteur |
| `categorie_id` | INT | FK → categorie.id | Rubrique principale |
| `seo_title` | VARCHAR(60) | | Balise `<title>` (max 60 car.) |
| `seo_meta_description` | VARCHAR(160) | | Balise `<meta description>` (max 160 car.) |
| `nb_vues` | INT | DEFAULT 0 | Compteur de consultations |

#### `categorie`
| Colonne | Type | Description |
|---|---|---|
| `id` | INT PK | Identifiant |
| `nom` | VARCHAR(100) | Ex : Politique, Militaire, Diplomatie |
| `slug` | VARCHAR(100) UNIQUE | Ex : `politique`, `militaire` |
| `description` | TEXT | Résumé de la rubrique |
| `meta_description` | VARCHAR(160) | SEO de la page catégorie |

#### `tag`
| Colonne | Type | Description |
|---|---|---|
| `id` | INT PK | Identifiant |
| `nom` | VARCHAR(100) | Ex : Iran, AIEA, Nucléaire |
| `slug` | VARCHAR(100) UNIQUE | Ex : `aiea`, `nucleaire` |

#### `image`
| Colonne | Type | Description |
|---|---|---|
| `id` | INT PK | Identifiant |
| `article_id` | INT FK | Référence article |
| `url` | VARCHAR(500) NOT NULL | Chemin du fichier uploadé |
| `alt` | VARCHAR(255) | Texte alternatif SEO |
| `legende` | VARCHAR(255) | Légende affichée sous l'image |
| `ordre` | INT DEFAULT 0 | Position dans la galerie |
| `est_principale` | TINYINT(1) DEFAULT 0 | 1 = image hero de l'article |

#### `article_tag` *(table de liaison n,n)*
| Colonne | Type | Description |
|---|---|---|
| `article_id` | INT FK | Référence article |
| `tag_id` | INT FK | Référence tag |

#### `auteur`
| Colonne | Type | Description |
|---|---|---|
| `id` | INT PK | Identifiant |
| `nom` | VARCHAR(100) | Nom affiché |
| `bio` | TEXT | Courte biographie |
| `photo_url` | VARCHAR(500) | Avatar |
| `email` | VARCHAR(150) UNIQUE | Email (non affiché en FO) |

#### `utilisateur` *(accès BackOffice)*
| Colonne | Type | Description |
|---|---|---|
| `id` | INT PK | Identifiant |
| `login` | VARCHAR(100) UNIQUE | Identifiant de connexion |
| `password_hash` | VARCHAR(255) | Mot de passe hashé (bcrypt) |
| `role` | ENUM('admin','editeur') | Niveau d'accès |

### 2.2 Diagramme Entité-Relation (textuel)

```
auteur (1) ──────< article (n) >────── categorie (1)
                       │  │
                   article_tag  image (n) [est_principale=1 → image hero]
                       │
                      tag (n)

utilisateur ──── accède au BackOffice
```

*(Insérer le schéma visuel exporté de draw.io ou dbdiagram.io)*

---

## 3. URL Rewriting

> Conformément au cours Rewriting URL, les URLs dynamiques PHP sont masquées et normalisées via `.htaccess` + `mod_rewrite`.

### 3.1 Règles `.htaccess`

```apache
Options +FollowSymlinks
RewriteEngine On

# Page d'accueil
RewriteRule ^$  index.php  [L]

# Article : /article/conflit-iran-sanctions-2024
RewriteRule ^article/([a-z0-9\-]+)$  pages/article.php?slug=$1  [L]

# Catégorie : /categorie/diplomatie
RewriteRule ^categorie/([a-z0-9\-]+)$  pages/categorie.php?slug=$1  [L]

# Tag : /tag/nucleaire
RewriteRule ^tag/([a-z0-9\-]+)$  pages/tag.php?slug=$1  [L]

# Page auteur : /auteur/jean-dupont
RewriteRule ^auteur/([a-z0-9\-]+)$  pages/auteur.php?slug=$1  [L]
```

### 3.2 Génération du slug (PHP)

```php
function generateSlug(string $titre): string {
    $slug = strtolower($titre);
    $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $slug); // supprime accents
    $slug = preg_replace('/[^a-z0-9\s\-]/', '', $slug);
    $slug = preg_replace('/[\s\-]+/', '-', $slug);
    return trim($slug, '-');
}
// Ex : "Conflit Iran : sanctions de l'ONU" → "conflit-iran-sanctions-de-l-onu"
```

### 3.3 Exemple d'URL avant / après

| URL dynamique (cachée) | URL propre affichée |
|---|---|
| `article.php?id=12` | `/article/conflit-iran-2024` |
| `categorie.php?id=3` | `/categorie/diplomatie` |
| `tag.php?id=5` | `/tag/nucleaire` |
| `auteur.php?id=2` | `/auteur/jean-dupont` |

---

## 4. Optimisation SEO

> Basé sur le cours SEO (ITUniversity) — Les 3 piliers : Technique, Contenu, Popularité.

### 4.1 Balises HTML essentielles (On-Page)

| Balise | Règle appliquée | Importance |
|---|---|---|
| `<title>` | 50–60 car. · mot-clé principal en premier · unique par page | ★★★★★ |
| `<meta name="description">` | 150–160 car. · incitatif · généré depuis `seo_meta_description` | ★★★★☆ |
| `<h1>` | Un seul par page · contient le mot-clé principal (= `titre` article) | ★★★★★ |
| `<h2>…<h6>` | Structure sémantique du contenu TinyMCE | ★★★★☆ |
| `<img alt="">` | Rempli depuis le champ `alt` de la table `image` | ★★★☆☆ |
| `<link rel="canonical">` | Évite le contenu dupliqué entre pagination et tags | ★★★★☆ |

### 4.2 Exemple de `<head>` généré dynamiquement (PHP)

```php
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title><?= htmlspecialchars($article['seo_title'] ?: $article['titre']) ?> — IranActu</title>
  <meta name="description" content="<?= htmlspecialchars($article['seo_meta_description']) ?>">
  <link rel="canonical" href="https://iranactu.com/article/<?= $article['slug'] ?>">

  <!-- Open Graph -->
  <meta property="og:title" content="<?= htmlspecialchars($article['titre']) ?>">
  <meta property="og:description" content="<?= htmlspecialchars($article['seo_meta_description']) ?>">
  <meta property="og:image" content="<?= $image_principale['url'] ?? '' ?>">

  <!-- Schema.org JSON-LD -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "NewsArticle",
    "headline": "<?= $article['titre'] ?>",
    "datePublished": "<?= $article['date_publication'] ?>",
    "dateModified": "<?= $article['date_modification'] ?>",
    "author": { "@type": "Person", "name": "<?= $auteur['nom'] ?>" },
    "image": "<?= $image_principale['url'] ?? '' ?>"
  }
  </script>
</head>
```

### 4.3 Structure Hn dans les articles

```html
<h1>Iran : l'AIEA publie un rapport alarmant sur le programme nucléaire</h1>
  <h2>Un enrichissement record confirmé</h2>
    <h3>Les chiffres du rapport</h3>
    <h3>Les réactions internationales</h3>
  <h2>Sanctions : ce que risque Téhéran</h2>
    <h3>Position des États-Unis</h3>
    <h3>Position de l'Union européenne</h3>
```

### 4.4 Fichiers techniques SEO

#### `robots.txt`
```
User-agent: *
Allow: /
Disallow: /admin/
Disallow: /login/
Sitemap: https://iranactu.com/sitemap.xml
```

#### `sitemap.xml` (généré dynamiquement)
```xml
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc>https://iranactu.com/article/conflit-iran-sanctions-2024</loc>
    <lastmod>2024-03-25</lastmod>
    <priority>0.9</priority>
  </url>
  <!-- ... généré pour chaque article publié -->
</urlset>
```

### 4.5 Checklist SEO — Points à vérifier (selon sujet)

| # | Point | Méthode | Statut |
|---|---|---|---|
| 1 | URL normalisée (rewriting) | `.htaccess` + slug en BDD | ☐ |
| 2 | Structure H1→H6 | TinyMCE + template PHP | ☐ |
| 3 | `<title>` unique par page | Champ `seo_title` en BDD | ☐ |
| 4 | `<meta description>` | Champ `seo_meta_description` en BDD | ☐ |
| 5 | `alt` sur les images | Champ `alt` dans la table `image` | ☐ |
| 6 | Test Lighthouse mobile | Chrome DevTools → Lighthouse | ☐ |
| 7 | Test Lighthouse desktop | Chrome DevTools → Lighthouse | ☐ |
| 8 | Mobile responsive | CSS media queries + viewport | ☐ |
| 9 | HTTPS activé | Config Docker / certificat | ☐ |
| 10 | `robots.txt` configuré | Fichier à la racine | ☐ |
| 11 | `sitemap.xml` | Script PHP de génération | ☐ |
| 12 | Schema.org (NewsArticle) | JSON-LD dans `<head>` | ☐ |

---

## 5. FrontOffice (FO)

### 5.1 Pages et routes

| URL propre | Fichier PHP | Description |
|---|---|---|
| `/` | `index.php` | Accueil — liste des derniers articles |
| `/article/{slug}` | `pages/article.php` | Détail d'un article |
| `/categorie/{slug}` | `pages/categorie.php` | Articles par rubrique |
| `/tag/{slug}` | `pages/tag.php` | Articles par tag |
| `/auteur/{slug}` | `pages/auteur.php` | Articles par auteur |

### 5.2 Captures d'écran à inclure

- Page d'accueil avec liste d'articles et chapeau
- Page article avec :
  - URL rewriting visible dans la barre d'adresse
  - `<h1>` pour le titre
  - `<h2>/<h3>` dans le corps de l'article
  - Image avec attribut `alt` visible dans l'inspecteur
  - Balises `<title>` et `<meta>` visibles dans le `<head>`

---

## 6. BackOffice (BO)

### 6.1 Accès par défaut

> **URL :** `http://localhost/admin/login/`
>
> **Login :** `admin`
>
> **Mot de passe :** `admin123`

### 6.2 Fonctionnalités

| Page BO | Description |
|---|---|
| `/admin/login/` | Formulaire d'authentification |
| `/admin/articles/` | Liste des articles (titre, slug, statut, date) |
| `/admin/articles/new` | Créer un nouvel article |
| `/admin/articles/edit/{id}` | Modifier un article existant |
| `/admin/categories/` | Gestion des rubriques |
| `/admin/tags/` | Gestion des tags |
| `/admin/auteurs/` | Gestion des auteurs |

### 6.3 Formulaire d'édition — Champs

| Champ | Balise générée | Notes |
|---|---|---|
| Titre | `<h1>` en FO | Obligatoire |
| Slug | URL `/article/{slug}` | Auto-généré, modifiable |
| Chapeau | `<p class="chapeau">` | Court résumé |
| Contenu | TinyMCE → HTML Hn | Éditeur WYSIWYG |
| Catégorie | `<select>` | Liste des catégories |
| Tags | Checkboxes | Relation n,n |
| Images | `<img alt="...">` en FO | Upload multiple, ordre drag&drop, 1 marquée principale |
| SEO Title | `<title>` | Max 60 car. |
| Meta description | `<meta name="description">` | Max 160 car. |
| Statut | `<select>` brouillon / publié | Workflow éditorial |

### 6.4 Captures d'écran à inclure

- Écran de login avec champs user/pass
- Liste des articles avec actions (éditer, supprimer)
- Formulaire d'édition avec TinyMCE visible
- Champs SEO (title, meta, alt) remplis

---

## 7. Architecture Docker

```yaml
# docker-compose.yml (simplifié)
services:
  web:
    image: php:8.2-apache
    ports:
      - "80:80"
    volumes:
      - ./src:/var/www/html
    depends_on:
      - db

  db:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: iranactu
      MYSQL_USER: user
      MYSQL_PASSWORD: password
    volumes:
      - ./sql/init.sql:/docker-entrypoint-initdb.d/init.sql
```

---

## 8. Livrables

| Livrable | Description | Statut |
|---|---|---|
| `projet.zip` | Site fonctionnel dans des conteneurs Docker | ☐ |
| Dépôt GitHub/GitLab | URL publique | ☐ |
| Document technique (ce fichier) | Captures FO/BO + modélisation BDD | ☐ |

---

*Document technique — Mini-projet Web Design 2026*