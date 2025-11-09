<?php
require_once 'config.php';
requireAdmin();

$stmt = $pdo->query("SELECT id, nom FROM categories ORDER BY nom");
$categories = $stmt->fetchAll();

$stmt = $pdo->query("SELECT id, titre FROM articles ORDER BY titre");
$articles = $stmt->fetchAll();

$errors = [];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: admin_promotions.php');
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM promotions WHERE id = ?");
$stmt->execute([$_GET['id']]);
$promotion = $stmt->fetch();

if (!$promotion) {
    header('Location: admin_promotions.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';
    $value = $_POST['value'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $target_type = $_POST['target_type'] ?? '';
    $article_id = $_POST['article_id'] ?? null;
    $category_id = $_POST['category_id'] ?? null;
    $label = $_POST['label'] ?? null;

    if (empty($type) || !in_array($type, ['percentage', 'fixed'])) {
        $errors[] = "Le type de promotion est invalide";
    }
    if (!is_numeric($value) || $value <= 0) {
        $errors[] = "La valeur de la promotion doit être un nombre positif";
    }
    if ($type === 'percentage' && $value > 100) {
        $errors[] = "Le pourcentage ne peut pas dépasser 100%";
    }
    if (empty($start_date) || empty($end_date)) {
        $errors[] = "Les dates sont obligatoires";
    }
    if (strtotime($end_date) <= strtotime($start_date)) {
        $errors[] = "La date de fin doit être postérieure à la date de début";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE promotions 
                              SET discount_type = ?, discount_value = ?, start_date = ?, end_date = ?, 
                                  product_id = ?, category_id = ?, label = ?
                              WHERE id = ?");
        
        try {
            $stmt->execute([
                $type === 'percentage' ? 'percent' : 'fixed',
                $value,
                $start_date,
                $end_date,
                $target_type === 'article' ? $article_id : null,
                $target_type === 'category' ? $category_id : null,
                $label,
                $_GET['id']
            ]);
            $_SESSION['success_message'] = 'Promotion modifiée avec succès !';
            header('Location: admin_promotions.php');
            exit();
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de la mise à jour : " . $e->getMessage();
        }
    }
}

$target_type = 'global';
if ($promotion['product_id']) {
    $target_type = 'article';
} elseif ($promotion['category_id']) {
    $target_type = 'category';
}

$start_date = date('Y-m-d\TH:i', strtotime($promotion['start_date']));
$end_date = date('Y-m-d\TH:i', strtotime($promotion['end_date']));

$page_title = 'Modifier une Promotion - Administration';
$admin_active = 'promotions';
$admin_title = 'Modifier une Promotion';
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
                               value="<?= htmlspecialchars($promotion['label'] ?? '') ?>"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Type *</label>
                        <select name="type" id="type" required
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:outline-none transition-all">
                            <option value="percentage" <?= $promotion['discount_type'] === 'percent' ? 'selected' : '' ?>>Pourcentage</option>
                            <option value="fixed" <?= $promotion['discount_type'] === 'fixed' ? 'selected' : '' ?>>Montant fixe</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Valeur *</label>
                        <input type="number" step="0.01" name="value" id="value" required
                               value="<?= htmlspecialchars($promotion['discount_value']) ?>"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Appliquer à *</label>
                        <select name="target_type" id="target_type" required
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:outline-none transition-all">
                            <option value="global" <?= $target_type === 'global' ? 'selected' : '' ?>>Tous les articles</option>
                            <option value="category" <?= $target_type === 'category' ? 'selected' : '' ?>>Une catégorie</option>
                            <option value="article" <?= $target_type === 'article' ? 'selected' : '' ?>>Un article</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Date de début *</label>
                        <input type="datetime-local" name="start_date" required
                               value="<?= $start_date ?>"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Date de fin *</label>
                        <input type="datetime-local" name="end_date" required
                               value="<?= $end_date ?>"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:outline-none transition-all">
                    </div>

                    <div id="category-select" class="<?= $target_type === 'category' ? '' : 'hidden' ?> md:col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Catégorie</label>
                        <select name="category_id" id="category_id"
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:outline-none transition-all">
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>" <?= $promotion['category_id'] == $category['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div id="article-select" class="<?= $target_type === 'article' ? '' : 'hidden' ?> md:col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Article</label>
                        <select name="article_id" id="article_id"
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:outline-none transition-all">
                            <?php foreach ($articles as $article): ?>
                                <option value="<?= $article['id'] ?>" <?= $promotion['product_id'] == $article['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($article['titre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-4 pt-4 border-t border-gray-200">
                    <button type="submit" 
                            class="px-8 py-3 bg-primary text-white font-bold rounded-lg hover:bg-primary/90 transition-all">
                        Modifier
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
