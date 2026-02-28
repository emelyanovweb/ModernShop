<?php
// includes/footer.php
require_once dirname(__DIR__) . '/config.php';

// Получаем настройки для подвала
$siteName = getSetting('site_name', 'ModernShop');
$siteDescription = getSetting('site_description', 'Лучшие товары по лучшим ценам');
$contactEmail = getSetting('contact_email', 'info@modernshop.ru');
$contactPhone = getSetting('contact_phone', '+7 (999) 123-45-67');
$contactAddress = getSetting('contact_address', 'г. Москва, ул. Примерная, д. 1');
$copyright = getSetting('copyright', '© ' . date('Y') . ' ModernShop. Все права защищены.');

// Социальные сети
$telegram = getSetting('social_telegram', '#');
$vk = getSetting('social_vk', '#');
$instagram = getSetting('social_instagram', '#');
$youtube = getSetting('social_youtube', '#');
$whatsapp = getSetting('social_whatsapp', '#');

// Дополнительные настройки подвала
$showMap = getSetting('footer_show_map', 'false');
$mapUrl = getSetting('footer_map_url', '');
$footerColumns = getSetting('footer_columns', '3');
$footerBackground = getSetting('footer_background', '#ffffff');
$footerTextColor = getSetting('footer_text_color', '#2c3e50');
$footerLinkColor = getSetting('footer_link_color', '#4a90e2');
$footerPadding = getSetting('footer_padding', '60px 0 20px');
?>

    </main>
    
    <footer class="footer" style="
        background-color: <?php echo $footerBackground; ?>;
        color: <?php echo $footerTextColor; ?>;
        padding: <?php echo $footerPadding; ?>;
    ">
        <div class="container">
            <div class="footer-content" style="
                grid-template-columns: repeat(<?php echo $footerColumns; ?>, 1fr);
            ">
                <!-- О компании -->
                <div class="footer-section">
                    <h3 style="color: <?php echo $footerLinkColor; ?>;"><?php echo $siteName; ?></h3>
                    <p style="color: <?php echo $footerTextColor; ?>;"><?php echo $siteDescription; ?></p>
                    <?php if ($showMap == 'true' && $mapUrl): ?>
                        <div class="footer-map">
                            <iframe 
                                src="<?php echo $mapUrl; ?>" 
                                width="100%" 
                                height="200" 
                                style="border:0; border-radius: 8px; margin-top: 15px;" 
                                allowfullscreen="" 
                                loading="lazy">
                            </iframe>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Контакты -->
                <div class="footer-section">
                    <h4 style="color: <?php echo $footerLinkColor; ?>;">Контакты</h4>
                    <?php if ($contactEmail): ?>
                        <p style="color: <?php echo $footerTextColor; ?>;">
                            <i class="fas fa-envelope" style="color: <?php echo $footerLinkColor; ?>;"></i>
                            <a href="mailto:<?php echo $contactEmail; ?>" style="color: <?php echo $footerTextColor; ?>; text-decoration: none;">
                                <?php echo $contactEmail; ?>
                            </a>
                        </p>
                    <?php endif; ?>
                    
                    <?php if ($contactPhone): ?>
                        <p style="color: <?php echo $footerTextColor; ?>;">
                            <i class="fas fa-phone" style="color: <?php echo $footerLinkColor; ?>;"></i>
                            <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $contactPhone); ?>" style="color: <?php echo $footerTextColor; ?>; text-decoration: none;">
                                <?php echo $contactPhone; ?>
                            </a>
                        </p>
                    <?php endif; ?>
                    
                    <?php if ($contactAddress): ?>
                        <p style="color: <?php echo $footerTextColor; ?>;">
                            <i class="fas fa-map-marker-alt" style="color: <?php echo $footerLinkColor; ?>;"></i>
                            <?php echo $contactAddress; ?>
                        </p>
                    <?php endif; ?>
                </div>
                
                <!-- Социальные сети -->
                <div class="footer-section">
                    <h4 style="color: <?php echo $footerLinkColor; ?>;">Мы в соцсетях</h4>
                    <div class="social-links">
                        <?php if ($telegram != '#'): ?>
                            <a href="<?php echo $telegram; ?>" target="_blank" rel="noopener noreferrer" style="color: <?php echo $footerTextColor; ?>;">
                                <i class="fab fa-telegram"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($vk != '#'): ?>
                            <a href="<?php echo $vk; ?>" target="_blank" rel="noopener noreferrer" style="color: <?php echo $footerTextColor; ?>;">
                                <i class="fab fa-vk"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($instagram != '#'): ?>
                            <a href="<?php echo $instagram; ?>" target="_blank" rel="noopener noreferrer" style="color: <?php echo $footerTextColor; ?>;">
                                <i class="fab fa-instagram"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($youtube != '#'): ?>
                            <a href="<?php echo $youtube; ?>" target="_blank" rel="noopener noreferrer" style="color: <?php echo $footerTextColor; ?>;">
                                <i class="fab fa-youtube"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($whatsapp != '#'): ?>
                            <a href="<?php echo $whatsapp; ?>" target="_blank" rel="noopener noreferrer" style="color: <?php echo $footerTextColor; ?>;">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Дополнительные ссылки -->
                    <div class="footer-links" style="margin-top: 20px;">
                        <a href="/privacy" style="color: <?php echo $footerTextColor; ?>; text-decoration: none; margin-right: 15px;">Политика конфиденциальности</a>
                        <a href="/terms" style="color: <?php echo $footerTextColor; ?>; text-decoration: none;">Условия использования</a>
                    </div>
                </div>
                
                <!-- Новостная рассылка (опционально) -->
                <?php if (getSetting('footer_show_newsletter', 'false') == 'true'): ?>
                <div class="footer-section">
                    <h4 style="color: <?php echo $footerLinkColor; ?>;">Подписка на новости</h4>
                    <p style="color: <?php echo $footerTextColor; ?>;">Получайте свежие новости и акции первыми</p>
                    <form class="newsletter-form" style="margin-top: 15px;">
                        <input type="email" placeholder="Ваш email" style="
                            width: 100%;
                            padding: 10px;
                            border: 2px solid rgba(<?php echo hexdec(substr($footerLinkColor,1,2)); ?>, <?php echo hexdec(substr($footerLinkColor,3,2)); ?>, <?php echo hexdec(substr($footerLinkColor,5,2)); ?>, 0.3);
                            border-radius: 8px;
                            margin-bottom: 10px;
                        ">
                        <button type="submit" style="
                            width: 100%;
                            padding: 10px;
                            background: <?php echo $footerLinkColor; ?>;
                            color: white;
                            border: none;
                            border-radius: 8px;
                            cursor: pointer;
                            transition: all 0.3s;
                        ">Подписаться</button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Нижняя часть подвала -->
            <div class="footer-bottom" style="
                text-align: center;
                padding-top: 20px;
                margin-top: 40px;
                border-top: 1px solid rgba(<?php echo hexdec(substr($footerTextColor,1,2)); ?>, <?php echo hexdec(substr($footerTextColor,3,2)); ?>, <?php echo hexdec(substr($footerTextColor,5,2)); ?>, 0.1);
                color: <?php echo $footerTextColor; ?>;
            ">
                <p><?php echo $copyright; ?></p>
                <p style="font-size: 0.9rem; margin-top: 5px;">
                    Работает на ModernShop CMS
                </p>
            </div>
        </div>
    </footer>
    
    <!-- Кнопка "Наверх" -->
    <button id="scrollToTop" class="scroll-top" style="
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: <?php echo $footerLinkColor; ?>;
        color: white;
        border: none;
        cursor: pointer;
        display: none;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        transition: all 0.3s;
        z-index: 999;
    ">
        <i class="fas fa-arrow-up"></i>
    </button>
    
    <script src="script.js"></script>
    
    <script>
    // Кнопка "Наверх"
    const scrollButton = document.getElementById('scrollToTop');
    
    window.addEventListener('scroll', function() {
        if (window.scrollY > 500) {
            scrollButton.style.display = 'flex';
        } else {
            scrollButton.style.display = 'none';
        }
    });
    
    scrollButton.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    
    // Анимация для социальных ссылок
    document.querySelectorAll('.social-links a').forEach(link => {
        link.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-3px)';
            this.style.color = '<?php echo $footerLinkColor; ?>';
        });
        
        link.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.color = '<?php echo $footerTextColor; ?>';
        });
    });
    
    // Новостная подписка
    document.querySelectorAll('.newsletter-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const email = this.querySelector('input[type="email"]').value;
            if (email) {
                alert('Спасибо за подписку! (демо-режим)');
                this.reset();
            }
        });
    });
    </script>
</body>
</html>