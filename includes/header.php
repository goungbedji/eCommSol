<?php
// Détecter la page actuelle
$current_page = basename($_SERVER['PHP_SELF']);
$is_home = ($current_page == 'home.php');
$is_shop = ($current_page == 'index.php' || $current_page == 'product.php');
?>
<!-- Header Navigation -->
<header class="sticky top-0 z-50 bg-white border-b border-gray-200 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between h-16 sm:h-20">
            <!-- Logo -->
            <a href="home.php" class="flex items-center gap-2 sm:gap-3 group">
                <div class="w-9 h-9 sm:w-10 sm:h-10 bg-gradient-to-br from-primary to-accent rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3z"/>
                    </svg>
                </div>
                <div class="hidden sm:block">
                    <h1 class="text-lg sm:text-xl font-bold text-gray-900"><?= htmlspecialchars($settings['nom_boutique'] ?? 'Boutique') ?></h1>
                    <?php if (!empty($settings['slogan'])): ?>
                        <p class="text-xs text-gray-500"><?= htmlspecialchars($settings['slogan']) ?></p>
                    <?php endif; ?>
                </div>
            </a>
            
            <!-- Navigation Desktop -->
            <nav class="hidden md:flex items-center gap-2">
                <a href="home.php" class="flex items-center gap-2 px-4 py-2 <?= $is_home ? 'text-primary bg-primary/10' : 'text-gray-600 hover:text-primary hover:bg-gray-50' ?> rounded-xl font-semibold transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Accueil
                </a>
                <a href="index.php" class="flex items-center gap-2 px-4 py-2 <?= $is_shop ? 'text-primary bg-primary/10' : 'text-gray-600 hover:text-primary hover:bg-gray-50' ?> rounded-xl font-semibold transition-all">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3z"/>
                    </svg>
                    Boutique
                </a>
            </nav>
            
            <!-- Actions -->
            <div class="flex items-center gap-2 sm:gap-3">
                <!-- Panier -->
                <a href="cart.php" class="relative group">
                    <div class="flex items-center gap-2 px-3 sm:px-4 py-2 bg-primary/10 text-primary rounded-xl hover:bg-primary hover:text-white transition-all">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3z"/>
                        </svg>
                        <span class="font-bold hidden sm:inline">Panier</span>
                        <span class="absolute -top-2 -right-2 w-5 h-5 sm:w-6 sm:h-6 bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center" id="cartCount">0</span>
                    </div>
                </a>
                
                <!-- Menu Mobile Button -->
                <button id="mobileMenuBtn" class="md:hidden p-2 text-gray-600 hover:text-primary hover:bg-gray-50 rounded-xl transition-all">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Menu Mobile -->
    <div id="mobileMenu" class="hidden md:hidden border-t border-gray-200 bg-white">
        <nav class="max-w-7xl mx-auto px-4 py-3 space-y-1">
            <a href="home.php" class="flex items-center gap-3 px-4 py-3 <?= $is_home ? 'text-primary bg-primary/10' : 'text-gray-600 hover:text-primary hover:bg-gray-50' ?> rounded-xl font-semibold transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Accueil
            </a>
            <a href="index.php" class="flex items-center gap-3 px-4 py-3 <?= $is_shop ? 'text-primary bg-primary/10' : 'text-gray-600 hover:text-primary hover:bg-gray-50' ?> rounded-xl font-semibold transition-all">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3z"/>
                </svg>
                Boutique
            </a>
        </nav>
    </div>
</header>

<script>
    // Toggle Mobile Menu
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileMenu = document.getElementById('mobileMenu');
    
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }
    
    // Update cart count on page load
    function updateCartCount() {
        const cart = JSON.parse(localStorage.getItem('cart')) || [];
        const count = cart.reduce((total, item) => total + item.quantity, 0);
        const cartCountElement = document.getElementById('cartCount');
        if (cartCountElement) {
            cartCountElement.textContent = count;
        }
    }
    
    // Initialize cart count
    updateCartCount();
</script>
