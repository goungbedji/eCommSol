-- Migration: ajouter tables promotions et coupons et colonnes commandes
-- Date: 2025-11-03
-- Sauvegarder la base avant exécution

-- Table promotions: applicables à un produit ou une catégorie
CREATE TABLE IF NOT EXISTS promotions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT DEFAULT NULL,
    category_id INT DEFAULT NULL,
    discount_type ENUM('percent','fixed') NOT NULL,
    discount_value DECIMAL(10,2) NOT NULL,
    label VARCHAR(100) DEFAULT NULL,
    start_date DATETIME DEFAULT NULL,
    end_date DATETIME DEFAULT NULL,
    active TINYINT(1) DEFAULT 1,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_promotions_product FOREIGN KEY (product_id) REFERENCES articles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table coupons
CREATE TABLE IF NOT EXISTS coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    discount_type ENUM('percent','fixed') NOT NULL,
    discount_value DECIMAL(10,2) NOT NULL,
    usage_limit INT DEFAULT NULL,
    used_count INT DEFAULT 0,
    start_date DATETIME DEFAULT NULL,
    end_date DATETIME DEFAULT NULL,
    active TINYINT(1) DEFAULT 1,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ajouter colonnes à commandes pour stocker coupon et montant de la remise
ALTER TABLE commandes
ADD COLUMN IF NOT EXISTS coupon_code VARCHAR(50) NULL,
ADD COLUMN IF NOT EXISTS discount_amount DECIMAL(10,2) DEFAULT 0;

-- FIN
