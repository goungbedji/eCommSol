<?php
require_once 'config.php';
requireAdmin();

$success = '';
$error = '';
$article = null;

// Récupérer l'article
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $article = $stmt->fetch();
    
    if (!$article) {
        header('Location: admin_dashboard.php');
        exit;
    }
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $titre = clean($_POST['titre']);
    $description = clean($_POST['description']);
    $prix = floatval($_POST['prix']);
    $stock = intval($_POST['stock']);
    $categorie_id = intval($_POST['categorie_id']);
    
    $photo = $_POST['photo_url'];
    $video = clean($_POST['video_url']);
    
    // Si aucune nouvelle photo URL, garder l'ancienne
    if (empty($photo)) {
        $photo = $article['photo'];
    }
    
    // Gérer l'upload de photo
    if (!empty($_FILES['photo_file']['name'])) {
        $target_dir = UPLOAD_DIR;
        $file_extension = strtolower(pathinfo($_FILES['photo_file']['name'], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($file_extension, $allowed_types)) {
            if (move_uploaded_file($_FILES['photo_file']['tmp_name'], $target_file)) {
                $photo = $target_file;
            }
        }
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE articles SET titre = ?, description = ?, prix = ?, photo = ?, video = ?, stock = ?, categorie_id = ? WHERE id = ?");
        $stmt->execute([$titre, $description, $prix, $photo, $video, $stock, $categorie_id, $id]);
        
        $success = 'Article modifié avec succès !';
        header("refresh:2;url=admin_dashboard.php");
    } catch (Exception $e) {
        $error = 'Erreur : ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier l'Article</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>✏️ Modifier l'Article</h1>
            <a href="admin_dashboard.php" class="back-btn">← Retour</a>
        </div>
    </div>
    
    <div class="container">
        <div class="form-container">
            <?php if ($success): ?>
                <div class="success"><?= $success ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="error"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $article['id'] ?>">
                
                <div class="form-group">
                    <label>Titre de l'article *</label>
                    <input type="text" name="titre" value="<?= htmlspecialchars($article['titre'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Description *</label>
                    <textarea name="description" required><?= htmlspecialchars($article['description'] ?? '') ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Prix (FCFA) *</label>
                    <input type="number" name="prix" step="0.01" min="0" value="<?= $article['prix'] ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Stock *</label>
                    <input type="number" name="stock" min="0" value="<?= $article['stock'] ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Catégorie *</label>
                    <select name="categorie_id" required>
                        <option value="">Sélectionner une catégorie</option>
                        <?php
                        $categories = $pdo->query("SELECT * FROM categories ORDER BY nom")->fetchAll();
                        foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= (isset($article['categorie_id']) && $article['categorie_id'] == $cat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['nom'] ?? '') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Photo actuelle</label>
                    <?php if (!empty($article['photo'])): ?>
                        <img src="<?= htmlspecialchars($article['photo'] ?? '') ?>" class="current-image" alt="<?= htmlspecialchars($article['titre'] ?? '') ?>">
                    <?php else: ?>
                        <div class="note">Aucune photo</div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label>Nouvelle Photo (URL)</label>
                    <input type="url" name="photo_url" placeholder="https://example.com/image.jpg">
                    <div class="note">Laissez vide pour garder l'image actuelle</div>
                </div>
                
                <div class="form-group">
                    <label>Nouvelle Photo (Upload fichier)</label>
                    <input type="file" name="photo_file" accept="image/*">
                </div>
                
                <div class="form-group">
                    <label>Vidéo (URL)</label>
                    <input type="url" name="video_url" value="<?= htmlspecialchars($article['video']) ?>" placeholder="https://youtube.com/watch?v=...">
                </div>
                
                <div style="margin-top: 30px;">
                    <button type="submit" class="btn">Enregistrer les modifications</button>
                    <a href="admin_dashboard.php" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>