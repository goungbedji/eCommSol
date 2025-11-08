<?php
require_once 'config.php';
requireAdmin();

// Récupérer la liste des catégories pour le formulaire
$stmt = $pdo->query("SELECT id, nom FROM categories ORDER BY nom");
$categories = $stmt->fetchAll();

// Récupérer la liste des articles pour le formulaire
$stmt = $pdo->query("SELECT id, titre FROM articles ORDER BY titre");
$articles = $stmt->fetchAll();

$errors = [];
$promotion = null;

// Vérifier si un ID est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: admin_promotions.php');
    exit();
}

// Récupérer la promotion
$stmt = $pdo->prepare("SELECT * FROM promotions WHERE id = ?");
$stmt->execute([$_GET['id']]);
$promotion = $stmt->fetch();

if (!$promotion) {
    header('Location: admin_promotions.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';
    $value = $_POST['value'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $target_type = $_POST['target_type'] ?? '';
    $article_id = $_POST['article_id'] ?? null;
    $category_id = $_POST['category_id'] ?? null;

    // Validation
    if (empty($type) || !in_array($type, ['percentage', 'fixed'])) {
        $errors[] = "Le type de promotion est invalide";
    }
    if (!is_numeric($value) || $value <= 0) {
        $errors[] = "La valeur de la promotion doit être un nombre positif";
    }
    if ($type === 'percentage' && $value > 100) {
        $errors[] = "Le pourcentage ne peut pas dépasser 100%";
    }
    if (empty($start_date) || empty($end_date)) {
        $errors[] = "Les dates sont obligatoires";
    }
    if (strtotime($end_date) <= strtotime($start_date)) {
        $errors[] = "La date de fin doit être postérieure à la date de début";
    }

    // Si pas d'erreurs, on met à jour
    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE promotions 
                              SET type = ?, value = ?, start_date = ?, end_date = ?, 
                                  article_id = ?, category_id = ? 
                              WHERE id = ?");
        
        try {
            $stmt->execute([
                $type,
                $value,
                $start_date,
                $end_date,
                $target_type === 'article' ? $article_id : null,
                $target_type === 'category' ? $category_id : null,
                $_GET['id']
            ]);
            header('Location: admin_promotions.php');
            exit();
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de la mise à jour : " . $e->getMessage();
        }
    }
}

// Déterminer le type de cible
$target_type = 'global';
if ($promotion['article_id']) {
    $target_type = 'article';
} elseif ($promotion['category_id']) {
    $target_type = 'category';
}

// Formater les dates pour l'affichage dans les champs datetime-local
$start_date = date('Y-m-d\TH:i', strtotime($promotion['start_date']));
$end_date = date('Y-m-d\TH:i', strtotime($promotion['end_date']));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier une Promotion - Administration</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>📊 Administration E-Commerce</h1>
            <a href="admin_logout.php" class="back-btn">Déconnexion</a>
        </div>
    </div>
    
    <div class="nav">
        <a href="admin_dashboard.php">Articles</a>
        <a href="admin_categories.php">Catégories</a>
        <a href="admin_promotions.php" class="active">Promotions</a>
        <a href="admin_coupons.php">Coupons</a>
        <a href="admin_commandes.php">Commandes</a>
        <a href="admin_settings.php">Paramètres</a>
        <a href="index.php" target="_blank">Voir la boutique</a>
    </div>

    <div class="container">
        <div class="section">
            <div class="section-header">
                <h2>Modifier une Promotion</h2>
            </div>
        
        <?php if (!empty($errors)): ?>
            <div class="error-messages">
                <?php foreach ($errors as $error): ?>
                    <p class="error"><?= htmlspecialchars($error !== null ? $error : '') ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="admin-form">
            <div class="form-group">
                <label for="type">Type de promotion :</label>
                <select name="type" id="type" required>
                    <option value="percentage" <?= $promotion['type'] === 'percentage' ? 'selected' : '' ?>>Pourcentage</option>
                    <option value="fixed" <?= $promotion['type'] === 'fixed' ? 'selected' : '' ?>>Montant fixe</option>
                </select>
            </div>

            <div class="form-group">
                <label for="value">Valeur :</label>
                <input type="number" step="0.01" name="value" id="value" required 
                       value="<?= htmlspecialchars($promotion['value']) ?>">
            </div>

            <div class="form-group">
                <label for="start_date">Date de début :</label>
                <input type="datetime-local" name="start_date" id="start_date" required 
                       value="<?= $start_date ?>">
            </div>

            <div class="form-group">
                <label for="end_date">Date de fin :</label>
                <input type="datetime-local" name="end_date" id="end_date" required 
                       value="<?= $end_date ?>">
            </div>

            <div class="form-group">
                <label for="target_type">Appliquer à :</label>
                <select name="target_type" id="target_type" required>
                    <option value="global" <?= $target_type === 'global' ? 'selected' : '' ?>>Tous les articles</option>
                    <option value="category" <?= $target_type === 'category' ? 'selected' : '' ?>>Une catégorie</option>
                    <option value="article" <?= $target_type === 'article' ? 'selected' : '' ?>>Un article spécifique</option>
                </select>
            </div>

            <div class="form-group" id="category-select" style="display: <?= $target_type === 'category' ? 'block' : 'none' ?>;">
                <label for="category_id">Catégorie :</label>
                <select name="category_id" id="category_id">
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>" 
                            <?= $promotion['category_id'] == $category['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['nom'] ?? '') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" id="article-select" style="display: <?= $target_type === 'article' ? 'block' : 'none' ?>;">
                <label for="article_id">Article :</label>
                <select name="article_id" id="article_id">
                    <?php foreach ($articles as $article): ?>
                        <option value="<?= $article['id'] ?>"
                            <?= $promotion['product_id'] == $article['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($article['titre'] ?? '') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn">Enregistrer</button>
                <a href="admin_promotions.php" class="btn">Annuler</a>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('target_type').addEventListener('change', function() {
            const categorySelect = document.getElementById('category-select');
            const articleSelect = document.getElementById('article-select');
            
            categorySelect.style.display = 'none';
            articleSelect.style.display = 'none';
            
            if (this.value === 'category') {
                categorySelect.style.display = 'block';
            } else if (this.value === 'article') {
                articleSelect.style.display = 'block';
            }
        });

        document.getElementById('type').addEventListener('change', function() {
            const valueInput = document.getElementById('value');
            if (this.value === 'percentage') {
                valueInput.setAttribute('max', '100');
            } else {
                valueInput.removeAttribute('max');
            }
        });
    </script>
</body>
</html>