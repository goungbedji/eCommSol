<?php
// Header admin réutilisable
// Variables d'entrée (optionnelles) :
// - $admin_active : 'dashboard'|'categories'|'promotions'|'coupons'|'commandes'|'settings'
// - $admin_title : titre de la page
// - $admin_actions : tableau de boutons ['href'=>'...','label'=>'...','class'=>'btn']
if (!isset($admin_active)) $admin_active = '';
if (!isset($admin_title)) $admin_title = '📊 Administration';
if (!isset($admin_actions)) $admin_actions = [];

$settings = getBoutiqueSettings();
?>
<!-- Admin Header -->
<header class="sticky top-0 z-50 bg-gradient-to-r from-primary to-accent shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between h-16">
            <!-- Logo Admin -->
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-white"><?= htmlspecialchars($admin_title) ?></h1>
                    <p class="text-xs text-white/80"><?= htmlspecialchars($settings['nom_boutique'] ?? 'Boutique') ?></p>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="flex items-center gap-3">
                <?php foreach ($admin_actions as $act): ?>
                    <a href="<?= htmlspecialchars($act['url'] ?? $act['href'] ?? '#') ?>" 
                       class="hidden sm:flex items-center gap-2 px-4 py-2 bg-white text-primary font-semibold rounded-xl hover:bg-white/90 transition-all">
                        <?= htmlspecialchars($act['label'] ?? '') ?>
                    </a>
                <?php endforeach; ?>
                
                <a href="index.php" target="_blank" 
                   class="hidden md:flex items-center gap-2 px-4 py-2 bg-white/20 text-white font-semibold rounded-xl hover:bg-white/30 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    Voir la boutique
                </a>
                
                <a href="admin_logout.php" 
                   class="flex items-center gap-2 px-4 py-2 bg-red-500 text-white font-semibold rounded-xl hover:bg-red-600 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span class="hidden sm:inline">Déconnexion</span>
                </a>
            </div>
        </div>
    </div>
</header>

<!-- Admin Navigation -->
<nav class="bg-white border-b border-gray-200 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex items-center gap-1 overflow-x-auto py-2">
            <a href="admin_dashboard.php" 
               class="flex items-center gap-2 px-4 py-2 <?= $admin_active === 'dashboard' ? 'text-primary bg-primary/10 border-b-2 border-primary' : 'text-gray-600 hover:text-primary hover:bg-gray-50' ?> rounded-t-xl font-semibold whitespace-nowrap transition-all">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                </svg>
                Tableau de bord
            </a>
            <a href="admin_articles.php" 
               class="flex items-center gap-2 px-4 py-2 <?= $admin_active === 'articles' ? 'text-primary bg-primary/10 border-b-2 border-primary' : 'text-gray-600 hover:text-primary hover:bg-gray-50' ?> rounded-t-xl font-semibold whitespace-nowrap transition-all">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"/>
                </svg>
                Articles
            </a>
            <a href="admin_categories.php" 
               class="flex items-center gap-2 px-4 py-2 <?= $admin_active === 'categories' ? 'text-primary bg-primary/10 border-b-2 border-primary' : 'text-gray-600 hover:text-primary hover:bg-gray-50' ?> rounded-t-xl font-semibold whitespace-nowrap transition-all">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"/>
                </svg>
                Catégories
            </a>
            <a href="admin_promotions.php" 
               class="flex items-center gap-2 px-4 py-2 <?= $admin_active === 'promotions' ? 'text-primary bg-primary/10 border-b-2 border-primary' : 'text-gray-600 hover:text-primary hover:bg-gray-50' ?> rounded-t-xl font-semibold whitespace-nowrap transition-all">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 010-2h1V3a1 1 0 011-1zm0 10a1 1 0 011 1v1h1a1 1 0 110 2H6v1a1 1 0 11-2 0v-1H3a1 1 0 110-2h1v-1a1 1 0 011-1zM12 2a1 1 0 01.967.744L14.146 7.2 17.5 9.134a1 1 0 010 1.732l-3.354 1.935-1.18 4.455a1 1 0 01-1.933 0L9.854 12.8 6.5 10.866a1 1 0 010-1.732l3.354-1.935 1.18-4.455A1 1 0 0112 2z" clip-rule="evenodd"/>
                </svg>
                Promotions
            </a>
            <a href="admin_coupons.php" 
               class="flex items-center gap-2 px-4 py-2 <?= $admin_active === 'coupons' ? 'text-primary bg-primary/10 border-b-2 border-primary' : 'text-gray-600 hover:text-primary hover:bg-gray-50' ?> rounded-t-xl font-semibold whitespace-nowrap transition-all">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5 5a3 3 0 015-2.236A3 3 0 0114.83 6H16a2 2 0 110 4h-5V9a1 1 0 10-2 0v1H4a2 2 0 110-4h1.17C5.06 5.687 5 5.35 5 5zm4 1V5a1 1 0 10-1 1h1zm3 0a1 1 0 10-1-1v1h1z" clip-rule="evenodd"/>
                    <path d="M9 11H3v5a2 2 0 002 2h4v-7zM11 18h4a2 2 0 002-2v-5h-6v7z"/>
                </svg>
                Coupons
            </a>
            <a href="admin_commandes.php" 
               class="flex items-center gap-2 px-4 py-2 <?= $admin_active === 'commandes' ? 'text-primary bg-primary/10 border-b-2 border-primary' : 'text-gray-600 hover:text-primary hover:bg-gray-50' ?> rounded-t-xl font-semibold whitespace-nowrap transition-all">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                </svg>
                Commandes
            </a>
            <a href="admin_settings.php" 
               class="flex items-center gap-2 px-4 py-2 <?= $admin_active === 'settings' ? 'text-primary bg-primary/10 border-b-2 border-primary' : 'text-gray-600 hover:text-primary hover:bg-gray-50' ?> rounded-t-xl font-semibold whitespace-nowrap transition-all">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                </svg>
                Paramètres
            </a>
            <a href="admin_stock_history.php" 
               class="flex items-center gap-2 px-4 py-2 <?= $admin_active === 'stock' ? 'text-primary bg-primary/10 border-b-2 border-primary' : 'text-gray-600 hover:text-primary hover:bg-gray-50' ?> rounded-t-xl font-semibold whitespace-nowrap transition-all">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3z"/>
                </svg>
                Historique Stock
            </a>
        </div>
    </div>
</nav>
