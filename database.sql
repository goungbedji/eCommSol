-- Créer la base de données
-- -----------------------------------------------------
-- Script complet de création de la base pour la plateforme
-- Exécuter ce fichier créera la base `ecommerce_db` et toutes les tables
-- -----------------------------------------------------



-- -----------------------------
-- Table: categories
-- -----------------------------
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nom` VARCHAR(100) NOT NULL UNIQUE,
    `description` TEXT NULL,
    `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------
-- Table: articles
-- -----------------------------
CREATE TABLE IF NOT EXISTS `articles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `titre` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `prix` DECIMAL(10,2) NOT NULL,
    `photo` VARCHAR(500) NULL,
    `video` VARCHAR(500) NULL,
    `stock` INT DEFAULT 0,
    `categorie_id` INT NULL,
    `categorie` VARCHAR(255) NULL,
    `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `date_modification` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_articles_categories` FOREIGN KEY (`categorie_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------
-- Table: article_photos
-- Used by admin pages to store multiple photos per article
-- -----------------------------
CREATE TABLE IF NOT EXISTS `article_photos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `article_id` INT NOT NULL,
    `photo_url` VARCHAR(1000) NOT NULL,
    `is_main` TINYINT(1) DEFAULT 0,
    `ordre` INT DEFAULT 0,
    `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_article_photos_article` FOREIGN KEY (`article_id`) REFERENCES `articles`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------
-- Table: commandes
-- -----------------------------
CREATE TABLE IF NOT EXISTS `commandes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nom_client` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `whatsapp` VARCHAR(50) NOT NULL,
    `adresse` TEXT NOT NULL,
    `ville` VARCHAR(100) NOT NULL,
    `total` DECIMAL(10,2) NOT NULL,
    `coupon_code` VARCHAR(50) NULL,
    `discount_amount` DECIMAL(10,2) DEFAULT 0,
    `statut` ENUM('en_attente','confirmee','expediee','livree','annulee') DEFAULT 'en_attente',
    `date_commande` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------
-- Table: details_commande
-- -----------------------------
CREATE TABLE IF NOT EXISTS `details_commande` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `commande_id` INT NOT NULL,
    `article_id` INT NOT NULL,
    `quantite` INT NOT NULL,
    `prix_unitaire` DECIMAL(10,2) NOT NULL,
    CONSTRAINT `fk_details_commande_commande` FOREIGN KEY (`commande_id`) REFERENCES `commandes`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_details_commande_article` FOREIGN KEY (`article_id`) REFERENCES `articles`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------
