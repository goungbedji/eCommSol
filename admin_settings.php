<?php
require_once 'config.php';
requireAdmin();

$success = '';
$error = '';

// Récupérer les infos de l'admin connecté
$admin = getAdminInfo();

// Récupérer les paramètres de la boutique
$settings = getBoutiqueSettings();

// Traitement du formulaire profil admin
if (isset($_POST['update_admin'])) {
    $nom = clean($_POST['nom']);
    $prenom = clean($_POST['prenom']);
    $email = clean($_POST['email']);
    $new_password = $_POST['new_password'];
    
    try {
        if (!empty($new_password)) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE admins SET nom = ?, prenom = ?, email = ?, password = ? WHERE id = ?");
            $stmt->execute([$nom, $prenom, $email, $hashed, $_SESSION['admin_id']]);
        } else {
            $stmt = $pdo->prepare("UPDATE admins SET nom = ?, prenom = ?, email = ? WHERE id = ?");
            $stmt->execute([$nom, $prenom, $email, $_SESSION['admin_id']]);
        }
        
        $_SESSION['success_message'] = 'Profil administrateur mis à jour avec succès !';
        $admin = getAdminInfo();
        header('Location: admin_settings.php');
        exit;
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Erreur : ' . $e->getMessage();
    }
}

// Traitement du formulaire paramètres boutique
if (isset($_POST['update_boutique'])) {
    $nom_boutique = clean($_POST['nom_boutique']);
    $slogan = clean($_POST['slogan']);
    $email_boutique = clean($_POST['email_boutique']);
    $whatsapp_boutique = clean($_POST['whatsapp_boutique']);
    $adresse_boutique = clean($_POST['adresse_boutique']);
    $ville = clean($_POST['ville']);
    $pays = clean($_POST['pays']);
    $description_boutique = clean($_POST['description_boutique']);
    
    try {
        $check = $pdo->query("SELECT COUNT(*) FROM boutique_settings")->fetchColumn();
        
        if ($check > 0) {
            $stmt = $pdo->prepare("UPDATE boutique_settings SET nom_boutique = ?, slogan = ?, email_boutique = ?, whatsapp_boutique = ?, adresse_boutique = ?, ville = ?, pays = ?, description_boutique = ? WHERE id = 1");
            $stmt->execute([$nom_boutique, $slogan, $email_boutique, $whatsapp_boutique, $adresse_boutique, $ville, $pays, $description_boutique]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO boutique_settings (nom_boutique, slogan, email_boutique, whatsapp_boutique, adresse_boutique, ville, pays, description_boutique) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nom_boutique, $slogan, $email_boutique, $whatsapp_boutique, $adresse_boutique, $ville, $pays, $description_boutique]);
        }
        
        $_SESSION['success_message'] = 'Paramètres de la boutique mis à jour avec succès !';
        $settings = getBoutiqueSettings();
        header('Location: admin_settings.php');
        exit;
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Erreur : ' . $e->getMessage();
    }
}

$page_title = 'Paramètres - Administration';
$admin_active = 'settings';
$admin_title = '⚙️ Paramètres';
$admin_actions = [];
?>
<?php include 'includes/admin_head.php'; ?>
<?php include 'includes/admin_header.php'; ?>

