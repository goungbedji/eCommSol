<?php
require_once 'config.php';
requireAdmin();

// Récupérer la liste des catégories pour le formulaire
$stmt = $pdo->query("SELECT id, nom FROM categories ORDER BY nom");
$categories = $stmt->fetchAll();

// Récupérer la liste des articles pour le formulaire
$stmt = $pdo->query("SELECT id, titre FROM articles ORDER BY titre");
$articles = $stmt->fetchAll();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $discount_type = $_POST['type'] ?? '';
    $discount_value = $_POST['value'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $target_type = $_POST['target_type'] ?? '';
    $product_id = $_POST['article_id'] ?? null;
    $category_id = $_POST['category_id'] ?? null;
    $label = $_POST['label'] ?? null;

    // Validation
    if (empty($discount_type) || !in_array($discount_type, ['percentage', 'fixed'])) {
        $errors[] = "Le type de promotion est invalide";
    }
    if (!is_numeric($discount_value) || $discount_value <= 0) {
        $errors[] = "La valeur de la promotion doit être un nombre positif";
    }
    if ($discount_type === 'percentage' && $discount_value > 100) {
        $errors[] = "Le pourcentage ne peut pas dépasser 100%";
    }
    if (empty($start_date) || empty($end_date)) {
        $errors[] = "Les dates sont obligatoires";
    }
    if (strtotime($end_date) <= strtotime($start_date)) {
        $errors[] = "La date de fin doit être postérieure à la date de début";
    }

    // Si pas d'erreurs, on enregistre
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO promotions (discount_type, discount_value, start_date, end_date, product_id, category_id, label, active) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
        
        try {
            $stmt->execute([
                $discount_type === 'percentage' ? 'percent' : 'fixed',
                $discount_value,
                $start_date,
                $end_date,
                $target_type === 'article' ? $product_id : null,
                $target_type === 'category' ? $category_id : null,
                $label
            ]);
            header('Location: admin_promotions.php');
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de l'enregistrement : " . $e->getMessage();
        }
    }
}

$page_title = 'Ajouter une Promotion - Administration';
$admin_active = 'promotions';
$admin_title = 'Ajouter une Promotion';
$admin_actions = [];
?>
<?php include 'includes/admin_head.php'; ?>
<?php include 'includes/admin_header.php'; ?>

<div class="min-h-screen py-8">
    <div class="max-w-2xl mx-auto px-4 sm:px-6">
        
        <?php if (!empty($errors)): ?>
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-xl">
                <?php foreach ($errors as $error): ?>
                    <p class="text-red-700 font-semibold"><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow-lg p-8">
            <form method="POST" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Libellé</label>
                        <input type="text" name="label" 
                               value="<?= isset($_POST['label']) ? htmlspecialchars($_POST['label']) : '' ?>"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Type *</label>
                        <select name="type" id="type" required
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:outline-none transition-all">
                            <option value="percentage" <?= isset($_POST['type']) && $_POST['type'] === 'percentage' ? 'selected' : '' ?>>Pourcentage</option>
                            <option value="fixed" <?= isset($_POST['type']) && $_POST['type'] === 'fixed' ? 'selected' : '' ?>>Montant fixe</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Valeur *</label>
                        <input type="number" step="0.01" name="value" id="value" required
                               value="<?= isset($_POST['value']) ? htmlspecialchars($_POST['value']) : '' ?>"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Appliquer à *</label>
                        <select name="target_type" id="target_type" required
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:outline-none transition-all">
                            <option value="global">Tous les articles</option>
                            <option value="category">Une catégorie</option>
                            <option value="article">Un article</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Date de début *</label>
                        <input type="datetime-local" name="start_date" required
                               value="<?= isset($_POST['start_date']) ? htmlspecialchars($_POST['start_date']) : '' ?>"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Date de fin *</label>
                        <input type="datetime-local" name="end_date" required
                               value="<?= isset($_POST['end_date']) ? htmlspecialchars($_POST['end_date']) : '' ?>"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:outline-none transition-all">
                    </div>

                    <div id="category-select" class="hidden md:col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Catégorie</label>
                        <select name="category_id" id="category_id"
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:outline-none transition-all">
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div id="article-select" class="hidden md:col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Article</label>
                        <select name="article_id" id="article_id"
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:outline-none transition-all">
                            <?php foreach ($articles as $article): ?>
                                <option value="<?= $article['id'] ?>"><?= htmlspecialchars($article['titre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-4 pt-4 border-t border-gray-200">
                    <button type="submit" 
                            class="px-8 py-3 bg-primary text-white font-bold rounded-lg hover:bg-primary/90 transition-all">
                        Enregistrer
                    </button>
                    <a href="admin_promotions.php" 
                       class="px-8 py-3 bg-gray-200 text-gray-700 font-bold rounded-lg hover:bg-gray-300 transition-all">
                        Annuler
                    </a>
                </div>
        </form>
    </div>

        </div>
    </div>
</div>

<script>
    document.getElementById('target_type').addEventListener('change', function() {
        const categorySelect = document.getElementById('category-select');
        const articleSelect = document.getElementById('article-select');
        
        categorySelect.classList.add('hidden');
        articleSelect.classList.add('hidden');
        
        if (this.value === 'category') {
            categorySelect.classList.remove('hidden');
        } else if (this.value === 'article') {
            articleSelect.classList.remove('hidden');
        }
    });

    document.getElementById('type').addEventListener('change', function() {
        const valueInput = document.getElementById('value');
        if (this.value === 'percentage') {
            valueInput.setAttribute('max', '100');
        } else {
            valueInput.removeAttribute('max');
        }
    });
</script>

</body>
</html>