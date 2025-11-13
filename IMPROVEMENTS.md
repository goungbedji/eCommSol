# 📋 Rapport d'Analyse et Suggestions d'Amélioration
**Projet :** E-Commerce PHP  
**Date :** 10 Novembre 2025  
**Analysé par :** Audit Complet du Code

---

## 1️⃣ CORRECTIONS APPLIQUÉES ✅

### 1.1 Sécurité
- ✅ **Display errors en production** : Désactivé `display_errors` en mode production, garder uniquement `log_errors`
- ✅ **Session cookies sécurisés** : Ajouté `Secure`, `HttpOnly`, `SameSite=Lax`
- ✅ **Upload file validation** : Vérification `getimagesize()`, MIME type, taille max 5MB
- ✅ **Incrément coupon** : Corrigé bug `times_used` qui utilisait la valeur obsolète de `used_count`

### 1.2 Affichage et UX
- ✅ **Prix promo** : Prix original barré correctement affiché en gris
- ✅ **Devise FCFA** : Remplacé "€" par "FCFA" dans `formatPriceWithPromo()`
- ✅ **Interface admin coupons** : Refactorisée avec Tailwind, design cohérent, statuts colorés

---

## 2️⃣ SUGGESTIONS D'AMÉLIORATION PAR PRIORITÉ

### 🔴 CRITIQUE (À faire ASAP)

#### 2.1 | Secrets exposés en dur dans `config.php`
**Fichier :** `config.php`  
**Problème :**
```php
define('SMTP_USER', 'taboutique12@gmail.com');
define('SMTP_PASS', 'llaechikhdtfhhoj');
define('DB_USER', 'root');
define('DB_PASS', '');
```
**Risque :** Credentials en clair dans le dépôt Git → compromission email + base de données.

**Solution :**
1. Créer un fichier `.env` (non commité) :
   ```
   DB_HOST=localhost
   DB_USER=root
   DB_PASS=
   DB_NAME=ecommerce_db
   SMTP_HOST=smtp.gmail.com
   SMTP_PORT=587
   SMTP_USER=taboutique12@gmail.com
   SMTP_PASS=xxxxx
   ENVIRONMENT=production
   ```

2. Installer `vlucas/dotenv` via Composer :
   ```bash
   composer require vlucas/dotenv
   ```

3. Charger `.env` au début de `config.php` :
   ```php
   $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
   $dotenv->load();
   
   define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
   define('DB_USER', $_ENV['DB_USER'] ?? 'root');
   // etc...
   ```

4. Ajouter à `.gitignore` :
   ```
   .env
   .env.local
   php-error.log
   ```

5. Créer `.env.example` (commité) :
   ```
   DB_HOST=localhost
   DB_USER=root
   DB_PASS=
   DB_NAME=ecommerce_db
   ENVIRONMENT=development
   ```

**Temps estimé :** 15 min  
**Bénéfice :** Sécurité critique, déploiement facile

---

#### 2.2 | Protection CSRF sur formulaires admin
**Fichier :** `admin_add_article.php`, `admin_edit_article.php`, `admin_delete_article.php`, `admin_coupon_add.php`, etc.  
**Problème :** Pas de token CSRF → vulnérable à attaques Cross-Site Request Forgery.  

**Solution rapide :**

1. Ajouter fonction dans `config.php` :
   ```php
   function generateCsrfToken() {
       if (!isset($_SESSION['csrf_token'])) {
           $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
       }
       return $_SESSION['csrf_token'];
   }
   
   function verifyCsrfToken($token) {
       return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
   }
   ```

2. Dans chaque formulaire (ex: `admin_add_article.php`) :
   ```php
   <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
   ```

3. Au traitement du formulaire (début de `if ($_SERVER['REQUEST_METHOD'] === 'POST')`) :
   ```php
   if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
       die('Token CSRF invalide. Accès refusé.');
   }
   ```

**Temps estimé :** 30 min (5 min par page admin)  
**Bénéfice :** Sécurité élevée, défense basique mais efficace

---

### 🟠 HAUTE PRIORITÉ (À faire dans les 2 semaines)

