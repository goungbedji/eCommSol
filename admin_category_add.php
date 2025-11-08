<?php
require_once 'config.php';
requireAdmin();

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $description = trim($_POST['description']);
    
    if (empty($nom)) {
        $error = "Le nom de la catégorie est requis";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO categories (nom, description) VALUES (?, ?)");
            $stmt->execute([$nom, $description]);
            header('Location: admin_categories.php?success=1');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Code d'erreur pour duplicate entry
                $error = "Une catégorie avec ce nom existe déjà";
            } else {
                $error = "Erreur lors de l'ajout de la catégorie";
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
    <title>Ajouter une catégorie - Administration</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>🏷️ Ajouter une catégorie</h1>
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
                       value="<?= isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : '' ?>">
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4"><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">Ajouter la catégorie</button>
            </div>
        </form>
    </div>
</body>
</html>