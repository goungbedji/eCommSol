<?php
// Configuration de la base de données
define('DB_HOST', 'sql203.infinityfree.com');
define('DB_USER', 'if0_40340701');
define('DB_PASS', 'tpWUtKrSLpVV');
define('DB_NAME', 'if0_40340701_ecommerce_db');

// Configuration des emails
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'taboutique12@gmail.com');
define('SMTP_PASS', 'llaechikhdtfhhoj');

// Dossier pour les uploads
define('UPLOAD_DIR', 'uploads/');

// Activer les erreurs en développement, désactiver en production
// NOTE: En production, display_errors doit être 0. Garder log_errors activé.
// Pour basculer entre 'development' et 'production', définissez une variable d'environnement
if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
}
// Activer la journalisation des erreurs dans un fichier local pour consultation côté serveur
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . DIRECTORY_SEPARATOR . 'php-error.log');

// Taille maximale autorisée pour les uploads (en octets). 5MB par défaut.
if (!defined('MAX_UPLOAD_SIZE')) {
    define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024);
}

// Créer le dossier uploads s'il n'existe pas (chemin absolu pour éviter les problèmes de CWD)
$uploadDirPath = __DIR__ . DIRECTORY_SEPARATOR . UPLOAD_DIR;
if (!file_exists($uploadDirPath)) {
    mkdir($uploadDirPath, 0777, true);
}

// Inclure les fonctions de formatage des prix (chemin absolu et fallback si manquant)
$priceFormatPath = __DIR__ . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'price_format.php';
if (file_exists($priceFormatPath)) {
    require_once $priceFormatPath;
} else {
    // Log clair pour l'hébergeur et éviter l'erreur fatale
    error_log("Fichier manquant: includes/price_format.php (attendu: $priceFormatPath)");
    // Définir un fallback minimal pour que l'application continue de fonctionner
    if (!function_exists('formatPriceWithPromo')) {
        function formatPriceWithPromo($article) {
            $price = isset($article['prix']) ? $article['prix'] : 0;
            return number_format($price, 0, ',', ' ') . ' FCFA';
        }
    }
}

// Connexion à la base de données
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Fonction pour envoyer un email à l'admin
function sendAdminNotification($subject, $message) {
    global $pdo;
    
    // Charger l'autoloader si présent (chemin absolu). Si absent, logguer et échouer proprement.
    $vendorAutoload = __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
    if (file_exists($vendorAutoload)) {
        require_once $vendorAutoload;
    } else {
        error_log("Autoloader manquant: $vendorAutoload. Impossible d'envoyer des emails sans PHPMailer.");
        return false;
    }

    // Récupérer l'email de l'admin
    $stmt = $pdo->query("SELECT email FROM admins LIMIT 1");
    $admin = $stmt->fetch();
    
    if (!$admin || !$admin['email']) {
        error_log("Aucun email d'administrateur trouvé");
        return false;
    }

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // Debug helpers: write SMTP conversation to PHP error log (0 = off, 2 = client & server)
        // Set to 2 temporarily while debugging; set back to 0 in production.
        $mail->SMTPDebug = 0;
        $mail->Debugoutput = function($str, $level) {
            error_log("PHPMailer debug (level $level): $str");
        };

        // Some hosts have TLS verification issues; for debugging we allow self-signed.
        // You can remove or tighten these options in production.
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
        ];

        // Configuration du serveur
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';

        // Destinataire et expéditeur
        $mail->setFrom(SMTP_USER, 'E-commerce Notification');
        $mail->addAddress($admin['email']);

        // Contenu
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log both PHPMailer's ErrorInfo and the exception message for clarity
        error_log("Erreur d'envoi d'email: " . $mail->ErrorInfo . ' | Exception: ' . $e->getMessage());
        return false;
    }
}

// Sécuriser les cookies de session puis démarrer la session
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
if (PHP_VERSION_ID >= 70300) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
} else {
    // Fallback compatible : définir quelques options via ini et session_set_cookie_params
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', $secure ? 1 : 0);
    // Note: session.cookie_samesite may not be supported on older PHP
    @session_set_cookie_params(0, '/', isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '', $secure, true);
}
session_start();

// Fonction pour vérifier si l'admin est connecté
function isAdmin() {
    return isset($_SESSION['admin_id']);
}

// Fonction pour rediriger si non connecté
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: admin_login.php');
        exit;
    }
}

// Fonction pour nettoyer les entrées
function clean($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Fonction pour formater le prix en FCFA
function formatPrice($price) {
    return number_format($price, 0, ',', ' ') . ' FCFA';
}

// Fonction pour récupérer les paramètres de la boutique
function getBoutiqueSettings() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT * FROM boutique_settings LIMIT 1");
        $settings = $stmt->fetch();
        // Si pas de paramètres, retourner des valeurs par défaut
        if (!$settings) {
            return [
                'nom_boutique' => 'Ma Boutique E-Commerce',
                'slogan' => 'Votre partenaire shopping',
                'email_boutique' => 'contact@maboutique.com',
                'whatsapp_boutique' => '+229 97 00 00 00',
                'adresse_boutique' => '',
                'ville' => 'Cotonou',
                'pays' => 'Bénin',
                'description_boutique' => ''
            ];
        }
        
        return $settings;
    } catch (Exception $e) {
        return [
            'nom_boutique' => 'Ma Boutique E-Commerce',
            'slogan' => 'Votre partenaire shopping',
            'email_boutique' => 'contact@maboutique.com',
            'whatsapp_boutique' => '+229 97 00 00 00',
            'adresse_boutique' => '',
            'ville' => 'Cotonou',
            'pays' => 'Bénin',
            'description_boutique' => ''
        ];
    }
}

