<?php
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean($_POST['username']);
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        header('Location: admin_dashboard.php');
        exit;
    } else {
        $error = 'Identifiants incorrects';
    }
}

$settings = getBoutiqueSettings();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Admin - <?= htmlspecialchars($settings['nom_boutique'] ?? 'Boutique') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#667eea',
                        accent: '#f97316',
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo/Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Connexion Admin</h1>
            <p class="text-gray-600"><?= htmlspecialchars($settings['nom_boutique'] ?? 'Boutique') ?></p>
        </div>
        
        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <?php if ($error): ?>
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-xl">
                    <div class="flex items-center gap-3">
                        <svg class="w-6 h-6 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-red-700 font-semibold"><?= $error ?></p>
                    </div>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Nom d'utilisateur</label>
                    <input type="text" name="username" required 
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-primary focus:outline-none transition-all"
                           placeholder="Entrez votre nom d'utilisateur">
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Mot de passe</label>
                    <input type="password" name="password" required 
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-primary focus:outline-none transition-all"
                           placeholder="Entrez votre mot de passe">
                </div>
                
                <button type="submit" 
                        class="w-full px-6 py-4 bg-primary text-white text-lg font-bold rounded-xl hover:bg-primary/90 transition-all">
                    Se connecter
                </button>
            </form>
        </div>
        
        <!-- Back to Shop -->
        <div class="text-center mt-6">
            <a href="index.php" class="text-gray-600 hover:text-primary font-semibold transition-colors">
                ← Retour à la boutique
            </a>
        </div>
    </div>
</body>
</html>
