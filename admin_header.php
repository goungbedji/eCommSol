<?php
// Header admin réutilisable
// Variables d'entrée (optionnelles) :
// - $admin_active : 'dashboard'|'categories'|'promotions'|'coupons'|'commandes'|'settings'
// - $admin_title : titre de la page
// - $admin_actions : tableau de boutons ['href'=>'...','label'=>'...','class'=>'btn']
if (!isset($admin_active)) $admin_active = '';
if (!isset($admin_title)) $admin_title = '📊 Administration E-Commerce';
if (!isset($admin_actions)) $admin_actions = [];
?>
<div class="header">
    <div class="header-content">
    <h1><?= htmlspecialchars($admin_title !== null ? $admin_title : '') ?></h1>
        <div>
            <?php foreach ($admin_actions as $act): ?>
                <a href="<?= htmlspecialchars($act['href'] !== null ? $act['href'] : '') ?>" class="<?= htmlspecialchars(($act['class'] ?? 'btn') !== null ? ($act['class'] ?? 'btn') : '') ?>"><?= htmlspecialchars($act['label'] !== null ? $act['label'] : '') ?></a>
            <?php endforeach; ?>
            <a href="admin_logout.php" class="back-btn">Déconnexion</a>
        </div>
    </div>
</div>

<div class="nav">
    <a href="admin_dashboard.php" <?= $admin_active === 'dashboard' ? 'class="active"' : '' ?>>Articles</a>
    <a href="admin_categories.php" <?= $admin_active === 'categories' ? 'class="active"' : '' ?>>Catégories</a>
    <a href="admin_promotions.php" <?= $admin_active === 'promotions' ? 'class="active"' : '' ?>>Promotions</a>
    <a href="admin_coupons.php" <?= $admin_active === 'coupons' ? 'class="active"' : '' ?>>Coupons</a>
    <a href="admin_commandes.php" <?= $admin_active === 'commandes' ? 'class="active"' : '' ?>>Commandes</a>
    <a href="admin_settings.php" <?= $admin_active === 'settings' ? 'class="active"' : '' ?>>Paramètres</a>
    <a href="index.php" target="_blank">Voir la boutique</a>
</div>
