<?php
require_once 'config.php';
requireAdmin();

// Récupérer tous les articles
$stmt = $pdo->query("SELECT a.*, c.nom as nom_categorie 
                     FROM articles a 
                     LEFT JOIN categories c ON a.categorie_id = c.id 
                     ORDER BY a.date_creation DESC");
$articles = $stmt->fetchAll();

// Récupérer les statistiques
$stats = [
    'articles' => $pdo->query("SELECT COUNT(*) FROM articles")->fetchColumn(),
    'commandes' => $pdo->query("SELECT COUNT(*) FROM commandes")->fetchColumn(),
    'commandes_en_attente' => $pdo->query("SELECT COUNT(*) FROM commandes WHERE statut = 'en_attente'")->fetchColumn()
];

// Calculs supplémentaires pour le dashboard
$monthStart = date('Y-m-01 00:00:00');
$monthEnd = date('Y-m-t 23:59:59');

// Chiffre d'affaires du mois
$stmt = $pdo->prepare("SELECT IFNULL(SUM(total),0) as ca FROM commandes WHERE date_commande BETWEEN ? AND ?");
$stmt->execute([$monthStart, $monthEnd]);
$monthly_ca = (float)$stmt->fetchColumn();

// Ventes par jour (pour le mois)
$stmt = $pdo->prepare("SELECT DATE(date_commande) as d, IFNULL(SUM(total),0) as revenue FROM commandes WHERE date_commande BETWEEN ? AND ? GROUP BY DATE(date_commande) ORDER BY DATE(date_commande)");
$stmt->execute([$monthStart, $monthEnd]);
$sales_by_day = $stmt->fetchAll();

// Top produits vendus (quantité)
$stmt = $pdo->query("SELECT a.titre, SUM(dc.quantite) as sold FROM details_commande dc LEFT JOIN articles a ON dc.article_id = a.id GROUP BY dc.article_id ORDER BY sold DESC LIMIT 10");
$top_products = $stmt->fetchAll();

// Nouveaux clients ce mois (par email)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM (SELECT email, MIN(date_commande) as first_order FROM commandes GROUP BY email HAVING first_order BETWEEN ? AND ?) x");
$stmt->execute([$monthStart, $monthEnd]);
$new_clients = (int)$stmt->fetchColumn();

// Commandes par statut
$stmt = $pdo->query("SELECT statut, COUNT(*) as cnt FROM commandes GROUP BY statut");
$orders_by_status = $stmt->fetchAll();

// Evolution du stock (net par jour)
$stmt = $pdo->prepare("SELECT DATE(date) as d, SUM(CASE WHEN type = 'entree' THEN quantite WHEN type IN ('sortie','commande') THEN -quantite ELSE 0 END) as net_change FROM stock_history WHERE date BETWEEN ? AND ? GROUP BY DATE(date) ORDER BY DATE(date)");
$stmt->execute([$monthStart, $monthEnd]);
$stock_evolution = $stmt->fetchAll();

$page_title = 'Tableau de bord - Administration';
$admin_active = 'dashboard';
$admin_title = '📊 Tableau de bord';
$admin_actions = [
    ['href' => 'admin_add_article.php', 'label' => '+ Ajouter un article']
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
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
            <!-- Total Articles -->
            <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-blue-500">
                <div class="text-sm text-gray-600 mb-2">Total Articles</div>
                <div class="text-3xl font-bold text-gray-900"><?= $stats['articles'] ?></div>
            </div>
            
            <!-- Total Commandes -->
            <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-purple-500">
                <div class="text-sm text-gray-600 mb-2">Total Commandes</div>
                <div class="text-3xl font-bold text-gray-900"><?= $stats['commandes'] ?></div>
            </div>
            
            <!-- En Attente -->
            <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-orange-500">
                <div class="text-sm text-gray-600 mb-2">En Attente</div>
                <div class="text-3xl font-bold text-gray-900"><?= $stats['commandes_en_attente'] ?></div>
            </div>
            
            <!-- CA Mensuel -->
            <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-green-500">
                <div class="text-sm text-gray-600 mb-2">CA ce mois</div>
                <div class="text-2xl font-bold text-gray-900"><?= number_format($monthly_ca, 0, ',', ' ') ?> FCFA</div>
            </div>
            
            <!-- Nouveaux Clients -->
            <div class="bg-white rounded-xl p-6 shadow-lg border-l-4 border-pink-500">
                <div class="text-sm text-gray-600 mb-2">Nouveaux Clients</div>
                <div class="text-3xl font-bold text-gray-900"><?= $new_clients ?></div>
            </div>
        </div>
        
        <!-- Charts Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Sales Chart -->
            <div class="lg:col-span-2 bg-white rounded-2xl p-6 shadow-lg">
                <h3 class="text-xl font-bold text-gray-900 mb-6">📈 Ventes journalières (mois)</h3>
                <canvas id="salesChart" height="100"></canvas>
            </div>
            
            <!-- Status Chart -->
            <div class="bg-white rounded-2xl p-6 shadow-lg">
                <h3 class="text-xl font-bold text-gray-900 mb-6">📊 Commandes par statut</h3>
                <canvas id="statusChart"></canvas>
            </div>
        </div>
        
        <!-- Second Row Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Top Products -->
            <div class="bg-white rounded-2xl p-6 shadow-lg">
                <h3 class="text-xl font-bold text-gray-900 mb-6">Top 10 produits</h3>
                <div class="space-y-2">
                    <?php foreach ($top_products as $index => $p): ?>
                        <div class="flex items-center justify-between py-2 border-b border-gray-100">
                            <div class="flex items-center gap-3">
                                <span class="text-gray-400 font-bold"><?= $index + 1 ?>.</span>
                                <span class="text-gray-900"><?= htmlspecialchars($p['titre'] ?? '—') ?></span>
                            </div>
                            <span class="text-primary font-bold"><?= (int)$p['sold'] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Stock Evolution -->
            <div class="lg:col-span-2 bg-white rounded-2xl p-6 shadow-lg">
                <h3 class="text-xl font-bold text-gray-900 mb-6">📦 Evolution du stock (net par jour)</h3>
                <canvas id="stockChart" height="100"></canvas>
            </div>
        </div>
        
        <!-- Articles Table -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-2xl font-bold text-gray-900">🛍️ Gestion des Articles</h2>
                <a href="admin_add_article.php" class="flex items-center gap-2 px-6 py-3 bg-primary text-white font-bold rounded-xl hover:bg-primary/90 shadow-lg hover:shadow-xl transition-all">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                    </svg>
                    Ajouter un article
                </a>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Image</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Titre</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Prix</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Stock</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Catégorie</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($articles as $article): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <?php if (!empty($article['photo'])): ?>
                                    <img src="<?= htmlspecialchars($article['photo']) ?>" 
                                         class="w-16 h-16 object-cover rounded-xl" 
                                         alt="<?= htmlspecialchars($article['titre']) ?>">
                                <?php else: ?>
                                    <div class="w-16 h-16 bg-gray-200 rounded-xl flex items-center justify-center">
                                        <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-900"><?= htmlspecialchars($article['titre']) ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-bold text-primary"><?= formatPrice($article['prix']) ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($article['stock'] == 0): ?>
                                    <span class="px-3 py-1 bg-red-100 text-red-700 rounded-lg font-bold text-sm">Rupture</span>
                                <?php elseif ($article['stock'] < 5): ?>
                                    <span class="px-3 py-1 bg-orange-100 text-orange-700 rounded-lg font-bold text-sm"><?= $article['stock'] ?></span>
                                <?php else: ?>
                                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-lg font-bold text-sm"><?= $article['stock'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-gray-600"><?= htmlspecialchars($article['nom_categorie'] ?? '(Non défini)') ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="admin_edit_article.php?id=<?= $article['id'] ?>" 
                                       class="px-4 py-2 bg-blue-500 text-white font-semibold rounded-lg hover:bg-blue-600 transition-all">
                                        Modifier
                                    </a>
                                    <a href="admin_delete_article.php?id=<?= $article['id'] ?>" 
                                       class="px-4 py-2 bg-red-500 text-white font-semibold rounded-lg hover:bg-red-600 transition-all"
                                       onclick="return confirm('Confirmer la suppression ?')">
                                        Supprimer
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // Prepare data from PHP
    const salesLabels = <?= json_encode(array_map(fn($r)=>$r['d'], $sales_by_day)) ?>;
    const salesData = <?= json_encode(array_map(fn($r)=>(float)$r['revenue'], $sales_by_day)) ?>;

    const statusLabels = <?= json_encode(array_map(fn($r)=>$r['statut'], $orders_by_status)) ?>;
    const statusData = <?= json_encode(array_map(fn($r)=>(int)$r['cnt'], $orders_by_status)) ?>;

    const stockLabels = <?= json_encode(array_map(fn($r)=>$r['d'], $stock_evolution)) ?>;
    const stockData = <?= json_encode(array_map(fn($r)=>(int)$r['net_change'], $stock_evolution)) ?>;

    // Sales chart
    const ctxSales = document.getElementById('salesChart').getContext('2d');
    new Chart(ctxSales, {
        type: 'line',
        data: {
            labels: salesLabels,
            datasets: [{
                label: 'CA (FCFA)',
                data: salesData,
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                fill: true,
                tension: 0.4,
                borderWidth: 3
            }]
        },
        options: { 
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false }
            }
        }
    });

    // Status chart (doughnut)
    const ctxStatus = document.getElementById('statusChart').getContext('2d');
    new Chart(ctxStatus, {
        type: 'doughnut',
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusData,
                backgroundColor: ['#f97316', '#667eea', '#10b981', '#ef4444', '#a78bfa']
            }]
        },
        options: { 
            responsive: true,
            maintainAspectRatio: true
        }
    });

    // Stock evolution chart
    const ctxStock = document.getElementById('stockChart').getContext('2d');
    new Chart(ctxStock, {
        type: 'bar',
        data: {
            labels: stockLabels,
            datasets: [{
                label: 'Net change',
                data: stockData,
                backgroundColor: '#f97316'
            }]
        },
        options: { 
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false }
            }
        }
    });
</script>

</body>
</html>
