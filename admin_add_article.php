<?php
require_once 'config.php';
requireAdmin();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = clean($_POST['titre']);
    $description = clean($_POST['description']);
    $prix = floatval($_POST['prix']);
    $stock = intval($_POST['stock']);
    $categorie_id = intval($_POST['categorie_id']);
    
    $photo_principale = $_POST['photo_url'];
    $video = clean($_POST['video_url']);
    
    try {
        $pdo->beginTransaction();
        
        // Insérer l'article
        $stmt = $pdo->prepare("INSERT INTO articles (titre, description, prix, photo, video, stock, categorie_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$titre, $description, $prix, $photo_principale, $video, $stock, $categorie_id]);
        $article_id = $pdo->lastInsertId();
        
        // Traiter les photos
        $photos_urls = isset($_POST['photos_urls']) ? array_filter(explode("\n", $_POST['photos_urls'])) : [];
        $photos_ordre = 1;
        
        // Ajouter la photo principale si elle existe
        if (!empty($photo_principale)) {
            $stmt = $pdo->prepare("INSERT INTO article_photos (article_id, photo_url, is_main, ordre) VALUES (?, ?, 1, ?)");
            $stmt->execute([$article_id, $photo_principale, $photos_ordre]);
            $photos_ordre++;
        }
        
        // Ajouter les photos supplémentaires (URLs)
        foreach ($photos_urls as $url) {
            $url = trim($url);
            if (!empty($url)) {
                $stmt = $pdo->prepare("INSERT INTO article_photos (article_id, photo_url, is_main, ordre) VALUES (?, ?, 0, ?)");
                $stmt->execute([$article_id, $url, $photos_ordre]);
                $photos_ordre++;
            }
        }
        
        // Traiter les uploads de fichiers multiples
        if (!empty($_FILES['photos_files']['name'][0])) {
            $target_dir = UPLOAD_DIR;
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            foreach ($_FILES['photos_files']['name'] as $key => $filename) {
                if (!empty($filename)) {
                    $file_extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    
                    if (in_array($file_extension, $allowed_types)) {
                        $new_filename = uniqid() . '_' . $key . '.' . $file_extension;
                        $target_file = $target_dir . $new_filename;
                        
                        if (move_uploaded_file($_FILES['photos_files']['tmp_name'][$key], $target_file)) {
                            $is_main = (empty($photo_principale) && $photos_ordre == 1) ? 1 : 0;
                            $stmt = $pdo->prepare("INSERT INTO article_photos (article_id, photo_url, is_main, ordre) VALUES (?, ?, ?, ?)");
                            $stmt->execute([$article_id, $target_file, $is_main, $photos_ordre]);
                            
                            // Mettre à jour la photo principale de l'article si c'est la première
                            if ($is_main) {
                                $stmt = $pdo->prepare("UPDATE articles SET photo = ? WHERE id = ?");
                                $stmt->execute([$target_file, $article_id]);
                            }
                            
                            $photos_ordre++;
                        }
                    }
                }
            }
        }
        
        $pdo->commit();
        $success = 'Article ajouté avec succès avec ' . ($photos_ordre - 1) . ' photo(s) !';
        header("refresh:2;url=admin_dashboard.php");
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = 'Erreur : ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Article</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .photos-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .photos-section h3 {
            color: #667eea;
            margin-bottom: 15px;
        }
        .photo-preview {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 10px;
        }
        .photo-preview img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
            border: 2px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>➕ Ajouter un Article</h1>
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
                <div class="form-group">
                    <label>Titre de l'article *</label>
                    <input type="text" name="titre" required>
                </div>
                
                <div class="form-group">
                    <label>Description *</label>
                    <textarea name="description" required></textarea>
                </div>
                
                <div class="form-group">
                    <label>Prix (FCFA) *</label>
                    <input type="number" name="prix" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label>Stock *</label>
                    <input type="number" name="stock" min="0" value="0" required>
                </div>
                
                <div class="form-group">
                    <label>Catégorie *</label>
                    <select name="categorie_id" required>
                        <option value="">Sélectionner une catégorie</option>
                        <?php
                        $categories = $pdo->query("SELECT * FROM categories ORDER BY nom")->fetchAll();
                        foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nom'] ?? '') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="photos-section">
                    <h3>📸 Photos de l'article</h3>
                    
                    <div class="form-group">
                        <label>Photo principale (URL)</label>
                        <input type="url" name="photo_url" placeholder="https://example.com/image.jpg">
                        <div class="note">Cette photo sera affichée en premier dans la boutique</div>
                    </div>
                    
                    <div class="form-group">
                        <label>Photos supplémentaires (URLs) - Une URL par ligne</label>
                        <textarea name="photos_urls" rows="4" placeholder="https://example.com/image1.jpg&#10;https://example.com/image2.jpg&#10;https://example.com/image3.jpg"></textarea>
                        <div class="note">Ajoutez plusieurs URLs, une par ligne</div>
                    </div>
                    
                    <div class="form-group">
                        <label>Upload de photos (Multiple)</label>
                        <input type="file" name="photos_files[]" accept="image/*" multiple id="photosInput">
                        <div class="note">Vous pouvez sélectionner plusieurs images à la fois (Ctrl+Clic)</div>
                        <div class="note">Formats acceptés: JPG, PNG, GIF, WEBP</div>
                        <div id="photoPreview" class="photo-preview"></div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Vidéo (URL YouTube, etc.)</label>
                    <input type="url" name="video_url" placeholder="https://youtube.com/watch?v=...">
                </div>
                
                <div style="margin-top: 30px;">
                    <button type="submit" class="btn">Ajouter l'article</button>
                    <a href="admin_dashboard.php" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Prévisualisation des images uploadées
        document.getElementById('photosInput').addEventListener('change', function(e) {
            const preview = document.getElementById('photoPreview');
            preview.innerHTML = '';
            
            const files = e.target.files;
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        preview.appendChild(img);
                    }
                    reader.readAsDataURL(file);
                }
            }
        });
    </script>
</body>
</html>