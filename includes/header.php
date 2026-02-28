<?php
// includes/header.php
require_once dirname(__DIR__) . '/config.php';

// Получаем количество товаров в корзине
$cartCount = array_sum(array_column($_SESSION['cart'], 'quantity'));

// Получаем настройки для шапки
$siteName = getSetting('site_name', 'ModernShop');
$logoPosition = getSetting('logo_position', 'left');
$navPosition = getSetting('nav_position', 'right');
$cartPosition = getSetting('cart_position', 'right');

// Получаем все категории для меню
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name LIMIT 10");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
$totalCategories = count($categories);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo $siteName; ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="custom.css.php?<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=<?php echo urlencode(getSetting('font_family', 'Inter')); ?>:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <?php if (getSetting('favicon')): ?>
    <link rel="icon" href="uploads/<?php echo getSetting('favicon'); ?>" type="image/x-icon">
    <?php endif; ?>
    
    <style>
        /* Динамические стили на основе настроек */
        .header-content {
            justify-content: <?php 
                if ($logoPosition == 'center' && $navPosition == 'center') echo 'center';
                elseif ($logoPosition == 'left' && $navPosition == 'right') echo 'space-between';
                elseif ($logoPosition == 'right' && $navPosition == 'left') echo 'space-between';
                else echo 'flex-start';
            ?>;
        }
        
        .logo {
            order: <?php echo $logoPosition == 'right' ? 3 : 1; ?>;
        }
        
        .nav-menu {
            order: <?php echo $navPosition == 'right' ? 2 : 1; ?>;
            margin-<?php echo $navPosition == 'center' ? '0 auto' : '0'; ?>;
        }
        
        .header-actions {
            order: <?php echo $cartPosition == 'right' ? 3 : 1; ?>;
        }
        
        /* Стили для бургер-меню */
        .burger-menu {
            display: none;
            flex-direction: column;
            cursor: pointer;
            padding: 10px;
            z-index: 1001;
        }
        
        .burger-line {
            width: 25px;
            height: 3px;
            background-color: var(--text-color);
            margin: 3px 0;
            transition: all 0.3s ease;
            border-radius: 3px;
        }
        
        .burger-menu.active .burger-line:nth-child(1) {
            transform: rotate(45deg) translate(5px, 5px);
        }
        
        .burger-menu.active .burger-line:nth-child(2) {
            opacity: 0;
        }
        
        .burger-menu.active .burger-line:nth-child(3) {
            transform: rotate(-45deg) translate(7px, -7px);
        }
        
        .mobile-nav-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .mobile-nav-overlay.active {
            display: block;
            opacity: 1;
        }
        
        .mobile-nav {
            position: fixed;
            top: 0;
            right: -100%;
            width: 80%;
            max-width: 400px;
            height: 100vh;
            background: var(--card-background);
            z-index: 1000;
            transition: right 0.3s ease;
            box-shadow: -5px 0 15px rgba(0,0,0,0.1);
            overflow-y: auto;
        }
        
        .mobile-nav.active {
            right: 0;
        }
        
        .mobile-nav-header {
            padding: 20px;
            border-bottom: 1px solid #edf2f7;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .mobile-nav-header img {
            height: 40px;
        }
        
        .mobile-nav-header h3 {
            font-size: 1.2rem;
            color: var(--text-color);
            margin: 0;
        }
        
        .mobile-nav-categories {
            padding: 20px;
        }
        
        .mobile-nav-categories h4 {
            margin-bottom: 15px;
            color: var(--text-light);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .mobile-nav-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .mobile-nav-links li {
            border-bottom: 1px solid #edf2f7;
        }
        
        .mobile-nav-links a {
            display: block;
            padding: 15px 20px;
            color: var(--text-color);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            position: relative;
        }
        
        .mobile-nav-links a:hover {
            background: #f7f9fc;
            color: var(--primary-color);
            padding-left: 25px;
        }
        
        .mobile-nav-links a i {
            margin-right: 10px;
            width: 20px;
            color: var(--primary-color);
        }
        
        .mobile-nav-footer {
            padding: 20px;
            border-top: 1px solid #edf2f7;
            margin-top: 20px;
        }
        
        .mobile-nav-footer .social-links {
            display: flex;
            gap: 15px;
            justify-content: center;
        }
        
        .mobile-nav-footer .social-links a {
            color: var(--text-light);
            font-size: 1.3rem;
            transition: color 0.3s;
        }
        
        .mobile-nav-footer .social-links a:hover {
            color: var(--primary-color);
        }
        
        @media (max-width: 768px) {
            .nav-menu {
                display: none;
            }
            
            .burger-menu {
                display: flex;
            }
            
            .header-content {
                justify-content: space-between !important;
            }
            
            .logo {
                order: 2 !important;
            }
            
            .burger-menu {
                order: 1 !important;
            }
            
            .header-actions {
                order: 3 !important;
            }
        }
    </style>
</head>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const burgerMenu = document.getElementById('burgerMenu');
    const mobileNav = document.getElementById('mobileNav');
    const navOverlay = document.getElementById('navOverlay');
    
    function toggleMenu() {
        burgerMenu.classList.toggle('active');
        mobileNav.classList.toggle('active');
        navOverlay.classList.toggle('active');
        
        // Блокируем прокрутку body при открытом меню
        if (mobileNav.classList.contains('active')) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    }
    
    burgerMenu.addEventListener('click', toggleMenu);
    
    navOverlay.addEventListener('click', toggleMenu);
    
    // Закрываем меню при нажатии на ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && mobileNav.classList.contains('active')) {
            toggleMenu();
        }
    });
    
    // Закрываем меню при изменении размера окна (если стали десктопом)
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768 && mobileNav.classList.contains('active')) {
            toggleMenu();
        }
    });
    
    // Добавляем активный класс для текущей страницы
    const currentPath = window.location.pathname;
    const currentPage = currentPath.split('/').pop();
    
    document.querySelectorAll('.nav-link, .mobile-nav-links a').forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPage || (currentPage === '' && href === 'index.php')) {
            link.style.color = 'var(--primary-color)';
            link.style.fontWeight = '600';
        }
    });
});
</script>

