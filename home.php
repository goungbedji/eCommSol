<?php
require_once 'config.php';

// Récupérer les paramètres de la boutique
$settings = getBoutiqueSettings();

// Récupérer quelques statistiques pour la page
$total_articles = $pdo->query("SELECT COUNT(*) FROM articles WHERE stock > 0")->fetchColumn();
$categories = $pdo->query("SELECT DISTINCT categorie FROM articles WHERE stock > 0 LIMIT 4")->fetchAll(PDO::FETCH_COLUMN);

// Récupérer les derniers articles pour la section "Nouveautés"
$stmt = $pdo->query("SELECT id, titre, description, prix, photo, stock, categorie, IFNULL(categorie_id, NULL) as categorie_id FROM articles WHERE stock > 0 ORDER BY date_creation DESC LIMIT 4");
$derniers_articles = $stmt->fetchAll();

// Appliquer promotions
foreach ($derniers_articles as &$a) {
    $promo = applyPromotionToArticle($a);
    $a['original_price'] = $promo['original_price'];
    $a['prix'] = $promo['price'];
    $a['promo_label'] = $promo['promo_label'];
    $a['promo_percent'] = $promo['discount_percent'];
}
unset($a);

$page_title = "Bienvenue - Space E-Commerce";
?>
<?php include 'includes/head.php'; ?>
    <!-- Hero Section -->
    <section class="relative h-screen flex items-center justify-center text-center text-white overflow-hidden bg-gradient-to-br from-primary via-purple-600 to-secondary">
        <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=1920')] bg-cover bg-center opacity-20"></div>
        <div class="relative z-10 max-w-4xl px-6 animate-fade-in-up">
            <h1 class="text-5xl md:text-7xl font-black mb-6 drop-shadow-2xl leading-tight">
                Vivez le Meilleur du Shopping
            </h1>
            <p class="text-xl md:text-2xl mb-10 font-light opacity-95">
                Découvrez nos produits de qualité à des prix imbattables
            </p>
            <div class="flex flex-col sm:flex-row gap-5 justify-center">
                <a href="index.php" class="inline-block px-10 py-4 text-lg font-semibold bg-white text-primary rounded-full shadow-2xl hover:shadow-3xl hover:-translate-y-1 transition-all duration-300">
                    🛍️ Découvrir la Boutique
                </a>
                <a href="#nouveautes" class="inline-block px-10 py-4 text-lg font-semibold bg-transparent text-white border-2 border-white rounded-full hover:bg-white hover:text-primary transition-all duration-300">
                    ✨ Voir les Nouveautés
                </a>
            </div>
        </div>
        <div class="absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce">
            <a href="#features" class="text-white text-4xl">↓</a>
        </div>
    </section>
    
    <!-- Features Section -->
    <section class="py-20 bg-white" id="features">
        <div class="max-w-7xl mx-auto px-6">
            <h2 class="text-4xl md:text-5xl font-bold text-center text-gray-800 mb-16">
                Pourquoi Nous Choisir ?
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">
                <div class="text-center p-8 rounded-2xl hover:-translate-y-3 hover:shadow-2xl transition-all duration-300">
                    <div class="text-6xl mb-6">🚚</div>
                    <h3 class="text-2xl font-semibold text-gray-800 mb-3">Livraison Rapide</h3>
                    <p class="text-gray-600 leading-relaxed">Recevez vos commandes en un temps record partout au Bénin</p>
                </div>
                <div class="text-center p-8 rounded-2xl hover:-translate-y-3 hover:shadow-2xl transition-all duration-300">
                    <div class="text-6xl mb-6">💳</div>
                    <h3 class="text-2xl font-semibold text-gray-800 mb-3">Paiement Sécurisé</h3>
                    <p class="text-gray-600 leading-relaxed">Vos transactions sont 100% sécurisées et protégées</p>
                </div>
                <div class="text-center p-8 rounded-2xl hover:-translate-y-3 hover:shadow-2xl transition-all duration-300">
                    <div class="text-6xl mb-6">⭐</div>
                    <h3 class="text-2xl font-semibold text-gray-800 mb-3">Qualité Premium</h3>
                    <p class="text-gray-600 leading-relaxed">Produits authentiques et de haute qualité garantis</p>
                </div>
                <div class="text-center p-8 rounded-2xl hover:-translate-y-3 hover:shadow-2xl transition-all duration-300">
                    <div class="text-6xl mb-6">💬</div>
                    <h3 class="text-2xl font-semibold text-gray-800 mb-3">Support 24/7</h3>
                    <p class="text-gray-600 leading-relaxed">Une équipe dédiée pour répondre à vos questions</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Categories Section -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-6">
            <h2 class="text-4xl md:text-5xl font-bold text-center text-gray-800 mb-16">
                Explorez Nos Catégories
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php foreach ($categories as $categorie): ?>
                    <a href="index.php?categorie=<?= urlencode((string)$categorie) ?>" 
                       class="bg-gradient-to-br from-primary to-secondary text-white py-12 px-6 rounded-2xl text-center text-2xl font-semibold hover:scale-105 hover:shadow-2xl transition-all duration-300">
                        <?= htmlspecialchars($categorie !== null ? $categorie : '(Non défini)') ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    
    <!-- Products Preview Section -->
    <section class="py-20 bg-white" id="nouveautes">
        <div class="max-w-7xl mx-auto px-6">
            <h2 class="text-4xl md:text-5xl font-bold text-center text-gray-800 mb-16">
                ✨ Nos Dernières Nouveautés
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                <?php foreach ($derniers_articles as $article): ?>
                    <div class="bg-white rounded-2xl overflow-hidden shadow-lg hover:-translate-y-3 hover:shadow-2xl transition-all duration-300 cursor-pointer" 
                         onclick="window.location='product.php?id=<?= $article['id'] ?>'">
                        <?php if (!empty($article['photo'])): ?>
                            <img src="<?= htmlspecialchars($article['photo'] ?? '') ?>" 
                                 class="w-full h-56 object-cover" 
                                 alt="<?= htmlspecialchars($article['titre'] ?? '') ?>">
                        <?php else: ?>
                            <div class="w-full h-56 bg-gray-200 flex items-center justify-center text-6xl">📦</div>
                        <?php endif; ?>
                        <div class="p-6">
                            <h3 class="text-xl font-semibold text-gray-800 mb-3 line-clamp-2">
                                <?= htmlspecialchars($article['titre'] ?? '') ?>
                            </h3>
                            <p class="text-2xl font-bold text-primary mb-3">
                                <?= formatPriceWithPromo($article) ?>
                            </p>
                            <?php if ($article['stock'] == 0): ?>
                                <span class="inline-block bg-red-600 text-white px-3 py-1 rounded-full text-sm font-semibold">
                                    Rupture de stock
                                </span>
                            <?php elseif ($article['stock'] < 3): ?>
                                <span class="inline-block bg-orange-400 text-gray-900 px-3 py-1 rounded-full text-sm font-semibold">
                                    Dernières pièces
                                </span>
                            <?php elseif ($article['stock'] < 5): ?>
                                <span class="inline-block bg-yellow-300 text-gray-900 px-3 py-1 rounded-full text-sm font-semibold">
                                    Stock faible
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-12">
                <a href="index.php" class="inline-block px-10 py-4 text-lg font-semibold bg-white text-primary border-2 border-primary rounded-full shadow-lg hover:bg-primary hover:text-white hover:-translate-y-1 transition-all duration-300">
                    Voir Tous les Produits
                </a>
            </div>
        </div>
    </section>
    
    <!-- CTA Section -->
    <section class="py-24 bg-gradient-to-br from-primary via-purple-600 to-secondary text-white text-center">
        <div class="max-w-4xl mx-auto px-6">
            <h2 class="text-4xl md:text-6xl font-bold mb-6">
                Prêt à Commencer ?
            </h2>
            <p class="text-xl md:text-2xl mb-10 opacity-95">
                Rejoignez des milliers de clients satisfaits
            </p>
            <a href="index.php" class="inline-block px-10 py-4 text-lg font-semibold bg-white text-primary rounded-full shadow-2xl hover:shadow-3xl hover:-translate-y-1 transition-all duration-300">
                🛒 Commencer Mes Achats
            </a>
        </div>
    </section>
    
    <!-- Footer 
    <footer class="footer">
        <div class="footer-content">
            <p>&copy; <?= date('Y') ?> Ma Boutique E-Commerce. Tous droits réservés.</p>
            <p style="margin-top: 10px; opacity: 0.8;">
                <strong><?= $total_articles ?></strong> produits disponibles | 
                Livraison partout au Bénin
            </p>
        </div>
    </footer>-->
    <?php include 'includes/footer.php'; ?>
</body>
</html>