-- Table: admins
-- -----------------------------
CREATE TABLE IF NOT EXISTS `admins` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(100) UNIQUE NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `nom` VARCHAR(100) NULL,
    `prenom` VARCHAR(100) NULL,
    `email` VARCHAR(255) NOT NULL,
    `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insérer un admin par défaut (mot de passe: ). Conserver si la ligne existe déjà.
INSERT INTO `admins` (`username`,`password`,`email`)
SELECT 'admin', '$2y$10$gAnE2KttTq3KEeHoK5mf.OKLXkecI4TmbWhmz3PwMh9aouoefPFN.', 'admin@example.com'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `admins` WHERE `username` = 'admin');

INSERT INTO `admins` (`username`,`password`,`email`)
SELECT 'kamikaze', '$2y$10$lhq/FxuF3DhhJduubPjyOulNtt9bD/BNN9MRmARhzDcI539YTEKqu', '1camigbdj@gmail.com'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `admins` WHERE `username` = 'kamikaze');

INSERT INTO `admins` (`username`,`password`,`email`)
SELECT 'chefbandit', '$2y$10$Dz08baNf4GTcd4prXBpb9uii8m/N51gcxJoH2SQuDDrW.G1UvFPcu', '2camigbdj@gmail.com'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `admins` WHERE `username` = 'adrenaline');

INSERT INTO `admins` (`username`,`password`,`email`)
SELECT 'adrenaline', '$2y$10$N5ZNG5xaapaMEluxb81hqej9Cihrzawjm0zAwq5M0ZLKzagZ2h5je', '3camigbdj@gmail.com'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `admins` WHERE `username` = 'adrenaline');
-- -----------------------------
-- Table: promotions
-- Promotions applicables à un produit ou à une catégorie
-- -----------------------------
CREATE TABLE IF NOT EXISTS `promotions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT DEFAULT NULL,
    `category_id` INT DEFAULT NULL,
    `discount_type` ENUM('percent','fixed') NOT NULL,
    `discount_value` DECIMAL(10,2) NOT NULL,
    `label` VARCHAR(100) DEFAULT NULL,
    `start_date` DATETIME DEFAULT NULL,
    `end_date` DATETIME DEFAULT NULL,
    `active` TINYINT(1) DEFAULT 1,
    `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_promotions_product` FOREIGN KEY (`product_id`) REFERENCES `articles`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_promotions_category` FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------
-- Table: coupons
-- On crée des colonnes compatibles avec les différentes versions du code
-- (max_uses / usage_limit) et (times_used / used_count) pour compatibilité.
-- -----------------------------
CREATE TABLE IF NOT EXISTS `coupons` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `type` ENUM('percentage','fixed') NOT NULL,
    `value` DECIMAL(10,2) NOT NULL,
    `max_uses` INT DEFAULT NULL,
    `usage_limit` INT DEFAULT NULL,
    `times_used` INT DEFAULT 0,
    `used_count` INT DEFAULT 0,
    `min_purchase` DECIMAL(10,2) DEFAULT NULL,
    `start_date` DATETIME DEFAULT NULL,
    `expiry_date` DATETIME DEFAULT NULL,
    `end_date` DATETIME DEFAULT NULL,
    `active` TINYINT(1) DEFAULT 1,
    `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `chk_coupon_value` CHECK ((`type` = 'percentage' AND `value` > 0 AND `value` <= 100) OR (`type` = 'fixed' AND `value` > 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Note: Les triggers ont été retirés pour la compatibilité avec l'hébergement
-- La synchronisation des champs devra être gérée au niveau de l'application PHP

-- -----------------------------
-- Table: stock_history
-- -----------------------------
CREATE TABLE IF NOT EXISTS `stock_history` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `article_id` INT NOT NULL,
    `type` ENUM('entree','sortie','commande','manuel') NOT NULL,
    `quantite` INT NOT NULL,
    `commentaire` VARCHAR(255) DEFAULT NULL,
    `date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_stock_history_article` FOREIGN KEY (`article_id`) REFERENCES `articles`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------
-- Table: boutique_settings
-- -----------------------------
CREATE TABLE IF NOT EXISTS `boutique_settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nom_boutique` VARCHAR(255) DEFAULT 'Ma Boutique E-Commerce',
    `slogan` VARCHAR(255) DEFAULT 'Votre partenaire shopping',
    `email_boutique` VARCHAR(255) DEFAULT 'contact@maboutique.com',
    `whatsapp_boutique` VARCHAR(50) DEFAULT '+229 97 00 00 00',
    `adresse_boutique` VARCHAR(255) DEFAULT '',
    `ville` VARCHAR(100) DEFAULT 'Cotonou',
    `pays` VARCHAR(100) DEFAULT 'Bénin',
    `description_boutique` TEXT DEFAULT NULL,
    `theme` VARCHAR(50) NOT NULL DEFAULT 'theme-default',
    `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insérer une ligne par défaut si absente
INSERT INTO `boutique_settings` (`nom_boutique`,`slogan`,`email_boutique`,`whatsapp_boutique`,`adresse_boutique`,`ville`,`pays`,`description_boutique`,`theme`)
SELECT 'Ma Boutique E-Commerce','Votre partenaire shopping','contact@maboutique.com','+229 97 00 00 00','', 'Cotonou','Bénin', '', 'theme-default'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `boutique_settings` LIMIT 1);

-- -----------------------------
-- Données d'exemple (non obligatoires)
-- -----------------------------
-- Quelques catégories et articles d'exemple
INSERT INTO `categories` (`nom`,`description`)
SELECT 'Électronique','Produits électroniques' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `categories` WHERE `nom`='Électronique');
INSERT INTO `categories` (`nom`,`description`)
SELECT 'Accessoires','Accessoires divers' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `categories` WHERE `nom`='Accessoires');

-- Insérer des articles d'exemple seulement s'il n'y en a pas
INSERT INTO `articles` (`titre`,`description`,`prix`,`photo`,`stock`,`categorie`)
SELECT 'Smartphone Pro X','Smartphone haut de gamme avec écran OLED et caméra 108MP',450000,'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=400',15,'Électronique'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `articles` WHERE `titre`='Smartphone Pro X');

INSERT INTO `articles` (`titre`,`description`,`prix`,`photo`,`stock`,`categorie`)
SELECT 'Laptop Ultra 15','Ordinateur portable performant pour le travail et les jeux',750000,'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=400',8,'Électronique'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `articles` WHERE `titre`='Laptop Ultra 15');

INSERT INTO `articles` (`titre`,`description`,`prix`,`photo`,`stock`,`categorie`)
SELECT 'Montre Connectée Sport','Montre intelligente avec suivi fitness et notifications',85000,'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400',25,'Accessoires'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `articles` WHERE `titre`='Montre Connectée Sport');

-- -----------------------------
-- Indexes additionnels utiles
-- -----------------------------
CREATE INDEX IF NOT EXISTS idx_articles_categorie_id ON `articles` (`categorie_id`);
CREATE INDEX IF NOT EXISTS idx_promotions_product_id ON `promotions` (`product_id`);
CREATE INDEX IF NOT EXISTS idx_coupons_code ON `coupons` (`code`);

-- Fin du script