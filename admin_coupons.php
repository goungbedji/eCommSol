<?php
require_once 'config.php';
requireAdmin();

// Supprimer un coupon si demandé
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM coupons WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header('Location: admin_coupons.php');
    exit();
}

// Récupérer tous les coupons
$stmt = $pdo->query("SELECT * FROM coupons ORDER BY expiry_date DESC");
$coupons = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Coupons - Administration</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <?php
    $admin_active = 'coupons';
    $admin_title = '🎫 Gestion des coupons';
    $admin_actions = [
        ['href' => 'admin_coupon_add.php', 'label' => '+ Ajouter un coupon', 'class' => 'btn']
    ];
    include 'admin_header.php';
    ?>

    <div class="container">
        <div class="section">
            <div class="section-header">
                <h2>Gestion des Coupons</h2>
                <a href="admin_coupon_add.php" class="btn">+ Ajouter un coupon</a>
            </div>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Type</th>
                    <th>Valeur</th>
                    <th>Utilisations restantes</th>
                    <th>Date d'expiration</th>
                    <th>Montant minimum</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($coupons as $coupon): ?>
                <tr>
                        <td><?= htmlspecialchars($coupon['code'] ?? '') ?></td>
                    <td><?= $coupon['type'] === 'percentage' ? 'Pourcentage' : 'Montant fixe' ?></td>
                        <td><?= $coupon['type'] === 'percentage' ? 
                            (isset($coupon['value']) ? htmlspecialchars($coupon['value']) . '%' : '') : 
                            (isset($coupon['value']) ? number_format($coupon['value'], 2) . '€' : '') ?></td>
                    <td><?= $coupon['max_uses'] === null ? 'Illimité' : 
                        ($coupon['max_uses'] - $coupon['times_used']) ?></td>
                        <td><?= htmlspecialchars($coupon['expiry_date'] ?? '') ?></td>
                    <td><?= $coupon['min_purchase'] ? number_format($coupon['min_purchase'], 2) . '€' : '-' ?></td>
                    <td>
                        <a href="admin_coupon_edit.php?id=<?= $coupon['id'] ?>" class="btn-small">Modifier</a>
                        <a href="admin_coupons.php?delete=<?= $coupon['id'] ?>" 
                           class="btn-small btn-danger" 
                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce coupon ?')">Supprimer</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
            </div>
        </div>
    </div>
</body>
</html>