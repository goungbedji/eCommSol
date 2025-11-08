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
            // Mise à jour avec nouveau mot de passe
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE admins SET nom = ?, prenom = ?, email = ?, password = ? WHERE id = ?");
            $stmt->execute([$nom, $prenom, $email, $hashed, $_SESSION['admin_id']]);
        } else {
            // Mise à jour sans changer le mot de passe
            $stmt = $pdo->prepare("UPDATE admins SET nom = ?, prenom = ?, email = ? WHERE id = ?");
            $stmt->execute([$nom, $prenom, $email, $_SESSION['admin_id']]);
        }
        
        $success = 'Profil administrateur mis à jour avec succès !';
        $admin = getAdminInfo(); // Rafraîchir les infos
    } catch (Exception $e) {
        $error = 'Erreur : ' . $e->getMessage();
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
        // Vérifier si un enregistrement existe
        $check = $pdo->query("SELECT COUNT(*) FROM boutique_settings")->fetchColumn();
        
        if ($check > 0) {
            // Mise à jour
            $stmt = $pdo->prepare("UPDATE boutique_settings SET nom_boutique = ?, slogan = ?, email_boutique = ?, whatsapp_boutique = ?, adresse_boutique = ?, ville = ?, pays = ?, description_boutique = ? WHERE id = 1");
            $stmt->execute([$nom_boutique, $slogan, $email_boutique, $whatsapp_boutique, $adresse_boutique, $ville, $pays, $description_boutique]);
        } else {
            // Insertion
            $stmt = $pdo->prepare("INSERT INTO boutique_settings (nom_boutique, slogan, email_boutique, whatsapp_boutique, adresse_boutique, ville, pays, description_boutique) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nom_boutique, $slogan, $email_boutique, $whatsapp_boutique, $adresse_boutique, $ville, $pays, $description_boutique]);
        }
        
        $success = 'Paramètres de la boutique mis à jour avec succès !';
        $settings = getBoutiqueSettings(); // Rafraîchir les paramètres
    } catch (Exception $e) {
        $error = 'Erreur : ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres - Admin</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .settings-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 30px;
        }
        .settings-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .settings-card h2 {
            color: #667eea;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #667eea;
        }
        .info-display {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #666;
        }
        .info-value {
            color: #333;
        }
        @media (max-width: 968px) {
            .settings-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php
    $admin_active = 'settings';
    $admin_title = '⚙️ Paramètres';
    $admin_actions = [];
    include 'admin_header.php';
    ?>

    <div class="container">
        <?php if ($success): ?>
            <div class="success"><?= $success ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        
        <div class="settings-container">
            <!-- Profil Administrateur -->
            <div class="settings-card">
                <h2>👤 Mon Profil Administrateur</h2>
                
                <div class="info-display">
                    <div class="info-row">
                        <span class="info-label">Nom d'utilisateur :</span>
                        <span class="info-value"><?= htmlspecialchars($admin['username']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Nom complet :</span>
                        <span class="info-value"><?= htmlspecialchars($admin['prenom'] . ' ' . $admin['nom']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email :</span>
                        <span class="info-value"><?= htmlspecialchars($admin['email']) ?></span>
                    </div>
                </div>
                
                <form method="POST">
                    <div class="form-group">
                        <label>Prénom *</label>
                        <input type="text" name="prenom" value="<?= htmlspecialchars($admin['prenom']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Nom *</label>
                        <input type="text" name="nom" value="<?= htmlspecialchars($admin['nom']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($admin['email']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Nouveau mot de passe (laisser vide pour ne pas changer)</label>
                        <input type="password" name="new_password" placeholder="••••••••">
                        <div class="note">Minimum 6 caractères</div>
                    </div>
                    
                    <button type="submit" name="update_admin" class="btn">💾 Enregistrer mon profil</button>
                </form>
            </div>
            
            <!-- Paramètres Boutique -->
            <div class="settings-card">
                <h2>🏪 Paramètres de la Boutique</h2>
                
                <form method="POST">
                    <div class="form-group">
                        <label>Nom de la boutique *</label>
                        <input type="text" name="nom_boutique" value="<?= htmlspecialchars($settings['nom_boutique'] ?? '') ?>" required>
                        <div class="note">Sera affiché partout sur le site</div>
                    </div>
                    
                    <div class="form-group">
                        <label>Slogan</label>
                        <input type="text" name="slogan" value="<?= htmlspecialchars($settings['slogan'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Email de contact *</label>
                        <input type="email" name="email_boutique" value="<?= htmlspecialchars($settings['email_boutique'] ?? '') ?>" required>
                        <div class="note">Email visible par les clients</div>
                    </div>
                    
                    <div class="form-group">
                        <label>WhatsApp de contact *</label>
                        <input type="text" name="whatsapp_boutique" value="<?= htmlspecialchars($settings['whatsapp_boutique'] ?? '') ?>" required placeholder="+229 XX XX XX XX">
                        <div class="note">Numéro WhatsApp pour les clients</div>
                    </div>
                    
                    <div class="form-group">
                        <label>Adresse physique</label>
                        <textarea name="adresse_boutique" rows="3"><?= htmlspecialchars($settings['adresse_boutique'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Ville *</label>
                        <input type="text" name="ville" value="<?= htmlspecialchars($settings['ville'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Pays *</label>
                        <input type="text" name="pays" value="<?= htmlspecialchars($settings['pays'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Description de la boutique</label>
                        <textarea name="description_boutique" rows="4"><?= htmlspecialchars($settings['description_boutique'] ?? '') ?></textarea>
                    </div>

                    
                    
                    <button type="submit" name="update_boutique" class="btn">💾 Enregistrer les paramètres</button>
                </form>
            </div>
        </div>
    </div>

    
</body>
</html>