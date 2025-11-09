<?php
require_once 'config.php';
requireAdmin();

// Mettre à jour le statut d'une commande
if (isset($_POST['update_status'])) {
    $commande_id = intval($_POST['commande_id']);
    $statut = $_POST['statut'];
    
    $stmt = $pdo->prepare("UPDATE commandes SET statut = ? WHERE id = ?");
    $stmt->execute([$statut, $commande_id]);
    
    $_SESSION['success_message'] = 'Statut de la commande mis à jour avec succès !';
    header('Location: admin_commandes.php');
    exit;
}

// Récupérer toutes les commandes avec les détails
$stmt = $pdo->query("
    SELECT c.*, 
           (SELECT COUNT(*) FROM details_commande WHERE commande_id = c.id) as nb_articles
    FROM commandes c 
    ORDER BY c.date_commande DESC
");
$commandes = $stmt->fetchAll();

$page_title = 'Gestion des Commandes - Administration';
$admin_active = 'commandes';
$admin_title = '📦 Gestion des commandes';
$admin_actions = [];
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
        
        <!-- Commandes Table -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-2xl font-bold text-gray-900">📦 Toutes les Commandes (<?= count($commandes) ?>)</h2>
            </div>
            
            <?php if (count($commandes) > 0): ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">N°</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Client</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Contact</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Ville</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Articles</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Statut</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($commandes as $commande): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-bold text-primary">#<?= $commande['id'] ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-900"><?= htmlspecialchars($commande['nom_client']) ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-600">
                                    <div>📧 <?= htmlspecialchars($commande['email']) ?></div>
                                    <div>📱 <?= htmlspecialchars($commande['whatsapp']) ?></div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-gray-600"><?= htmlspecialchars($commande['ville']) ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-lg font-bold text-sm">
                                    <?= $commande['nb_articles'] ?> article(s)
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-bold text-green-600"><?= formatPrice($commande['total']) ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <?php
                                $status_colors = [
                                    'en_attente' => 'bg-orange-100 text-orange-700',
                                    'confirmee' => 'bg-blue-100 text-blue-700',
                                    'expediee' => 'bg-purple-100 text-purple-700',
                                    'livree' => 'bg-green-100 text-green-700',
                                    'annulee' => 'bg-red-100 text-red-700'
                                ];
                                $color = $status_colors[$commande['statut']] ?? 'bg-gray-100 text-gray-700';
                                ?>
                                <span class="px-3 py-1 <?= $color ?> rounded-lg font-bold text-sm">
                                    <?= ucfirst(str_replace('_', ' ', $commande['statut'])) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-600">
                                    <?= date('d/m/Y', strtotime($commande['date_commande'])) ?><br>
                                    <span class="text-xs text-gray-400"><?= date('H:i', strtotime($commande['date_commande'])) ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <button onclick="viewCommande(<?= $commande['id'] ?>)" 
                                            class="px-4 py-2 bg-primary text-white font-semibold rounded-lg hover:bg-primary/90 transition-all">
                                        Voir détails
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-20">
                <div class="inline-block p-8 bg-gray-50 rounded-3xl mb-6">
                    <svg class="w-32 h-32 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-gray-900 mb-3">Aucune commande pour le moment</h2>
                <p class="text-lg text-gray-600">Les commandes de vos clients apparaîtront ici.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal pour les détails -->
<div id="commandeModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closeModal()"></div>
        
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between rounded-t-2xl">
                <h2 class="text-2xl font-bold text-gray-900">Détails de la commande</h2>
                <button onclick="closeModal()" class="p-2 hover:bg-gray-100 rounded-xl transition-all">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div id="modalBody" class="p-6"></div>
        </div>
    </div>
</div>

<script>
    function viewCommande(id) {
        fetch('admin_commande_details.php?id=' + id)
            .then(response => response.text())
            .then(html => {
                document.getElementById('modalBody').innerHTML = html;
                document.getElementById('commandeModal').classList.remove('hidden');
            });
    }
    
    function closeModal() {
        document.getElementById('commandeModal').classList.add('hidden');
    }
</script>

</body>
</html>
