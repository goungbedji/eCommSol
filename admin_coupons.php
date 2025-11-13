<?php
require_once 'config.php';
requireAdmin();

// Supprimer un coupon si demandé
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM coupons WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header('Location: admin_coupons.php');
    exit();
}

// Récupérer tous les coupons
$stmt = $pdo->query("SELECT * FROM coupons ORDER BY expiry_date DESC");
$coupons = $stmt->fetchAll();

$page_title = 'Gestion des Coupons - Administration';
$admin_active = 'coupons';
$admin_title = '🎫 Gestion des Coupons';
$admin_actions = [
    ['href' => 'admin_coupon_add.php', 'label' => '+ Ajouter un coupon']
];
?>
<?php include 'includes/admin_head.php'; ?>
<?php include 'includes/admin_header.php'; ?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        
        <!-- Tableau des coupons -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="px-6 py-6 border-b border-gray-200">
                <h2 class="text-2xl font-bold text-gray-900">Liste des Coupons</h2>
                <p class="text-gray-600 text-sm mt-1">Gérez vos codes de réduction et promotions</p>
            </div>
            
            <div class="overflow-x-auto">
                <?php if (count($coupons) > 0): ?>
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Code</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Valeur</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Utilisé / Limite</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Expiration</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Minimum</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Statut</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($coupons as $coupon): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="font-bold text-primary"><?= htmlspecialchars($coupon['code'] ?? '') ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $coupon['type'] === 'percentage' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' ?>">
                                        <?= $coupon['type'] === 'percentage' ? '% Pourcentage' : '💰 Montant fixe' ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-semibold text-gray-900">
                                    <?= $coupon['type'] === 'percentage' ? 
                                        htmlspecialchars($coupon['value']) . '%' : 
                                        number_format($coupon['value'], 0, ',', ' ') . ' FCFA' ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    <?php 
                                    $used = (int)($coupon['times_used'] ?? $coupon['used_count'] ?? 0);
                                    $limit = $coupon['max_uses'] ?? $coupon['usage_limit'] ?? null;
                                    if ($limit === null) {
                                        echo '<span class="text-green-600 font-semibold">' . $used . ' / ∞</span>';
                                    } else {
                                        $remaining = max(0, $limit - $used);
                                        echo '<span>' . $used . ' / ' . $limit . '</span>';
                                        if ($remaining <= 0) {
                                            echo '<span class="ml-2 px-2 py-1 bg-red-100 text-red-700 text-xs font-bold rounded">Épuisé</span>';
                                        }
                                    }
                                    ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    <?php 
                                    $expiry = $coupon['expiry_date'] ?? $coupon['end_date'] ?? null;
                                    if ($expiry) {
                                        $expiry_date = new DateTime($expiry);
                                        $now = new DateTime();
                                        $is_expired = $expiry_date < $now;
                                        echo '<span class="' . ($is_expired ? 'text-red-600 font-semibold' : 'text-gray-700') . '">';
                                        echo htmlspecialchars($expiry_date->format('d/m/Y'));
                                        if ($is_expired) echo ' (Expiré)';
                                        echo '</span>';
                                    } else {
                                        echo '<span class="text-gray-500">Pas de limite</span>';
                                    }
                                    ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    <?= $coupon['min_purchase'] ? 
                                        number_format($coupon['min_purchase'], 0, ',', ' ') . ' FCFA' : 
                                        '<span class="text-gray-500">-</span>' ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?= ($coupon['active'] ?? 1) ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' ?>">
                                        <?= ($coupon['active'] ?? 1) ? '✓ Actif' : '✗ Inactif' ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <a href="admin_coupon_edit.php?id=<?= $coupon['id'] ?>" 
                                           class="px-4 py-2 bg-primary text-white text-sm font-semibold rounded-lg hover:bg-primary/90 transition-all">
                                            Modifier
                                        </a>
                                        <a href="admin_coupons.php?delete=<?= $coupon['id'] ?>" 
                                           class="px-4 py-2 bg-red-500 text-white text-sm font-semibold rounded-lg hover:bg-red-600 transition-all"
                                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce coupon ?')">
                                            Supprimer
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        <h3 class="text-gray-600 text-lg font-semibold mb-2">Aucun coupon trouvé</h3>
                        <p class="text-gray-500 mb-6">Créez votre premier coupon pour commencer à offrir des réductions.</p>
                        <a href="admin_coupon_add.php" class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-white font-bold rounded-lg hover:bg-primary/90 transition-all">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                            </svg>
                            + Ajouter un coupon
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>