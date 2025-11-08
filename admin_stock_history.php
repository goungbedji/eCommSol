<?php
require_once 'config.php';
requireAdmin();

// Récupérer les mouvements de stock
$stmt = $pdo->query("SELECT h.*, a.titre FROM stock_history h LEFT JOIN articles a ON h.article_id = a.id ORDER BY h.date DESC LIMIT 100");
$mouvements = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historique des mouvements de stock</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .stock-table { width:100%; border-collapse:collapse; margin-top:30px; }
        .stock-table th, .stock-table td { padding:10px; border-bottom:1px solid #eee; text-align:left; }
        .stock-table th { background:#f6f6f6; }
        .badge-entree { background:#38a169; color:white; padding:3px 10px; border-radius:10px; font-size:13px; }
        .badge-sortie { background:#e53e3e; color:white; padding:3px 10px; border-radius:10px; font-size:13px; }
        .badge-cmd { background:#3182ce; color:white; padding:3px 10px; border-radius:10px; font-size:13px; }
        .badge-manu { background:#f6ad55; color:#222; padding:3px 10px; border-radius:10px; font-size:13px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>📦 Historique des mouvements de stock</h1>
            <a href="admin_dashboard.php" class="back-btn">Retour</a>
        </div>
    </div>
    <div class="container">
        <table class="stock-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Article</th>
                    <th>Type</th>
                    <th>Quantité</th>
                    <th>Commentaire</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($mouvements as $m): ?>
                <tr>
                    <td><?= date('d/m/Y H:i', strtotime($m['date'])) ?></td>
                    <td><?= htmlspecialchars($m['titre']) ?></td>
                    <td>
                        <?php if ($m['type'] == 'entree'): ?>
                            <span class="badge-entree">Entrée</span>
                        <?php elseif ($m['type'] == 'sortie'): ?>
                            <span class="badge-sortie">Sortie</span>
                        <?php elseif ($m['type'] == 'commande'): ?>
                            <span class="badge-cmd">Commande</span>
                        <?php else: ?>
                            <span class="badge-manu">Manuel</span>
                        <?php endif; ?>
                    </td>
                    <td><?= ($m['type'] == 'sortie' || $m['type'] == 'commande') ? '-' : '+' ?><?= $m['quantite'] ?></td>
                    <td><?= htmlspecialchars($m['commentaire']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>