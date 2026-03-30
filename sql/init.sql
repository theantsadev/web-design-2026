-- Création des tables pour le site d'informations sur la guerre en Iran

-- Table: auteur
CREATE TABLE IF NOT EXISTS auteur (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nom VARCHAR(255) NOT NULL,
  bio TEXT,
  photo_url VARCHAR(500),
  email VARCHAR(255) UNIQUE NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: utilisateur
CREATE TABLE IF NOT EXISTS utilisateur (
  id INT PRIMARY KEY AUTO_INCREMENT,
  login VARCHAR(100) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin', 'editeur') DEFAULT 'editeur',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: categorie
CREATE TABLE IF NOT EXISTS categorie (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nom VARCHAR(255) NOT NULL UNIQUE,
  slug VARCHAR(255) UNIQUE NOT NULL,
  description TEXT,
  meta_description VARCHAR(160),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: tag
CREATE TABLE IF NOT EXISTS tag (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nom VARCHAR(255) NOT NULL UNIQUE,
  slug VARCHAR(255) UNIQUE NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: article
CREATE TABLE IF NOT EXISTS article (
  id INT PRIMARY KEY AUTO_INCREMENT,
  titre VARCHAR(255) NOT NULL,
  slug VARCHAR(255) UNIQUE NOT NULL,
  chapeau TEXT,
  contenu_html LONGTEXT,
  seo_title VARCHAR(60),
  seo_meta_description VARCHAR(160),
  date_publication DATETIME,
  date_modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  statut ENUM('brouillon', 'publie', 'archive') DEFAULT 'brouillon',
  auteur_id INT,
  categorie_id INT,
  nb_vues INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (auteur_id) REFERENCES auteur(id) ON DELETE SET NULL,
  FOREIGN KEY (categorie_id) REFERENCES categorie(id) ON DELETE SET NULL,
  KEY idx_slug (slug),
  KEY idx_statut (statut),
  KEY idx_categorie_id (categorie_id),
  KEY idx_auteur_id (auteur_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: image
CREATE TABLE IF NOT EXISTS image (
  id INT PRIMARY KEY AUTO_INCREMENT,
  article_id INT NOT NULL,
  url VARCHAR(500) NOT NULL,
  alt VARCHAR(255),
  legende VARCHAR(500),
  ordre INT DEFAULT 0,
  est_principale BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (article_id) REFERENCES article(id) ON DELETE CASCADE,
  KEY idx_article_id (article_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table de liaison: article_tag (n à n)
CREATE TABLE IF NOT EXISTS article_tag (
  article_id INT NOT NULL,
  tag_id INT NOT NULL,
  PRIMARY KEY (article_id, tag_id),
  FOREIGN KEY (article_id) REFERENCES article(id) ON DELETE CASCADE,
  FOREIGN KEY (tag_id) REFERENCES tag(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertion des utilisateurs par défaut
INSERT INTO utilisateur (login, password_hash, role) VALUES
  ('admin', '$2y$10$ADaKUr3eMvEL.JQvxJDoAul/G8LKD7/l2F2W5b3VKO.mjxhIV4Q9W', 'admin'),
  ('editeur', '$2y$10$ADaKUr3eMvEL.JQvxJDoAul/G8LKD7/l2F2W5b3VKO.mjxhIV4Q9W', 'editeur');

-- Insertion des catégories par défaut
INSERT INTO categorie (nom, slug, description, meta_description) VALUES
  ('Politique', 'politique', 'Articles sur la politique iranienne', 'Dernières actualités politiques de l\'Iran'),
  ('Économie', 'economie', 'Articles sur l\'économie iranienne', 'Informations économiques sur l\'Iran'),
  ('Société', 'societe', 'Articles sur la société iranienne', 'Actualités sociales et culturelles de l\'Iran'),
  ('Militaire', 'militaire', 'Articles sur les forces armées', 'Informations militaires et de défense');

-- Insertion des auteurs par défaut
INSERT INTO auteur (nom, email, bio) VALUES
  ('Admin', 'admin@iranweb.local', 'Administrateur du site'),
  ('Jean Dupont', 'jean@iranweb.local', 'Journaliste spécialisé en géopolitique'),
  ('Marie Martin', 'marie@iranweb.local', 'Experte en relations internationales');

-- Insertion des tags par défaut
INSERT INTO tag (nom, slug) VALUES
  ('Tensions', 'tensions'),
  ('Diplomatie', 'diplomatie'),
  ('Sanctions', 'sanctions'),
  ('Nucléaire', 'nucleaire'),
  ('Proche-Orient', 'proche-orient');


INSERT INTO article (titre, slug, chapeau, contenu_html, seo_title, seo_meta_description, date_publication, statut, auteur_id, categorie_id) VALUES (
  'Les tensions nucléaires entre l\'Iran et l\'Occident s\'intensifient',
  'tensions-nucleaires-iran-occident',
  'Une nouvelle escalade diplomatique menace les négociations. Les inspecteurs de l\'AIEA rapportent une augmentation significative de l\'enrichissement d\'uranium iranien.',
  '<h2>Context Géopolitique</h2><p>Les relations entre l\'Iran et les puissances occidentales ont atteint un tournant critique. Les dernières semaines ont vu une série de décisions qui risquent de déstabiliser la région.</p><h2>Enrichissement d\'Uranium: Les chiffres clés</h2><p>Selon les rapports de l\'Agence Internationale de l\'Énergie Atomique (AIEA):</p><ul><li>L\'Iran a augmenté ses réserves d\'uranium enrichi à 60%</li><li>Cette concentration dépasse largement les limites de l\'accord JCPOA</li><li>Les inspecteurs ont enregistré 10 000+ kilogrammes de matière enrichie</li></ul><h2>Réactions Internationales</h2><p>Les États-Unis et l\'Union Européenne ont exprimé leur inquiétude. Des réunions d\'urgence ont été convoquées au Conseil de Sécurité des Nations Unies.</p><blockquote>Cette escalade pose un risque grave pour la stabilité régionale.</blockquote><h2>Perspectives d\'Avenir</h2><p>Les négociateurs travaillent pour trouver une solution diplomatique avant que la situation ne s\'aggrave davantage. Les prochaines semaines seront décisives.</p>',
  'Tensions nucléaires Iran 2026',
  'L\'Iran intensifie son enrichissement d\'uranium. Les puissances occidentales expriment leur inquiétude face à cette nouvelle escalade.',
  NOW(),
  'publie',
  2,
  1
);

INSERT INTO article_tag (article_id, tag_id) VALUES
(LAST_INSERT_ID(), 1),
(LAST_INSERT_ID(), 4),
(LAST_INSERT_ID(), 5);

INSERT INTO image (article_id, url, alt, legende, ordre, est_principale) VALUES
(LAST_INSERT_ID(), 'https://images.unsplash.com/photo-1526778548025-fa2f459cd5c1?w=600&h=400&fit=crop', 'Installation nucléaire iranienne', 'Complexe nucléaire de Natanz', 0, 1),
(LAST_INSERT_ID(), 'https://images.unsplash.com/photo-1554224311-beee415c201f?w=600&h=400&fit=crop', 'Débat politique', 'Débat au Conseil de Sécurité de l\'ONU', 1, 0);
