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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord Admin</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-3px);
        }
        .chart-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .top-products {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .top-products h2 {
            margin-top: 0;
            color: #333;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
        }
        .top-products ul {
            list-style-type: none;
            padding: 0;
        }
        .top-products li {
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
        }
        .orders-status {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <?php
    // Header commun pour l'admin
    $admin_active = 'dashboard';
    $admin_title = '📊 Tableau de bord';
    $admin_actions = [
        ['href' => 'admin_add_article.php', 'label' => '+ Ajouter un article', 'class' => 'btn btn-primary']
    ];
    include 'admin_header.php';
    ?>

    <div class="container">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="success">
                <?= $_SESSION['success_message'] ?>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="error">
                <?= $_SESSION['error_message'] ?>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
        
        <div class="stats">
            <div class="stat-card">
                <h3>Total Articles</h3>
                <div class="number"><?= $stats['articles'] ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Commandes</h3>
                <div class="number"><?= $stats['commandes'] ?></div>
            </div>
            <div class="stat-card">
                <h3>En Attente</h3>
                <div class="number"><?= $stats['commandes_en_attente'] ?></div>
            </div>
            <div class="stat-card">
                <h3>CA ce mois</h3>
                <div class="number"><?= number_format($monthly_ca,2,',',' ') ?> €</div>
            </div>
            <div class="stat-card">
                <h3>Nouveaux clients (mois)</h3>
                <div class="number"><?= $new_clients ?></div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="chart-container" style="grid-column: span 2;">
                <h3>Ventes journalières (mois)</h3>
                <canvas id="salesChart" height="140"></canvas>
            </div>

            <div class="orders-status">
                <h3>Commandes par statut</h3>
                <canvas id="statusChart" height="200"></canvas>
            </div>

            <div class="top-products">
                <h3>Top 10 produits vendus</h3>
                <ul>
                    <?php foreach ($top_products as $p): ?>
                        <li>
                            <span><?= htmlspecialchars($p['titre'] ?? '—') ?></span>
                            <strong><?= (int)$p['sold'] ?> vendus</strong>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="chart-container" style="grid-column: span 2;">
                <h3>Evolution du stock (net par jour)</h3>
                <canvas id="stockChart" height="140"></canvas>
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

            // Helper: read CSS variables from body and convert hex to rgba
            const cs = getComputedStyle(document.body);
            const primary = (cs.getPropertyValue('--primary') || '#667eea').trim();
            const accent = (cs.getPropertyValue('--accent') || '#f97316').trim();
            const success = (cs.getPropertyValue('--success') || '#9ae6b4').trim();
            const danger = (cs.getPropertyValue('--danger') || '#fc8181').trim();
            const muted = (cs.getPropertyValue('--muted') || '#c4b5fd').trim();

            function hexToRgba(hex, alpha) {
                if (!hex) return 'rgba(100,100,100,' + alpha + ')';
                hex = hex.replace('#','');
                if (hex.length === 3) hex = hex.split('').map(h => h+h).join('');
                const bigint = parseInt(hex, 16);
                const r = (bigint >> 16) & 255;
                const g = (bigint >> 8) & 255;
                const b = bigint & 255;
                return `rgba(${r}, ${g}, ${b}, ${alpha})`;
            }

            // Sales chart (uses theme primary color)
            const ctxSales = document.getElementById('salesChart').getContext('2d');
            new Chart(ctxSales, {
                type: 'line',
                data: {
                    labels: salesLabels,
                    datasets: [{
                        label: 'CA (€)',
                        data: salesData,
                        borderColor: primary,
                        backgroundColor: hexToRgba(primary, 0.08),
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: { responsive: true }
            });

            // Status chart (doughnut) — use theme palette
            const ctxStatus = document.getElementById('statusChart').getContext('2d');
            new Chart(ctxStatus, {
                type: 'doughnut',
                data: {
                    labels: statusLabels,
                    datasets: [{
                        data: statusData,
                        backgroundColor: [accent, primary, success, danger, muted]
                    }]
                },
                options: { responsive: true }
            });

            // Stock evolution chart (use accent color)
            const ctxStock = document.getElementById('stockChart').getContext('2d');
            new Chart(ctxStock, {
                type: 'bar',
                data: {
                    labels: stockLabels,
                    datasets: [{
                        label: 'Net change',
                        data: stockData,
                        backgroundColor: accent
                    }]
                },
                options: { responsive: true }
            });
        </script>
        
        <div class="section">
            <div class="section-header">
                <h2>Gestion des Articles</h2>
                <a href="admin_add_article.php" class="btn">+ Ajouter un article</a>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Titre</th>
                        <th>Prix</th>
                        <th>Stock</th>
                        <th>Catégorie</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($articles as $article): ?>
                    <tr>
                        <td>
                                            <?php if (!empty($article['photo'])): ?>
                                                <img src="<?= htmlspecialchars($article['photo'] ?? '') ?>" class="article-img" alt="<?= htmlspecialchars($article['titre'] ?? '') ?>">
                                            <?php else: ?>
                                                <div style="width:60px;height:60px;background:#ddd;border-radius:5px;"></div>
                                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($article['titre'] ?? '') ?></td>
                        <td><?= formatPrice($article['prix']) ?></td>
                        <td><?= $article['stock'] ?></td>
                        <td><?= htmlspecialchars($article['nom_categorie'] ?? '(Non défini)') ?></td>
                        <td>
                            <div class="actions">
                                <a href="admin_edit_article.php?id=<?= $article['id'] ?>" class="btn btn-warning">Modifier</a>
                                <a href="admin_delete_article.php?id=<?= $article['id'] ?>" 
                                   class="btn btn-danger" 
                                   onclick="return confirm('Confirmer la suppression ?')">Supprimer</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>