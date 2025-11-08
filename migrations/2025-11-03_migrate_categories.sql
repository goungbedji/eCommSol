-- Migration: ajouter table `categories`, remplir `categorie_id` dans `articles` et créer la contrainte
-- Date: 2025-11-03
-- IMPORTANT: Faites une sauvegarde complète de la base (mysqldump) avant d'exécuter ce fichier.

-- 1) Créer la table categories si elle n'existe pas
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2) Ajouter la colonne categorie_id dans articles si elle n'existe pas (MySQL 8+ supporte IF NOT EXISTS)
ALTER TABLE articles ADD COLUMN IF NOT EXISTS categorie_id INT NULL;

-- 3) Insérer les catégories manquantes basées sur l'ancienne colonne texte `categorie`
--    (N'insère que les valeurs non-nulles/non-vides et uniques)
INSERT INTO categories (nom)
SELECT DISTINCT TRIM(a.categorie) AS nom
FROM articles a
WHERE a.categorie IS NOT NULL AND TRIM(a.categorie) <> ''
  AND TRIM(a.categorie) NOT IN (SELECT nom FROM categories);

-- 4) Mettre à jour articles.categorie_id en se basant sur la correspondance par nom
UPDATE articles a
JOIN categories c ON TRIM(a.categorie) = c.nom
SET a.categorie_id = c.id
WHERE a.categorie IS NOT NULL AND TRIM(a.categorie) <> '';

-- 5) Vérifier les lignes qui n'ont pas été associées
--    (optionnel : vous pouvez examiner le résultat avant d'aller plus loin)
SELECT COUNT(*) AS without_category_id FROM articles WHERE categorie_id IS NULL;

-- Si le résultat ci-dessus > 0, vous pouvez examiner quelques exemples :
SELECT id, titre, categorie FROM articles WHERE categorie_id IS NULL LIMIT 50;

-- 6) (Optionnel) Si vous êtes satisfait et voulez forcer l'intégrité, ajouter la contrainte FK
--    Note: ajouter une FK échouera si certaines categorie_id n'existent pas. Assurez-vous que toutes
--    les valeurs nullables sont acceptables ou corrigez les lignes orphelines avant.
ALTER TABLE articles
ADD CONSTRAINT IF NOT EXISTS fk_articles_categories FOREIGN KEY (categorie_id) REFERENCES categories(id) ON DELETE SET NULL;

-- 7) (Optionnel) Rendre la colonne non null si vous voulez forcer la présence d'une catégorie
--    (Assurez-vous que toutes les lignes ont un categorie_id non-null avant d'exécuter)
-- ALTER TABLE articles MODIFY categorie_id INT NOT NULL;

-- 8) (Optionnel) Supprimer l'ancienne colonne texte `categorie` si vous êtes certain
-- ALTER TABLE articles DROP COLUMN categorie;

-- FIN
