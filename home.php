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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue - Ma Boutique E-Commerce</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/price.css">
    <style>
        /* Hero Section */
        .hero-section {
            height: 100vh;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.9) 0%, rgba(118, 75, 162, 0.9) 100%),
                        url('https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=1920') center/cover;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .hero-content {
            max-width: 900px;
            padding: 0 20px;
            animation: fadeInUp 1s ease;
        }
        
        .hero-title {
            font-size: 4rem;
            font-weight: 900;
            margin-bottom: 20px;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            line-height: 1.2;
        }
        
        .hero-subtitle {
            font-size: 1.5rem;
            margin-bottom: 40px;
            font-weight: 300;
            opacity: 0.95;
        }
        
        .hero-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn-hero {
            padding: 18px 40px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-block;
        }
        
        .btn-primary {
            background: white;
            color: #667eea;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        .btn-primary:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
        }
        
        .btn-secondary {
            background: transparent;
            color: white;
            border: 2px solid white;
        }
        
        .btn-secondary:hover {
            background: white;
            color: #667eea;
        }
        
        .scroll-indicator {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            animation: bounce 2s infinite;
        }
        
        .scroll-indicator a {
            color: white;
            text-decoration: none;
            font-size: 2rem;
        }
        
        /* Features Section */
        .features-section {
            padding: 80px 20px;
            background: white;
        }
        
        .section-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 50px;
        }
        
        .features-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
        }
        
        .feature-card {
            text-align: center;
            padding: 30px;
            border-radius: 15px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .feature-icon {
            font-size: 4rem;
            margin-bottom: 20px;
        }
        
        .feature-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }
        
        .feature-description {
            color: #666;
            line-height: 1.6;
        }
        
        /* Categories Section */
        .categories-section {
            padding: 80px 20px;
            background: #f8f9fa;
        }
        
        .categories-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .category-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 20px;
            border-radius: 15px;
            text-align: center;
            text-decoration: none;
            font-size: 1.5rem;
            font-weight: 600;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .category-card:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }
        
        /* Products Preview Section */
        .products-preview-section {
            padding: 80px 20px;
            background: white;
        }
        
        .products-preview-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
        }
        
        .preview-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
        }
        
        .preview-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        .preview-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .preview-content {
            padding: 20px;
        }
        
        .preview-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }
        
        .preview-price {
            font-size: 1.5rem;
            font-weight: bold;
            color: #667eea;
        }
        
        /* CTA Section */
        .cta-section {
            padding: 100px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-align: center;
        }
        
        .cta-title {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .cta-subtitle {
            font-size: 1.3rem;
            margin-bottom: 40px;
            opacity: 0.95;
        }
        
        /* Footer */
        .footer {
            background: #2c3e50;
            color: white;
            padding: 40px 20px;
            text-align: center;
        }
        
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateX(-50%) translateY(0);
            }
            40% {
                transform: translateX(-50%) translateY(-10px);
            }
            60% {
                transform: translateX(-50%) translateY(-5px);
            }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            .hero-subtitle {
                font-size: 1.2rem;
            }
            .hero-buttons {
                flex-direction: column;
            }
            .btn-hero {
                width: 100%;
            }
            .section-title {
                font-size: 2rem;
            }
            .cta-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <h1 class="hero-title">Vivez le Meilleur du Shopping</h1>
            <p class="hero-subtitle">Découvrez nos produits de qualité à des prix imbattables</p>
            <div class="hero-buttons">
                <a href="index.php" class="btn-hero btn-primary">🛍️ Découvrir la Boutique</a>
                <a href="#nouveautes" class="btn-hero btn-secondary">✨ Voir les Nouveautés</a>
            </div>
        </div>
        <div class="scroll-indicator">
            <a href="#features">↓</a>
        </div>
    </section>
    
    <!-- Features Section -->
    <section class="features-section" id="features">
        <h2 class="section-title">Pourquoi Nous Choisir ?</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">🚚</div>
                <h3 class="feature-title">Livraison Rapide</h3>
                <p class="feature-description">Recevez vos commandes en un temps record partout au Bénin</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">💳</div>
                <h3 class="feature-title">Paiement Sécurisé</h3>
                <p class="feature-description">Vos transactions sont 100% sécurisées et protégées</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">⭐</div>
                <h3 class="feature-title">Qualité Premium</h3>
                <p class="feature-description">Produits authentiques et de haute qualité garantis</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">💬</div>
                <h3 class="feature-title">Support 24/7</h3>
                <p class="feature-description">Une équipe dédiée pour répondre à vos questions</p>
            </div>
        </div>
    </section>
    
    <!-- Categories Section -->
    <section class="categories-section">
        <h2 class="section-title">Explorez Nos Catégories</h2>
        <div class="categories-grid">
            <?php foreach ($categories as $categorie): ?>
                <a href="index.php?categorie=<?= urlencode((string)$categorie) ?>" class="category-card">
                    <?= htmlspecialchars($categorie !== null ? $categorie : '(Non défini)') ?>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
    
    <!-- Products Preview Section -->
    <section class="products-preview-section" id="nouveautes">
        <h2 class="section-title">✨ Nos Dernières Nouveautés</h2>
        <div class="products-preview-grid">
            <?php foreach ($derniers_articles as $article): ?>
                <div class="preview-card" onclick="window.location='product.php?id=<?= $article['id'] ?>'">
                    <?php if (!empty($article['photo'])): ?>
                        <img src="<?= htmlspecialchars($article['photo'] ?? '') ?>" class="preview-image" alt="<?= htmlspecialchars($article['titre'] ?? '') ?>">
                    <?php else: ?>
                        <div class="preview-image" style="background: #e0e0e0; display: flex; align-items: center; justify-content: center; font-size: 3rem;">📦</div>
                    <?php endif; ?>
                    <div class="preview-content">
                        <h3 class="preview-title"><?= htmlspecialchars($article['titre'] ?? '') ?></h3>
                        <p class="preview-price"><?= formatPriceWithPromo($article) ?></p>
                        <?php if ($article['stock'] == 0): ?>
                            <span class="badge-stock" style="background:#e53e3e;color:white;padding:4px 10px;border-radius:12px;font-weight:600;display:inline-block;margin-top:8px;">Rupture de stock</span>
                        <?php elseif ($article['stock'] < 3): ?>
                            <span class="badge-stock" style="background:#f6ad55;color:#222;padding:4px 10px;border-radius:12px;font-weight:600;display:inline-block;margin-top:8px;">Dernières pièces</span>
                        <?php elseif ($article['stock'] < 5): ?>
                            <span class="badge-stock" style="background:#faf089;color:#222;padding:4px 10px;border-radius:12px;font-weight:600;display:inline-block;margin-top:8px;">Stock faible</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div style="text-align: center; margin-top: 40px;">
            <a href="index.php" class="btn-hero btn-primary">Voir Tous les Produits</a>
        </div>
    </section>
    
    <!-- CTA Section -->
    <section class="cta-section">
        <h2 class="cta-title">Prêt à Commencer ?</h2>
        <p class="cta-subtitle">Rejoignez des milliers de clients satisfaits</p>
        <a href="index.php" class="btn-hero btn-primary">🛒 Commencer Mes Achats</a>
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