#### 2.3 | Protéger le dossier `uploads/`
**Fichier :** `uploads/.htaccess` (créer)  
**Problème :** Fichiers uploadés exécutables → risque RCE.

**Solution :**
Créer `uploads/.htaccess` :
```apache
# Bloquer l'exécution PHP
<FilesMatch "\.php$">
    Deny from all
</FilesMatch>

# Bloquer les scripts JavaScript/CGI
<FilesMatch "\.php|\.phtml|\.php3|\.php4|\.php5|\.php7|\.phps|\.pht|\.cgi|\.pl|\.jsp|\.asp|\.sh|\.bat$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Empêcher la liste des fichiers
Options -Indexes

# CORS restreint
Header set X-Content-Type-Options "nosniff"
Header set X-Frame-Options "SAMEORIGIN"
```

**Alternative (si pas Apache) :** Configurer en Nginx :
```nginx
location /uploads/ {
    location ~ \.php$ {
        deny all;
    }
}
```

**Temps estimé :** 5 min  
**Bénéfice :** Prévention RCE majeure

---

#### 2.4 | Ajouter HTTPS forcé en production
**Fichier :** `config.php`  
**Problème :** Sans HTTPS, les cookies et données transitent en clair.

**Solution :**
Ajouter au début de `config.php` (après `session_start()`) :
```php
if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'http') {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit;
}
```

Ou en production : configurer le serveur pour forcer HTTPS (via `.htaccess` ou Nginx).

**Temps estimé :** 5 min  
**Bénéfice :** Sécurité de la connexion

---

#### 2.5 | Validation et sanitisation renforcées
**Fichier :** `config.php` (fonction `clean()`)  
**Problème :** La fonction `clean()` ne suffit pas pour tous les cas (elle fait juste `htmlspecialchars + strip_tags`).

**Solution :**
Enrichir la fonction dans `config.php` :
```php
function clean($data) {
    if (is_array($data)) {
        return array_map('clean', $data);
    }
    
    $data = trim($data);
    $data = stripslashes($data); // Si magic_quotes actif
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    $data = strip_tags($data);
    
    return $data;
}

// Fonction spécialisée pour emails
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : false;
}

// Fonction pour URLs
function validateUrl($url) {
    return filter_var($url, FILTER_VALIDATE_URL) ? $url : false;
}
```

**Temps estimé :** 10 min  
**Bénéfice :** Validation plus robuste

---

### 🟡 MOYEN PRIORITÉ (À faire dans le mois)

#### 2.6 | Ajouter des tests unitaires basiques
**Fichier :** Créer `tests/` (dossier)  
**Problème :** Pas de tests → risque de régression à chaque changement.

**Solution :**
1. Installer PHPUnit :
   ```bash
   composer require --dev phpunit/phpunit
   ```

2. Créer `tests/PromotionTest.php` :
   ```php
   <?php
   require_once __DIR__ . '/../config.php';
   
   use PHPUnit\Framework\TestCase;
   
   class PromotionTest extends TestCase {
       public function testApplyPercentagePromotion() {
           $article = ['id' => 1, 'prix' => 100, 'categorie_id' => 1];
           // Mock promotion...
           $result = applyPromotionToArticle($article);
           $this->assertLessThan(100, $result['price']);
       }
   }
   ?>
   ```

3. Exécuter les tests :
   ```bash
   vendor/bin/phpunit tests/
   ```

**Temps estimé :** 1 heure (pour les tests basiques)  
**Bénéfice :** Confiance dans les changements futurs

---

#### 2.7 | Ajouter linter PHP (PHPStan / PHPCS)
**Fichier :** `composer.json`  

**Solution :**
```bash
composer require --dev phpstan/phpstan squizlabs/php_codesniffer
```

Créer `phpstan.neon` :
```yaml
parameters:
    level: 5
    paths:
        - admin_*.php
        - config.php
        - includes/
    excludePaths:
        - vendor/
```

Ajouter scripts dans `composer.json` :
```json
"scripts": {
    "lint": "phpstan analyse",
    "format": "phpcbf",
    "check": "phpcs"
}
```

