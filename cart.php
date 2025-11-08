<?php
require_once 'config.php';

// Récupérer les paramètres de la boutique
$settings = getBoutiqueSettings();

$page_title = 'Mon Panier - ' . htmlspecialchars($settings['nom_boutique'] ?? 'Boutique');
?>
<?php include 'includes/head.php'; ?>
    <?php include 'includes/header.php'; ?>
    
    <!-- Page Header -->
    <div class="bg-gradient-to-br from-primary/5 to-accent/5 py-8 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div>
                <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-2">🛒 Mon Panier</h1>
                <p class="text-gray-600">Gérez vos articles avant de passer commande</p>
            </div>
        </div>
    </div>
    
    <!-- Cart Content -->
    <div class="bg-white py-8 sm:py-12 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div id="cartContent"></div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        let articles = [];

        async function loadCart() {
            if (cart.length === 0) {
                document.getElementById('cartContent').innerHTML = `
                    <div class="text-center py-12 sm:py-20">
                        <div class="inline-block p-8 bg-gray-50 rounded-3xl mb-6">
                            <svg class="w-24 h-24 sm:w-32 sm:h-32 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3">Votre panier est vide</h2>
                        <p class="text-base sm:text-lg text-gray-600 mb-8">Découvrez nos produits et ajoutez-les à votre panier</p>
                        <a href="index.php" class="inline-flex items-center gap-2 px-6 sm:px-8 py-3 sm:py-4 bg-primary text-white text-base sm:text-lg font-bold rounded-xl hover:bg-primary/90 shadow-lg hover:shadow-xl transition-all">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3z"/>
                            </svg>
                            Voir la boutique
                        </a>
                    </div>
                `;
                return;
            }

            const ids = cart.map(item => item.id).join(',');
            const response = await fetch(`get_articles.php?ids=${ids}`);
            articles = await response.json();

            renderCart();
        }

        function renderCart() {
            let total = 0;
            let itemsHtml = '';

            cart.forEach(cartItem => {
                const article = articles.find(a => a.id == cartItem.id);
                if (!article) return;

                const subtotal = article.prix * cartItem.quantity;
                total += subtotal;

                itemsHtml += `
                    <div class="bg-white border border-gray-200 rounded-2xl p-4 sm:p-6 hover:shadow-lg transition-all">
                        <div class="flex gap-4">
                            <img src="${article.photo || ''}" class="w-20 h-20 sm:w-24 sm:h-24 object-cover rounded-xl flex-shrink-0" alt="${article.titre}">
                            <div class="flex-1 min-w-0">
                                <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-1 truncate">${article.titre}</h3>
                                <p class="text-lg sm:text-xl font-bold text-primary mb-3">${formatPrice(article.prix)}</p>
                                
                                <div class="flex flex-wrap items-center gap-3">
                                    <div class="flex items-center gap-2 bg-gray-100 rounded-xl p-1">
                                        <button onclick="updateQuantity(${article.id}, -1)" class="w-8 h-8 flex items-center justify-center bg-white rounded-lg hover:bg-primary hover:text-white transition-all font-bold">
                                            −
                                        </button>
                                        <span class="w-10 text-center font-bold">${cartItem.quantity}</span>
                                        <button onclick="updateQuantity(${article.id}, 1)" class="w-8 h-8 flex items-center justify-center bg-white rounded-lg hover:bg-primary hover:text-white transition-all font-bold">
                                            +
                                        </button>
                                    </div>
                                    <span class="text-sm sm:text-base font-semibold text-gray-700">Sous-total: <span class="text-primary">${formatPrice(subtotal)}</span></span>
                                </div>
                            </div>
                            <button onclick="removeItem(${article.id})" class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 flex items-center justify-center text-red-500 hover:bg-red-50 rounded-xl transition-all">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                `;
            });

            document.getElementById('cartContent').innerHTML = `
                <div class="grid lg:grid-cols-3 gap-8">
                    <!-- Cart Items -->
                    <div class="lg:col-span-2 space-y-4">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Articles dans le panier (${cart.length})</h2>
                        ${itemsHtml}
                    </div>
                    
                    <!-- Order Summary -->
                    <div class="lg:col-span-1">
                        <div class="bg-gradient-to-br from-gray-50 to-white border border-gray-200 rounded-2xl p-6 sticky top-24">
                            <h3 class="text-xl font-bold text-gray-900 mb-6">Résumé de la commande</h3>
                            
                            <div class="space-y-4 mb-6">
                                <div class="flex justify-between text-gray-700">
                                    <span>Sous-total</span>
                                    <span class="font-semibold">${formatPrice(total)}</span>
                                </div>
                                <div class="flex justify-between text-gray-700">
                                    <span>Livraison</span>
                                    <span class="text-sm text-gray-500">Calculé à la caisse</span>
                                </div>
                                <div class="border-t border-gray-200 pt-4">
                                    <div class="flex justify-between text-lg font-bold text-gray-900">
                                        <span>Total</span>
                                        <span class="text-primary">${formatPrice(total)}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <button onclick="window.location='checkout.php'" class="w-full flex items-center justify-center gap-2 px-6 py-4 bg-primary text-white text-lg font-bold rounded-xl hover:bg-primary/90 shadow-lg hover:shadow-xl transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                                Passer la commande
                            </button>
                            
                            <a href="index.php" class="block text-center mt-4 text-primary hover:text-primary/80 font-semibold transition-colors">
                                ← Continuer mes achats
                            </a>
                        </div>
                    </div>
                </div>
            `;
        }

        function updateQuantity(articleId, change) {
            const item = cart.find(i => i.id === articleId);
            const article = articles.find(a => a.id == articleId);
            
            if (item) {
                item.quantity += change;
                if (item.quantity <= 0) {
                    removeItem(articleId);
                } else if (item.quantity > article.stock) {
                    item.quantity = article.stock;
                    showNotification('Stock maximum atteint', 'warning');
                }
                localStorage.setItem('cart', JSON.stringify(cart));
                renderCart();
            }
        }

        function removeItem(articleId) {
            if (confirm('Voulez-vous vraiment retirer cet article du panier ?')) {
                cart = cart.filter(i => i.id !== articleId);
                localStorage.setItem('cart', JSON.stringify(cart));
                showNotification('Article retiré du panier', 'success');
                loadCart();
            }
        }

        function formatPrice(price) {
            return new Intl.NumberFormat('fr-FR', {
                style: 'decimal',
                minimumFractionDigits: 0
            }).format(price) + ' FCFA';
        }

        function showNotification(message, type = 'info') {
            const colors = {
                success: 'bg-green-500',
                warning: 'bg-orange-500',
                info: 'bg-blue-500'
            };
            
            const notification = document.createElement('div');
            notification.className = `fixed top-24 right-6 ${colors[type]} text-white px-6 py-3 rounded-xl shadow-lg z-50 animate-fade-in`;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        loadCart();
    </script>
</body>
</html>
