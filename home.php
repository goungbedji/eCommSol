<?php
require_once 'config.php';

// Récupérer les paramètres de la boutique
$settings = getBoutiqueSettings();

// Récupérer quelques statistiques pour la page
$total_articles = $pdo->query("SELECT COUNT(*) FROM articles WHERE stock > 0")->fetchColumn();
$categories = $pdo->query("SELECT DISTINCT categorie FROM articles WHERE stock > 0 LIMIT 4")->fetchAll(PDO::FETCH_COLUMN);

// Récupérer les derniers articles pour la section "Nouveautés"
$stmt = $pdo->query("SELECT id, titre, description, prix, photo, stock, categorie, IFNULL(categorie_id, NULL) as categorie_id FROM articles WHERE stock > 0 ORDER BY date_creation DESC LIMIT 4");
$derniers_articles = $stmt->fetchAll();

// Appliquer promotions
foreach ($derniers_articles as &$a) {
    $promo = applyPromotionToArticle($a);
    $a['original_price'] = $promo['original_price'];
    $a['prix'] = $promo['price'];
    $a['promo_label'] = $promo['promo_label'];
    $a['promo_percent'] = $promo['discount_percent'];
}
unset($a);

$page_title = "Bienvenue - Space E-Commerce";
?>
<?php include 'includes/head.php'; ?>
    <?php include 'includes/header.php'; ?>
    
    <?php include 'includes/sections/hero.php'; ?>
    
    <?php include 'includes/sections/features.php'; ?>
    
    <?php include 'includes/sections/products.php'; ?>
    
    <?php include 'includes/sections/categories.php'; ?>
    
    <?php include 'includes/sections/cta.php'; ?>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
