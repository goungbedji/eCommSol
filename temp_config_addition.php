// Fonction pour récupérer un article par son ID
function getArticle($id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch (Exception $e) {
        return null;
    }
}