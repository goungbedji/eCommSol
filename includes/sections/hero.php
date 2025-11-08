<!-- Hero Section -->
<section class="relative min-h-screen flex items-center justify-center overflow-hidden">
    <!-- Background Image -->
    <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=1920')] bg-cover bg-center"></div>
    
    <!-- Overlay -->
    <div class="absolute inset-0 bg-gradient-to-br from-white/75 via-blue-50/70 to-amber-50/75"></div>
    
    <!-- Decorative Elements -->
    <div class="absolute inset-0">
        <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-primary/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-secondary/10 rounded-full blur-3xl"></div>
    </div>
    
    <!-- Content -->
    <div class="relative z-10 max-w-7xl mx-auto px-6 py-20">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <!-- Left Content -->
            <div class="text-center lg:text-left">
                <div class="inline-block mb-6">
                    <span class="relative inline-block px-4 py-2 text-gray-900 font-bold text-base">
                        <span class="absolute inset-0 bg-secondary transform -skew-x-12 opacity-60"></span>
                        <span class="relative">🎉 Bienvenue sur Space E-Commerce</span>
                    </span>
                </div>
                
                <h1 class="text-5xl md:text-7xl font-extrabold text-gray-900 mb-6 leading-tight">
                    Vivez le Meilleur du 
                    <span class="bg-gradient-to-r from-primary to-accent bg-clip-text text-transparent">Shopping</span>
                </h1>
                
                <p class="text-xl md:text-2xl text-gray-600 mb-10 leading-relaxed">
                    Découvrez nos produits de qualité à des prix imbattables. Une expérience shopping unique vous attend.
                </p>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                    <a href="index.php" class="group inline-flex items-center gap-3 px-10 py-5 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-lg font-bold rounded-2xl shadow-xl hover:shadow-2xl hover:scale-105 transition-all duration-300">
                        <svg class="w-6 h-6 group-hover:rotate-12 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3z"/>
                        </svg>
                        Découvrir la boutique
                    </a>
                    
                    <a href="#nouveautes" class="inline-flex items-center gap-3 px-10 py-5 bg-gradient-to-r from-amber-500 to-orange-500 text-white text-lg font-bold rounded-2xl shadow-xl hover:shadow-2xl hover:scale-105 transition-all duration-300">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 010-2h1V3a1 1 0 011-1zm0 10a1 1 0 011 1v1h1a1 1 0 110 2H6v1a1 1 0 11-2 0v-1H3a1 1 0 110-2h1v-1a1 1 0 011-1zM12 2a1 1 0 01.967.744L14.146 7.2 17.5 9.134a1 1 0 010 1.732l-3.354 1.935-1.18 4.455a1 1 0 01-1.933 0L9.854 12.8 6.5 10.866a1 1 0 010-1.732l3.354-1.935 1.18-4.455A1 1 0 0112 2z" clip-rule="evenodd"/>
                        </svg>
                        Voir les nouveautés
                    </a>
                </div>
                
                <!-- Stats -->
                <div class="grid grid-cols-3 gap-6 mt-12">
                    <div class="text-center lg:text-left">
                        <div class="text-3xl font-bold text-primary">500+</div>
                        <div class="text-sm text-gray-600">Produits</div>
                    </div>
                    <div class="text-center lg:text-left">
                        <div class="text-3xl font-bold text-primary">5000+</div>
                        <div class="text-sm text-gray-600">Clients</div>
                    </div>
                    <div class="text-center lg:text-left">
                        <div class="text-3xl font-bold text-primary">24/7</div>
                        <div class="text-sm text-gray-600">Support</div>
                    </div>
                </div>
            </div>
            
            <!-- Right Content - Visual -->
            <div class="relative hidden lg:block h-[500px]">
                <div class="relative">
                    <!-- Floating Cards with Images -->
                    <div class="absolute top-0 right-0 w-64 h-80 bg-white rounded-3xl shadow-2xl transform rotate-6 hover:rotate-3 transition-transform duration-300 overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400" 
                             alt="Product" 
                             class="w-full h-full object-cover">
                    </div>
                    
                    <div class="absolute top-20 left-0 w-64 h-80 bg-white rounded-3xl shadow-2xl transform -rotate-6 hover:-rotate-3 transition-transform duration-300 overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400" 
                             alt="Product" 
                             class="w-full h-full object-cover">
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scroll Indicator -->
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce">
        <a href="#features" class="flex flex-col items-center text-primary">
            <span class="text-sm font-semibold mb-2">Découvrir</span>
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </a>
    </div>
</section>
