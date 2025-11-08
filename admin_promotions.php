<?php
require_once 'config.php';

// Vérifier si l'administrateur est connecté
require_once 'config.php';
requireAdmin();

// Supprimer une promotion si demandé
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM promotions WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header('Location: admin_promotions.php');
    exit();
}

// Récupérer toutes les promotions avec les détails des articles et catégories
$stmt = $pdo->query("SELECT p.*, 
                            a.titre as product_name,
                            c.nom as category_name 
                     FROM promotions p 
                     LEFT JOIN articles a ON p.product_id = a.id
                     LEFT JOIN categories c ON p.category_id = c.id 
                     WHERE p.active = 1
                     ORDER BY p.start_date DESC");
$promotions = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Promotions - Administration</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php
    $admin_active = 'promotions';
    $admin_title = '📣 Gestion des promotions';
    $admin_actions = [
        ['href' => 'admin_promotion_add.php', 'label' => '+ Ajouter une promotion', 'class' => 'btn']
    ];
    include 'admin_header.php';
    ?>

    <div class="container">
        <div class="section">
            <div class="section-header">
                <h2>Gestion des Promotions</h2>
                <a href="admin_promotion_add.php" class="btn">+ Ajouter une promotion</a>
            </div>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Type</th>
                    <th>Valeur</th>
                    <th>Date début</th>
                    <th>Date fin</th>
                    <th>Article/Catégorie</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($promotions as $promotion): ?>
                <tr>
                    <td><?= htmlspecialchars($promotion['id'] ?? '') ?></td>
                    <td><?= $promotion['discount_type'] === 'percent' ? 'Pourcentage' : 'Montant fixe' ?></td>
                    <td><?= $promotion['discount_type'] === 'percent' ? 
                        (isset($promotion['discount_value']) ? htmlspecialchars($promotion['discount_value']) . '%' : '') : 
                        (isset($promotion['discount_value']) ? number_format($promotion['discount_value'], 2) . '€' : '') ?></td>
                    <td><?= htmlspecialchars($promotion['start_date'] ?? '') ?></td>
                    <td><?= htmlspecialchars($promotion['end_date'] ?? '') ?></td>
                    <td>
                        <?php if ($promotion['product_id']): ?>
                            Article: <?= htmlspecialchars($promotion['product_name'] ?? '') ?>
                        <?php elseif ($promotion['category_id']): ?>
                            Catégorie: <?= htmlspecialchars($promotion['category_name'] ?? '') ?>
                        <?php else: ?>
                            Tous les articles
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="admin_promotion_edit.php?id=<?= $promotion['id'] ?>" class="btn">Modifier</a>
                        <a href="admin_promotions.php?delete=<?= $promotion['id'] ?>" 
                           class="btn btn-danger" 
                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette promotion ?')">Supprimer</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>