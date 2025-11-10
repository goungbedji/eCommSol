<?php
require_once 'config.php';
requireAdmin();

// Supprimer une promotion si demandé
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM promotions WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $_SESSION['success_message'] = 'Promotion supprimée avec succès !';
    header('Location: admin_promotions.php');
    exit();
}

// Récupérer toutes les promotions avec les détails des articles et catégories
$stmt = $pdo->query("SELECT p.*, 
                            a.titre as product_name,
                            c.nom as category_name 
                     FROM promotions p 
                     LEFT JOIN articles a ON p.product_id = a.id
                     LEFT JOIN categories c ON p.category_id = c.id 
                     WHERE p.active = 1
                     ORDER BY p.start_date DESC");
$promotions = $stmt->fetchAll();

$page_title = 'Gestion des Promotions - Administration';
$admin_active = 'promotions';
$admin_title = '📣 Gestion des promotions';
$admin_actions = [
    ['href' => 'admin_promotion_add.php', 'label' => '+ Ajouter une promotion']
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
        
        <!-- Promotions Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($promotions as $promo): ?>
                <?php
                $now = time();
                $start = strtotime($promo['start_date']);
                $end = strtotime($promo['end_date']);
                $is_active = ($now >= $start && $now <= $end);
                $is_upcoming = ($now < $start);
                $is_expired = ($now > $end);
                ?>
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-all">
                    <!-- Header with status -->
                    <div class="<?= $is_active ? 'bg-gradient-to-r from-green-500 to-green-600' : ($is_upcoming ? 'bg-gradient-to-r from-blue-500 to-blue-600' : 'bg-gradient-to-r from-gray-400 to-gray-500') ?> p-6 text-white">
                        <div class="flex items-center justify-between mb-3">
                            <span class="px-3 py-1 bg-white/20 rounded-lg text-xs font-bold">
                                #<?= $promo['id'] ?>
                            </span>
                            <?php if ($is_active): ?>
                                <span class="px-3 py-1 bg-white text-green-600 rounded-lg text-xs font-bold">🔥 Active</span>
                            <?php elseif ($is_upcoming): ?>
                                <span class="px-3 py-1 bg-white text-blue-600 rounded-lg text-xs font-bold">⏰ À venir</span>
                            <?php else: ?>
                                <span class="px-3 py-1 bg-white text-gray-600 rounded-lg text-xs font-bold">❌ Expirée</span>
                            <?php endif; ?>
                        </div>
                        <div class="text-3xl font-extrabold mb-2">
                            <?php if ($promo['discount_type'] === 'percent'): ?>
                                -<?= htmlspecialchars($promo['discount_value']) ?>%
                            <?php else: ?>
                                -<?= number_format($promo['discount_value'], 0, ',', ' ') ?> FCFA
                            <?php endif; ?>
                        </div>
                        <div class="text-sm opacity-90">
                            <?= $promo['discount_type'] === 'percent' ? '📊 Réduction en %' : '💰 Réduction fixe' ?>
                        </div>
                    </div>
                    
                    <!-- Body -->
                    <div class="p-6 space-y-3">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Période:</span>
                            <span class="font-semibold text-gray-900 text-right">
                                <?= date('d/m/Y', $start) ?><br>
                                <span class="text-xs text-gray-500">au <?= date('d/m/Y', $end) ?></span>
                            </span>
                        </div>
                        
                        <div class="pt-3 border-t border-gray-200">
                            <span class="text-xs text-gray-600 block mb-2">Cible:</span>
                            <?php if ($promo['product_id']): ?>
                                <div class="px-3 py-2 bg-blue-50 text-blue-700 rounded-lg text-sm font-semibold">
                                    📦 <?= htmlspecialchars($promo['product_name']) ?>
                                </div>
                            <?php elseif ($promo['category_id']): ?>
                                <div class="px-3 py-2 bg-purple-50 text-purple-700 rounded-lg text-sm font-semibold">
                                    🏷️ <?= htmlspecialchars($promo['category_name']) ?>
                                </div>
                            <?php else: ?>
                                <div class="px-3 py-2 bg-green-50 text-green-700 rounded-lg text-sm font-semibold">
                                    🌟 Tous les articles
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="pt-3 border-t border-gray-200 flex items-center gap-2">
                            <a href="admin_promotion_edit.php?id=<?= $promo['id'] ?>" 
                               class="flex-1 px-4 py-2 bg-blue-500 text-white font-semibold rounded-lg hover:bg-blue-600 transition-all text-center">
                                Modifier
                            </a>
                            <a href="admin_promotions.php?delete=<?= $promo['id'] ?>" 
                               class="flex-1 px-4 py-2 bg-red-500 text-white font-semibold rounded-lg hover:bg-red-600 transition-all text-center"
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette promotion ?')">
                                Supprimer
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <!-- Add New Card -->
            <a href="admin_promotion_add.php" 
               class="bg-gradient-to-br from-primary/10 to-accent/10 border-2 border-dashed border-primary/30 rounded-2xl p-6 hover:border-primary hover:shadow-xl transition-all flex flex-col items-center justify-center min-h-[350px] group">
                <div class="w-16 h-16 bg-primary/20 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <svg class="w-8 h-8 text-primary" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">Nouvelle promotion</h3>
                <p class="text-sm text-gray-600">Cliquez pour créer</p>
            </a>
        </div>
    </div>
</div>

</body>
</html>
