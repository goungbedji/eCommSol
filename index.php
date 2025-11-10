<?php
require_once 'config.php';

// Récupérer les paramètres de la boutique
$settings = getBoutiqueSettings();

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

// Appliquer promotions
foreach ($articles as &$a) {
    $promo = applyPromotionToArticle($a);
    $a['original_price'] = $promo['original_price'];
    $a['prix'] = $promo['price'];
    $a['promo_label'] = $promo['promo_label'];
    $a['promo_percent'] = $promo['discount_percent'];
}
unset($a);

$page_title = htmlspecialchars($settings['nom_boutique'] ?? 'Boutique');
?>
<?php include 'includes/head.php'; ?>
    <?php include 'includes/header.php'; ?>
    
    <!-- Barre de recherche -->
    <div class="bg-white border-b border-gray-200 py-6">
        <div class="max-w-7xl mx-auto px-6">
            <form action="search.php" method="GET" class="relative" id="searchForm">
                <div class="relative">
                    <input 
                        type="text" 
                        name="q" 
                        class="w-full px-6 py-4 pr-14 border-2 border-gray-300 rounded-2xl focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all text-lg" 
                        placeholder=" Rechercher un produit, une catégorie..." 
                        id="searchInput"
                        autocomplete="off"
                        minlength="2"
                    >
                    <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 w-10 h-10 bg-primary text-white rounded-xl hover:bg-primary/90 transition-all flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>
                </div>
                
                <!-- Suggestions de recherche -->
                <div class="absolute w-full mt-2 bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden z-50 hidden" id="searchSuggestions"></div>
            </form>
        </div>
    </div>
    
    <!-- Filtres de catégories -->
    <div class="bg-gradient-to-br from-gray-50 to-white py-8">
        <div class="max-w-7xl mx-auto px-6">
            <h3 class="text-2xl font-bold text-gray-900 mb-6">Filtrer par catégorie</h3>
            <div class="flex flex-wrap gap-3">
                <a href="index.php" class="px-6 py-3 rounded-xl font-semibold transition-all <?= !$categorie_id ? 'bg-primary text-white shadow-lg' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' ?>">
                    Tous les produits
                </a>
                <?php foreach ($categories as $cat): ?>
                    <a href="?categorie=<?= $cat['id'] ?>" class="px-6 py-3 rounded-xl font-semibold transition-all <?= $categorie_id == $cat['id'] ? 'bg-primary text-white shadow-lg' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' ?>">
                        <?= htmlspecialchars($cat['nom']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Grille de produits -->
    <div class="bg-white py-12">
        <div class="max-w-7xl mx-auto px-6">
            <?php if (count($articles) > 0): ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <?php foreach ($articles as $article): ?>
                        <div class="group bg-white rounded-2xl overflow-hidden border border-gray-200 hover:border-primary hover:shadow-xl transition-all duration-300 cursor-pointer" onclick="window.location='product.php?id=<?= $article['id'] ?>'">
                            <div class="relative overflow-hidden">
                                <?php if (!empty($article['photo'])): ?>
                                    <img src="<?= htmlspecialchars($article['photo'] ?? '') ?>" class="w-full h-56 object-cover group-hover:scale-110 transition-transform duration-500" alt="<?= htmlspecialchars($article['titre'] ?? '') ?>">
                                <?php else: ?>
                                    <div class="w-full h-56 bg-gradient-to-br from-blue-50 via-white to-amber-50 flex items-center justify-center">
                                        <svg class="w-20 h-20 text-primary/30" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($article['stock'] == 0): ?>
                                    <div class="absolute top-3 right-3 bg-red-500 text-white px-3 py-1 rounded-full text-xs font-bold">
                                        Épuisé
                                    </div>
                                <?php elseif ($article['stock'] < 3): ?>
                                    <div class="absolute top-3 right-3 bg-orange-500 text-white px-3 py-1 rounded-full text-xs font-bold">
                                        Stock: <?= $article['stock'] ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($article['promo_label'])): ?>
                                    <div class="absolute top-3 left-3 bg-red-500 text-white px-3 py-1 rounded-full text-xs font-bold">
                                        <?= htmlspecialchars($article['promo_label']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="p-4">
                                <div class="text-xs font-semibold text-primary mb-2"><?= htmlspecialchars($article['nom_categorie'] ?? '(Non défini)') ?></div>
                                <h3 class="text-base font-bold text-gray-900 mb-2 line-clamp-2 group-hover:text-primary transition-colors">
                                    <?= htmlspecialchars($article['titre'] ?? '') ?>
                                </h3>
                                <p class="text-sm text-gray-600 mb-3 line-clamp-2"><?= htmlspecialchars($article['description'] ?? '') ?></p>
                                
                                <div class="flex items-center justify-between">
                                    <div>
                                        <?= formatPriceWithPromo($article) ?>
                                    </div>
                                    <button class="w-9 h-9 bg-primary/10 text-primary rounded-full flex items-center justify-center group-hover:bg-primary group-hover:text-white transition-all" onclick="event.stopPropagation(); addToCart(<?= $article['id'] ?>)">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-20">
                    <div class="inline-block p-8 bg-gray-50 rounded-3xl mb-6">
                        <svg class="w-24 h-24 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                    </div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-3">Aucun produit disponible</h2>
                    <p class="text-lg text-gray-600 mb-6">Revenez plus tard pour découvrir nos nouveaux articles !</p>
                    <a href="index.php" class="inline-block px-8 py-3 bg-primary text-white font-bold rounded-xl hover:bg-primary/90 transition-all">
                        Voir tous les produits
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

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
                searchSuggestions.classList.add('hidden');
                return;
            }
            
            searchSuggestions.innerHTML = '<div class="p-4 text-center text-gray-500"><svg class="animate-spin h-5 w-5 mx-auto text-primary" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></div>';
            searchSuggestions.classList.remove('hidden');
            
            searchTimeout = setTimeout(() => {
                fetch(`api/search.php?q=${encodeURIComponent(query)}&limit=5`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.results.length > 0) {
                            displaySuggestions(data.results);
                        } else {
                            searchSuggestions.innerHTML = '<div class="p-4 text-center text-gray-500">Aucun résultat trouvé</div>';
                        }
                    })
                    .catch(error => {
                        console.error('Erreur recherche:', error);
                        searchSuggestions.innerHTML = '<div class="p-4 text-center text-red-500">Erreur de recherche</div>';
                    });
            }, 300);
        });
        
        function displaySuggestions(results) {
            let html = '<div class="divide-y divide-gray-100">';
            
            results.forEach(item => {
                const imgSrc = item.photo || 'placeholder.jpg';
                html += `
                    <div class="p-3 hover:bg-gray-50 cursor-pointer transition-colors flex items-center gap-3" onclick="window.location='${item.url}'">
                        <img src="${imgSrc}" class="w-12 h-12 object-cover rounded-lg" alt="${item.titre}" onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2250%22 height=%2250%22><rect fill=%22%23e0e0e0%22 width=%2250%22 height=%2250%22/><text x=%2250%%22 y=%2250%%22 text-anchor=%22middle%22 dy=%22.3em%22 font-size=%2220%22>📦</text></svg>'">
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold text-gray-900 truncate">${item.titre}</div>
                            <div class="text-xs text-primary">${item.categorie}</div>
                        </div>
                        <div class="font-bold text-primary whitespace-nowrap">${item.prix}</div>
                    </div>
                `;
            });
            
            html += '</div>';
            searchSuggestions.innerHTML = html;
        }
        
        // Fermer suggestions si clic ailleurs
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchSuggestions.contains(e.target)) {
                searchSuggestions.classList.add('hidden');
            }
        });
        
        // Rouvrir suggestions au focus
        searchInput.addEventListener('focus', function() {
            if (this.value.trim().length >= 2 && searchSuggestions.innerHTML) {
                searchSuggestions.classList.remove('hidden');
            }
        });
    </script>
</body>
</html>