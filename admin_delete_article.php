<?php
require_once 'config.php';
requireAdmin();

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    try {
        $pdo->beginTransaction();
        
        // Récupérer toutes les photos de l'article avant suppression
        $stmt = $pdo->prepare("SELECT photo_url FROM article_photos WHERE article_id = ?");
        $stmt->execute([$id]);
        $photos = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Récupérer aussi la photo principale de l'article (si elle existe)
        $stmt = $pdo->prepare("SELECT photo FROM articles WHERE id = ?");
        $stmt->execute([$id]);
        $article = $stmt->fetch();
        
        // Supprimer les photos de la galerie de la base de données
        $stmt = $pdo->prepare("DELETE FROM article_photos WHERE article_id = ?");
        $stmt->execute([$id]);
        
        // Supprimer l'article de la base de données (les détails de commande resteront grâce à ON DELETE CASCADE)
        $stmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
        $stmt->execute([$id]);
        
        $pdo->commit();
        
        // Supprimer les fichiers physiques des photos (uniquement les uploads locaux)
        $deleted_files = 0;
        
        // Supprimer toutes les photos de la galerie
        foreach ($photos as $photo_url) {
            if (!empty($photo_url) && strpos($photo_url, 'uploads/') === 0 && file_exists($photo_url)) {
                if (unlink($photo_url)) {
                    $deleted_files++;
                }
            }
        }
        
        // Supprimer la photo principale si elle est locale
        if ($article && !empty($article['photo'])) {
            if (strpos($article['photo'], 'uploads/') === 0 && file_exists($article['photo'])) {
                if (unlink($article['photo'])) {
                    $deleted_files++;
                }
            }
        }
        
        $_SESSION['success_message'] = 'Article supprimé avec succès ! (' . $deleted_files . ' fichier(s) supprimé(s))';
        header('Location: admin_dashboard.php');
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = 'Erreur lors de la suppression : ' . $e->getMessage();
        header('Location: admin_dashboard.php');
        exit;
    }
} else {
    $_SESSION['error_message'] = 'Aucun article spécifié pour la suppression';
    header('Location: admin_dashboard.php');
    exit;
}
?>