<?php
require_once '../config.php';

header('Content-Type: application/json');

// Récupérer le terme de recherche
$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;

// Si pas de recherche, retourner vide
if (empty($query) || strlen($query) < 2) {
    echo json_encode([
        'success' => false,
        'message' => 'Recherche trop courte (minimum 2 caractères)',
        'results' => []
    ]);
    exit;
}

try {
    // Recherche dans titre, description et catégorie
    $searchTerm = '%' . $query . '%';
    
    $stmt = $pdo->prepare("
        SELECT 
            id,
            titre,
            description,
            prix,
            photo,
            stock,
            categorie
        FROM articles 
        WHERE stock > 0 
        AND (
            titre LIKE ? 
            OR description LIKE ? 
            OR categorie LIKE ?
        )
        ORDER BY 
            CASE 
                WHEN titre LIKE ? THEN 1
                WHEN categorie LIKE ? THEN 2
                ELSE 3
            END,
            date_creation DESC
        LIMIT ?
    ");
    
    $stmt->execute([
        $searchTerm, 
        $searchTerm, 
        $searchTerm,
        $searchTerm,
        $searchTerm,
        $limit
    ]);
    
    $results = $stmt->fetchAll();
    
    // Formater les résultats
    $formatted_results = array_map(function($article) {
        return [
            'id' => $article['id'],
            'titre' => $article['titre'],
            'description' => substr($article['description'], 0, 100) . '...',
            'prix' => formatPrice($article['prix']),
            'prix_raw' => $article['prix'],
            'photo' => $article['photo'],
            'stock' => $article['stock'],
            'categorie' => $article['categorie'],
            'url' => 'product.php?id=' . $article['id']
        ];
    }, $results);
    
    echo json_encode([
        'success' => true,
        'count' => count($formatted_results),
        'query' => $query,
        'results' => $formatted_results
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la recherche',
        'error' => $e->getMessage(),
        'results' => []
    ]);
}
?>