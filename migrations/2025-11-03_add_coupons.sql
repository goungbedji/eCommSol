-- Migration: créer la table coupons
-- Date: 2025-11-03

DROP TABLE IF EXISTS coupons;
CREATE TABLE coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    type ENUM('percentage', 'fixed') NOT NULL,
    value DECIMAL(10,2) NOT NULL,
    max_uses INT DEFAULT NULL,
    times_used INT DEFAULT 0,
    min_purchase DECIMAL(10,2) DEFAULT NULL,
    expiry_date DATETIME NOT NULL,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT check_value CHECK (
        (type = 'percentage' AND value > 0 AND value <= 100) OR
        (type = 'fixed' AND value > 0)
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;