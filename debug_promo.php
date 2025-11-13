<?php
require_once 'config.php';

// Vérifier la promo pour l'article id=3 (Montre Connectée Sport)
$stmt = $pdo->prepare('SELECT * FROM articles WHERE id = 3');
$stmt->execute();
$article = $stmt->fetch();

echo "=== ARTICLE ===\n";
var_dump($article);

echo "\n=== PROMOTION APPLIQUÉE ===\n";
$promo = applyPromotionToArticle($article);
var_dump($promo);

echo "\n=== PRIX FORMATÉ ===\n";
echo formatPriceWithPromo($article);
?>
