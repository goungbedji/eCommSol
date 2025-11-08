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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Commandes</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php
    $admin_active = 'commandes';
    $admin_title = '📦 Gestion des commandes';
    $admin_actions = [];
    include 'admin_header.php';
    ?>

    <div class="container">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="success" style="margin-bottom: 20px;">
                <?= $_SESSION['success_message'] ?>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="error" style="margin-bottom: 20px;">
                <?= $_SESSION['error_message'] ?>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
        
        <div class="section">
            <h2 style="margin-bottom: 20px;">Toutes les Commandes</h2>
            
            <?php if (count($commandes) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Client</th>
                        <th>Email</th>
                        <th>WhatsApp</th>
                        <th>Ville</th>
                        <th>Articles</th>
                        <th>Total</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($commandes as $commande): ?>
                    <tr>
                        <td>#<?= $commande['id'] ?></td>
                        <td><?= htmlspecialchars($commande['nom_client']) ?></td>
                        <td><?= htmlspecialchars($commande['email']) ?></td>
                        <td><?= htmlspecialchars($commande['whatsapp']) ?></td>
                        <td><?= htmlspecialchars($commande['ville']) ?></td>
                        <td><?= $commande['nb_articles'] ?> article(s)</td>
                        <td><?= formatPrice($commande['total']) ?></td>
                        <td>
                            <span class="status status-<?= $commande['statut'] ?>">
                                <?= ucfirst(str_replace('_', ' ', $commande['statut'])) ?>
                            </span>
                        </td>
                        <td><?= date('d/m/Y H:i', strtotime($commande['date_commande'])) ?></td>
                        <td>
                            <button class="btn" onclick="viewCommande(<?= $commande['id'] ?>)">Voir détails</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">
                <h2>Aucune commande pour le moment</h2>
                <p>Les commandes de vos clients apparaîtront ici.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Modal pour les détails -->
    <div id="commandeModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Détails de la commande</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div id="modalBody"></div>
        </div>
    </div>

    <script>
        function viewCommande(id) {
            fetch('admin_commande_details.php?id=' + id)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('modalBody').innerHTML = html;
                    document.getElementById('commandeModal').style.display = 'block';
                });
        }
        
        function closeModal() {
            document.getElementById('commandeModal').style.display = 'none';
        }
        
        window.onclick = function(event) {
            const modal = document.getElementById('commandeModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
    
</body>
</html>