Puis : `composer run lint`

**Temps estimé :** 20 min  
**Bénéfice :** Détection automatique des bugs

---

#### 2.8 | Ajouter logging structuré
**Fichier :** `config.php`  
**Problème :** Les erreurs vont toutes au même `php-error.log` → difficile à debuguer.

**Solution :**
Créer fonction `logActivity()` dans `config.php` :
```php
function logActivity($action, $details, $level = 'INFO') {
    $log_file = __DIR__ . '/logs/activity_' . date('Y-m-d') . '.log';
    if (!is_dir(__DIR__ . '/logs')) mkdir(__DIR__ . '/logs', 0755, true);
    
    $message = sprintf(
        "[%s] [%s] %s | Détails: %s\n",
        date('Y-m-d H:i:s'),
        $level,
        $action,
        json_encode($details)
    );
    
    file_put_contents($log_file, $message, FILE_APPEND);
}
```

Usage :
```php
logActivity('ARTICLE_ADDED', ['id' => $article_id, 'titre' => $titre], 'INFO');
logActivity('UPLOAD_FAILED', ['filename' => $filename, 'error' => $error], 'WARNING');
```

**Temps estimé :** 15 min  
**Bénéfice :** Audit trail, débogage facile

---

### 🟢 NICE TO HAVE (Améliorations UX/DX)

#### 2.9 | Pagination du tableau admin des coupons
**Fichier :** `admin_coupons.php`  
**Problème :** Si 1000 coupons, le tableau est énorme.

**Solution :**
```php
// Dans admin_coupons.php
$per_page = 20;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $per_page;

$stmt = $pdo->query("SELECT COUNT(*) FROM coupons");
$total = $stmt->fetchColumn();
$pages = ceil($total / $per_page);

$stmt = $pdo->query("SELECT * FROM coupons ORDER BY expiry_date DESC LIMIT $per_page OFFSET $offset");
$coupons = $stmt->fetchAll();
```

Puis ajouter des boutons pagination en HTML.

**Temps estimé :** 20 min  
**Bénéfice :** Meilleure UX pour gros volumes

---

#### 2.10 | Barre de recherche/filtrage dans les tableaux admin
**Fichier :** `admin_articles.php`, `admin_coupons.php`, etc.  

**Solution :**
Ajouter input search qui filtre la table avec AJAX ou rechargement simple.

**Exemple :**
```php
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($search) {
    $search_term = '%' . $search . '%';
    $stmt = $pdo->prepare("SELECT * FROM coupons WHERE code LIKE ? OR ... LIMIT 50");
    $stmt->execute([$search_term]);
} else {
    $stmt = $pdo->query("SELECT * FROM coupons ORDER BY expiry_date DESC LIMIT 50");
}
$coupons = $stmt->fetchAll();
```

**Temps estimé :** 15 min  
**Bénéfice :** Facilité de gestion

---

#### 2.11 | Export CSV/Excel des données admin
**Fichier :** Créer `admin_export.php`  

**Exemple :**
```php
if (isset($_GET['export']) && $_GET['export'] === 'coupons') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=coupons_' . date('Y-m-d') . '.csv');
    
    $stmt = $pdo->query("SELECT * FROM coupons");
    $coupons = $stmt->fetchAll();
    
    echo "Code,Type,Valeur,Utilisé,Limite\n";
    foreach ($coupons as $c) {
        echo sprintf("%s,%s,%s,%d,%s\n", $c['code'], $c['type'], $c['value'], $c['used_count'], $c['max_uses']);
    }
    exit;
}
```

**Temps estimé :** 30 min  
**Bénéfice :** Facilité rapports/analytics

---

#### 2.12 | API REST pour les opérations admin
**Fichier :** Créer `api/admin/`  
**Problème :** Admin utilise formulaires POST simples → pas de feedback AJAX.

