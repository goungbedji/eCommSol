# 🛍️ E-Commerce PHP - Documentation Complète

**Projet :** Plateforme de E-Commerce PHP avec gestion d'articles, promotions, coupons et commandes  
**Stack :** PHP 7.4+, MySQL, Tailwind CSS, JavaScript Vanilla  
**Statut :** Production-ready avec recommandations de sécurité  

---

## 📋 Table des Matières

1. [Architecture](#architecture)
2. [Installation et Configuration](#installation-et-configuration)
3. [Structure du Projet](#structure-du-projet)
4. [Frontend - Interfaces Utilisateur](#frontend---interfaces-utilisateur)
5. [Backend - Fonctions PHP](#backend---fonctions-php)
6. [Base de Données](#base-de-données)
7. [Flux de Fonctionnement](#flux-de-fonctionnement)
8. [API Endpoints](#api-endpoints)
9. [Gestion Admin](#gestion-admin)
10. [Sécurité](#sécurité)
11. [Déploiement](#déploiement)

---

## 🏗️ Architecture

### Vue générale
```
┌──────────────────────────────────────────────────────────────┐
│                    NAVIGATEUR (Client)                        │
│  - Pages Front (index, product, search, cart, checkout)      │
│  - Admin Panel (articles, coupons, promotions, commandes)    │
└────────────────────┬─────────────────────────────────────────┘
                     │ HTTP/HTTPS
┌────────────────────▼─────────────────────────────────────────┐
│                  PHP APPLICATION                              │
│  ├─ config.php (Configuration + Fonctions globales)          │
│  ├─ includes/ (Headers, footers, prix)                       │
│  ├─ Pages Front (index.php, product.php, search.php)        │
│  ├─ Pages Admin (admin_*.php)                                │
│  └─ API (api/search.php, get_articles.php)                  │
└────────────────────┬─────────────────────────────────────────┘
                     │ SQL
┌────────────────────▼─────────────────────────────────────────┐
│               MySQL DATABASE                                  │
│  ├─ articles (produits)                                      │
│  ├─ categories (catégories)                                  │
│  ├─ promotions (réductions)                                  │
│  ├─ coupons (codes promo)                                    │
│  ├─ commandes (orders)                                       │
│  ├─ admins (users administrateur)                            │
│  └─ boutique_settings (config magasin)                       │
└──────────────────────────────────────────────────────────────┘
```

### Stack Technologique
- **Backend :** PHP 7.4+ avec PDO (MySQL)
- **Frontend :** HTML5 + Tailwind CSS (JIT compilation via CDN)
- **Templating :** PHP natif (pas de framework)
- **Email :** PHPMailer 7.0 (SMTP Gmail)
- **Gestion des dépendances :** Composer

---

## 🚀 Installation et Configuration

### Prérequis
- PHP 7.4+ (recommandé 8.0+)
- MySQL 5.7+ ou MariaDB 10.3+
- Composer
- Apache avec `.htaccess` ou Nginx
- SMTP accessible (pour emails)

### Étapes d'installation

1. **Cloner le projet**
   ```bash
   git clone https://github.com/goungbedji/eCommSol.git
   cd ecommerce
   ```

2. **Installer les dépendances**
   ```bash
   composer install
   ```

3. **Créer la base de données**
   ```bash
   mysql -u root < database.sql
   ```

4. **Configurer les variables d'environnement**
   - Copier `config.php` et remplir les constantes :
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'ecommerce_db');
     define('SMTP_USER', 'votre@email.com');
     define('SMTP_PASS', 'votre_mot_de_passe');
     define('UPLOAD_DIR', 'uploads/');
     ```

5. **Définir les permissions**
   ```bash
   chmod 755 uploads/
   chmod 644 php-error.log
   ```

6. **Accéder à l'application**
   - Frontend : http://localhost/ecommerce/
   - Admin : http://localhost/ecommerce/admin_login.php
   - Credentials par défaut : `admin` / (voir `database.sql`)

---

## 📁 Structure du Projet

```
ecommerce/
├── admin_*.php                 # Pages d'administration (articles, coupons, etc.)
├── index.php                   # Page d'accueil (catalogue produits)
├── product.php                 # Détail d'un produit
├── search.php                  # Recherche de produits
├── cart.php                    # Panier
├── checkout.php                # Commande/paiement
├── home.php                    # Variante page d'accueil
├── config.php                  # Configuration + fonctions globales
├── database.sql                # Schéma BD initial
├── composer.json               # Dépendances PHP
├── IMPROVEMENTS.md             # Suggestions d'amélioration
├── includes/
│   ├── head.php               # <head> HTML (CSS, fonts)
│   ├── header.php             # Navigation front
│   ├── footer.php             # Pied de page
│   ├── admin_head.php         # <head> admin
│   ├── admin_header.php       # Navigation admin
│   ├── price_format.php       # Formatage prix avec promos
│   └── sections/              # Sections réutilisables
│       ├── hero.php
│       ├── products.php
│       ├── categories.php
│       └── features.php
├── css/
│   ├── style.css              # Styles personnalisés
│   ├── desktop.css            # Responsive desktop
│   ├── mobile.css             # Responsive mobile
│   └── themes.css             # Thèmes
├── uploads/                    # Dossier images uploadées
│   └── .htaccess              # Restrictions serveur
├── api/
│   ├── search.php             # API recherche
│   └── uploads/               # Images API
├── migrations/                 # Scripts migration DB
│   ├── 2025-11-03_add_coupons.sql
│   ├── 2025-11-03_add_promotions.sql
│   ├── 2025-11-03_add_stock_history.sql
│   ├── 2025-11-03_migrate_categories.sql
│   └── 2025-11-04_add_theme_column.sql
├── vendor/                     # Dépendances Composer (PHPMailer)
└── logs/                       # Fichiers log (créé à runtime)
```

---

## 🎨 Frontend - Interfaces Utilisateur

### 1. Page d'Accueil (`index.php`)

**Objectif :** Afficher le catalogue de produits avec filtrage par catégorie.

**Fonctionnalités :**
- ✅ Affichage grille responsive (1-4 colonnes selon écran)
- ✅ Filtrage par catégorie (boutons dynamiques)
- ✅ Recherche en temps réel
- ✅ Affichage promotions (badge rouge + prix barré)
- ✅ Statut stock (badge orange/rouge si peu de stock)
- ✅ Bouton "Ajouter au panier" AJAX
- ✅ Responsive design Tailwind

**Éléments clés :**
```php
// Récupération articles avec promotions
foreach ($articles as &$a) {
    $promo = applyPromotionToArticle($a);
    $a['original_price'] = $promo['original_price'];
    $a['prix'] = $promo['price'];
    $a['promo_label'] = $promo['promo_label'];
}
```

**Affichage promo :**
- Badge : `<?= $article['promo_label'] ?>` (ex: "promo", "-15%")
- Prix : Via fonction `formatPriceWithPromo()` (prix barré + réduit)

---

### 2. Détail Produit (`product.php`)

**Objectif :** Afficher tous les détails d'un produit avec images et vidéo.

**Fonctionnalités :**
- ✅ Galerie images (main + thumbnails)
- ✅ Modal zoom sur images
- ✅ Vidéo YouTube intégrée (si présente)
- ✅ Quantité sélectionnable (+/-)
- ✅ Prix avec promotion appliquée
- ✅ Statut stock (bouton Ajouter ou "Rupture")
- ✅ Breadcrumb navigation

**Code clé :**
```php
// Récupération photos de l'article
$stmt = $pdo->prepare("SELECT * FROM article_photos WHERE article_id = ? ORDER BY is_main DESC");
$stmt->execute([$id]);
$photos = $stmt->fetchAll();

// Application promo
$promoInfo = applyPromotionToArticle($article);
$displayPrice = $promoInfo['price'];
```

---

### 3. Recherche (`search.php`)

**Objectif :** Chercher des produits par terme (titre, description, catégorie).

**Fonctionnalités :**
- ✅ Recherche SQL avec LIKE préparée (injection-safe)
- ✅ Tri par pertinence (titre > catégorie > description)
- ✅ Minimum 2 caractères
- ✅ Affichage nombre résultats

**Requête SQL :**
```sql
SELECT * FROM articles 
WHERE stock > 0 
AND (titre LIKE ? OR description LIKE ? OR categorie LIKE ?)
ORDER BY CASE 
    WHEN titre LIKE ? THEN 1
    WHEN categorie LIKE ? THEN 2
    ELSE 3
END, date_creation DESC
```

---

### 4. Panier (`cart.php`)

**Objectif :** Afficher et modifier le panier (localStorage côté client).

**Fonctionnalités :**
- ✅ Stockage JSON dans `localStorage` (pas de sessions)
- ✅ Récupération articles via API `get_articles.php`
- ✅ Modification quantités (+/-)
- ✅ Suppression articles
- ✅ Calcul total avec promotions
- ✅ Code coupon (champ input)
- ✅ Bouton "Commander"

**JavaScript clé :**
```javascript
// Ajouter au panier
let cart = JSON.parse(localStorage.getItem('cart')) || [];
cart.push({ id: articleId, quantity: qty });
localStorage.setItem('cart', JSON.stringify(cart));

// Afficher panier
fetch('get_articles.php?ids=' + cartIds)
    .then(r => r.json())
    .then(articles => { /* afficher */ });
```

---

### 5. Commande (`checkout.php`)

**Objectif :** Formulaire de commande et confirmation.

**Fonctionnalités :**
- ✅ Formulaire contact (nom, email, WhatsApp, adresse, ville)
- ✅ Code coupon optionnel (validation + application réduction)
- ✅ Résumé articles + total
- ✅ Sauvegarde en BD (`commandes` + `details_commande`)
- ✅ Email de confirmation admin + client
- ✅ Historique stock (log `stock_history`)

**Processus :**
```php
// 1. Valider coupon
$coupon = validateCouponCode($_POST['coupon_code']);
if ($coupon) {
    $discount = calculateDiscount($coupon, $total);
    $total -= $discount;
}

// 2. Créer commande
$stmt = $pdo->prepare("INSERT INTO commandes (nom_client, email, ...) VALUES (...)");
$stmt->execute([...]);
$commande_id = $pdo->lastInsertId();

// 3. Ajouter détails articles
foreach ($cart as $item) {
    $stmt = $pdo->prepare("INSERT INTO details_commande (commande_id, article_id, quantite, prix_unitaire) VALUES (...)");
    $stmt->execute([$commande_id, $item['id'], $item['qty'], $price]);
    
    // Réduire stock
    $stmt = $pdo->prepare("UPDATE articles SET stock = stock - ? WHERE id = ?");
    $stmt->execute([$item['qty'], $item['id']]);
    
    // Logger mouvement
    logStockHistory($item['id'], 'commande', $item['qty']);
}

// 4. Envoyer email
sendAdminNotification("Nouvelle commande #$commande_id", $message);
```

---

## ⚙️ Backend - Fonctions PHP

Toutes les fonctions principales sont dans `config.php`. Voici les plus critiques :

### 1. Gestion Promotions

#### `getActivePromotionForArticle($article)`
Retourne la promotion active pour un article (priorité : produit > catégorie).

```php
function getActivePromotionForArticle($article) {
    global $pdo;
    $now = date('Y-m-d H:i:s');
    
    // 1. Chercher promo sur ce produit
    $stmt = $pdo->prepare("SELECT * FROM promotions 
        WHERE product_id = ? AND active = 1 
        AND (start_date IS NULL OR start_date <= ?)
        AND (end_date IS NULL OR end_date >= ?)
        LIMIT 1");
    $stmt->execute([$article['id'], $now, $now]);
    if ($promo = $stmt->fetch()) return $promo;
    
    // 2. Sinon, chercher sur sa catégorie
    if (isset($article['categorie_id']) && $article['categorie_id']) {
        $stmt = $pdo->prepare("SELECT * FROM promotions 
            WHERE category_id = ? AND active = 1 ...LIMIT 1");
        $stmt->execute([$article['categorie_id'], $now, $now]);
        if ($promo = $stmt->fetch()) return $promo;
    }
    
    return null;
}
```

#### `applyPromotionToArticle($article)`
Calcule le prix réduit et le pourcentage de remise.

```php
function applyPromotionToArticle($article) {
    $result = [
        'original_price' => (float)$article['prix'],
        'price' => (float)$article['prix'],
        'discount_percent' => 0,
        'promo_label' => null
    ];
    
    $promo = getActivePromotionForArticle($article);
    if (!$promo) return $result;
    
    // Calcul réduction
    if ($promo['discount_type'] === 'percent') {
        $percent = (float)$promo['discount_value'];
        $price = $result['original_price'] * (1 - $percent / 100);
        $result['price'] = round($price, 2);
        $result['discount_percent'] = $percent;
    } else {
        $fixed = (float)$promo['discount_value'];
        $price = max(0, $result['original_price'] - $fixed);
        $result['price'] = round($price, 2);
        $result['discount_percent'] = round((($result['original_price'] - $price) / $result['original_price']) * 100, 2);
    }
    
    // Label (custom ou auto-généré)
    $result['promo_label'] = $promo['label'] ?: 
        ($promo['discount_type'] === 'percent' ? 
            '-' . $promo['discount_value'] . '%' : 
            '-' . number_format($promo['discount_value'], 0));
    
    return $result;
}
```

### 2. Gestion Coupons

#### `validateCouponCode($code)`
Vérifie si un coupon est valide et disponible.

```php
function validateCouponCode($code) {
    global $pdo;
    $now = date('Y-m-d H:i:s');
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM coupons 
            WHERE code = ? AND active = 1 
            AND (start_date IS NULL OR start_date <= ?)
            AND (end_date IS NULL OR end_date >= ?)
            LIMIT 1");
        $stmt->execute([$code, $now, $now]);
        $coupon = $stmt->fetch();
        
        if (!$coupon) return null;
        
        // Vérifier limite d'usage
        if ($coupon['usage_limit'] !== null && $coupon['used_count'] >= $coupon['usage_limit']) 
            return null;
        
        return $coupon;
    } catch (Exception $e) {
        return null;
    }
}
```

#### `incrementCouponUsage($code)`
Incrémente le compteur d'utilisation du coupon.

```php
function incrementCouponUsage($code) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("UPDATE coupons 
            SET used_count = used_count + 1, times_used = times_used + 1 
            WHERE code = ?");
        return $stmt->execute([$code]);
    } catch (Exception $e) {
        return false;
    }
}
```

### 3. Gestion Admin

#### `isAdmin()`
Vérifie si l'utilisateur courant est admin.

```php
function isAdmin() {
    return isset($_SESSION['admin_id']);
}
```

#### `requireAdmin()`
Redirige vers login si non-admin.

```php
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: admin_login.php');
        exit;
    }
}
```

#### `getAdminInfo()`
Récupère les infos de l'admin connecté.

```php
function getAdminInfo() {
    global $pdo;
    if (!isset($_SESSION['admin_id'])) return null;
    
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    return $stmt->fetch();
}
```

### 4. Configuration Boutique

#### `getBoutiqueSettings()`
Récupère les paramètres du magasin (nom, slogan, email, etc.).

```php
function getBoutiqueSettings() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT * FROM boutique_settings LIMIT 1");
        $settings = $stmt->fetch();
        
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
        return [...]; // Valeurs par défaut
    }
}
```

### 5. Formatage et Validation

#### `clean($data)`
Nettoie une chaîne (échappe HTML, supprime balises).

```php
function clean($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
```

#### `formatPrice($price)`
Formate un prix en FCFA.

```php
function formatPrice($price) {
    return number_format($price, 0, ',', ' ') . ' FCFA';
}
```

#### `formatPriceWithPromo($article)` (dans `includes/price_format.php`)
Formate prix avec promo (prix barré + réduit).

```php
function formatPriceWithPromo($article) {
    if (isset($article['original_price']) && isset($article['promo_label'])) {
        $original = (float)$article['original_price'];
        $reduced = (float)$article['prix'];
    } else {
        $promo = applyPromotionToArticle($article);
        $original = $promo['original_price'];
        $reduced = $promo['price'];
    }
    
    $html = '<div class="flex flex-col gap-1">';
    
    if ($reduced < $original) {
        $html .= '<span class="text-sm text-gray-500 line-through">' . formatPrice($original) . '</span>';
        $html .= '<span class="text-lg font-bold text-primary">' . formatPrice($reduced) . '</span>';
    } else {
        $html .= '<span class="text-lg font-bold text-gray-900">' . formatPrice($reduced) . '</span>';
    }
    
    $html .= '</div>';
    return $html;
}
```

### 6. Email

#### `sendAdminNotification($subject, $message)`
Envoie un email à l'admin via SMTP/PHPMailer.

```php
function sendAdminNotification($subject, $message) {
    global $pdo;
    
    require_once __DIR__ . '/vendor/autoload.php';
    
    // Récupérer email admin
    $stmt = $pdo->query("SELECT email FROM admins LIMIT 1");
    $admin = $stmt->fetch();
    if (!$admin) return false;
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        
        $mail->setFrom(SMTP_USER, 'E-commerce Notification');
        $mail->addAddress($admin['email']);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;
        
        return $mail->send();
    } catch (Exception $e) {
        error_log("Email error: " . $e->getMessage());
        return false;
    }
}
```

---

## 🗄️ Base de Données

### Schéma Principal

#### `articles` - Produits
```sql
CREATE TABLE articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    prix DECIMAL(10,2) NOT NULL,
    photo VARCHAR(500),
    video VARCHAR(500),
    stock INT DEFAULT 0,
    categorie_id INT,
    categorie VARCHAR(255),        -- Ancien champ, pour compatibilité
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categorie_id) REFERENCES categories(id) ON DELETE SET NULL
);
```

#### `article_photos` - Photos multiples
```sql
CREATE TABLE article_photos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    article_id INT NOT NULL,
    photo_url VARCHAR(1000) NOT NULL,
    is_main TINYINT(1) DEFAULT 0,
    ordre INT DEFAULT 0,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE
);
```

#### `categories` - Catégories
```sql
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### `promotions` - Réductions
```sql
CREATE TABLE promotions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT DEFAULT NULL,        -- Promo sur 1 produit
    category_id INT DEFAULT NULL,       -- OU sur toute une catégorie
    discount_type ENUM('percent','fixed') NOT NULL,  -- % ou montant
    discount_value DECIMAL(10,2) NOT NULL,
    label VARCHAR(100) DEFAULT NULL,    -- Label custom (ex: "Black Friday")
    start_date DATETIME DEFAULT NULL,
    end_date DATETIME DEFAULT NULL,
    active TINYINT(1) DEFAULT 1,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES articles(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);
```

#### `coupons` - Codes promo
```sql
CREATE TABLE coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    type ENUM('percentage','fixed') NOT NULL,
    value DECIMAL(10,2) NOT NULL,
    max_uses INT DEFAULT NULL,          -- Limite globale d'usage
    usage_limit INT DEFAULT NULL,       -- Alias de max_uses
    times_used INT DEFAULT 0,           -- Fois utilisé
    used_count INT DEFAULT 0,           -- Alias de times_used
    min_purchase DECIMAL(10,2) DEFAULT NULL,  -- Montant minimum panier
    start_date DATETIME DEFAULT NULL,
    expiry_date DATETIME DEFAULT NULL,
    end_date DATETIME DEFAULT NULL,     -- Alias de expiry_date
    active TINYINT(1) DEFAULT 1,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### `commandes` - Commandes
```sql
CREATE TABLE commandes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_client VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    whatsapp VARCHAR(50) NOT NULL,
    adresse TEXT NOT NULL,
    ville VARCHAR(100) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    coupon_code VARCHAR(50),
    discount_amount DECIMAL(10,2) DEFAULT 0,
    statut ENUM('en_attente','confirmee','expediee','livree','annulee') DEFAULT 'en_attente',
    date_commande TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### `details_commande` - Lignes de commande
```sql
CREATE TABLE details_commande (
    id INT AUTO_INCREMENT PRIMARY KEY,
    commande_id INT NOT NULL,
    article_id INT NOT NULL,
    quantite INT NOT NULL,
    prix_unitaire DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (commande_id) REFERENCES commandes(id) ON DELETE CASCADE,
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE
);
```

#### `stock_history` - Historique mouvement stock
```sql
CREATE TABLE stock_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    article_id INT NOT NULL,
    type ENUM('entree','sortie','commande','manuel') NOT NULL,
    quantite INT NOT NULL,
    commentaire VARCHAR(255),
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE
);
```

#### `admins` - Administrateurs
```sql
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,     -- bcrypt hash
    nom VARCHAR(100),
    prenom VARCHAR(100),
    email VARCHAR(255) NOT NULL,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### `boutique_settings` - Configuration
```sql
CREATE TABLE boutique_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_boutique VARCHAR(255) DEFAULT 'Ma Boutique E-Commerce',
    slogan VARCHAR(255),
    email_boutique VARCHAR(255),
    whatsapp_boutique VARCHAR(50),
    adresse_boutique VARCHAR(255),
    ville VARCHAR(100),
    pays VARCHAR(100),
    description_boutique TEXT,
    theme VARCHAR(50) DEFAULT 'theme-default',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## 🔄 Flux de Fonctionnement

### Flux 1 : Achat d'un Article

```
1. Client visite index.php
   ↓
2. Articles chargés + promotions appliquées
   ├─ applyPromotionToArticle() pour chaque article
   ├─ Données stockées dans $article[original_price, prix, promo_label]
   ↓
3. Client clique sur un produit → product.php?id=X
   ↓
4. Photos chargées de article_photos
   ↓
5. Client sélectionne quantité + clique "Ajouter au panier"
   ├─ JavaScript : localStorage['cart'] = [{id, qty}, ...]
   ↓
6. Client va au panier (cart.php)
   ├─ Récupère articles via get_articles.php?ids=1,2,3
   ├─ Affiche articles avec prix (promotions incluses)
   ↓
7. Client entre coupon (optionnel)
   ├─ validateCouponCode() vérifie
   ├─ Calcule réduction supplémentaire
   ↓
8. Client clique "Commander" → checkout.php
   ├─ Formulaire : nom, email, adresse, ville, coupon
   ↓
9. Soumission
   ├─ INSERT INTO commandes (nom_client, email, ...)
   ├─ FOR EACH article_in_cart:
   │   ├─ INSERT INTO details_commande
   │   ├─ UPDATE articles SET stock = stock - qty
   │   ├─ INSERT INTO stock_history (mouvement "commande")
   ├─ IF coupon: incrementCouponUsage(code)
   ├─ sendAdminNotification() → Email admin
   ↓
10. Redirection page de confirmation + email client
```

### Flux 2 : Ajout Article par Admin

```
1. Admin visite admin_dashboard.php
   ├─ requireAdmin() vérifie session
   ↓
2. Admin clique "+ Ajouter un article" → admin_add_article.php
   ↓
3. Formulaire :
   ├─ Titre, description, prix, stock, catégorie
   ├─ Photo principale (URL)
   ├─ Photos supplémentaires (URLs)
   ├─ Upload de photos (local)
   ├─ Vidéo YouTube (URL)
   ↓
4. Soumission (POST)
   ├─ Validation inputs (clean())
   ├─ Upload images : move_uploaded_file() + getimagesize() + MIME check
   ├─ INSERT INTO articles (titre, description, ...)
   ├─ FOR EACH photo_url: INSERT INTO article_photos
   ↓
5. Redirection admin_dashboard.php avec succès
```

### Flux 3 : Création Promotion

```
1. Admin visite admin_promotions.php
   ↓
2. Admin clique "+ Ajouter une promotion" → admin_promotion_add.php
   ↓
3. Formulaire :
   ├─ Type : Article spécifique OU Catégorie
   ├─ Réduction : % OU Montant fixe
   ├─ Label (optionnel, sinon auto-généré)
   ├─ Dates : start_date + end_date
   ├─ Actif : checkbox
   ↓
4. INSERT INTO promotions
   ├─ IF product_id = X: promo sur 1 article
   ├─ IF category_id = Y: promo sur toute une catégorie
   ↓
5. À chaque affichage de produit :
   ├─ getActivePromotionForArticle() vérifie :
   │  ├─ active = 1
   │  ├─ NOW() BETWEEN start_date ET end_date
   ├─ applyPromotionToArticle() calcule le prix réduit
   ├─ formatPriceWithPromo() affiche prix barré + réduit + badge
```

---

## 🔌 API Endpoints

### `get_articles.php?ids=1,2,3`
**Méthode :** GET  
**Rôle :** Récupère les détails de plusieurs articles (pour le panier).  

**Réponse :**
```json
[
  {
    "id": 1,
    "titre": "Smartphone Pro X",
    "prix": 450000,
    "stock": 15,
    "original_price": 450000,
    "promo_label": null,
    "promo_percent": 0
  },
  {
    "id": 3,
    "titre": "Montre Connectée Sport",
    "prix": 35000,
    "stock": 21,
    "original_price": 45000,
    "promo_label": "promo",
    "promo_percent": 22.22
  }
]
```

### `api/search.php?q=laptop`
**Méthode :** GET  
**Rôle :** Recherche d'articles avec suggestions.

**Réponse :**
```json
[
  {
    "id": 2,
    "titre": "Laptop Ultra 15",
    "description": "Ordinateur portable performant..."
  }
]
```

---

## 👨‍💼 Gestion Admin

### Pages Admin

| Page | URL | Rôle |
|------|-----|------|
| Dashboard | `admin_dashboard.php` | Vue d'ensemble (stats articles, commandes, etc.) |
| Articles | `admin_articles.php` | Liste articles avec edit/delete |
| Ajouter Article | `admin_add_article.php` | Création article |
| Éditer Article | `admin_edit_article.php?id=X` | Modification article |
| Supprimer Article | `admin_delete_article.php?id=X` | Suppression (avec confirmation) |
| Catégories | `admin_categories.php` | Gestion catégories |
| Ajouter Catégorie | `admin_category_add.php` | Création catégorie |
| Éditer Catégorie | `admin_category_edit.php?id=X` | Modification catégorie |
| Promotions | `admin_promotions.php` | Liste promotions |
| Ajouter Promo | `admin_promotion_add.php` | Création promotion |
| Éditer Promo | `admin_promotion_edit.php?id=X` | Modification promotion |
| Coupons | `admin_coupons.php` | Liste coupons (refactorisé Tailwind) |
| Ajouter Coupon | `admin_coupon_add.php` | Création coupon |
| Éditer Coupon | `admin_coupon_edit.php?id=X` | Modification coupon |
| Commandes | `admin_commandes.php` | Liste commandes |
| Détails Commande | `admin_commande_details.php?id=X` | Vue complète d'une commande |
| Historique Stock | `admin_stock_history.php` | Logs mouvements stock |
| Paramètres | `admin_settings.php` | Config boutique (nom, email, etc.) |
| Login | `admin_login.php` | Authentification admin |
| Logout | `admin_logout.php` | Déconnexion |

### Authentification

**Processus :**
1. Admin accède `admin_login.php`
2. Saisit `username` + `password`
3. Vérification : `password_verify($pwd, $hash)` (bcrypt)
4. Session créée : `$_SESSION['admin_id'] = $admin['id']`
5. Redirection `admin_dashboard.php`

**Protection :** Fonction `requireAdmin()` appelée en haut de chaque page admin → redirection login si non-connecté.

---

## 🔒 Sécurité

### ✅ Mesures Implémentées

1. **Prepared Statements (PDO)**
   ```php
   $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
   $stmt->execute([$id]);  // Pas de concaténation SQL
   ```

2. **Protection XSS**
   ```php
   echo htmlspecialchars($user_input);  // Échappe HTML
   ```

3. **Input Validation**
   ```php
   $id = intval($_GET['id']);  // Cast en entier
   $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
   ```

4. **File Upload Validation**
   ```php
   $check = @getimagesize($tmp_file);  // Vérifie vraiment une image
   $mime = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $tmp_file);
   if (!in_array($mime, ['image/jpeg', 'image/png'])) { /* Rejeter */ }
   ```

5. **Session Sécurisée**
   ```php
   session_set_cookie_params([
       'secure' => $https,
       'httponly' => true,
       'samesite' => 'Lax'
   ]);
   ```

6. **Logs Erreurs**
   ```php
   ini_set('display_errors', 0);  // Caché en production
   ini_set('log_errors', 1);      // Loggé en privé
   ```

### 🚨 Recommandations À Faire

1. **Variables d'environnement** (.env) pour secrets
2. **Tokens CSRF** sur formulaires admin
3. **`.htaccess`** dans `uploads/` pour bloquer PHP
4. **HTTPS forcé** en production
5. **Tests unitaires** (PHPUnit)
6. **Linter PHP** (PHPStan)

Voir `IMPROVEMENTS.md` pour détails.

---

## 📦 Déploiement

### Prérequis Serveur
- PHP 7.4+ (recommandé 8.0+)
- MySQL 5.7+ ou MariaDB 10.3+
- Accès SMTP pour emails (Gmail SMTP recommandé)
- HTTPS (certificat SSL/TLS)
- Composer installé

### Checklist Déploiement

- [ ] Base de données créée (`database.sql`)
- [ ] `config.php` configuré (DB, SMTP, ENVIRONMENT=production)
- [ ] `.env` créé (ne pas commiter)
- [ ] Permissions : `uploads/` 755, `php-error.log` 644
- [ ] `uploads/.htaccess` créé pour sécurité
- [ ] HTTPS activé
- [ ] Sauvegardes automatiques configurées (DB + fichiers)
- [ ] Monitoring activé (logs, alertes erreurs)
- [ ] Tests formulaires, uploads, emails
- [ ] WAF / protections serveur activés
- [ ] DNS/domaine configuré

### Commandes Installation Rapide

```bash
# 1. Clone
git clone https://github.com/goungbedji/eCommSol.git
cd ecommerce

# 2. Dépendances
composer install

# 3. BD
mysql -u root -p < database.sql

# 4. Config
cp config.php.example config.php
# Éditer config.php avec valeurs production

# 5. Permissions
chmod 755 uploads/
chmod 644 php-error.log

# 6. Tester
curl http://localhost/ecommerce/

# 7. Admin
open http://localhost/ecommerce/admin_login.php
# Login : admin / (voir database.sql pour hash)
```

---

## 📞 Support & Contact

- **Repository :** https://github.com/goungbedji/eCommSol
- **Issues :** Créer une issue sur GitHub
- **Email Support :** (à définir)

---

**Fin de la documentation.**  
Version : 1.0 | Date : 10 Novembre 2025
