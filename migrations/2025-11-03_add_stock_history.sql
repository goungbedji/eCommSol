-- Migration: créer la table stock_history
-- Date: 2025-11-03

CREATE TABLE IF NOT EXISTS stock_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    article_id INT NOT NULL,
    type ENUM('entree','sortie','commande','manuel') NOT NULL,
    quantite INT NOT NULL,
    commentaire VARCHAR(255),
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
