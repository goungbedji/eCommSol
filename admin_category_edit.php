<?php
require_once 'config.php';
requireAdmin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: admin_categories.php');
    exit;
}

$success = $error = '';

// Récupérer la catégorie
$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$id]);
$categorie = $stmt->fetch();

if (!$categorie) {
    header('Location: admin_categories.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $description = trim($_POST['description']);
    
    if (empty($nom)) {
        $error = "Le nom de la catégorie est requis";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE categories SET nom = ?, description = ? WHERE id = ?");
            $stmt->execute([$nom, $description, $id]);
            header('Location: admin_categories.php?success=1');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = "Une catégorie avec ce nom existe déjà";
            } else {
                $error = "Erreur lors de la modification de la catégorie";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier une catégorie - Administration</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>🏷️ Modifier une catégorie</h1>
            <a href="admin_categories.php" class="back-btn">Retour</a>
        </div>
    </div>

    <div class="container">
        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error !== null ? $error : '') ?></div>
        <?php endif; ?>

        <form method="post" class="form">
            <div class="form-group">
                <label for="nom">Nom de la catégorie *</label>
                <input type="text" id="nom" name="nom" required 
                       value="<?= htmlspecialchars($categorie['nom']) ?>">
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4"><?= htmlspecialchars($categorie['description']) ?></textarea>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">Modifier la catégorie</button>
            </div>
        </form>
    </div>
</body>
</html>