<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <!-- Бургер-меню (показываем если категорий > 5 или на мобильных) -->
                <div class="burger-menu" id="burgerMenu">
                    <span class="burger-line"></span>
                    <span class="burger-line"></span>
                    <span class="burger-line"></span>
                </div>
                
                <a href="index.php" class="logo">
                    <?php if (getSetting('site_logo') && file_exists('uploads/' . getSetting('site_logo'))): ?>
                        <img src="uploads/<?php echo getSetting('site_logo'); ?>" alt="<?php echo $siteName; ?>" style="height: 40px;">
                    <?php else: ?>
                        <i class="fas fa-store"></i>
                        <?php echo $siteName; ?>
                    <?php endif; ?>
                </a>
                
                <!-- Десктопное меню (показываем только если категорий <= 5) -->
                <?php if ($totalCategories <= 5): ?>
                <nav class="nav-menu">
                    <a href="index.php" class="nav-link">Главная</a>
                    <?php foreach ($categories as $category): ?>
                        <a href="index.php?category=<?php echo $category['id']; ?>" class="nav-link">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </a>
                    <?php endforeach; ?>
                    <?php if (isset($_GET['admin']) || basename($_SERVER['PHP_SELF']) == 'admin.php'): ?>
                        <a href="admin.php" class="nav-link">Админка</a>
                    <?php endif; ?>
                </nav>
                <?php endif; ?>
                
                <div class="header-actions">
                    <a href="cart.php" class="cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                        <?php if ($cartCount > 0): ?>
                            <span class="cart-count"><?php echo $cartCount; ?></span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Оверлей для мобильного меню -->
    <div class="mobile-nav-overlay" id="navOverlay"></div>
    
    <!-- Мобильное/выдвижное меню (для всех случаев, когда категорий > 5) -->
    <div class="mobile-nav" id="mobileNav">
        <div class="mobile-nav-header">
            <?php if (getSetting('site_logo') && file_exists('uploads/' . getSetting('site_logo'))): ?>
                <img src="uploads/<?php echo getSetting('site_logo'); ?>" alt="<?php echo $siteName; ?>">
            <?php else: ?>
                <i class="fas fa-store" style="font-size: 2rem; color: var(--primary-color);"></i>
            <?php endif; ?>
            <h3><?php echo $siteName; ?></h3>
        </div>
        
        <div class="mobile-nav-categories">
            <h4><i class="fas fa-folder"></i> Категории</h4>
            <ul class="mobile-nav-links">
                <li>
                    <a href="index.php">
                        <i class="fas fa-home"></i>
                        Главная
                    </a>
                </li>
                <?php foreach ($categories as $category): ?>
                    <li>
                        <a href="index.php?category=<?php echo $category['id']; ?>">
                            <i class="fas fa-tag"></i>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
                <?php if (isset($_GET['admin']) || basename($_SERVER['PHP_SELF']) == 'admin.php'): ?>
                    <li>
                        <a href="admin.php">
                            <i class="fas fa-cog"></i>
                            Админка
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
        
        <div class="mobile-nav-footer">
            <div class="social-links">
                <a href="#"><i class="fab fa-telegram"></i></a>
                <a href="#"><i class="fab fa-vk"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
            </div>
        </div>
    </div>
    
    <main class="main-content">