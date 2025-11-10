<!-- Categories Section -->
<section id="categories" class="relative py-16 flex items-center justify-center bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 overflow-hidden">
    <!-- Decorative Elements -->
    <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-blue-500/20 rounded-full blur-3xl"></div>
    <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-purple-500/20 rounded-full blur-3xl"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-indigo-500/10 rounded-full blur-3xl"></div>
    
    <div class="relative z-10 w-full">
        <!-- Section Header -->
        <div class="text-center mb-10 px-6">
            <span class="inline-block bg-white/10 backdrop-blur-sm text-white px-5 py-2 rounded-full text-sm font-semibold shadow-lg border border-white/20 mb-4">
                ✨ Catégories
            </span>
            <h2 class="text-3xl md:text-4xl font-extrabold text-white mb-3">
                Parmi nos nombreuses catégories
            </h2>
            <p class="text-base text-gray-300 max-w-2xl mx-auto">
                Trouvez exactement ce que vous cherchez
            </p>
        </div>
        
        <!-- Auto-scrolling Carousel -->
        <div class="relative overflow-hidden">
            <div class="max-w-7xl mx-auto px-6">
                <div id="categoryCarousel" class="flex gap-5 animate-scroll">
                <?php 
                $category_images = [
                    'Électronique' => 'https://images.unsplash.com/photo-1498049794561-7780e7231661?w=800',
                    'Mode' => 'https://images.unsplash.com/photo-1445205170230-053b83016050?w=800',
                    'Maison' => 'https://images.unsplash.com/photo-1484101403633-562f891dc89a?w=800',
                    'Sport' => 'https://images.unsplash.com/photo-1461896836934-ffe607ba8211?w=800',
                    'Beauté' => 'https://images.unsplash.com/photo-1596462502278-27bfdc403348?w=800',
                    'Livres' => 'https://images.unsplash.com/photo-1495446815901-a7297e633e8d?w=800',
                    'Jouets' => 'https://images.unsplash.com/photo-1558060370-d644479cb6f7?w=800',
                    'Alimentation' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=800',
                ];
                
                // Dupliquer pour effet infini
                $all_categories = array_merge($categories, $categories);
                foreach ($all_categories as $categorie): 
                    $cat_name = $categorie !== null ? $categorie : '(Non défini)';
                    $image = $category_images[$cat_name] ?? 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=800';
                ?>
                    <a href="index.php?categorie=<?= urlencode((string)$categorie) ?>" 
                       class="group relative flex-shrink-0 w-64 h-80 rounded-2xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-300">
                        <img src="<?= $image ?>" 
                             alt="<?= htmlspecialchars($cat_name) ?>" 
                             class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                        
                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/50 to-transparent"></div>
                        
                        <div class="absolute inset-0 flex flex-col justify-end p-6">
                            <h3 class="text-2xl font-bold text-white mb-2 group-hover:translate-y-[-8px] transition-transform duration-300">
                                <?= htmlspecialchars($cat_name) ?>
                            </h3>
                            <div class="flex items-center gap-2 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <span class="text-sm font-semibold">Explorer</span>
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        @keyframes scroll {
            0% {
                transform: translateX(0);
            }
            100% {
                transform: translateX(-50%);
            }
        }
        
        .animate-scroll {
            animation: scroll 30s linear infinite;
        }
        
        .animate-scroll:hover {
            animation-play-state: paused;
        }
    </style>
</section>
