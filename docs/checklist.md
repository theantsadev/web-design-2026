# 📋 Checklist - Mini-projet Web Design 2026

**Projet :** Site d'informations sur la guerre en Iran
**Date limite :** Mardi 31 mars 2026 à 14h00
**Technologies :** PHP 8, MySQL 8, Apache, Docker, TinyMCE

---

## 1. Base de données MySQL

- [x] Créer la table `article` (id, titre, slug, chapeau, contenu_html, date_publication, date_modification, statut, auteur_id, categorie_id, seo_title, seo_meta_description, nb_vues)
- [x] Créer la table `categorie` (id, nom, slug, description, meta_description)
- [x] Créer la table `tag` (id, nom, slug)
- [x] Créer la table `image` (id, article_id, url, alt, legende, ordre, est_principale)
- [x] Créer la table `article_tag` (table de liaison n,n)
- [x] Créer la table `auteur` (id, nom, bio, photo_url, email)
- [x] Créer la table `utilisateur` (id, login, password_hash, role)
- [x] Configurer toutes les contraintes (FK, UNIQUE, NOT NULL, etc.)
- [x] Implémenter le script `sql/init.sql` pour Docker

---

## 2. FrontOffice (FO) - Pages publiques

- [ ] Créer page d'accueil (`index.php`)
  - [ ] Afficher liste des derniers articles
  - [ ] Afficher le chapeau de chaque article
- [ ] Créer page article (`pages/article.php?slug=...`)
  - [ ] Afficher titre, date, auteur
  - [ ] Afficher contenu HTML (TinyMCE)
  - [ ] Afficher images avec alt et légende
  - [ ] Afficher tags associés
  - [ ] Afficher catégorie
- [ ] Créer page catégorie (`pages/categorie.php?slug=...`)
  - [ ] Filtrer articles par catégorie
  - [ ] Afficher titre et description
- [ ] Créer page tag (`pages/tag.php?slug=...`)
  - [ ] Filtrer articles par tag
- [ ] Créer page auteur (`pages/auteur.php?slug=...`)
  - [ ] Afficher infos auteur
  - [ ] Lister ses articles

---

## 3. BackOffice (BO) - Gestion contenu

- [ ] Implémenter système d'authentification
  - [ ] Page login (`/admin/login/`)
  - [ ] Identifiants par défaut : `admin` / `admin123`
  - [ ] Hash des mots de passe (bcrypt)
  - [ ] Sessions utilisateur
- [ ] Gestion articles
  - [ ] Liste articles (`/admin/articles/`)
  - [ ] Créer article (`/admin/articles/new`)
  - [ ] Éditer article (`/admin/articles/edit/{id}`)
  - [ ] Supprimer article
- [ ] Gestion catégories (`/admin/categories/`)
  - [ ] CRUD complet
- [ ] Gestion tags (`/admin/tags/`)
  - [ ] CRUD complet
- [ ] Gestion auteurs (`/admin/auteurs/`)
  - [ ] CRUD complet
- [ ] Formulaire d'édition article
  - [ ] Champ Titre (obligatoire)
  - [ ] Champ Slug (auto-généré, modifiable)
  - [ ] Champ Chapeau
  - [ ] Éditeur TinyMCE pour le contenu
  - [ ] Select Catégorie
  - [ ] Checkboxes Tags
  - [ ] Upload images multiple
  - [ ] Drag & drop pour l'ordre des images
  - [ ] Marquer une image comme principale
  - [ ] Champ SEO Title (max 60 car.)
  - [ ] Champ Meta Description (max 160 car.)
  - [ ] Select Statut (brouillon/publié/archivé)

---

## 4. URL Rewriting

- [x] Créer fichier `.htaccess`
  - [x] RewriteEngine On
  - [x] Règle page d'accueil : `^$` → `index.php`
  - [x] Règle article : `^article/([a-z0-9\-]+)$` → `pages/article.php?slug=$1`
  - [x] Règle catégorie : `^categorie/([a-z0-9\-]+)$` → `pages/categorie.php?slug=$1`
  - [x] Règle tag : `^tag/([a-z0-9\-]+)$` → `pages/tag.php?slug=$1`
  - [x] Règle auteur : `^auteur/([a-z0-9\-]+)$` → `pages/auteur.php?slug=$1`
- [x] Implémenter fonction `generateSlug()` en PHP
  - [x] Convertir en minuscules
  - [x] Translittérer les accents (UTF-8 → ASCII)
  - [x] Supprimer caractères spéciaux
  - [x] Remplacer espaces/tirets multiples par un seul tiret
