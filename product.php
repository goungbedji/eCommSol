<?php
require_once 'config.php';

// Récupérer les paramètres de la boutique
$settings = getBoutiqueSettings();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails du produit - <?= htmlspecialchars($settings['nom_boutique'] ?? '') ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/mobile.css">
    <link rel="stylesheet" href="css/product-mobile.css">
</head>
<body><?php

$id = intval($_GET['id']);
$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
$stmt->execute([$id]);
$article = $stmt->fetch();

if (!$article) {
    header('Location: index.php');
    exit;
}

// Récupérer toutes les photos de l'article
$stmt = $pdo->prepare("SELECT * FROM article_photos WHERE article_id = ? ORDER BY is_main DESC, ordre ASC");
$stmt->execute([$id]);
$photos = $stmt->fetchAll();

// Si pas de photos dans la galerie, utiliser la photo principale de l'article
if (empty($photos) && !empty($article['photo'])) {
    $photos = [['photo_url' => $article['photo'], 'is_main' => 1]];
}

// Appliquer les promotions au produit courant
$promoInfo = applyPromotionToArticle($article);
$displayPrice = $promoInfo['price'];
$originalPrice = $promoInfo['original_price'];
$promoLabel = $promoInfo['promo_label'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($article['titre'] ?? '') ?> - Ma Boutique</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/price.css">
    <style>
        .gallery-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .main-image-container {
            width: 100%;
            height: 450px;
            border-radius: 15px;
            overflow: hidden;
            background: #f0f0f0;
            position: relative;
        }
        .main-image {
            width: 100%;
            height: 100%;
            object-fit: contain;
            cursor: zoom-in;
        }
        .thumbnails-container {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding: 10px 0;
        }
        .thumbnails-container::-webkit-scrollbar {
            height: 8px;
        }
        .thumbnails-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        .thumbnails-container::-webkit-scrollbar-thumb {
            background: #667eea;
            border-radius: 10px;
        }
        .thumbnail {
            min-width: 80px;
            width: 80px;
            height: 80px;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            border: 3px solid transparent;
            transition: all 0.3s;
        }
        .thumbnail:hover {
            border-color: #667eea;
            transform: scale(1.05);
        }
        .thumbnail.active {
            border-color: #667eea;
            box-shadow: 0 0 10px rgba(102, 126, 234, 0.5);
        }
        .thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .no-image {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
            color: #999;
            background: #e0e0e0;
        }
        .badge-gallery {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(102, 126, 234, 0.9);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }
        
        /* Modal pour zoom image */
        .image-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.95);
            cursor: zoom-out;
        }
        .image-modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .image-modal img {
            max-width: 90%;
            max-height: 90%;
            object-fit: contain;
        }
        .close-modal {
            position: absolute;
            top: 20px;
            right: 30px;
            color: white;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
        }
        .close-modal:hover {
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1><?= htmlspecialchars($article['titre'] ?? '') ?></h1>
            <a href="index.php" class="back-btn">← Retour à la boutique</a>
        </div>
    </div>
    
    <div class="container">
        <div class="product-detail">
            <div class="product-image-container">
                <div class="gallery-container">
                    <div class="main-image-container" onclick="openImageModal()">
                        <?php if (!empty($photos)): ?>
                       <img src="<?= htmlspecialchars($photos[0]['photo_url'] ?? '') ?>" 
                           class="main-image" 
                           id="mainImage" 
                           alt="<?= htmlspecialchars($article['titre'] ?? '') ?>">
                            <?php if (count($photos) > 1): ?>
                                <span class="badge-gallery">📸 <?= count($photos) ?> photos</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="main-image no-image">📦</div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (count($photos) > 1): ?>
                    <div class="thumbnails-container">
                        <?php foreach ($photos as $index => $photo): ?>
                            <div class="thumbnail <?= $index === 0 ? 'active' : '' ?>" 
                                 onclick="changeImage('<?= htmlspecialchars($photo['photo_url']) ?>', this)">
                                <img src="<?= htmlspecialchars($photo['photo_url'] ?? '') ?>" alt="Photo <?= $index + 1 ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="product-content">
                <div class="product-category"><?= htmlspecialchars($article['categorie'] ?? '(Non défini)') ?></div>
                <h1 class="product-title" style="font-size: 32px;"><?= htmlspecialchars($article['titre'] ?? '') ?></h1>
                <div class="product-price" style="font-size: 36px; margin-bottom: 20px;">
                    <?= formatPriceWithPromo($article) ?>
                </div>
                

                <div class="product-meta">
                    <div class="meta-item">
                        <span class="meta-label">Stock</span>
                        <span class="meta-value">
                            <?= $article['stock'] ?> disponible(s)
                            <?php if ($article['stock'] == 0): ?>
                                <span class="badge-stock" style="background:#e53e3e;color:white;padding:4px 10px;border-radius:12px;font-weight:600;margin-left:10px;">Rupture de stock</span>
                            <?php elseif ($article['stock'] < 3): ?>
                                <span class="badge-stock" style="background:#f6ad55;color:#222;padding:4px 10px;border-radius:12px;font-weight:600;margin-left:10px;">Dernières pièces</span>
                            <?php elseif ($article['stock'] < 5): ?>
                                <span class="badge-stock" style="background:#faf089;color:#222;padding:4px 10px;border-radius:12px;font-weight:600;margin-left:10px;">Stock faible</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="meta-item">
                            <span class="meta-label">Catégorie</span>
                            <span class="meta-value"><?= htmlspecialchars($article['categorie'] ?? '(Non défini)') ?></span>
                        </div>
                </div>
                
                <p class="product-description" style="display: block; -webkit-line-clamp: unset;"><?= nl2br(htmlspecialchars($article['description'] ?? '')) ?></p>
                
                <?php if ($article['video']): ?>
                    <div class="video-container">
                        <?php
                        $video_url = $article['video'];
                        if (strpos($video_url, 'youtube.com') !== false || strpos($video_url, 'youtu.be') !== false) {
                            preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^\&\?\/]+)/', $video_url, $matches);
                            $video_id = $matches[1] ?? '';
                            if ($video_id) {
                                echo '<iframe src="https://www.youtube.com/embed/' . $video_id . '" allowfullscreen></iframe>';
                            }
                        }
                        ?>
                    </div>
                <?php endif; ?>
                
                <div class="quantity-selector">
                    <label>Quantité:</label>
                    <div class="quantity-controls">
                        <button class="qty-btn" onclick="decreaseQty()">-</button>
                        <input type="number" id="quantity" class="qty-input" value="1" min="1" max="<?= $article['stock'] ?>">
                        <button class="qty-btn" onclick="increaseQty()">+</button>
                    </div>
                </div>
                
                <button class="add-to-cart-btn" onclick="addToCart()">
                    🛒 Ajouter au panier
                </button>
            </div>
        </div>
    </div>
    
    <!-- Modal pour zoom image -->
    <div class="image-modal" id="imageModal" onclick="closeImageModal()">
        <span class="close-modal">&times;</span>
        <img src="" id="modalImage" alt="">
    </div>

    <script>
        const maxStock = <?= $article['stock'] ?>;
        const articleId = <?= $article['id'] ?>;
        
        function decreaseQty() {
            const qtyInput = document.getElementById('quantity');
            if (qtyInput.value > 1) {
                qtyInput.value = parseInt(qtyInput.value) - 1;
            }
        }
        
        function increaseQty() {
            const qtyInput = document.getElementById('quantity');
            if (qtyInput.value < maxStock) {
                qtyInput.value = parseInt(qtyInput.value) + 1;
            }
        }
        
        function addToCart() {
            const quantity = parseInt(document.getElementById('quantity').value);
            let cart = JSON.parse(localStorage.getItem('cart')) || [];
            
            const existingItem = cart.find(item => item.id === articleId);
            
            if (existingItem) {
                existingItem.quantity += quantity;
            } else {
                cart.push({ id: articleId, quantity: quantity });
            }
            
            localStorage.setItem('cart', JSON.stringify(cart));
            
            alert('Article ajouté au panier !');
            window.location.href = 'cart.php';
        }
        
        // Changer l'image principale
        function changeImage(imageUrl, thumbnail) {
            const mainImage = document.getElementById('mainImage');
            mainImage.src = imageUrl;
            
            // Mettre à jour les thumbnails actifs
            document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
            thumbnail.classList.add('active');
        }
        
        // Ouvrir le modal de zoom
        function openImageModal() {
            const mainImage = document.getElementById('mainImage');
            const modal = document.getElementById('imageModal');
            const modalImage = document.getElementById('modalImage');
            
            modalImage.src = mainImage.src;
            modal.classList.add('active');
        }
        
        // Fermer le modal
        function closeImageModal() {
            const modal = document.getElementById('imageModal');
            modal.classList.remove('active');
        }
        
        // Fermer avec la touche ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeImageModal();
            }
        });
    </script>
</body>
</html>