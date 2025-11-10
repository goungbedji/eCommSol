<?php
require_once 'config.php';
requireAdmin();

// Récupérer tous les articles avec leurs catégories
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$categorie_filter = isset($_GET['categorie']) ? intval($_GET['categorie']) : 0;

$sql = "SELECT a.*, c.nom as nom_categorie 
        FROM articles a 
        LEFT JOIN categories c ON a.categorie_id = c.id 
        WHERE 1=1";

$params = [];

if (!empty($search)) {
    $sql .= " AND (a.titre LIKE ? OR a.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($categorie_filter > 0) {
    $sql .= " AND a.categorie_id = ?";
    $params[] = $categorie_filter;
}

$sql .= " ORDER BY a.date_creation DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$articles = $stmt->fetchAll();

// Récupérer les catégories pour le filtre
$categories = $pdo->query("SELECT * FROM categories ORDER BY nom")->fetchAll();

$page_title = 'Gestion des Articles - Administration';
$admin_active = 'articles';
$admin_title = 'Gestion des Articles';
$admin_actions = [
    ['label' => 'Ajouter un article', 'url' => 'admin_add_article.php', 'class' => 'bg-primary text-white']
];
?>
<?php include 'includes/admin_head.php'; ?>
<?php include 'includes/admin_header.php'; ?>

<div class="min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-xl">
                <p class="text-green-700 font-semibold"><?= $_SESSION['success_message'] ?></p>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <!-- Filtres -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Rechercher</label>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Titre ou description..."
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:outline-none transition-all">
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Catégorie</label>
                    <select name="categorie" 
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:outline-none transition-all">
                        <option value="0">Toutes les catégories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $categorie_filter == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="flex items-end gap-2">
                    <button type="submit" 
                            class="px-6 py-3 bg-primary text-white font-bold rounded-lg hover:bg-primary/90 transition-all">
                        Filtrer
                    </button>
                    <a href="admin_articles.php" 
                       class="px-6 py-3 bg-gray-200 text-gray-700 font-bold rounded-lg hover:bg-gray-300 transition-all">
                        Réinitialiser
                    </a>
                </div>
            </form>
        </div>

        <!-- Liste des articles -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b-2 border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Photo</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Article</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Catégorie</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Prix</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Stock</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (empty($articles)): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    <p class="text-lg font-semibold mb-2">Aucun article trouvé</p>
                                    <a href="admin_add_article.php" class="text-primary hover:underline">Ajouter votre premier article</a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($articles as $article): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <?php if ($article['photo']): ?>
                                            <img src="<?= htmlspecialchars($article['photo']) ?>" 
                                                 alt="<?= htmlspecialchars($article['titre']) ?>"
                                                 class="w-16 h-16 object-cover rounded-lg border-2 border-gray-200">
                                        <?php else: ?>
                                            <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                                                <span class="text-gray-400 text-xs">Pas d'image</span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900"><?= htmlspecialchars($article['titre']) ?></div>
                                        <div class="text-sm text-gray-500 line-clamp-2"><?= htmlspecialchars(substr($article['description'], 0, 80)) ?>...</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">
                                            <?= htmlspecialchars($article['nom_categorie'] ?? 'Sans catégorie') ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="font-bold text-gray-900"><?= number_format($article['prix'], 0, ',', ' ') ?> FCFA</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if ($article['stock'] <= 5): ?>
                                            <span class="px-3 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded-full">
                                                <?= $article['stock'] ?> unités
                                            </span>
                                        <?php elseif ($article['stock'] <= 20): ?>
                                            <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full">
                                                <?= $article['stock'] ?> unités
                                            </span>
                                        <?php else: ?>
                                            <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">
                                                <?= $article['stock'] ?> unités
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <?= date('d/m/Y', strtotime($article['date_creation'])) ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="admin_edit_article.php?id=<?= $article['id'] ?>" 
                                               class="px-4 py-2 bg-blue-500 text-white text-sm font-semibold rounded-lg hover:bg-blue-600 transition-all">
                                                Modifier
                                            </a>
                                            <a href="admin_delete_article.php?id=<?= $article['id'] ?>" 
                                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet article ?')"
                                               class="px-4 py-2 bg-red-500 text-white text-sm font-semibold rounded-lg hover:bg-red-600 transition-all">
                                                Supprimer
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Statistiques rapides -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="text-sm font-bold text-gray-500 mb-2">Total Articles</div>
                <div class="text-3xl font-bold text-gray-900"><?= count($articles) ?></div>
            </div>
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="text-sm font-bold text-gray-500 mb-2">Stock Total</div>
                <div class="text-3xl font-bold text-gray-900"><?= array_sum(array_column($articles, 'stock')) ?></div>
            </div>
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="text-sm font-bold text-gray-500 mb-2">Valeur Stock</div>
                <div class="text-3xl font-bold text-gray-900">
                    <?= number_format(array_sum(array_map(function($a) { return $a['prix'] * $a['stock']; }, $articles)), 0, ',', ' ') ?> FCFA
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
