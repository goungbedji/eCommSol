<?php
function formatPriceWithPromo($article) {
    $promo = applyPromotionToArticle($article);
    
    $html = '<div class="price-container">';
    
    if ($promo['price'] < $promo['original_price']) {
        // Prix barré
        $html .= '<span class="price-original">' . number_format($promo['original_price'], 2) . '€</span>';
        // Prix promotionnel
        $html .= '<span class="price-promo">' . number_format($promo['price'], 2) . '€</span>';
        // Badge promo
        $html .= '<span class="promo-badge">PROMO -' . number_format($promo['discount_percent']) . '%</span>';
    } else {
        // Prix normal
        $html .= '<span class="price-normal">' . number_format($promo['price'], 2) . '€</span>';
    }
    
    $html .= '</div>';
    
    return $html;
}