<?php
function formatPriceWithPromo($article) {
    // Utiliser original_price si elle existe (elle a été stockée dans index.php)
    // Sinon recalculer via applyPromotionToArticle()
    if (isset($article['original_price']) && isset($article['promo_label'])) {
        // Les données de promo ont déjà été calculées et stockées dans l'article
        $original = (float)$article['original_price'];
        $reduced = (float)$article['prix'];
    } else {
        // Recalculer au cas où
        $promo = applyPromotionToArticle($article);
        $original = $promo['original_price'];
        $reduced = $promo['price'];
    }
    
    $html = '<div class="flex flex-col gap-1">';
    
    if ($reduced < $original) {
        // Prix barré (original)
        $html .= '<span class="text-sm text-gray-500 line-through">' . formatPrice($original) . '</span>';
        // Prix promotionnel (réduit)
        $html .= '<span class="text-lg font-bold text-primary">' . formatPrice($reduced) . '</span>';
    } else {
        // Prix normal
        $html .= '<span class="text-lg font-bold text-gray-900">' . formatPrice($reduced) . '</span>';
    }
    
    $html .= '</div>';
    
    return $html;
}