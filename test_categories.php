<?php
require_once 'config.php';

// 1. Vérifions d'abord la table categories
echo "<h2>Contenu de la table categories :</h2>";
$stmt = $pdo->query("SELECT * FROM categories ORDER BY id");
$categories = $stmt->fetchAll();
echo "<pre>";
print_r($categories);
echo "</pre>";

// 2. Vérifions les articles avec leurs catégories
echo "<h2>Articles avec leurs catégories :</h2>";
$stmt = $pdo->query("SELECT a.id, a.titre, a.categorie_id, a.categorie, c.nom as nom_categorie 
                     FROM articles a 
                     LEFT JOIN categories c ON a.categorie_id = c.id 
                     ORDER BY a.id DESC LIMIT 5");
$articles = $stmt->fetchAll();
echo "<pre>";
print_r($articles);
echo "</pre>";