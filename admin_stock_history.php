<?php
require_once 'config.php';
requireAdmin();

// Récupérer les mouvements de stock
$stmt = $pdo->query("SELECT h.*, a.titre FROM stock_history h LEFT JOIN articles a ON h.article_id = a.id ORDER BY h.date DESC LIMIT 100");
$mouvements = $stmt->fetchAll();

$page_title = 'Historique des mouvements de stock - Administration';
$admin_active = 'stock';
$admin_title = '📦 Historique des mouvements de stock';
$admin_actions = [];
?>
<?php include 'includes/admin_head.php'; ?>
<?php include 'includes/admin_header.php'; ?>

<!-- Main Content -->
<div class="min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        
        <!-- Stock History Table -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-2xl font-bold text-gray-900">📦 Derniers mouvements (100)</h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Article</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Quantité</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Commentaire</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($mouvements as $m): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 font-semibold">
                                    <?= date('d/m/Y', strtotime($m['date'])) ?>
                                </div>
                                <div class="text-xs text-gray-500">
                                    <?= date('H:i', strtotime($m['date'])) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-900">
                                    <?= htmlspecialchars($m['titre']) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($m['type'] == 'entree'): ?>
                                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-green-100 text-green-700 rounded-lg font-bold text-sm">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd"/>
                                        </svg>
                                        Entrée
                                    </span>
                                <?php elseif ($m['type'] == 'sortie'): ?>
                                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-red-100 text-red-700 rounded-lg font-bold text-sm">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd" transform="rotate(180 10 10)"/>
                                        </svg>
                                        Sortie
                                    </span>
                                <?php elseif ($m['type'] == 'commande'): ?>
                                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-100 text-blue-700 rounded-lg font-bold text-sm">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3z"/>
                                        </svg>
                                        Commande
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-orange-100 text-orange-700 rounded-lg font-bold text-sm">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                                        </svg>
                                        Manuel
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($m['type'] == 'sortie' || $m['type'] == 'commande'): ?>
                                    <span class="text-lg font-bold text-red-600">
                                        -<?= $m['quantite'] ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-lg font-bold text-green-600">
                                        +<?= $m['quantite'] ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-gray-600">
                                    <?= htmlspecialchars($m['commentaire']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Legend -->
        <div class="mt-6 bg-white rounded-2xl shadow-lg p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">📋 Légende des types de mouvements</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="flex items-center gap-3 p-4 bg-green-50 rounded-xl">
                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-green-100 text-green-700 rounded-lg font-bold text-sm">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd"/>
                        </svg>
                        Entrée
                    </span>
                    <span class="text-sm text-gray-700">Réapprovisionnement</span>
                </div>
                
                <div class="flex items-center gap-3 p-4 bg-red-50 rounded-xl">
                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-red-100 text-red-700 rounded-lg font-bold text-sm">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd" transform="rotate(180 10 10)"/>
                        </svg>
                        Sortie
                    </span>
                    <span class="text-sm text-gray-700">Retrait manuel</span>
                </div>
                
                <div class="flex items-center gap-3 p-4 bg-blue-50 rounded-xl">
                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-100 text-blue-700 rounded-lg font-bold text-sm">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3z"/>
                        </svg>
                        Commande
                    </span>
                    <span class="text-sm text-gray-700">Vente client</span>
                </div>
                
                <div class="flex items-center gap-3 p-4 bg-orange-50 rounded-xl">
                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-orange-100 text-orange-700 rounded-lg font-bold text-sm">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                        </svg>
                        Manuel
                    </span>
                    <span class="text-sm text-gray-700">Ajustement manuel</span>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