**Exemple :**
```php
// api/admin/coupon.php
header('Content-Type: application/json');

if ($_POST['action'] === 'toggle_status') {
    $stmt = $pdo->prepare("UPDATE coupons SET active = NOT active WHERE id = ?");
    $result = $stmt->execute([$_POST['id']]);
    echo json_encode(['success' => $result]);
}
```

**Temps estimé :** 2-3 heures pour une API complète  
**Bénéfice :** Admin UI reactive, meilleure UX

---

#### 2.13 | Système de rôles et permissions
**Fichier :** Ajouter table `admin_roles` et `admin_permissions`  
**Problème :** Tous les admins ont les mêmes droits → risque de manipulation accidentelle.

**Solution :**
```sql
CREATE TABLE admin_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100),
    permissions TEXT -- JSON array
);

ALTER TABLE admins ADD COLUMN role_id INT, ADD FOREIGN KEY (role_id) REFERENCES admin_roles(id);
```

Puis vérifier les permissions sur chaque page :
```php
function canModifyArticles() {
    $admin = getAdminInfo();
    // Check permissions...
}
```

**Temps estimé :** 1-2 heures  
**Bénéfice :** Contrôle accès granulaire

---

#### 2.14 | Mise en cache des requêtes (Redis/APCu)
**Fichier :** `config.php`  
**Problème :** `getBoutiqueSettings()` / `getActivePromotionForArticle()` requêtent la DB à chaque page.

**Solution :**
```php
function getBoutiqueSettings($use_cache = true) {
    global $pdo;
    $cache_key = 'boutique_settings';
    
    if ($use_cache && function_exists('apcu_fetch')) {
        $cached = apcu_fetch($cache_key);
        if ($cached !== false) return $cached;
    }
    
    // Query DB...
    $settings = ...;
    
    if (function_exists('apcu_store')) {
        apcu_store($cache_key, $settings, 3600); // 1 heure
    }
    
    return $settings;
}
```

**Temps estimé :** 30 min  
**Bénéfice :** Performance +30-50%

---

#### 2.15 | Documentation API / Swagger
**Fichier :** Créer `docs/API.md` ou `swagger.yaml`  

Documenter tous les endpoints publics (recherche, produits, panier).

**Temps estimé :** 1 heure  
**Bénéfice :** Facilité d'intégration mobile/tiers

---

## 3️⃣ CHECKLIST DE DÉPLOIEMENT PRODUCTION

- [ ] Activer HTTPS (certificat SSL/TLS)
- [ ] Configurer `.env` avec variables d'environnement
- [ ] Désactiver `display_errors` en production
- [ ] Ajouter `.htaccess` dans `uploads/`
- [ ] Vérifier droits fichiers (permissions 644/755)
- [ ] Configurer sauvegardes automatiques (Base + fichiers)
- [ ] Mettre en place monitoring (logs, alertes erreurs)
- [ ] Tester migrations DB avec vraies données
- [ ] Rotation des secrets (SMTP password, DB password)
- [ ] Activer WAF/protections serveur
- [ ] Tester les formulaires et uploads côté serveur
- [ ] Vérifier les emails (SMTP fonctionne bien)

---

## 4️⃣ RÉSUMÉ PAR TIMELINE

| Phase | Durée | Actions |
|-------|-------|---------|
| **URGENT** | 1 jour | 2.1 (Secrets), 2.3 (uploads/.htaccess), 2.2 (CSRF) |
| **COURT TERME** | 1 semaine | 2.4 (HTTPS), 2.5 (validation), 2.8 (logging) |
| **MOYEN TERME** | 2-4 semaines | 2.6 (tests), 2.7 (linter), 2.9-2.11 (features) |
| **LONG TERME** | 1-3 mois | 2.12 (API), 2.13 (rôles), 2.14 (cache) |

---

## 5️⃣ RESSOURCES UTILES

- **PHP Security:** https://owasp.org/www-project-top-ten/
- **Dotenv:** https://github.com/vlucas/phpdotenv
- **PHPUnit:** https://phpunit.de/
- **PHPStan:** https://phpstan.org/
- **CSRF tokens:** https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html

---

**Fin du rapport.** Questions ? Contactez l'équipe de développement.
