<?php
require_once 'config.php';
requireAdmin();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = strtoupper($_POST['code'] ?? '');
    $type = $_POST['type'] ?? '';
    $value = $_POST['value'] ?? '';
    $max_uses = $_POST['max_uses'] !== '' ? $_POST['max_uses'] : null;
    $expiry_date = $_POST['expiry_date'] ?? '';
    $min_purchase = $_POST['min_purchase'] !== '' ? $_POST['min_purchase'] : null;

    // Validation
    if (empty($code) || strlen($code) < 3) {
        $errors[] = "Le code doit faire au moins 3 caractères";
    }
    if (empty($type) || !in_array($type, ['percentage', 'fixed'])) {
        $errors[] = "Le type de réduction est invalide";
    }
    if (!is_numeric($value) || $value <= 0) {
        $errors[] = "La valeur de la réduction doit être un nombre positif";
    }
    if ($type === 'percentage' && $value > 100) {
        $errors[] = "Le pourcentage ne peut pas dépasser 100%";
    }
    if ($max_uses !== null && (!is_numeric($max_uses) || $max_uses < 1)) {
        $errors[] = "Le nombre d'utilisations doit être un nombre positif";
    }
    if (empty($expiry_date)) {
        $errors[] = "La date d'expiration est obligatoire";
    }
    if ($min_purchase !== null && (!is_numeric($min_purchase) || $min_purchase < 0)) {
        $errors[] = "Le montant minimum d'achat doit être un nombre positif";
    }

    // Vérifier si le code existe déjà
    $stmt = $pdo->prepare("SELECT id FROM coupons WHERE code = ?");
    $stmt->execute([$code]);
    if ($stmt->fetch()) {
        $errors[] = "Ce code existe déjà";
    }

    // Si pas d'erreurs, on enregistre
    if (empty($errors)) {
        $usage_limit = $max_uses;
        $times_used = $used_count = 0;

        $stmt = $pdo->prepare("INSERT INTO coupons (code, type, value, max_uses, usage_limit, times_used, used_count, expiry_date, min_purchase) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        try {
            $stmt->execute([
                $code,
                $type,
                $value,
                $max_uses,
                $usage_limit,
                $times_used,
                $used_count,
                $expiry_date,
                $min_purchase
            ]);
            $_SESSION['success_message'] = 'Coupon ajouté avec succès !';
            header('Location: admin_coupons.php');
            exit();
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de l'enregistrement : " . $e->getMessage();
        }
    }
}

$page_title = 'Ajouter un Coupon - Administration';
$admin_active = 'coupons';
$admin_title = 'Ajouter un Coupon';
$admin_actions = [];
?>
<?php include 'includes/admin_head.php'; ?>
<?php include 'includes/admin_header.php'; ?>

<div class="min-h-screen py-8">
    <div class="max-w-2xl mx-auto px-4 sm:px-6">
        
        <?php if (!empty($errors)): ?>
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-xl">
                <?php foreach ($errors as $error): ?>
                    <p class="text-red-700 font-semibold"><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow-lg p-8">
            <form method="POST" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Code du coupon *</label>
                        <input type="text" name="code" required minlength="3" 
                               value="<?= isset($_POST['code']) ? htmlspecialchars($_POST['code']) : '' ?>"
                               onkeyup="this.value = this.value.toUpperCase()"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Type de réduction *</label>
                        <select name="type" id="type" required
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:outline-none transition-all">
                            <option value="percentage" <?= isset($_POST['type']) && $_POST['type'] === 'percentage' ? 'selected' : '' ?>>Pourcentage</option>
                            <option value="fixed" <?= isset($_POST['type']) && $_POST['type'] === 'fixed' ? 'selected' : '' ?>>Montant fixe</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Valeur *</label>
                        <input type="number" step="0.01" name="value" id="value" required
                               value="<?= isset($_POST['value']) ? htmlspecialchars($_POST['value']) : '' ?>"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Utilisations max</label>
                        <input type="number" name="max_uses" min="1"
                               value="<?= isset($_POST['max_uses']) ? htmlspecialchars($_POST['max_uses']) : '' ?>"
                               placeholder="Illimité"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Date d'expiration *</label>
                        <input type="datetime-local" name="expiry_date" required
                               value="<?= isset($_POST['expiry_date']) ? htmlspecialchars($_POST['expiry_date']) : '' ?>"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Achat minimum (FCFA)</label>
                        <input type="number" step="0.01" name="min_purchase" min="0"
                               value="<?= isset($_POST['min_purchase']) ? htmlspecialchars($_POST['min_purchase']) : '' ?>"
                               placeholder="Aucun"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-primary focus:outline-none transition-all">
                    </div>
                </div>

                <div class="flex items-center gap-4 pt-4 border-t border-gray-200">
                    <button type="submit" 
                            class="px-8 py-3 bg-primary text-white font-bold rounded-lg hover:bg-primary/90 transition-all">
                        Enregistrer
                    </button>
                    <a href="admin_coupons.php" 
                       class="px-8 py-3 bg-gray-200 text-gray-700 font-bold rounded-lg hover:bg-gray-300 transition-all">
                        Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('type').addEventListener('change', function() {
        const valueInput = document.getElementById('value');
        if (this.value === 'percentage') {
            valueInput.setAttribute('max', '100');
        } else {
            valueInput.removeAttribute('max');
        }
    });
</script>

</body>
</html>
