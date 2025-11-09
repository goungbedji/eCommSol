<?php
require_once 'config.php';
requireAdmin();

// Supprimer une catégorie
if (isset($_POST['delete']) && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['success_message'] = 'Catégorie supprimée avec succès !';
        header('Location: admin_categories.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Impossible de supprimer cette catégorie. Elle est peut-être utilisée par des articles.";
    }
}

// Récupérer toutes les catégories
$colExists = 0;
try {
    $colExists = (int)$pdo->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '".DB_NAME."' AND TABLE_NAME = 'articles' AND COLUMN_NAME = 'categorie_id'")->fetchColumn();
} catch (Exception $e) {
    $colExists = 0;
}

if ($colExists) {
    $sql = "SELECT c.*, COUNT(a.id) as nb_articles 
            FROM categories c 
            LEFT JOIN articles a ON c.id = a.categorie_id 
            GROUP BY c.id
            ORDER BY c.nom";
} else {
    $sql = "SELECT c.*, COUNT(a.id) as nb_articles 
            FROM categories c 
            LEFT JOIN articles a ON a.categorie = c.nom 
            GROUP BY c.id
            ORDER BY c.nom";
}

$stmt = $pdo->query($sql);
$categories = $stmt->fetchAll();

$page_title = 'Gestion des catégories - Administration';
$admin_active = 'categories';
$admin_title = '🏷️ Gestion des catégories';
$admin_actions = [
    ['href' => 'admin_category_add.php', 'label' => '+ Nouvelle catégorie']
];
?>
<?php include 'includes/admin_head.php'; ?>
<?php include 'includes/admin_header.php'; ?>

<!-- Main Content -->
<div class="min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        
        <!-- Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-xl">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-green-700 font-semibold"><?= $_SESSION['success_message'] ?></p>
                </div>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-xl">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-red-700 font-semibold"><?= $_SESSION['error_message'] ?></p>
                </div>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
        
        <!-- Categories Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($categories as $categorie): ?>
                <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-all">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-gray-900 mb-2"><?= htmlspecialchars($categorie['nom']) ?></h3>
                            <p class="text-sm text-gray-600 mb-3"><?= htmlspecialchars($categorie['description']) ?></p>
                        </div>
                        <div class="px-3 py-1 bg-primary/10 text-primary rounded-lg font-bold text-sm">
                            <?= $categorie['nb_articles'] ?> articles
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <a href="admin_category_edit.php?id=<?= $categorie['id'] ?>" 
                           class="flex-1 px-4 py-2 bg-blue-500 text-white font-semibold rounded-lg hover:bg-blue-600 transition-all text-center">
                            Modifier
                        </a>
                        <?php if ($categorie['nb_articles'] == 0): ?>
                            <form method="post" class="flex-1" 
                                  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?');">
                                <input type="hidden" name="id" value="<?= $categorie['id'] ?>">
                                <button type="submit" name="delete" 
                                        class="w-full px-4 py-2 bg-red-500 text-white font-semibold rounded-lg hover:bg-red-600 transition-all">
                                    Supprimer
                                </button>
                            </form>
                        <?php else: ?>
                            <button disabled 
                                    class="flex-1 px-4 py-2 bg-gray-300 text-gray-500 font-semibold rounded-lg cursor-not-allowed"
                                    title="Impossible de supprimer : articles liés">
                                Supprimer
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <!-- Add New Card -->
            <a href="admin_category_add.php" 
               class="bg-gradient-to-br from-primary/10 to-accent/10 border-2 border-dashed border-primary/30 rounded-2xl p-6 hover:border-primary hover:shadow-xl transition-all flex flex-col items-center justify-center min-h-[200px] group">
                <div class="w-16 h-16 bg-primary/20 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <svg class="w-8 h-8 text-primary" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">Nouvelle catégorie</h3>
                <p class="text-sm text-gray-600">Cliquez pour ajouter</p>
            </a>
        </div>
    </div>
</div>

</body>
</html>
