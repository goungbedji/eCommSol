<?php
require_once 'config.php';
requireAdmin();

$error = '';
$article = null;

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $article = $stmt->fetch();
    
    if (!$article) {
        header('Location: admin_dashboard.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $titre = clean($_POST['titre']);
    $description = clean($_POST['description']);
    $prix = floatval($_POST['prix']);
    $stock = intval($_POST['stock']);
    $categorie_id = intval($_POST['categorie_id']);
    
    $photo = $_POST['photo_url'];
    $video = clean($_POST['video_url']);
    
    if (empty($photo)) {
        $photo = $article['photo'];
    }
    
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
        
        $_SESSION['success_message'] = 'Article modifié avec succès !';
        header("Location: admin_dashboard.php");
        exit;
    } catch (Exception $e) {
        $error = 'Erreur : ' . $e->getMessage();
    }
}

$page_title = 'Modifier l\'Article - Administration';
$admin_active = 'dashboard';
$admin_title = 'Modifier l\'Article';
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
                <input type="hidden" name="id" value="<?= $article['id'] ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Titre de l'article *</label>
                        <input type="text" name="titre" required 
                               value="<?= htmlspecialchars($article['titre']) ?>"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:outline-none transition-all">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Description *</label>
                        <textarea name="description" required rows="4"
                                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:outline-none transition-all"><?= htmlspecialchars($article['description']) ?></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Prix (FCFA) *</label>
                        <input type="number" name="prix" step="0.01" min="0" required
                               value="<?= $article['prix'] ?>"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:outline-none transition-all">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Stock *</label>
                        <input type="number" name="stock" min="0" required
                               value="<?= $article['stock'] ?>"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:outline-none transition-all">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Catégorie *</label>
                        <select name="categorie_id" required
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:outline-none transition-all">
                            <?php
                            $categories = $pdo->query("SELECT * FROM categories ORDER BY nom")->fetchAll();
                            foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $article['categorie_id'] == $cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Photo</h3>
                    
                    <?php if ($article['photo']): ?>
                        <div class="mb-4">
                            <p class="text-sm text-gray-600 mb-2">Photo actuelle :</p>
                            <img src="<?= htmlspecialchars($article['photo']) ?>" alt="Photo actuelle" 
                                 class="w-32 h-32 object-cover rounded-lg border-2 border-gray-300">
                        </div>
                    <?php endif; ?>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Photo (URL)</label>
                            <input type="url" name="photo_url" placeholder="https://example.com/image.jpg"
                                   value="<?= htmlspecialchars($article['photo']) ?>"
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:outline-none transition-all">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Ou upload une nouvelle photo</label>
                            <input type="file" name="photo_file" accept="image/*"
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:outline-none transition-all">
                            <p class="text-xs text-gray-500 mt-1">Formats: JPG, PNG, GIF, WEBP</p>
                        </div>
                    </div>
                </div>
                
                <div class="border-t border-gray-200 pt-6">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Vidéo (URL)</label>
                    <input type="url" name="video_url" placeholder="https://youtube.com/watch?v=..."
                           value="<?= htmlspecialchars($article['video']) ?>"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:outline-none transition-all">
                </div>
                
                <div class="flex items-center gap-4 pt-6 border-t border-gray-200">
                    <button type="submit" 
                            class="px-8 py-3 bg-primary text-white font-bold rounded-lg hover:bg-primary/90 transition-all">
                        Modifier l'article
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

</body>
</html>
