<?php
require_once 'config.php';

// Récupérer les paramètres de la boutique
$settings = getBoutiqueSettings();

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

$page_title = htmlspecialchars($article['titre'] ?? 'Produit') . ' - ' . htmlspecialchars($settings['nom_boutique'] ?? 'Boutique');
?>
<?php include 'includes/head.php'; ?>
    <?php include 'includes/header.php'; ?>
    
    <!-- Breadcrumb -->
    <div class="bg-gray-50 border-b border-gray-200 py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="flex items-center gap-2 text-sm">
                <a href="index.php" class="text-gray-500 hover:text-primary transition-colors">Boutique</a>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-gray-900 font-semibold truncate"><?= htmlspecialchars($article['titre'] ?? '') ?></span>
            </div>
        </div>
    </div>
    
    <!-- Product Detail -->
    <div class="bg-white py-8 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="grid lg:grid-cols-2 gap-8 lg:gap-12">
                <!-- Gallery -->
                <div class="space-y-4">
                    <!-- Main Image -->
                    <div class="relative aspect-square bg-gray-100 rounded-2xl overflow-hidden group cursor-zoom-in" onclick="openImageModal()">
                        <?php if (!empty($photos)): ?>
                            <img src="<?= htmlspecialchars($photos[0]['photo_url'] ?? '') ?>" 
                                 class="w-full h-full object-contain transition-transform duration-300 group-hover:scale-105" 
                                 id="mainImage" 
                                 alt="<?= htmlspecialchars($article['titre'] ?? '') ?>">
                            
                            <?php if (count($photos) > 1): ?>
                                <div class="absolute top-4 right-4 bg-white/90 backdrop-blur-sm text-primary px-3 py-2 rounded-xl text-sm font-bold shadow-lg">
                                    📸 <?= count($photos) ?> photos
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($promoLabel)): ?>
                                <div class="absolute top-4 left-4 bg-red-500 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-lg">
                                    <?= htmlspecialchars($promoLabel) ?>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center">
                                <svg class="w-32 h-32 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Thumbnails -->
                    <?php if (count($photos) > 1): ?>
                        <div class="flex gap-3 overflow-x-auto pb-2">
                            <?php foreach ($photos as $index => $photo): ?>
                                <div class="thumbnail flex-shrink-0 w-20 h-20 sm:w-24 sm:h-24 rounded-xl overflow-hidden border-2 cursor-pointer transition-all hover:border-primary <?= $index === 0 ? 'border-primary shadow-lg' : 'border-gray-200' ?>" 
                                     onclick="changeImage('<?= htmlspecialchars($photo['photo_url']) ?>', this)">
                                    <img src="<?= htmlspecialchars($photo['photo_url'] ?? '') ?>" 
                                         class="w-full h-full object-cover" 
                                         alt="Photo <?= $index + 1 ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Product Info -->
                <div class="space-y-6">
                    <!-- Category -->
                    <div class="text-sm font-semibold text-primary">
                        <?= htmlspecialchars($article['categorie'] ?? '(Non défini)') ?>
                    </div>
                    
                    <!-- Title -->
                    <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900">
                        <?= htmlspecialchars($article['titre'] ?? '') ?>
                    </h1>
                    
                    <!-- Price -->
                    <div class="text-4xl font-bold text-primary">
                        <?= formatPriceWithPromo($article) ?>
                    </div>
                    
                    <!-- Stock -->
                    <div class="flex items-center gap-2 text-gray-700">
                        <span class="font-semibold">Stock:</span>
                        <span><?= $article['stock'] ?> disponible(s)</span>
                    </div>
                    
                    <!-- Description -->
                    <div class="prose prose-gray max-w-none">
                        <p class="text-gray-700 leading-relaxed whitespace-pre-line">
                            <?= htmlspecialchars($article['description'] ?? '') ?>
                        </p>
                    </div>
                    
                    <!-- Video -->
                    <?php if ($article['video']): ?>
                        <div class="aspect-video rounded-2xl overflow-hidden bg-gray-100">
                            <?php
                            $video_url = $article['video'];
                            if (strpos($video_url, 'youtube.com') !== false || strpos($video_url, 'youtu.be') !== false) {
                                preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^\&\?\/]+)/', $video_url, $matches);
                                $video_id = $matches[1] ?? '';
                                if ($video_id) {
                                    echo '<iframe class="w-full h-full" src="https://www.youtube.com/embed/' . $video_id . '" allowfullscreen></iframe>';
                                }
                            }
                            ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Quantity Selector -->
                    <?php if ($article['stock'] > 0): ?>
                        <div class="space-y-3">
                            <label class="block text-sm font-semibold text-gray-700">Quantité:</label>
                            <div class="flex items-center gap-4">
                                <div class="flex items-center gap-2 bg-gray-100 rounded-xl p-1">
                                    <button onclick="decreaseQty()" class="w-10 h-10 flex items-center justify-center bg-white rounded-lg hover:bg-primary hover:text-white transition-all font-bold text-xl">
                                        −
                                    </button>
                                    <input type="number" id="quantity" value="1" min="1" max="<?= $article['stock'] ?>" 
                                           class="w-16 text-center font-bold text-lg bg-transparent border-none focus:outline-none">
                                    <button onclick="increaseQty()" class="w-10 h-10 flex items-center justify-center bg-white rounded-lg hover:bg-primary hover:text-white transition-all font-bold text-xl">
                                        +
                                    </button>
                                </div>
                                <span class="text-sm text-gray-500">Max: <?= $article['stock'] ?></span>
                            </div>
                        </div>
                        
                        <!-- Add to Cart Button -->
                        <button onclick="addToCart()" class="w-full flex items-center justify-center gap-3 px-8 py-4 bg-primary text-white text-lg font-bold rounded-xl hover:bg-primary/90 shadow-lg hover:shadow-xl transition-all">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3z"/>
                            </svg>
                            Ajouter au panier
                        </button>
                    <?php else: ?>
                        <div class="p-4 bg-red-50 border-l-4 border-red-500 rounded-xl">
                            <p class="text-red-700 font-semibold">Ce produit est actuellement en rupture de stock.</p>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Back to Shop -->
                    <a href="index.php" class="inline-flex items-center gap-2 text-gray-600 hover:text-primary transition-colors font-semibold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Retour à la boutique
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Image Modal -->
    <div id="imageModal" class="hidden fixed inset-0 z-50 bg-black/95 cursor-zoom-out" onclick="closeImageModal()">
        <button class="absolute top-6 right-6 text-white hover:text-primary transition-colors">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
        <div class="flex items-center justify-center h-full p-4">
            <img src="" id="modalImage" class="max-w-full max-h-full object-contain" alt="">
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

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
            
            // Update cart count
            if (document.getElementById('cartCount')) {
                const count = cart.reduce((total, item) => total + item.quantity, 0);
                document.getElementById('cartCount').textContent = count;
            }
        }
        
        function changeImage(imageUrl, thumbnail) {
            const mainImage = document.getElementById('mainImage');
            mainImage.src = imageUrl;
            
            // Update active thumbnail
            document.querySelectorAll('.thumbnail').forEach(t => {
                t.classList.remove('border-primary', 'shadow-lg');
                t.classList.add('border-gray-200');
            });
            thumbnail.classList.remove('border-gray-200');
            thumbnail.classList.add('border-primary', 'shadow-lg');
        }
        
        function openImageModal() {
            const mainImage = document.getElementById('mainImage');
            const modal = document.getElementById('imageModal');
            const modalImage = document.getElementById('modalImage');
            
            if (mainImage.src) {
                modalImage.src = mainImage.src;
                modal.classList.remove('hidden');
            }
        }
        
        function closeImageModal() {
            const modal = document.getElementById('imageModal');
            modal.classList.add('hidden');
        }
        
        // Close with ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeImageModal();
            }
        });
    </script>
</body>
</html>
