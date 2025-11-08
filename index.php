<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ma Boutique E-Commerce</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/mobile.css">
</head>
<body>
<?php
// Récupérer les paramètres de la boutique
$settings = getBoutiqueSettings();

// Récupérer tous les articles disponibles
$stmt = $pdo->query("SELECT * FROM articles WHERE stock > 0 ORDER BY date_creation DESC");
$articles = $stmt->fetchAll();

// Récupérer les catégories
$stmt = $pdo->query("SELECT id, nom FROM categories ORDER BY nom");
$categories = $stmt->fetchAll();

// Filtrer par catégorie si demandé
$categorie_id = isset($_GET['categorie']) ? (int)$_GET['categorie'] : 0;
if ($categorie_id) {
    $stmt = $pdo->prepare("SELECT a.*, c.nom as nom_categorie 
                          FROM articles a 
                          LEFT JOIN categories c ON a.categorie_id = c.id 
                          WHERE a.categorie_id = ? AND a.stock > 0 
                          ORDER BY a.date_creation DESC");
    $stmt->execute([$categorie_id]);
} else {
    $stmt = $pdo->query("SELECT a.*, c.nom as nom_categorie 
                         FROM articles a 
                         LEFT JOIN categories c ON a.categorie_id = c.id 
                         WHERE a.stock > 0 
                         ORDER BY a.date_creation DESC");
}
$articles = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($settings['nom_boutique'] ?? '') ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/search.css">
    <link rel="stylesheet" href="css/price.css">
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div style="display: flex; align-items: center; gap: 20px;">
                <a href="home.php" style="color: white; text-decoration: none; font-size: 16px;">← Accueil</a>
                <h1>🛍️ <?= htmlspecialchars($settings['nom_boutique'] ?? '') ?></h1>
            </div>
            <a href="cart.php" class="cart-btn">
                🛒 Panier
                <span class="cart-count" id="cartCount">0</span>
            </a>
        </div>
    </div>
    
    <!-- Barre de recherche -->
    <div class="search-container">
        <form action="search.php" method="GET" class="search-box" id="searchForm">
            <input 
                type="text" 
                name="q" 
                class="search-input" 
                placeholder="🔍 Rechercher un produit, une catégorie..." 
                id="searchInput"
                autocomplete="off"
                minlength="2"
            >
            <button type="submit" class="search-button">🔍</button>
            
            <!-- Suggestions de recherche -->
            <div class="search-suggestions" id="searchSuggestions"></div>
        </form>
    </div>
    
    <div class="container">
        <div class="filters">
            <h3>Filtrer par catégorie</h3>
            <div class="filter-buttons">
                <a href="index.php" class="filter-btn <?= !$categorie_filtre ? 'active' : '' ?>">Tous les produits</a>
                <?php foreach ($categories as $cat): ?>
                    <a href="?categorie=<?= $cat['id'] ?>" class="filter-btn <?= $categorie_id == $cat['id'] ? 'active' : '' ?>">
                        <?= htmlspecialchars($cat['nom']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        
        <?php if (count($articles) > 0): ?>
            <div class="products-grid">
                <?php foreach ($articles as $article): ?>
                    <div class="product-card" onclick="window.location='product.php?id=<?= $article['id'] ?>'">
                        <?php if (!empty($article['photo'])): ?>
                            <img src="<?= htmlspecialchars($article['photo'] ?? '') ?>" class="product-image" alt="<?= htmlspecialchars($article['titre'] ?? '') ?>">
                        <?php else: ?>
                            <div class="product-image" style="display: flex; align-items: center; justify-content: center; background: #e0e0e0; color: #999; font-size: 40px;">📦</div>
                        <?php endif; ?>
                        
                        <div class="product-info">
                            <div class="product-category"><?= htmlspecialchars($article['nom_categorie'] ?? '(Non défini)') ?></div>
                            <h3 class="product-title"><?= htmlspecialchars($article['titre'] ?? '') ?></h3>
                            <p class="product-description"><?= htmlspecialchars($article['description'] ?? '') ?></p>
                            
                            <div class="product-footer">
                                <div>
                                    <?= formatPriceWithPromo($article) ?>
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
            <div class="empty-state">
                <h2>Aucun produit disponible</h2>
                <p>Revenez plus tard pour découvrir nos nouveaux articles !</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer Dynamique -->
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
        
        // Système de recherche avec suggestions
        const searchInput = document.getElementById('searchInput');
        const searchSuggestions = document.getElementById('searchSuggestions');
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            
            clearTimeout(searchTimeout);
            
            if (query.length < 2) {
                searchSuggestions.classList.remove('active');
                return;
            }
            
            searchSuggestions.innerHTML = '<div class="loading-suggestions">🔍 Recherche en cours...</div>';
            searchSuggestions.classList.add('active');
            
            searchTimeout = setTimeout(() => {
                fetch(`api/search.php?q=${encodeURIComponent(query)}&limit=5`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.results.length > 0) {
                            displaySuggestions(data.results);
                        } else {
                            searchSuggestions.innerHTML = '<div class="no-suggestions">Aucun résultat trouvé</div>';
                        }
                    })
                    .catch(error => {
                        console.error('Erreur recherche:', error);
                        searchSuggestions.innerHTML = '<div class="no-suggestions">Erreur de recherche</div>';
                    });
            }, 300);
        });
        
        function displaySuggestions(results) {
            let html = '';
            
            results.forEach(item => {
                const imgSrc = item.photo || 'placeholder.jpg';
                html += `
                    <div class="suggestion-item" onclick="window.location='${item.url}'">
                        <img src="${imgSrc}" class="suggestion-image" alt="${item.titre}" onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2250%22 height=%2250%22><rect fill=%22%23e0e0e0%22 width=%2250%22 height=%2250%22/><text x=%2250%%22 y=%2250%%22 text-anchor=%22middle%22 dy=%22.3em%22 font-size=%2220%22>📦</text></svg>'">
                        <div class="suggestion-info">
                            <div class="suggestion-title">${item.titre}</div>
                            <div class="suggestion-category">${item.categorie}</div>
                        </div>
                        <div class="suggestion-price">${item.prix}</div>
                    </div>
                `;
            });
            
            searchSuggestions.innerHTML = html;
        }
        
        // Fermer suggestions si clic ailleurs
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchSuggestions.contains(e.target)) {
                searchSuggestions.classList.remove('active');
            }
        });
        
        // Rouvrir suggestions au focus
        searchInput.addEventListener('focus', function() {
            if (this.value.trim().length >= 2) {
                searchSuggestions.classList.add('active');
            }
        });
    </script>
</body>
</html>