- [ ] Tester les URLs propres en local

---

## 5. Optimisation SEO

### 5.1 On-Page SEO

- [ ] Balise `<title>` unique par page (50-60 car.)
- [ ] Balise `<meta name="description">` (150-160 car.)
- [ ] Un seul `<h1>` par page (titre article)
- [ ] Structure sémantique `<h2>`, `<h3>`, ... `<h6>` dans le contenu
- [ ] Attributs `alt` sur toutes les images (depuis table `image`)
- [ ] Balises Open Graph
  - [ ] `og:title`
  - [ ] `og:description`
  - [ ] `og:image`
- [ ] Schema.org JSON-LD
  - [ ] Type NewsArticle
  - [ ] headline, datePublished, dateModified, author, image

### 5.2 Fichiers techniques

- [ ] Créer `robots.txt` à la racine
  - [ ] Allow /
  - [ ] Disallow /admin/, /login/
  - [ ] Sitemap déclaré
- [ ] Créer/générer `sitemap.xml` dynamiquement (PHP)
  - [ ] Lister tous les articles publiés
  - [ ] Inclure lastmod et priority

### 5.3 Responsive et performance

- [ ] CSS media queries (mobile-first)
- [ ] Meta viewport : `width=device-width, initial-scale=1.0`
- [ ] HTTPS activé (Docker/certificat)
- [ ] Test Lighthouse mobile
- [ ] Test Lighthouse desktop

### 5.4 Checklist SEO finale

| #   | Point                      | Statut |
| --- | -------------------------- | ------ |
| 1   | URL normalisée (rewriting) | ☐      |
| 2   | Structure H1→H6            | ☐      |
| 3   | `<title>` unique par page  | ☐      |
| 4   | `<meta description>`       | ☐      |
| 5   | `alt` sur les images       | ☐      |
| 6   | Test Lighthouse mobile     | ☐      |
| 7   | Test Lighthouse desktop    | ☐      |
| 8   | Mobile responsive          | ☐      |
| 9   | HTTPS activé               | ☐      |
| 10  | `robots.txt` configuré     | ☐      |
| 11  | `sitemap.xml`              | ☐      |
| 12  | Schema.org (NewsArticle)   | ☐      |

---

## 6. Configuration Docker

- [x] Créer `docker-compose.yml`
  - [x] Service web : php:8.2-apache sur port 80
  - [x] Service db : mysql:8.0
  - [x] Variables d'environnement MySQL
  - [x] Volumes (`./src:/var/www/html`, sql init)
- [ ] Créer `Dockerfile` si besoin de customisation PHP
- [ ] Tester le déploiement : `docker-compose up`
- [ ] Vérifier accès à `http://localhost`
- [ ] Vérifier base de données initialisée

---

## 7. Livrables finaux

- [ ] Compléter le document technique `todo.md`
  - [ ] Remplir Numéro ETU
  - [ ] Remplir noms du Binôme
  - [ ] Ajouter lien du dépôt GitHub/GitLab
- [ ] Captures d'écran FO
  - [ ] Page d'accueil
  - [ ] Page article (avec URL rewriting visible, structure Hn, images alt)
  - [ ] Inspecteur HTML (balises title, meta, schema.org)
- [ ] Captures d'écran BO
  - [ ] Page login
  - [ ] Liste articles
  - [ ] Formulaire édition article (TinyMCE, champs SEO)
- [ ] Ajouter diagramme BDD au document technique
  - [ ] ER diagram (draw.io ou dbdiagram.io)
- [ ] Créer `projet.zip`
  - [ ] Code source (src/)
  - [ ] Docker (docker-compose.yml, sql/init.sql)
  - [ ] Document technique avec captures
- [ ] Dépôt GitHub/GitLab
  - [ ] Créer un dépôt public
  - [ ] Pousser tout le code
  - [ ] Copier le lien
- [ ] Vérifier accès default BO
  - [ ] URL : `http://localhost/admin/login/`
  - [ ] Login : `admin`
  - [ ] Mot de passe : `admin123`

---

## 📅 Dates clés

- **Aujourd'hui :** 30 mars 2026
- **Délai livraison :** 31 mars 2026 à 14h00 (1 jour ⏰)

---

## 📝 Notes

- Contenu HTML riche via TinyMCE (pas de découpage en blocs)
- Images dans table séparée avec relation 1,n
- Statuts articles : brouillon, publié, archivé
- Rôles utilisateurs : admin, editeur
