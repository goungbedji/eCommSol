<!-- Products Preview Section -->
<section class="relative py-16 bg-white overflow-hidden" id="nouveautes">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-5">
        <div class="absolute top-0 left-0 w-full h-full" style="background-image: radial-gradient(circle at 2px 2px, #1e40af 1px, transparent 0); background-size: 40px 40px;"></div>
    </div>
    
    <!-- Decorative Shapes -->
    <div class="absolute top-10 right-10 w-64 h-64 bg-secondary/10 rounded-full blur-3xl"></div>
    <div class="absolute bottom-10 left-10 w-80 h-80 bg-primary/10 rounded-full blur-3xl"></div>
    
    <div class="relative z-10 max-w-7xl mx-auto px-6">
        <!-- Section Header -->
        <div class="text-center mb-10">
            <span class="inline-block bg-white/80 backdrop-blur-sm text-primary px-5 py-2 rounded-full text-sm font-semibold shadow-lg border border-primary/20 mb-4">
                ✨ Nouveautés
            </span>
            <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-3">
                Découvrez nos derniers produits
            </h2>
        </div>
        
        <!-- Products Grid - Creative Layout -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <?php 
            $index = 0;
            foreach ($derniers_articles as $article): 
                $imageHeight = 'h-56';
                $index++;
            ?>
                <div class="group bg-white rounded-2xl overflow-hidden border border-gray-200 hover:border-primary hover:shadow-xl transition-all duration-300 cursor-pointer" 
                     onclick="window.location='product.php?id=<?= $article['id'] ?>'">
                    <div class="relative overflow-hidden">
                        <?php if (!empty($article['photo'])): ?>
                            <img src="<?= htmlspecialchars($article['photo'] ?? '') ?>" 
                                 class="w-full <?= $imageHeight ?> object-cover group-hover:scale-110 transition-transform duration-500" 
                                 alt="<?= htmlspecialchars($article['titre'] ?? '') ?>">
                        <?php else: ?>
                            <div class="w-full <?= $imageHeight ?> bg-gradient-to-br from-blue-50 via-white to-amber-50 flex items-center justify-center">
                                <svg class="w-20 h-20 text-primary/30" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($article['stock'] == 0): ?>
                            <div class="absolute top-3 right-3 bg-red-500 text-white px-3 py-1 rounded-full text-xs font-bold">
                                Épuisé
                            </div>
                        <?php elseif ($article['stock'] < 3): ?>
                            <div class="absolute top-3 right-3 bg-orange-500 text-white px-3 py-1 rounded-full text-xs font-bold">
                                Dernières pièces
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="p-4">
                        <h3 class="text-base font-bold text-gray-900 mb-2 line-clamp-2 group-hover:text-primary transition-colors">
                            <?= htmlspecialchars($article['titre'] ?? '') ?>
                        </h3>
                        <div class="flex items-center justify-between">
                            <p class="text-xl font-bold text-primary">
                                <?= formatPriceWithPromo($article) ?>
                            </p>
                            <button class="w-9 h-9 bg-primary/10 text-primary rounded-full flex items-center justify-center group-hover:bg-primary group-hover:text-white transition-all">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- View All Button -->
        <div class="text-center mt-8">
            <a href="index.php" class="group inline-flex items-center gap-3 px-10 py-4 bg-primary text-white text-lg font-bold rounded-2xl shadow-xl hover:shadow-2xl hover:scale-105 transition-all duration-300">
                <svg class="w-6 h-6 group-hover:rotate-12 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3z"/>
                </svg>
                Voir tous les produits
            </a>
        </div>
        
        <!-- Arrow to Categories -->
        <div class="flex justify-center mt-12">
            <svg class="w-24 h-24 text-primary animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" stroke-dasharray="4 4"/>
            </svg>
        </div>
    </div>
</section>
