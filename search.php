<?php
require_once 'config.php';

// Récupérer les paramètres de la boutique
$settings = getBoutiqueSettings();

// Récupérer le terme de recherche
$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$articles = [];
$count = 0;

if (!empty($query) && strlen($query) >= 2) {
    $searchTerm = '%' . $query . '%';
    
    $stmt = $pdo->prepare("
        SELECT * FROM articles 
        WHERE stock > 0 
        AND (
            titre LIKE ? 
            OR description LIKE ? 
            OR categorie LIKE ?
        )
        ORDER BY 
            CASE 
                WHEN titre LIKE ? THEN 1
                WHEN categorie LIKE ? THEN 2
                ELSE 3
            END,
            date_creation DESC
    ");
    
    $stmt->execute([
        $searchTerm, 
        $searchTerm, 
        $searchTerm,
        $searchTerm,
        $searchTerm
    ]);
    
    $articles = $stmt->fetchAll();
    $count = count($articles);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche : <?= htmlspecialchars($query !== null ? $query : '') ?> - <?= htmlspecialchars(isset($settings['nom_boutique']) && $settings['nom_boutique'] !== null ? $settings['nom_boutique'] : '') ?></title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .search-header {
            background: white;
            padding: 30px 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .search-info {
            max-width: 1200px;
            margin: 0 auto;
        }
        .search-info h2 {
            color: #333;
            margin-bottom: 10px;
        }
        .search-query {
            color: #667eea;
            font-weight: bold;
        }
        .search-count {
            color: #666;
            font-size: 14px;
        }
        .no-results {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
            max-width: 600px;
            margin: 0 auto;
        }
        .no-results h2 {
            color: #666;
            margin-bottom: 20px;
        }
        .suggestions {
            margin-top: 20px;
            color: #666;
        }
        .suggestions ul {
            list-style: none;
            padding: 0;
        }
        .suggestions li {
            margin: 10px 0;
        }
        .back-search {
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div style="display: flex; align-items: center; gap: 20px;">
                <a href="index.php" style="color: white; text-decoration: none; font-size: 16px;">← Boutique</a>
                <h1>🔍 Recherche</h1>
            </div>
            <a href="cart.php" class="cart-btn">
                🛒 Panier
                <span class="cart-count" id="cartCount">0</span>
            </a>
        </div>
    </div>
    
    <div class="search-header">
        <div class="search-info">
            <h2>Résultats pour : <span class="search-query">"<?= htmlspecialchars($query !== null ? $query : '') ?>"</span></h2>
            <p class="search-count"><?= $count ?> produit<?= $count > 1 ? 's' : '' ?> trouvé<?= $count > 1 ? 's' : '' ?></p>
        </div>
    </div>
    
    <div class="container">
        <?php if ($count > 0): ?>
            <div class="products-grid">
                <?php foreach ($articles as $article): ?>
                    <div class="product-card" onclick="window.location='product.php?id=<?= $article['id'] ?>'">
                        <?php if ($article['photo']): ?>
                            <img src="<?= htmlspecialchars($article['photo']) ?>" class="product-image" alt="<?= htmlspecialchars($article['titre']) ?>">
                        <?php else: ?>
                            <div class="product-image" style="display: flex; align-items: center; justify-content: center; background: #e0e0e0; color: #999; font-size: 40px;">📦</div>
                        <?php endif; ?>
                        
                        <div class="product-info">
                            <div class="product-category"><?= htmlspecialchars(isset($article['categorie']) && $article['categorie'] !== null ? $article['categorie'] : '(Non défini)') ?></div>
                            <h3 class="product-title"><?= htmlspecialchars(isset($article['titre']) && $article['titre'] !== null ? $article['titre'] : '') ?></h3>
                            <p class="product-description"><?= htmlspecialchars(isset($article['description']) && $article['description'] !== null ? $article['description'] : '') ?></p>
                            
                            <div class="product-footer">
                                <div>
                                    <div class="product-price"><?= formatPrice($article['prix']) ?></div>
                                    <span class="stock-badge">Stock: <?= $article['stock'] ?></span>
                                </div>
                                <button class="add-to-cart" onclick="event.stopPropagation(); addToCart(<?= $article['id'] ?>)">
                                    Ajouter
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-results">
                <h2>😕 Aucun résultat trouvé</h2>
                <p>Nous n'avons trouvé aucun produit correspondant à <strong>"<?= htmlspecialchars($query !== null ? $query : '') ?>"</strong></p>
                
                <div class="suggestions">
                    <p><strong>Suggestions :</strong></p>
                    <ul>
                        <li>✓ Vérifiez l'orthographe</li>
                        <li>✓ Utilisez des termes plus généraux</li>
                        <li>✓ Essayez des mots-clés différents</li>
                    </ul>
                </div>
                
                <div class="back-search">
                    <a href="index.php" class="btn">← Retour à la boutique</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Footer -->
    <style>
        .site-footer {
            background: #2c3e50;
            color: white;
            padding: 40px 20px 20px;
            margin-top: 50px;
            text-align: center;
        }
        .footer-contact {
            margin: 20px 0;
            font-size: 14px;
        }
        .footer-contact a {
            color: #667eea;
            text-decoration: none;
            margin: 0 15px;
        }
        .footer-contact a:hover {
            color: white;
        }
    </style>
    <footer class="site-footer">
        <p><strong><?= htmlspecialchars($settings['nom_boutique'] ?? '') ?></strong></p>
        <?php if (!empty($settings['slogan'])): ?>
        <p style="font-style: italic; opacity: 0.8; margin-top: 5px;"><?= htmlspecialchars($settings['slogan'] ?? '') ?></p>
        <?php endif; ?>
        <div class="footer-contact">
            📧 <a href="mailto:<?= htmlspecialchars($settings['email_boutique'] ?? '') ?>"><?= htmlspecialchars($settings['email_boutique'] ?? '') ?></a>
            |
            📱 <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $settings['whatsapp_boutique'] ?? '') ?>" target="_blank"><?= htmlspecialchars($settings['whatsapp_boutique'] ?? '') ?></a>
        </div>
        <p style="opacity: 0.6; margin-top: 15px;">&copy; <?= date('Y') ?> <?= htmlspecialchars($settings['nom_boutique'] ?? '') ?>. Tous droits réservés.</p>
    </footer>

    <script>
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        
        function updateCartCount() {
            const count = cart.reduce((total, item) => total + item.quantity, 0);
            document.getElementById('cartCount').textContent = count;
        }
        
        function addToCart(articleId) {
            const existingItem = cart.find(item => item.id === articleId);
            
            if (existingItem) {
                existingItem.quantity++;
            } else {
                cart.push({ id: articleId, quantity: 1 });
            }
            
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartCount();
            
            alert('Article ajouté au panier !');
        }
        
        updateCartCount();
    </script>
</body>
</html>