<?php
// Footer dynamique avec les informations de la boutique
$settings = getBoutiqueSettings();
$total_products = $GLOBALS['pdo']->query("SELECT COUNT(*) FROM articles WHERE stock > 0")->fetchColumn();
?>

<footer class="bg-white border-t border-gray-200 mt-16">
    
    <!-- Main Footer -->
    <div class="max-w-7xl mx-auto px-6 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 mb-12">
            
            <!-- À propos - Section avec fond noir -->
            <div class="lg:col-span-4 bg-gray-900 rounded-2xl p-8 flex flex-col justify-center">
                <div class="space-y-4">
                    <h3 class="text-2xl font-bold text-white leading-tight">
                        Space E-Commerce
                    </h3>
                    <p class="text-lg text-gray-300 font-light">
                        Votre partenaire shopping de luxe
                    </p>
                    <div class="h-1 w-24 bg-gradient-to-r from-primary to-secondary rounded-full"></div>
                </div>
            </div>
            
            <!-- Autres colonnes -->
            <div class="lg:col-span-8 grid grid-cols-1 md:grid-cols-3 gap-10">
            
            <!-- Liens Rapides -->
            <div>
                <h4 class="text-lg font-bold text-gray-900 mb-4">
                    Liens Rapides
                </h4>
                <ul class="space-y-2">
                    <li><a href="home.php" class="text-gray-600 hover:text-gray-900 transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/></svg>
                        Accueil
                    </a></li>
                    <li><a href="index.php" class="text-gray-600 hover:text-gray-900 transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"/></svg>
                        Tous les Produits
                    </a></li>
                    <li><a href="cart.php" class="text-gray-600 hover:text-gray-900 transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3z"/></svg>
                        Mon Panier
                    </a></li>
                    <li><a href="index.php#promotions" class="text-gray-600 hover:text-gray-900 transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 010-2h1V3a1 1 0 011-1zm0 10a1 1 0 011 1v1h1a1 1 0 110 2H6v1a1 1 0 11-2 0v-1H3a1 1 0 110-2h1v-1a1 1 0 011-1zM12 2a1 1 0 01.967.744L14.146 7.2 17.5 9.134a1 1 0 010 1.732l-3.354 1.935-1.18 4.455a1 1 0 01-1.933 0L9.854 12.8 6.5 10.866a1 1 0 010-1.732l3.354-1.935 1.18-4.455A1 1 0 0112 2z" clip-rule="evenodd"/></svg>
                        Promotions
                    </a></li>
                </ul>
            </div>
            
            <!-- Contact -->
            <div>
                <h4 class="text-lg font-bold text-gray-900 mb-4">
                    Contactez-nous
                </h4>
                <ul class="space-y-3">
                    <li class="flex items-start gap-2 text-gray-600">
                        <svg class="w-5 h-5 text-gray-900 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                        </svg>
                        <a href="mailto:<?= htmlspecialchars($settings['email_boutique'] ?? '') ?>" class="hover:text-gray-900 break-all">
                            <?= htmlspecialchars($settings['email_boutique'] ?? 'contact@boutique.com') ?>
                        </a>
                    </li>
                    <li class="flex items-start gap-2 text-gray-600">
                        <svg class="w-5 h-5 text-gray-900 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                        </svg>
                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $settings['whatsapp_boutique'] ?? '') ?>" target="_blank" class="hover:text-gray-900">
                            <?= htmlspecialchars($settings['whatsapp_boutique'] ?? '+229 XX XX XX XX') ?>
                        </a>
                    </li>
                    <?php if (!empty($settings['adresse_boutique'])): ?>
                    <li class="flex items-start gap-2 text-gray-600">
                        <svg class="w-5 h-5 text-gray-900 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                        <span>
                            <?= htmlspecialchars($settings['adresse_boutique']) ?>,
                            <?= htmlspecialchars($settings['ville'] ?? '') ?>
                        </span>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <!-- Informations -->
            <div>
                <h4 class="text-lg font-bold text-gray-900 mb-4">
                    Informations
                </h4>
                <ul class="space-y-3 text-gray-600">
                    <li class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-gray-900 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                            <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707l-2-2A1 1 0 0015 7h-1z"/>
                        </svg>
                        <span><strong>Livraison :</strong> Partout au Bénin</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-gray-900 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                        <span><strong>Paiement :</strong> À la livraison</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-gray-900 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                        <span><strong>Support :</strong> 7j/7</span>
                    </li>
                </ul>
            </div>
            
            </div>
        </div>
        
    </div>
</footer>