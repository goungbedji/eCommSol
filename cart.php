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
    <title>Mon Panier - <?= htmlspecialchars($settings['nom_boutique'] ?? '') ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/mobile.css">
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>🛒 Mon Panier</h1>
            <a href="index.php" class="back-btn">← Continuer mes achats</a>
        </div>
    </div>
    
    <div class="container">
        <div id="cartContent"></div>
    </div>

    <script>
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        let articles = [];

        async function loadCart() {
            if (cart.length === 0) {
                document.getElementById('cartContent').innerHTML = `
                    <div class="empty-cart">
                        <h2>Votre panier est vide</h2>
                        <p style="color: #999; margin-bottom: 20px;">Découvrez nos produits et ajoutez-les à votre panier</p>
                        <a href="index.php" style="display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 8px; font-weight: 600;">Voir la boutique</a>
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
                    <div class="cart-item">
                        <img src="${article.photo || ''}" class="item-image" alt="${article.titre}">
                        <div class="item-details">
                            <div class="item-title">${article.titre}</div>
                            <div class="item-price">${formatPrice(article.prix)}</div>
                            <div class="item-controls">
                                <button class="qty-btn" onclick="updateQuantity(${article.id}, -1)">-</button>
                                <span class="qty-display">${cartItem.quantity}</span>
                                <button class="qty-btn" onclick="updateQuantity(${article.id}, 1)">+</button>
                                <span style="margin-left: 20px; font-weight: 600;">Sous-total: ${formatPrice(subtotal)}</span>
                            </div>
                        </div>
                        <button class="remove-btn" onclick="removeItem(${article.id})">Supprimer</button>
                    </div>
                `;
            });

            document.getElementById('cartContent').innerHTML = `
                <div class="cart-container">
                    <div class="cart-items">
                        <h2 style="margin-bottom: 20px;">Articles dans le panier</h2>
                        ${itemsHtml}
                    </div>
                    <div class="cart-summary">
                        <div class="summary-title">Résumé de la commande</div>
                        <div class="summary-row">
                            <span>Sous-total</span>
                            <span>${formatPrice(total)}</span>
                        </div>
                        <div class="summary-row">
                            <span>Livraison</span>
                            <span>Calculé à la caisse</span>
                        </div>
                        <div class="summary-row">
                            <span>Total</span>
                            <span>${formatPrice(total)}</span>
                        </div>
                        <button class="checkout-btn" onclick="window.location='checkout.php'">
                            Passer la commande
                        </button>
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
                    alert('Stock maximum atteint');
                }
                localStorage.setItem('cart', JSON.stringify(cart));
                renderCart();
            }
        }

        function removeItem(articleId) {
            cart = cart.filter(i => i.id !== articleId);
            localStorage.setItem('cart', JSON.stringify(cart));
            loadCart();
        }

        function formatPrice(price) {
            return new Intl.NumberFormat('fr-FR', {
                style: 'decimal',
                minimumFractionDigits: 0
            }).format(price) + ' FCFA';
        }

        loadCart();
    </script>
</body>
</html>