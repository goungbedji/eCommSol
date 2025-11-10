<?php
require_once 'config.php';
requireAdmin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: admin_categories.php');
    exit;
}

$error = '';

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
            $_SESSION['success_message'] = 'Catégorie modifiée avec succès !';
            header('Location: admin_categories.php');
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
$page_title = 'Modifier une catégorie - Administration';
$admin_active = 'categories';
$admin_title = 'Modifier une catégorie';
$admin_actions = [];
?>

<?php include 'includes/admin_head.php'; ?>
<?php include 'includes/admin_header.php'; ?>

<div class="min-h-screen py-8">
    <div class="max-w-2xl mx-auto px-4 sm:px-6">
        
        <?php if ($error): ?>
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-xl">
                <p class="text-red-700 font-semibold"><?= htmlspecialchars($error) ?></p>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow-lg p-8">
            <form method="post" class="space-y-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Nom de la catégorie *</label>
                    <input type="text" name="nom" required 
                           value="<?= htmlspecialchars($categorie['nom']) ?>"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:outline-none transition-all">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Description</label>
                    <textarea name="description" rows="4"
                              class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:outline-none transition-all"><?= htmlspecialchars($categorie['description']) ?></textarea>
                </div>

                <div class="flex items-center gap-4 pt-4">
                    <button type="submit" 
                            class="px-8 py-3 bg-primary text-white font-bold rounded-lg hover:bg-primary/90 transition-all">
                        Modifier la catégorie
                    </button>
                    <a href="admin_categories.php" 
                       class="px-8 py-3 bg-gray-200 text-gray-700 font-bold rounded-lg hover:bg-gray-300 transition-all">
                        Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>