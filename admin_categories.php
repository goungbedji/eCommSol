<?php
require_once 'config.php';
requireAdmin();

// Supprimer une catégorie
if (isset($_POST['delete']) && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: admin_categories.php?success=1');
        exit;
    } catch (PDOException $e) {
        $error = "Impossible de supprimer cette catégorie. Elle est peut-être utilisée par des articles.";
    }
}

// Récupérer toutes les catégories
// Détecter si la colonne categorie_id existe dans la table articles
$colExists = 0;
try {
    $colExists = (int)$pdo->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '".DB_NAME."' AND TABLE_NAME = 'articles' AND COLUMN_NAME = 'categorie_id'")->fetchColumn();
} catch (Exception $e) {
    // En cas d'erreur (droits, etc.), on tombera sur le fallback
    $colExists = 0;
}

if ($colExists) {
    $sql = "SELECT c.*, COUNT(a.id) as nb_articles 
            FROM categories c 
            LEFT JOIN articles a ON c.id = a.categorie_id 
            GROUP BY c.id
            ORDER BY c.nom";
} else {
    // Fallback : ancienne colonne texte `categorie` dans articles
    $sql = "SELECT c.*, COUNT(a.id) as nb_articles 
            FROM categories c 
            LEFT JOIN articles a ON a.categorie = c.nom 
            GROUP BY c.id
            ORDER BY c.nom";
}

$stmt = $pdo->query($sql);
$categories = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des catégories - Administration</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php
    $admin_active = 'categories';
    $admin_title = '🏷️ Gestion des catégories';
    $admin_actions = [
        ['href' => 'admin_category_add.php', 'label' => 'Nouvelle catégorie', 'class' => 'btn btn-primary'],
        ['href' => 'admin_dashboard.php', 'label' => 'Retour', 'class' => 'back-btn']
    ];
    include 'admin_header.php';
    ?>

    <div class="container">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert success">Opération réussie !</div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert error"><?= htmlspecialchars($error !== null ? $error : '') ?></div>
        <?php endif; ?>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Description</th>
                        <th>Nombre d'articles</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $categorie): ?>
                        <tr>
                            <td><?= htmlspecialchars($categorie['nom'] ?? '') ?></td>
                            <td><?= htmlspecialchars($categorie['description'] ?? '') ?></td>
                            <td><?= $categorie['nb_articles'] ?></td>
                            <td>
                                <a href="admin_category_edit.php?id=<?= $categorie['id'] ?>" 
                                   class="btn btn-small">Modifier</a>
                                <?php if ($categorie['nb_articles'] == 0): ?>
                                    <form method="post" style="display: inline;" 
                                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?');">
                                        <input type="hidden" name="id" value="<?= $categorie['id'] ?>">
                                        <button type="submit" name="delete" class="btn btn-small btn-danger">
                                            Supprimer
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>