<?php
require_once 'config.php';
requireAdmin();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = strtoupper($_POST['code'] ?? '');
    $type = $_POST['type'] ?? '';
    $value = $_POST['value'] ?? '';
    $max_uses = $_POST['max_uses'] !== '' ? $_POST['max_uses'] : null;
    $expiry_date = $_POST['expiry_date'] ?? '';
    $min_purchase = $_POST['min_purchase'] !== '' ? $_POST['min_purchase'] : null;

    // Validation
    if (empty($code) || strlen($code) < 3) {
        $errors[] = "Le code doit faire au moins 3 caractères";
    }
    if (empty($type) || !in_array($type, ['percentage', 'fixed'])) {
        $errors[] = "Le type de réduction est invalide";
    }
    if (!is_numeric($value) || $value <= 0) {
        $errors[] = "La valeur de la réduction doit être un nombre positif";
    }
    if ($type === 'percentage' && $value > 100) {
        $errors[] = "Le pourcentage ne peut pas dépasser 100%";
    }
    if ($max_uses !== null && (!is_numeric($max_uses) || $max_uses < 1)) {
        $errors[] = "Le nombre d'utilisations doit être un nombre positif";
    }
    if (empty($expiry_date)) {
        $errors[] = "La date d'expiration est obligatoire";
    }
    if ($min_purchase !== null && (!is_numeric($min_purchase) || $min_purchase < 0)) {
        $errors[] = "Le montant minimum d'achat doit être un nombre positif";
    }

    // Vérifier si le code existe déjà
    $stmt = $pdo->prepare("SELECT id FROM coupons WHERE code = ?");
    $stmt->execute([$code]);
    if ($stmt->fetch()) {
        $errors[] = "Ce code existe déjà";
    }

    // Si pas d'erreurs, on enregistre
    if (empty($errors)) {
        // Synchronisation des champs de compatibilité
        $usage_limit = $max_uses;
        $times_used = $used_count = 0;

        $stmt = $pdo->prepare("INSERT INTO coupons (code, type, value, max_uses, usage_limit, times_used, used_count, expiry_date, min_purchase) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        try {
            $stmt->execute([
                $code,
                $type,
                $value,
                $max_uses,
                $usage_limit,
                $times_used,
                $used_count,
                $expiry_date,
                $min_purchase
            ]);
            header('Location: admin_coupons.php');
            exit();
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de l'enregistrement : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Coupon - Administration</title>
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
        <a href="admin_promotions.php">Promotions</a>
        <a href="admin_coupons.php" class="active">Coupons</a>
        <a href="admin_commandes.php">Commandes</a>
        <a href="admin_settings.php">Paramètres</a>
        <a href="index.php" target="_blank">Voir la boutique</a>
    </div>

    <div class="container">
        <div class="section">
            <div class="section-header">
                <h2>Ajouter un Coupon</h2>
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
                <label for="code">Code du coupon :</label>
                <input type="text" name="code" id="code" required minlength="3" 
                       value="<?= isset($_POST['code']) ? htmlspecialchars($_POST['code']) : '' ?>"
                       onkeyup="this.value = this.value.toUpperCase()">
            </div>

            <div class="form-group">
                <label for="type">Type de réduction :</label>
                <select name="type" id="type" required>
                    <option value="percentage" <?= isset($_POST['type']) && $_POST['type'] === 'percentage' ? 'selected' : '' ?>>Pourcentage</option>
                    <option value="fixed" <?= isset($_POST['type']) && $_POST['type'] === 'fixed' ? 'selected' : '' ?>>Montant fixe</option>
                </select>
            </div>

            <div class="form-group">
                <label for="value">Valeur :</label>
                <input type="number" step="0.01" name="value" id="value" required
                       value="<?= isset($_POST['value']) ? htmlspecialchars($_POST['value']) : '' ?>">
            </div>

            <div class="form-group">
                <label for="max_uses">Nombre maximum d'utilisations (vide pour illimité) :</label>
                <input type="number" name="max_uses" id="max_uses" min="1"
                       value="<?= isset($_POST['max_uses']) ? htmlspecialchars($_POST['max_uses']) : '' ?>">
            </div>

            <div class="form-group">
                <label for="expiry_date">Date d'expiration :</label>
                <input type="datetime-local" name="expiry_date" id="expiry_date" required
                       value="<?= isset($_POST['expiry_date']) ? htmlspecialchars($_POST['expiry_date']) : '' ?>">
            </div>

            <div class="form-group">
                <label for="min_purchase">Montant minimum d'achat (vide pour aucun) :</label>
                <input type="number" step="0.01" name="min_purchase" id="min_purchase" min="0"
                       value="<?= isset($_POST['min_purchase']) ? htmlspecialchars($_POST['min_purchase']) : '' ?>">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn">Enregistrer</button>
                <a href="admin_coupons.php" class="btn">Annuler</a>
            </div>
        </form>
    </div>

    <script>
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