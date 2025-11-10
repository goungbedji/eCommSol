<?php
require_once 'config.php';
requireAdmin();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $description = trim($_POST['description']);
    
    if (empty($nom)) {
        $error = "Le nom de la catégorie est requis";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO categories (nom, description) VALUES (?, ?)");
            $stmt->execute([$nom, $description]);
            $_SESSION['success_message'] = 'Catégorie ajoutée avec succès !';
            header('Location: admin_categories.php');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = "Une catégorie avec ce nom existe déjà";
            } else {
                $error = "Erreur lors de l'ajout de la catégorie";
            }
        }
    }
}

$page_title = 'Ajouter une catégorie - Administration';
$admin_active = 'categories';
$admin_title = 'Ajouter une catégorie';
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
                           value="<?= isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : '' ?>"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:outline-none transition-all">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Description</label>
                    <textarea name="description" rows="4"
                              class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:outline-none transition-all"><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>
                </div>

                <div class="flex items-center gap-4 pt-4">
                    <button type="submit" 
                            class="px-8 py-3 bg-primary text-white font-bold rounded-lg hover:bg-primary/90 transition-all">
                        Ajouter la catégorie
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