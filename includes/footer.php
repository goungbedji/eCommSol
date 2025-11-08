<?php
// Footer dynamique avec les informations de la boutique
$settings = getBoutiqueSettings();
?>
<style>
    .site-footer {
        background: var(--panel-bg);
        color: var(--text);
        padding: 50px 20px 20px;
        margin-top: 60px;
    }
    .footer-content {
        max-width: 1200px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 40px;
        margin-bottom: 30px;
    }
    .footer-section h3 {
        color: var(--primary);
        margin-bottom: 20px;
        font-size: 1.2rem;
    }
    .slogan { font-style: italic; color: var(--primary); }
    .footer-section p, .footer-section a {
        color: var(--muted);
        line-height: 1.8;
        text-decoration: none;
        display: block;
        margin-bottom: 10px;
    }
    .footer-section a:hover {
        color: var(--text);
    }
    .contact-item {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 15px;
    }
    .contact-icon {
        font-size: 1.5rem;
    }
    .footer-bottom {
        text-align: center;
        padding-top: 20px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        color: rgba(255, 255, 255, 0.6);
    }
    @media (max-width: 768px) {
        .footer-content {
            grid-template-columns: 1fr;
        }
    }
</style>

<footer class="site-footer">
    <div class="footer-content">
        <!-- À propos -->
        <div class="footer-section">
            <h3><?= htmlspecialchars($settings['nom_boutique'] ?? '') ?></h3>
            <p><?= htmlspecialchars($settings['description_boutique'] ?? '') ?></p>
            <?php if (!empty($settings['slogan'])): ?>
                <p class="slogan">"<?= htmlspecialchars($settings['slogan'] ?? '') ?>"</p>
            <?php endif; ?>
        </div>
        
        <!-- Navigation -->
        <div class="footer-section">
            <h3>Navigation</h3>
            <a href="home.php">🏠 Accueil</a>
            <a href="index.php">🛍️ Boutique</a>
            <a href="cart.php">🛒 Panier</a>
            <a href="admin_login.php">🔐 Administration</a>
        </div>
        
        <!-- Contact -->
        <div class="footer-section">
            <h3>Contactez-nous</h3>
            
            <div class="contact-item">
                <span class="contact-icon">📧</span>
                <a href="mailto:<?= htmlspecialchars($settings['email_boutique'] ?? '') ?>">
                    <?= htmlspecialchars($settings['email_boutique'] ?? '') ?>
                </a>
            </div>
            
            <div class="contact-item">
                <span class="contact-icon">📱</span>
                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $settings['whatsapp_boutique'] ?? '') ?>" target="_blank">
                    <?= htmlspecialchars($settings['whatsapp_boutique'] ?? '') ?>
                </a>
            </div>
            
            <?php if (!empty($settings['adresse_boutique'])): ?>
            <div class="contact-item">
                <span class="contact-icon">📍</span>
                <span>
                    <?= htmlspecialchars($settings['adresse_boutique'] ?? '') ?><br>
                    <?= htmlspecialchars($settings['ville'] ?? '') ?>, <?= htmlspecialchars($settings['pays'] ?? '') ?>
                </span>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Horaires / Infos -->
        <div class="footer-section">
            <h3>Informations</h3>
            <p><strong>Livraison :</strong> Partout au <?= htmlspecialchars($settings['pays'] ?? '') ?></p>
            <p><strong>Paiement :</strong> À la livraison</p>
            <p><strong>Support :</strong> 7j/7</p>
            <?php
            $total_products = $GLOBALS['pdo']->query("SELECT COUNT(*) FROM articles WHERE stock > 0")->fetchColumn();
            ?>
        </div>
    </div>
    
    <div class="footer-bottom">
    <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($settings['nom_boutique'] ?? '') ?>. Tous droits réservés.</p>
        <p style="margin-top: 10px;">Propulsé par EComm-Sol</p>
    </div>
</footer>