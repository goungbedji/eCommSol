<?php
require_once 'config.php';
requireAdmin();

// Vérifier que l'ID est fourni
if (!isset($_GET['id'])) {
    echo "<p style='color: red; padding: 20px;'>❌ Aucune commande spécifiée</p>";
    exit;
}

$id = intval($_GET['id']);

// Récupérer la commande
$stmt = $pdo->prepare("SELECT * FROM commandes WHERE id = ?");
$stmt->execute([$id]);
$commande = $stmt->fetch();

if (!$commande) {
    echo "<p style='color: red; padding: 20px;'>❌ Commande introuvable</p>";
    exit;
}

// Récupérer les articles de la commande
$stmt = $pdo->prepare("
    SELECT dc.*, a.titre, a.photo 
    FROM details_commande dc
    LEFT JOIN articles a ON dc.article_id = a.id
    WHERE dc.commande_id = ?
");
$stmt->execute([$id]);
$articles = $stmt->fetchAll();
?>

<style>
    .detail-row {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #eee;
    }
    .detail-label {
        font-weight: 600;
        color: #666;
        font-size: 14px;
    }
    .detail-value {
        color: #333;
        font-size: 14px;
        text-align: right;
    }
    .section-title {
        font-size: 18px;
        font-weight: bold;
        color: #333;
        margin-top: 25px;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #667eea;
    }
    .article-item {
        display: flex;
        gap: 15px;
        padding: 15px;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        margin-bottom: 10px;
        background: #f8f9fa;
        transition: transform 0.2s;
    }
    .article-item:hover {
        transform: translateX(5px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .article-item img {
        width: 70px;
        height: 70px;
        object-fit: cover;
        border-radius: 8px;
        border: 2px solid #e0e0e0;
    }
    .article-placeholder {
        width: 70px;
        height: 70px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        color: white;
    }
    .article-info {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .article-title {
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
        font-size: 16px;
    }
    .article-details {
        color: #666;
        font-size: 14px;
        margin-bottom: 5px;
    }
    .article-price {
        color: #667eea;
        font-weight: 700;
        font-size: 16px;
    }
    .summary-total {
        display: flex;
        justify-content: space-between;
        margin-top: 25px;
        padding: 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 10px;
        font-size: 22px;
        font-weight: bold;
        color: white;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }
    .status-form {
        margin-top: 30px;
        padding: 25px;
        background: #f8f9fa;
        border-radius: 10px;
        border: 2px solid #e0e0e0;
    }
    .status-form label {
        font-weight: 600;
        color: #333;
        display: block;
        margin-bottom: 12px;
        font-size: 15px;
    }
    .status-form select {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #ddd;
        border-radius: 8px;
        font-size: 15px;
        margin-bottom: 15px;
        background: white;
        cursor: pointer;
        transition: border-color 0.3s;
    }
    .status-form select:focus {
        outline: none;
        border-color: #667eea;
    }
    .status-form button {
        width: 100%;
        padding: 14px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .status-form button:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }
    .status-badge {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
    }
    .status-en_attente {
        background: #fff3cd;
        color: #856404;
    }
    .status-confirmee {
        background: #d1ecf1;
        color: #0c5460;
    }
    .status-expediee {
        background: #d4edda;
        color: #155724;
    }
    .status-livree {
        background: #d4edda;
        color: #155724;
    }
    .status-annulee {
        background: #f8d7da;
        color: #721c24;
    }
    .info-section {
        background: white;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        border: 1px solid #e0e0e0;
    }
    .empty-articles {
        text-align: center;
        padding: 40px;
        color: #999;
        font-size: 16px;
    }
    .promo-price {
        color: #e44d26;
        font-weight: bold;
    }
    .promo-badge {
        background-color: #e44d26;
        color: white;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 0.8em;
        font-weight: bold;
        display: inline-block;
        margin-left: 5px;
    }
</style>

<div class="info-section">
    <h3 style="margin-bottom: 15px; color: #667eea; font-size: 20px;">📦 Informations de la commande</h3>
    
    <div class="detail-row">
        <span class="detail-label">N° Commande:</span>
        <span class="detail-value" style="font-weight: bold; color: #667eea;">#<?= $commande['id'] ?></span>
    </div>

    <div class="detail-row">
        <span class="detail-label">Date de commande:</span>
        <span class="detail-value"><?= date('d/m/Y à H:i', strtotime($commande['date_commande'])) ?></span>
    </div>

    <div class="detail-row">
        <span class="detail-label">Statut actuel:</span>
        <span class="detail-value">
            <span class="status-badge status-<?= $commande['statut'] ?>">
                <?= ucfirst(str_replace('_', ' ', $commande['statut'])) ?>
            </span>
        </span>
    </div>
</div>

<div class="info-section">
    <h3 style="margin-bottom: 15px; color: #667eea; font-size: 20px;">👤 Informations du client</h3>
    
    <div class="detail-row">
        <span class="detail-label">Nom complet:</span>
        <span class="detail-value"><?= htmlspecialchars($commande['nom_client']) ?></span>
    </div>

    <div class="detail-row">
        <span class="detail-label">Email:</span>
        <span class="detail-value">
            <a href="mailto:<?= htmlspecialchars($commande['email'] ?? '') ?>" style="color: #667eea; text-decoration: none;">
                <?= htmlspecialchars($commande['email'] ?? '') ?>
            </a>
        </span>
    </div>

    <div class="detail-row">
        <span class="detail-label">WhatsApp:</span>
        <span class="detail-value">
            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $commande['whatsapp'] ?? '') ?>" target="_blank" style="color: #25D366; text-decoration: none;">
                <?= htmlspecialchars($commande['whatsapp'] ?? '') ?> 📱
            </a>
        </span>
    </div>

    <div class="detail-row">
        <span class="detail-label">Ville:</span>
    <span class="detail-value"><?= htmlspecialchars($commande['ville'] ?? '') ?></span>
    </div>

    <div class="detail-row" style="border-bottom: none;">
        <span class="detail-label">Adresse de livraison:</span>
        <span class="detail-value" style="max-width: 60%; text-align: right;">
            <?= nl2br(htmlspecialchars($commande['adresse'] ?? '')) ?>
        </span>
    </div>
</div>

<h3 class="section-title">🛍️ Articles commandés (<?= count($articles) ?>)</h3>

<?php if (count($articles) > 0): ?>
    <?php foreach ($articles as $article): ?>
    <div class="article-item">
        <?php if (!empty($article['photo'])): ?>
            <img src="<?= htmlspecialchars($article['photo'] ?? '') ?>" alt="<?= htmlspecialchars($article['titre'] ?? '') ?>">
        <?php else: ?>
            <div class="article-placeholder">📦</div>
        <?php endif; ?>
        
        <div class="article-info">
            <div class="article-title">
                <?= htmlspecialchars($article['titre'] ?? '') ?>
            </div>
            <div class="article-details">
                <?php
                // Vérifier si l'article était en promotion au moment de la commande
                $article_actuel = getArticle($article['article_id']);
                if ($article_actuel && $article['prix_unitaire'] < $article_actuel['prix']) : ?>
                    Quantité: <strong><?= $article['quantite'] ?></strong> × 
                    <span class="promo-price"><?= formatPrice($article['prix_unitaire']) ?></span>
                    <span class="original-price" style="text-decoration: line-through; color: #999; font-size: 0.9em;">
                        <?= formatPrice($article_actuel['prix']) ?>
                    </span>
                    <span class="promo-badge">PROMO</span>
                <?php else: ?>
                    Quantité: <strong><?= $article['quantite'] ?></strong> × <?= formatPrice($article['prix_unitaire']) ?>
                <?php endif; ?>
            </div>
            <div class="article-price">
                Sous-total: <?= formatPrice($article['prix_unitaire'] * $article['quantite']) ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="empty-articles">
        <p>❌ Aucun article trouvé pour cette commande</p>
    </div>
<?php endif; ?>

<div class="summary-total">
    <span>💰 TOTAL DE LA COMMANDE</span>
    <span><?= formatPrice($commande['total']) ?></span>
</div>

<div class="status-form">
    <form method="POST" action="admin_commandes.php">
        <input type="hidden" name="commande_id" value="<?= $commande['id'] ?>">
        
        <label>🔄 Modifier le statut de la commande:</label>
        <select name="statut">
            <option value="en_attente" <?= $commande['statut'] == 'en_attente' ? 'selected' : '' ?>>⏳ En attente</option>
            <option value="confirmee" <?= $commande['statut'] == 'confirmee' ? 'selected' : '' ?>>✅ Confirmée</option>
            <option value="expediee" <?= $commande['statut'] == 'expediee' ? 'selected' : '' ?>>🚚 Expédiée</option>
            <option value="livree" <?= $commande['statut'] == 'livree' ? 'selected' : '' ?>>🎉 Livrée</option>
            <option value="annulee" <?= $commande['statut'] == 'annulee' ? 'selected' : '' ?>>❌ Annulée</option>
        </select>
        
        <button type="submit" name="update_status">
            💾 Enregistrer le nouveau statut
        </button>
    </form>
</div>

<script>
// Fermer la modal après mise à jour du statut
window.onload = function() {
    const form = document.querySelector('form');
    form.addEventListener('submit', function() {
        setTimeout(() => {
            if (window.parent && window.parent.closeModal) {
                window.parent.closeModal();
            }
        }, 500);
    });
}
</script>