<!-- Main Content -->
<div class="min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        
        <!-- Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-xl">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-green-700 font-semibold"><?= $_SESSION['success_message'] ?></p>
                </div>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-xl">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-red-700 font-semibold"><?= $_SESSION['error_message'] ?></p>
                </div>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
        
        <!-- Settings Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Profil Administrateur -->
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 pb-4 border-b-2 border-primary">👤 Mon Profil Administrateur</h2>
                
                <div class="bg-gradient-to-br from-primary/5 to-accent/5 rounded-xl p-6 mb-6">
                    <div class="space-y-3">
                        <div class="flex justify-between items-center py-2 border-b border-gray-200">
                            <span class="font-semibold text-gray-700">Nom d'utilisateur :</span>
                            <span class="text-gray-900 font-bold"><?= htmlspecialchars($admin['username']) ?></span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-200">
                            <span class="font-semibold text-gray-700">Nom complet :</span>
                            <span class="text-gray-900"><?= htmlspecialchars($admin['prenom'] . ' ' . $admin['nom']) ?></span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="font-semibold text-gray-700">Email :</span>
                            <span class="text-gray-900"><?= htmlspecialchars($admin['email']) ?></span>
                        </div>
                    </div>
                </div>
                
                <form method="POST" class="space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Prénom *</label>
                        <input type="text" name="prenom" value="<?= htmlspecialchars($admin['prenom']) ?>" required 
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Nom *</label>
                        <input type="text" name="nom" value="<?= htmlspecialchars($admin['nom']) ?>" required 
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Email *</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($admin['email']) ?>" required 
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Nouveau mot de passe</label>
                        <input type="password" name="new_password" placeholder="Laisser vide pour ne pas changer" 
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                        <p class="text-xs text-gray-500 mt-1">Minimum 6 caractères</p>
                    </div>
                    
                    <button type="submit" name="update_admin" 
                            class="w-full flex items-center justify-center gap-2 px-6 py-4 bg-primary text-white text-lg font-bold rounded-xl hover:bg-primary/90 shadow-lg hover:shadow-xl transition-all">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M7.707 10.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V6h5a2 2 0 012 2v7a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2h5v5.586l-1.293-1.293zM9 4a1 1 0 012 0v2H9V4z"/>
                        </svg>
                        Enregistrer mon profil
                    </button>
                </form>
            </div>
            
            <!-- Paramètres Boutique -->
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 pb-4 border-b-2 border-primary">🏪 Paramètres de la Boutique</h2>
                
                <form method="POST" class="space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Nom de la boutique *</label>
                        <input type="text" name="nom_boutique" value="<?= htmlspecialchars($settings['nom_boutique'] ?? '') ?>" required 
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                        <p class="text-xs text-gray-500 mt-1">Sera affiché partout sur le site</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Slogan</label>
                        <input type="text" name="slogan" value="<?= htmlspecialchars($settings['slogan'] ?? '') ?>" 
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Email de contact *</label>
                        <input type="email" name="email_boutique" value="<?= htmlspecialchars($settings['email_boutique'] ?? '') ?>" required 
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                        <p class="text-xs text-gray-500 mt-1">Email visible par les clients</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">WhatsApp de contact *</label>
                        <input type="text" name="whatsapp_boutique" value="<?= htmlspecialchars($settings['whatsapp_boutique'] ?? '') ?>" required placeholder="+229 XX XX XX XX" 
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                        <p class="text-xs text-gray-500 mt-1">Numéro WhatsApp pour les clients</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Adresse physique</label>
                        <textarea name="adresse_boutique" rows="3" 
                                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all"><?= htmlspecialchars($settings['adresse_boutique'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Ville *</label>
                            <input type="text" name="ville" value="<?= htmlspecialchars($settings['ville'] ?? '') ?>" required 
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Pays *</label>
                            <input type="text" name="pays" value="<?= htmlspecialchars($settings['pays'] ?? '') ?>" required 
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Description de la boutique</label>
                        <textarea name="description_boutique" rows="4" 
                                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all"><?= htmlspecialchars($settings['description_boutique'] ?? '') ?></textarea>
                    </div>
                    
                    <button type="submit" name="update_boutique" 
                            class="w-full flex items-center justify-center gap-2 px-6 py-4 bg-gradient-to-r from-primary to-accent text-white text-lg font-bold rounded-xl hover:shadow-xl transition-all">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M7.707 10.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V6h5a2 2 0 012 2v7a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2h5v5.586l-1.293-1.293zM9 4a1 1 0 012 0v2H9V4z"/>
                        </svg>
                        Enregistrer les paramètres
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>