// Fonction pour récupérer les infos de l'admin connecté
function getAdminInfo() {
    global $pdo;
    if (!isset($_SESSION['admin_id'])) {
        return null;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        return $stmt->fetch();
    } catch (Exception $e) {
        return null;
    }
}

// Retourne la promotion active applicable à un article (priorité au produit, sinon catégorie)
function getActivePromotionForArticle($article) {
    global $pdo;
    $now = date('Y-m-d H:i:s');

    // Vérifier promotion produit
    try {
        $stmt = $pdo->prepare("SELECT * FROM promotions WHERE product_id = ? AND active = 1 AND (start_date IS NULL OR start_date <= ?) AND (end_date IS NULL OR end_date >= ?) LIMIT 1");
        $stmt->execute([$article['id'], $now, $now]);
        $promo = $stmt->fetch();
        if ($promo) return $promo;
    } catch (Exception $e) {
        // ignore
    }

    // Vérifier promotion par catégorie (si categorie_id existe)
    try {
        if (isset($article['categorie_id']) && $article['categorie_id']) {
            $stmt = $pdo->prepare("SELECT * FROM promotions WHERE category_id = ? AND active = 1 AND (start_date IS NULL OR start_date <= ?) AND (end_date IS NULL OR end_date >= ?) LIMIT 1");
            $stmt->execute([$article['categorie_id'], $now, $now]);
            $promo = $stmt->fetch();
            if ($promo) return $promo;
        }
    } catch (Exception $e) {
        // ignore
    }

    // Fallback : si ancienne colonne `categorie` (texte) existe
    if (isset($article['categorie']) && $article['categorie']) {
        try {
            $stmt = $pdo->prepare("SELECT p.* FROM promotions p JOIN categories c ON p.category_id = c.id WHERE c.nom = ? AND p.active = 1 AND (p.start_date IS NULL OR p.start_date <= ?) AND (p.end_date IS NULL OR p.end_date >= ?) LIMIT 1");
            $stmt->execute([trim($article['categorie']), $now, $now]);
            $promo = $stmt->fetch();
            if ($promo) return $promo;
        } catch (Exception $e) {
            // ignore
        }
    }

    return null;
}

// Appliquer une promotion sur un article et retourner les prix
function applyPromotionToArticle($article) {
    $result = [
        'original_price' => (float)$article['prix'],
        'price' => (float)$article['prix'],
        'discount_percent' => 0,
        'promo_label' => null
    ];

    $promo = getActivePromotionForArticle($article);
    if (!$promo) return $result;

    if ($promo['discount_type'] === 'percent') {
        $percent = (float)$promo['discount_value'];
        $price = $result['original_price'] * (1 - $percent / 100);
        $result['price'] = round($price, 2);
        $result['discount_percent'] = $percent;
    } else {
        $fixed = (float)$promo['discount_value'];
        $price = max(0, $result['original_price'] - $fixed);
        $result['price'] = round($price, 2);
        $result['discount_percent'] = $result['original_price'] > 0 ? round((($result['original_price'] - $price) / $result['original_price']) * 100, 2) : 0;
    }

    $result['promo_label'] = $promo['label'] ?: ($promo['discount_type'] === 'percent' ? ('-' . rtrim(rtrim(number_format($promo['discount_value'],2), '0'), '.') . '%') : ('-' . number_format($promo['discount_value'], 0) . '')); 
    return $result;
}

// Valider un code coupon
function validateCouponCode($code) {
    global $pdo;
    $now = date('Y-m-d H:i:s');
    try {
        $stmt = $pdo->prepare("SELECT * FROM coupons WHERE code = ? AND active = 1 AND (start_date IS NULL OR start_date <= ?) AND (end_date IS NULL OR end_date >= ?) LIMIT 1");
        $stmt->execute([$code, $now, $now]);
        $coupon = $stmt->fetch();
        if (!$coupon) return null;
        if ($coupon['usage_limit'] !== null && $coupon['used_count'] >= $coupon['usage_limit']) return null;
        return $coupon;
    } catch (Exception $e) {
        return null;
    }
}

// Incrémenter l'utilisation d'un coupon
function incrementCouponUsage($code) {
    global $pdo;
    try {
        // Incrémenter correctement les deux compteurs de façon atomique
        $stmt = $pdo->prepare("UPDATE coupons SET used_count = used_count + 1, times_used = times_used + 1 WHERE code = ?");
        return $stmt->execute([$code]);
    } catch (Exception $e) {
        return false;
    }
}

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
?>