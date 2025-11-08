<?php
require_once 'config.php';

header('Content-Type: application/json');

if (isset($_GET['ids'])) {
    $ids = explode(',', $_GET['ids']);
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    
    $stmt = $pdo->prepare("SELECT id, titre, description, prix, photo, stock, categorie, IFNULL(categorie_id, NULL) as categorie_id FROM articles WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $articles = $stmt->fetchAll();
    
    // Appliquer promotions si existantes
    foreach ($articles as &$a) {
        $promo = applyPromotionToArticle($a);
        $a['original_price'] = $promo['original_price'];
        $a['prix'] = $promo['price'];
        $a['promo_label'] = $promo['promo_label'];
        $a['promo_percent'] = $promo['discount_percent'];
    }
    unset($a);

    echo json_encode($articles);
} else {
    echo json_encode([]);
}
?>