<?php
require_once 'config.php';

// Récupérer les paramètres de la boutique
$settings = getBoutiqueSettings();

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = clean($_POST['nom']);
    $email = clean($_POST['email']);
    $whatsapp = clean($_POST['whatsapp']);
    $adresse = clean($_POST['adresse']);
    $ville = clean($_POST['ville']);
    $cart_data = json_decode($_POST['cart_data'], true);
    $coupon_code = isset($_POST['coupon']) ? trim($_POST['coupon']) : '';
    
    if (empty($cart_data)) {
        $error = 'Votre panier est vide';
    } else {
        try {
            $pdo->beginTransaction();
            
            // Calculer le total
            $total = 0;
            $ids = array_column($cart_data, 'id');
            $placeholders = str_repeat('?,', count($ids) - 1) . '?';
            $stmt = $pdo->prepare("SELECT id, titre, prix, stock, IFNULL(categorie_id, NULL) as categorie_id, categorie FROM articles WHERE id IN ($placeholders)");
            $stmt->execute($ids);
            $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($cart_data as $item) {
                $article = array_filter($articles, fn($a) => $a['id'] == $item['id']);
                $article = reset($article);
                if ($article) {
                    // Appliquer la promotion si elle existe
                    $promo = applyPromotionToArticle($article);
                    $unit_price = $promo['price'];
                    $total += $unit_price * $item['quantity'];
                }
            }
            
            // Appliquer coupon si fourni
            $discount_amount = 0;
            $applied_coupon = null;
            if (!empty($coupon_code)) {
                $coupon = validateCouponCode($coupon_code);
                if ($coupon) {
                    if ($coupon['discount_type'] === 'percent') {
                        $discount_amount = round($total * ((float)$coupon['discount_value'] / 100), 2);
                    } else {
                        $discount_amount = min($total, (float)$coupon['discount_value']);
                    }
                    $applied_coupon = $coupon;
                    $total = max(0, $total - $discount_amount);
                }
            }

            // Créer la commande
            $stmt = $pdo->prepare("INSERT INTO commandes (nom_client, email, whatsapp, adresse, ville, total, coupon_code, discount_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nom, $email, $whatsapp, $adresse, $ville, $total, $applied_coupon ? $applied_coupon['code'] : null, $discount_amount]);
            $commande_id = $pdo->lastInsertId();
            
            // Ajouter les détails de commande
            foreach ($cart_data as $item) {
                $article = array_filter($articles, fn($a) => $a['id'] == $item['id']);
                $article = reset($article);
                
                if ($article && $article['stock'] >= $item['quantity']) {
                    $promo = applyPromotionToArticle($article);
                    $unit_price = $promo['price'];

                    $stmt = $pdo->prepare("INSERT INTO details_commande (commande_id, article_id, quantite, prix_unitaire) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$commande_id, $item['id'], $item['quantity'], $unit_price]);
                    
                    // Mettre à jour le stock
                    $stmt = $pdo->prepare("UPDATE articles SET stock = stock - ? WHERE id = ?");
                    $stmt->execute([$item['quantity'], $item['id']]);

                    // Enregistrer le mouvement de stock
                    $stmt = $pdo->prepare("INSERT INTO stock_history (article_id, type, quantite, commentaire) VALUES (?, 'commande', ?, ?)");
                    $commentaire = 'Commande client: ' . $nom;
                    $stmt->execute([$item['id'], $item['quantity'], $commentaire]);

                    // Vérifier le stock restant
                    $stmt = $pdo->prepare("SELECT stock, titre FROM articles WHERE id = ?");
                    $stmt->execute([$item['id']]);
                    $updated = $stmt->fetch();
                    if ($updated && $updated['stock'] < 5) {
                        $alertSubject = "Alerte Stock Faible - " . $settings['nom_boutique'];
                        $alertMessage = "<h2>Alerte Stock Faible</h2><p>L'article <strong>" . htmlspecialchars($updated['titre']) . "</strong> a un stock de <strong>" . $updated['stock'] . "</strong> unité(s).</p>";
                        sendAdminNotification($alertSubject, $alertMessage);
                    }
                }
            }
            
            $pdo->commit();
            $success = true;
            
            if ($applied_coupon) {
                incrementCouponUsage($applied_coupon['code']);
            }

            // Envoyer notification admin
            $emailSubject = "Nouvelle commande - " . $settings['nom_boutique'];
            $currency = isset($settings['devise']) && $settings['devise'] ? $settings['devise'] : 'FCFA';
            $emailMessage = "
                <h2>Nouvelle commande reçue</h2>
                <div style='margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px;'>
                    <p><strong>Client:</strong> " . htmlspecialchars($nom !== null ? $nom : '') . "</p>
                    <p><strong>Email:</strong> " . htmlspecialchars($email !== null ? $email : '') . "</p>
                    <p><strong>WhatsApp:</strong> " . htmlspecialchars($whatsapp !== null ? $whatsapp : '') . "</p>
                    <p><strong>Adresse:</strong> " . htmlspecialchars($adresse !== null ? $adresse : '') . "</p>
                    <p><strong>Ville:</strong> " . htmlspecialchars($ville !== null ? $ville : '') . "</p>
                </div>
                
                <h3>Articles commandés:</h3>
                <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px;'>
                    <tr style='background: #667eea; color: white;'>
                        <th style='padding: 10px; text-align: left;'>Article</th>
                        <th style='padding: 10px; text-align: center;'>Quantité</th>
                        <th style='padding: 10px; text-align: right;'>Prix unitaire</th>
                        <th style='padding: 10px; text-align: right;'>Sous-total</th>
                    </tr>";

            foreach ($cart_data as $item) {
                $found = array_filter($articles, fn($a) => $a['id'] == $item['id']);
                $found = reset($found);
                if ($found) {
                    $promo = applyPromotionToArticle($found);
                    $prix_unitaire = $promo['price'];
                    $sous_total = $prix_unitaire * $item['quantity'];
                    
                    $emailMessage .= "<tr style='border-bottom: 1px solid #eee;'>
                        <td style='padding: 10px;'>" . htmlspecialchars($found['titre']) . "</td>
                        <td style='padding: 10px; text-align: center;'>" . $item['quantity'] . "</td>
                        <td style='padding: 10px; text-align: right;'>" . number_format($prix_unitaire, 0, ',', ' ') . " " . $currency . "</td>
                        <td style='padding: 10px; text-align: right;'>" . number_format($sous_total, 0, ',', ' ') . " " . $currency . "</td>
                    </tr>";
                    
                    if ($promo['price'] < $promo['original_price']) {
                        $emailMessage .= "<tr>
                            <td colspan='4' style='padding: 5px 10px; color: #e44d26; font-size: 0.9em;'>
                                ↳ Prix promotionnel appliqué (prix original: " . number_format($promo['original_price'], 0, ',', ' ') . " " . $currency . ")
                            </td>
                        </tr>";
                    }
                }
            }
            
            if ($discount_amount > 0 && $applied_coupon) {
                $emailMessage .= "<tr style='background: #fff3cd;'>
                    <td colspan='3' style='padding: 10px;'><strong>Coupon appliqué:</strong> " . htmlspecialchars($applied_coupon['code']) . "</td>
                    <td style='padding: 10px; text-align: right;'>-" . number_format($discount_amount, 0, ',', ' ') . " " . $currency . "</td>
                </tr>";
            }
            
            $emailMessage .= "<tr style='background: #667eea; color: white; font-weight: bold;'>
                    <td colspan='3' style='padding: 10px;'>TOTAL</td>
                    <td style='padding: 10px; text-align: right;'>" . number_format($total, 0, ',', ' ') . " " . $currency . "</td>
                </tr>
            </table>";
            
            sendAdminNotification($emailSubject, $emailMessage);
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Erreur lors de la commande : ' . $e->getMessage();
        }
    }
}

$page_title = 'Finaliser la commande - ' . htmlspecialchars($settings['nom_boutique'] ?? 'Boutique');
?>
<?php include 'includes/head.php'; ?>
    <?php include 'includes/header.php'; ?>
    
    <?php if ($success): ?>
        <!-- Success Page -->
        <div class="min-h-screen bg-gradient-to-br from-green-50 to-blue-50 flex items-center justify-center py-12 px-4 sm:px-6">
            <div class="max-w-2xl w-full">
                <div class="bg-white rounded-3xl shadow-2xl p-8 sm:p-12 text-center">
                    <div class="inline-block p-6 bg-green-100 rounded-full mb-6">
                        <svg class="w-20 h-20 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    
                    <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-4">🎉 Commande confirmée !</h1>
                    <p class="text-lg text-gray-600 mb-8">Merci pour votre commande ! Nous vous contacterons bientôt sur WhatsApp pour la confirmation.</p>
                    
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="index.php" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-primary text-white text-lg font-bold rounded-xl hover:bg-primary/90 shadow-lg hover:shadow-xl transition-all">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3z"/>
                            </svg>
                            Retour à la boutique
                        </a>
                        <a href="home.php" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-white text-gray-700 text-lg font-semibold rounded-xl border-2 border-gray-200 hover:border-primary hover:text-primary transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            Accueil
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <script>
            localStorage.removeItem('cart');
            // Update cart count
            if (document.getElementById('cartCount')) {
                document.getElementById('cartCount').textContent = '0';
            }
        </script>
    <?php else: ?>
        <!-- Checkout Form -->
        <div class="bg-gradient-to-br from-primary/5 to-accent/5 py-8 sm:py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6">
                <div class="flex items-center gap-4 mb-6">
                    <a href="cart.php" class="flex items-center gap-2 text-gray-600 hover:text-primary transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Retour au panier
                    </a>
                </div>
                <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900">Finaliser la commande</h1>
            </div>
        </div>
        
        <div class="bg-white py-8 sm:py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6">
                <?php if ($error): ?>
                    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-xl">
                        <div class="flex items-center gap-3">
                            <svg class="w-6 h-6 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <p class="text-red-700 font-semibold"><?= $error ?></p>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="grid lg:grid-cols-3 gap-8">
                    <!-- Récapitulatif (affiché en premier sur mobile) -->
                    <div class="lg:col-span-1 order-1 lg:order-2">
                        <div class="bg-gradient-to-br from-gray-50 to-white border border-gray-200 rounded-2xl p-6 lg:sticky lg:top-24">
                            <h3 class="text-xl font-bold text-gray-900 mb-6">Récapitulatif</h3>
                            <div id="orderSummary"></div>
                        </div>
                    </div>
                    
                    <!-- Formulaire (affiché en second sur mobile) -->
                    <div class="lg:col-span-2 order-2 lg:order-1">
                        <div class="bg-white border border-gray-200 rounded-2xl p-6 sm:p-8">
                            <h2 class="text-2xl font-bold text-gray-900 mb-6">Informations de livraison</h2>
                            
                            <form method="POST" id="checkoutForm" class="space-y-6">
                                <input type="hidden" name="cart_data" id="cartData">
                                
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nom complet *</label>
                                    <input type="text" name="nom" required placeholder="Votre nom complet" class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Email *</label>
                                    <input type="email" name="email" required placeholder="votre.email@gmail.com" class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">WhatsApp *</label>
                                    <input type="tel" name="whatsapp" required placeholder="+229 01 XX XX XX XX" class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Adresse complète *</label>
                                    <textarea name="adresse" required placeholder="Votre adresse de livraison complète" rows="3" class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all"></textarea>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Ville *</label>
                                    <input type="text" name="ville" required placeholder="Votre ville" class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Code promo (facultatif)</label>
                                    <input type="text" name="coupon" placeholder="Entrez un code promo" class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                                </div>
                                
                                <button type="submit" class="w-full flex items-center justify-center gap-2 px-6 py-4 bg-primary text-white text-lg font-bold rounded-xl hover:bg-primary/90 shadow-lg hover:shadow-xl transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Confirmer la commande
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php include 'includes/footer.php'; ?>

    <script>
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        
        if (cart.length === 0 && !<?= $success ? 'true' : 'false' ?>) {
            window.location.href = 'cart.php';
        }
        
        async function loadOrderSummary() {
            if (cart.length === 0) return;
            
            const ids = cart.map(item => item.id).join(',');
            const response = await fetch(`get_articles.php?ids=${ids}`);
            const articles = await response.json();
            
            let total = 0;
            let summaryHtml = '<div class="space-y-3 mb-6">';
            
            cart.forEach(cartItem => {
                const article = articles.find(a => a.id == cartItem.id);
                if (!article) return;
                
                const subtotal = article.prix * cartItem.quantity;
                total += subtotal;
                
                summaryHtml += `
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-700">${article.titre} <span class="text-gray-500">(x${cartItem.quantity})</span></span>
                        <span class="font-semibold text-gray-900">${formatPrice(subtotal)}</span>
                    </div>
                `;
            });
            
            summaryHtml += `
                </div>
                <div class="border-t border-gray-200 pt-4">
                    <div class="flex justify-between text-lg font-bold text-gray-900">
                        <span>Total</span>
                        <span class="text-primary">${formatPrice(total)}</span>
                    </div>
                </div>
            `;
            
            document.getElementById('orderSummary').innerHTML = summaryHtml;
            document.getElementById('cartData').value = JSON.stringify(cart);
        }
        
        function formatPrice(price) {
            return new Intl.NumberFormat('fr-FR', {
                style: 'decimal',
                minimumFractionDigits: 0
            }).format(price) + ' FCFA';
        }
        
        loadOrderSummary();
    </script>
</body>
</html>
