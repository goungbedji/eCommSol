<?php
require_once 'config.php';
requireAdmin();

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
        
        $stmt = $pdo->prepare("INSERT INTO articles (titre, description, prix, photo, video, stock, categorie_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$titre, $description, $prix, $photo_principale, $video, $stock, $categorie_id]);
        $article_id = $pdo->lastInsertId();
        
        $photos_urls = isset($_POST['photos_urls']) ? array_filter(explode("\n", $_POST['photos_urls'])) : [];
        $photos_ordre = 1;
        
        if (!empty($photo_principale)) {
            $stmt = $pdo->prepare("INSERT INTO article_photos (article_id, photo_url, is_main, ordre) VALUES (?, ?, 1, ?)");
            $stmt->execute([$article_id, $photo_principale, $photos_ordre]);
            $photos_ordre++;
        }
        
        foreach ($photos_urls as $url) {
            $url = trim($url);
            if (!empty($url)) {
                $stmt = $pdo->prepare("INSERT INTO article_photos (article_id, photo_url, is_main, ordre) VALUES (?, ?, 0, ?)");
                $stmt->execute([$article_id, $url, $photos_ordre]);
                $photos_ordre++;
            }
        }
        
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
        $_SESSION['success_message'] = 'Article ajouté avec succès avec ' . ($photos_ordre - 1) . ' photo(s) !';
        header("Location: admin_dashboard.php");
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = 'Erreur : ' . $e->getMessage();
    }
}

$page_title = 'Ajouter un Article - Administration';
$admin_active = 'dashboard';
$admin_title = 'Ajouter un Article';
$admin_actions = [];
?>
<?php include 'includes/admin_head.php'; ?>
<?php include 'includes/admin_header.php'; ?>

<div class="min-h-screen py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6">
        
        <?php if ($error): ?>
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-xl">
                <p class="text-red-700 font-semibold"><?= $error ?></p>
            </div>
        <?php endif; ?>
        
        <div class="bg-white rounded-xl shadow-lg p-8">
            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Titre de l'article *</label>
                        <input type="text" name="titre" required 
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:outline-none transition-all">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Description *</label>
                        <textarea name="description" required rows="4"
                                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:outline-none transition-all"></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Prix (FCFA) *</label>
                        <input type="number" name="prix" step="0.01" min="0" required
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:outline-none transition-all">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Stock *</label>
                        <input type="number" name="stock" min="0" value="0" required
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:outline-none transition-all">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Catégorie *</label>
                        <select name="categorie_id" required
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:outline-none transition-all">
                            <option value="">Sélectionner une catégorie</option>
                            <?php
                            $categories = $pdo->query("SELECT * FROM categories ORDER BY nom")->fetchAll();
                            foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Photos</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Photo principale (URL)</label>
                            <input type="url" name="photo_url" placeholder="https://example.com/image.jpg"
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:outline-none transition-all">
                            <p class="text-xs text-gray-500 mt-1">Cette photo sera affichée en premier</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Photos supplémentaires (URLs)</label>
                            <textarea name="photos_urls" rows="3" placeholder="https://example.com/image1.jpg&#10;https://example.com/image2.jpg"
                                      class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:outline-none transition-all"></textarea>
                            <p class="text-xs text-gray-500 mt-1">Une URL par ligne</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Upload de photos</label>
                            <input type="file" name="photos_files[]" accept="image/*" multiple id="photosInput"
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:outline-none transition-all">
                            <p class="text-xs text-gray-500 mt-1">Formats: JPG, PNG, GIF, WEBP</p>
                            <div id="photoPreview" class="flex gap-2 flex-wrap mt-3"></div>
                        </div>
                    </div>
                </div>
                
                <div class="border-t border-gray-200 pt-6">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Vidéo (URL)</label>
                    <input type="url" name="video_url" placeholder="https://youtube.com/watch?v=..."
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:outline-none transition-all">
                </div>
                
                <div class="flex items-center gap-4 pt-6 border-t border-gray-200">
                    <button type="submit" 
                            class="px-8 py-3 bg-primary text-white font-bold rounded-lg hover:bg-primary/90 transition-all">
                        Ajouter l'article
                    </button>
                    <a href="admin_dashboard.php" 
                       class="px-8 py-3 bg-gray-200 text-gray-700 font-bold rounded-lg hover:bg-gray-300 transition-all">
                        Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
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
                    img.className = 'w-24 h-24 object-cover rounded-lg border-2 border-gray-300';
                    preview.appendChild(img);
                }
                reader.readAsDataURL(file);
            }
        }
    });
</script>

</body>
</html>
