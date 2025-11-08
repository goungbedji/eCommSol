<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement - <?= htmlspecialchars($settings['nom_boutique'] ?? '') ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/mobile.css">
    <link rel="stylesheet" href="css/checkout-mobile.css">
</head>
<body>
<?php
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
                    // compute discount on total
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
                    // appliquer promo prix par article
                    $promo = applyPromotionToArticle($article);
                    $unit_price = $promo['price'];

                    $stmt = $pdo->prepare("INSERT INTO details_commande (commande_id, article_id, quantite, prix_unitaire) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$commande_id, $item['id'], $item['quantity'], $unit_price]);
                    
                    // Mettre à jour le stock
                    $stmt = $pdo->prepare("UPDATE articles SET stock = stock - ? WHERE id = ?");
                    $stmt->execute([$item['quantity'], $item['id']]);

                    // Enregistrer le mouvement de stock (commande)
                    $stmt = $pdo->prepare("INSERT INTO stock_history (article_id, type, quantite, commentaire) VALUES (?, 'commande', ?, ?)");
                    $commentaire = 'Commande client: ' . $nom;
                    $stmt->execute([$item['id'], $item['quantity'], $commentaire]);

                    // Vérifier le stock restant et alerter l'admin si < 5
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
            
            // Si un coupon a été appliqué, incrémenter le compteur d'utilisation
            if ($applied_coupon) {
                incrementCouponUsage($applied_coupon['code']);
            }

            // Envoyer une notification à l'admin
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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finaliser la commande</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>✅ Finaliser la commande</h1>
            <a href="cart.php" class="back-btn">← Retour au panier</a>
        </div>
    </div>
    
    <div class="container">
        <?php if ($success): ?>
            <div class="success-message">
                <h2>🎉 Commande confirmée !</h2>
                <p>Merci pour votre commande ! Nous vous contacterons bientôt sur WhatsApp pour la confirmation.</p>
                <a href="index.php" style="display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 8px; font-weight: 600;">Retour à la boutique</a>
            </div>
            <script>
                localStorage.removeItem('cart');
            </script>
        <?php else: ?>
            <div class="checkout-container">
                <h2 style="margin-bottom: 30px;">Informations de livraison</h2>
                
                <?php if ($error): ?>
                    <div class="error-message"><?= $error ?></div>
                <?php endif; ?>
                
                <div id="orderSummary" class="order-summary"></div>
                
                <form method="POST" id="checkoutForm">
                    <input type="hidden" name="cart_data" id="cartData">
                    
                    <div class="form-group">
                        <label>Nom complet *</label>
                        <input type="text" name="nom" required placeholder="Votre nom complet">
                    </div>
                    
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" required placeholder="votre.email@gmail.com">
                    </div>
                    
                    <div class="form-group">
                        <label>WhatsApp *</label>
                        <input type="tel" name="whatsapp" required placeholder="+229 01 XX XX XX XX">
                    </div>
                    
                    <div class="form-group">
                        <label>Adresse complète *</label>
                        <textarea name="adresse" required placeholder="Votre adresse de livraison complète"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Ville *</label>
                        <input type="text" name="ville" required placeholder="Votre ville">
                    </div>
                    
                    <div class="form-group">
                        <label>Code promo (facultatif)</label>
                        <input type="text" name="coupon" placeholder="Entrez un code promo">
                    </div>
                    
                    <button type="submit" class="submit-btn">Confirmer la commande</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

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
            let summaryHtml = '<div class="summary-title">Récapitulatif de votre commande</div>';
            
            cart.forEach(cartItem => {
                const article = articles.find(a => a.id == cartItem.id);
                if (!article) return;
                
                const subtotal = article.prix * cartItem.quantity;
                total += subtotal;
                
                summaryHtml += `
                    <div class="summary-item">
                        <span>${article.titre} (x${cartItem.quantity})</span>
                        <span>${formatPrice(subtotal)}</span>
                    </div>
                `;
            });
            
            summaryHtml += `
                <div class="summary-total">
                    <span>Total</span>
                    <span>${formatPrice(total)}</span>
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