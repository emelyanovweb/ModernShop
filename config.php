<?php
session_start();

$host = 'localhost';
$dbname = '';
$username = '';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Создание таблиц, если они не существуют
    createTables($pdo);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Инициализация корзины
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Функция создания таблиц
function createTables($pdo) {
    
    
    
    // Таблица категорий
    $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Таблица товаров
    $pdo->exec("CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category_id INT,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10, 2) NOT NULL,
        image VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
    )");
    
    // Таблица скидок
    $pdo->exec("CREATE TABLE IF NOT EXISTS discounts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) UNIQUE NOT NULL,
        type ENUM('percentage', 'fixed') NOT NULL,
        value DECIMAL(10, 2) NOT NULL,
        valid_until DATETIME,
        max_uses INT DEFAULT 1,
        used INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Таблица настроек магазина
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) UNIQUE NOT NULL,
        setting_value TEXT,
        setting_type ENUM('text', 'color', 'number', 'select', 'image', 'textarea') DEFAULT 'text',
        setting_group VARCHAR(50) DEFAULT 'general',
        setting_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    // Таблица для пользовательских CSS
    $pdo->exec("CREATE TABLE IF NOT EXISTS custom_css (
        id INT AUTO_INCREMENT PRIMARY KEY,
        css_code TEXT,
        is_active BOOLEAN DEFAULT true,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    // Добавление тестовых данных, если таблицы пусты
    $stmt = $pdo->query("SELECT COUNT(*) FROM categories");
    if ($stmt->fetchColumn() == 0) {
        // Тестовые категории
        $pdo->exec("INSERT INTO categories (name, description) VALUES 
            ('Электроника', 'Современные гаджеты и устройства'),
            ('Одежда', 'Модная одежда для всей семьи'),
            ('Книги', 'Лучшие книги от популярных авторов')");
        
        // Тестовые товары
        $pdo->exec("INSERT INTO products (category_id, name, description, price, image) VALUES 
            (1, 'Смартфон XYZ', 'Новейший смартфон с отличной камерой', 29999.99, 'phone.jpg'),
            (1, 'Ноутбук Pro', 'Мощный ноутбук для работы и игр', 59999.99, 'laptop.jpg'),
            (2, 'Футболка Classic', 'Удобная хлопковая футболка', 999.99, 'tshirt.jpg'),
            (3, 'Программирование на PHP', 'Книга для начинающих разработчиков', 1499.99, 'book.jpg')");
        
        // Тестовые скидки
        $pdo->exec("INSERT INTO discounts (code, type, value, valid_until, max_uses) VALUES 
            ('WELCOME10', 'percentage', 10, DATE_ADD(NOW(), INTERVAL 30 DAY), 100),
            ('SUMMER500', 'fixed', 500, DATE_ADD(NOW(), INTERVAL 60 DAY), 50)");
    }
    
    // Добавление настроек по умолчанию
    $defaultSettings = [
        
        // Добавьте в массив $defaultSettings в config.php:

// Настройки подвала
['site_name', 'ModernShop', 'text', 'footer', 100],
['site_description', 'Лучшие товары по лучшим ценам', 'textarea', 'footer', 101],
['contact_email', 'info@modernshop.ru', 'text', 'footer', 102],
['contact_phone', '+7 (999) 123-45-67', 'text', 'footer', 103],
['contact_address', 'г. Москва, ул. Примерная, д. 1', 'text', 'footer', 104],
['copyright', '© 2026 ModernShop. Все права защищены.', 'text', 'footer', 105],

// Социальные сети
['social_telegram', '#', 'text', 'footer', 110],
['social_vk', '#', 'text', 'footer', 111],
['social_instagram', '#', 'text', 'footer', 112],
['social_youtube', '#', 'text', 'footer', 113],
['social_whatsapp', '#', 'text', 'footer', 114],

// Дизайн подвала
['footer_background', '#ffffff', 'color', 'footer', 120],
['footer_text_color', '#2c3e50', 'color', 'footer', 121],
['footer_link_color', '#4a90e2', 'color', 'footer', 122],
['footer_padding', '60px 0 20px', 'text', 'footer', 123],
['footer_columns', '3', 'number', 'footer', 124],

// Дополнительные функции
['footer_show_map', 'false', 'select', 'footer', 130],
['footer_map_url', '', 'text', 'footer', 131],
['footer_show_newsletter', 'false', 'select', 'footer', 132],

        // Общие настройки
        ['site_name', 'ModernShop', 'text', 'general', 1],
        ['site_description', 'Лучший интернет-магазин', 'textarea', 'general', 2],
        ['site_logo', '', 'image', 'general', 3],
        ['favicon', '', 'image', 'general', 4],
        ['admin_email', 'admin@modernshop.ru', 'text', 'general', 5],
        ['site_phone', '+7 (999) 123-45-67', 'text', 'general', 6],
        ['currency', 'RUB', 'select', 'general', 7],
        ['products_per_page', '12', 'number', 'general', 8],
        ['enable_discounts', 'true', 'select', 'general', 9],
        ['enable_reviews', 'true', 'select', 'general', 10],
        
        // Цвета
        ['primary_color', '#4a90e2', 'color', 'colors', 10],
        ['primary_light', '#6ba5e8', 'color', 'colors', 11],
        ['primary_dark', '#3a7bc8', 'color', 'colors', 12],
        ['secondary_color', '#ffffff', 'color', 'colors', 13],
        ['text_color', '#2c3e50', 'color', 'colors', 14],
        ['text_light', '#7f8c8d', 'color', 'colors', 15],
        ['background_color', '#f8fafc', 'color', 'colors', 16],
        ['card_background', '#ffffff', 'color', 'colors', 17],
        ['success_color', '#38a169', 'color', 'colors', 18],
        ['error_color', '#e53e3e', 'color', 'colors', 19],
        ['warning_color', '#d69e2e', 'color', 'colors', 20],
        
        // Кнопки
        ['button_radius', '4%', 'text', 'buttons', 30],
        ['button_padding', '12px 24px', 'text', 'buttons', 31],
        ['button_font_size', '1rem', 'text', 'buttons', 32],
        ['button_font_weight', '600', 'text', 'buttons', 33],
        ['button_shadow', '0 4px 6px rgba(0,0,0,0.1)', 'text', 'buttons', 34],
        ['button_hover_scale', '1.02', 'text', 'buttons', 35],
        
        // Расположение
        ['header_position', 'sticky', 'select', 'layout', 40],
        ['header_height', '70px', 'text', 'layout', 41],
        ['logo_position', 'left', 'select', 'layout', 42],
        ['nav_position', 'right', 'select', 'layout', 43],
        ['cart_position', 'right', 'select', 'layout', 44],
        ['products_per_row_desktop', '4', 'number', 'layout', 45],
        ['products_per_row_tablet', '2', 'number', 'layout', 46],
        ['products_per_row_mobile', '1', 'number', 'layout', 47],
        
        // Шрифты
        ['font_family', 'Inter', 'text', 'fonts', 50],
        ['font_size_base', '16px', 'text', 'fonts', 51],
        ['heading_font_weight', '700', 'text', 'fonts', 52],
        
        // Плавающая кнопка
        ['floating_button_enabled', 'true', 'select', 'floating', 60],
        ['floating_button_animation', 'float', 'select', 'floating', 61],
        ['floating_button_bottom', '30px', 'text', 'floating', 62],
        
        // Корзина
        ['cart_icon_color', '#2c3e50', 'color', 'cart', 70],
        ['cart_count_bg', '#4a90e2', 'color', 'cart', 71],
        ['cart_count_color', '#ffffff', 'color', 'cart', 72],
        
        // Товары
        ['product_image_height', '200px', 'text', 'products', 80],
        ['product_border_radius', '12px', 'text', 'products', 81],
        ['product_shadow', '0 4px 6px rgba(0,0,0,0.05)', 'text', 'products', 82],
        ['product_shadow_hover', '0 10px 15px rgba(0,0,0,0.1)', 'text', 'products', 83],
        
        // Категории
        ['category_image_height', '150px', 'text', 'categories', 90],
        ['category_border_radius', '12px', 'text', 'categories', 91],
    ];

    // Проверяем и добавляем настройки
    foreach ($defaultSettings as $setting) {
        $stmt = $pdo->prepare("SELECT id FROM settings WHERE setting_key = ?");
        $stmt->execute([$setting[0]]);
        if (!$stmt->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value, setting_type, setting_group, setting_order) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute($setting);
        }
    }

    // Добавляем пользовательский CSS по умолчанию
    $stmt = $pdo->query("SELECT id FROM custom_css LIMIT 1");
    if (!$stmt->fetch()) {
        $pdo->exec("INSERT INTO custom_css (css_code) VALUES ('/* Пользовательские стили */')");
    }
}

// Функция получения настроек
function getSettings($group = null) {
    global $pdo;
    
    $sql = "SELECT * FROM settings";
    $params = [];
    
    if ($group) {
        $sql .= " WHERE setting_group = ? ORDER BY setting_order";
        $params[] = $group;
    } else {
        $sql .= " ORDER BY setting_group, setting_order";
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    $settings = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $settings[$row['setting_key']] = $row;
    }
    
    return $settings;
}

// Функция получения значения настройки
function getSetting($key, $default = '') {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result ? $result['setting_value'] : $default;
}

// Функция обновления настройки
function updateSetting($key, $value) {
    global $pdo;
    
    $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
    return $stmt->execute([$value, $key]);
}

// Функция генерации CSS из настроек
function generateCustomCSS() {
    global $pdo;
    
    $css = ":root {\n";
    
    // Получаем все цветовые настройки
    $settings = getSettings('colors');
    foreach ($settings as $key => $setting) {
        $css .= "    --{$key}: " . $setting['setting_value'] . ";\n";
    }
    
    // Добавляем настройки кнопок
    $buttonSettings = getSettings('buttons');
    foreach ($buttonSettings as $key => $setting) {
        $css .= "    --{$key}: " . $setting['setting_value'] . ";\n";
    }
    
    // Добавляем настройки расположения
    $layoutSettings = getSettings('layout');
    foreach ($layoutSettings as $key => $setting) {
        $css .= "    --{$key}: " . $setting['setting_value'] . ";\n";
    }
    
    // Добавляем настройки шрифтов
    $fontSettings = getSettings('fonts');
    foreach ($fontSettings as $key => $setting) {
        if ($key == 'font_family') {
            $css .= "    --{$key}: '" . $setting['setting_value'] . "', sans-serif;\n";
        } else {
            $css .= "    --{$key}: " . $setting['setting_value'] . ";\n";
        }
    }
    
    // Добавляем настройки товаров
    $productSettings = getSettings('products');
    foreach ($productSettings as $key => $setting) {
        $css .= "    --{$key}: " . $setting['setting_value'] . ";\n";
    }
    
    // Добавляем настройки категорий
    $categorySettings = getSettings('categories');
    foreach ($categorySettings as $key => $setting) {
        $css .= "    --{$key}: " . $setting['setting_value'] . ";\n";
    }
    
    $css .= "}\n\n";
    
    // Добавляем специфические стили для корзины
    $cartIconColor = getSetting('cart_icon_color', '#2c3e50');
    $cartCountBg = getSetting('cart_count_bg', '#4a90e2');
    $cartCountColor = getSetting('cart_count_color', '#ffffff');
    
    $css .= ".cart-icon { color: {$cartIconColor}; }\n";
    $css .= ".cart-count { background: {$cartCountBg}; color: {$cartCountColor}; }\n";
    
    // Добавляем стили для плавающей кнопки
    $floatingEnabled = getSetting('floating_button_enabled', 'true');
    if ($floatingEnabled == 'false') {
        $css .= ".floating-cart-btn { display: none; }\n";
    } else {
        $bottom = getSetting('floating_button_bottom', '30px');
        $animation = getSetting('floating_button_animation', 'float');
        
        $css .= ".floating-cart-btn { bottom: {$bottom}; }\n";
        
        if ($animation == 'none') {
            $css .= ".floating-cart-btn { animation: none; }\n";
        } elseif ($animation == 'pulse') {
            $css .= "@keyframes pulse {\n";
            $css .= "    0%, 100% { transform: translateX(-50%) scale(1); }\n";
            $css .= "    50% { transform: translateX(-50%) scale(1.05); }\n";
            $css .= "}\n";
            $css .= ".floating-cart-btn { animation: pulse 2s ease-in-out infinite; }\n";
        }
    }
    
    // Добавляем стили для шапки
    $headerPosition = getSetting('header_position', 'sticky');
    $headerHeight = getSetting('header_height', '70px');
    
    $css .= ".header { position: {$headerPosition}; height: {$headerHeight}; }\n";
    
    // Добавляем стили для сетки товаров
    $desktopCols = getSetting('products_per_row_desktop', '4');
    $tabletCols = getSetting('products_per_row_tablet', '2');
    $mobileCols = getSetting('products_per_row_mobile', '1');
    
    $css .= "@media (min-width: 1024px) { .products-grid { grid-template-columns: repeat({$desktopCols}, 1fr); } }\n";
    $css .= "@media (min-width: 768px) and (max-width: 1023px) { .products-grid { grid-template-columns: repeat({$tabletCols}, 1fr); } }\n";
    $css .= "@media (max-width: 767px) { .products-grid { grid-template-columns: repeat({$mobileCols}, 1fr); } }\n";
    
    // Получаем пользовательский CSS
    $stmt = $pdo->query("SELECT css_code FROM custom_css WHERE is_active = true ORDER BY id DESC LIMIT 1");
    $customCSS = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($customCSS) {
        $css .= "\n" . $customCSS['css_code'];
    }
    
    return $css;
}

// Функция для сохранения CSS в файл
function saveCustomCSS() {
    $css = generateCustomCSS();
    file_put_contents('custom.css', $css);
}

// Функция для применения скидки
function applyDiscount($code, $total) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM discounts WHERE code = ? AND valid_until >= NOW() AND used < max_uses");
    $stmt->execute([$code]);
    $discount = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($discount) {
        if ($discount['type'] == 'percentage') {
            $total = $total * (1 - $discount['value'] / 100);
        } else {
            $total = max(0, $total - $discount['value']);
        }
        
        // Увеличиваем счетчик использований
        $stmt = $pdo->prepare("UPDATE discounts SET used = used + 1 WHERE id = ?");
        $stmt->execute([$discount['id']]);
        
        return ['total' => $total, 'discount' => $discount];
    }
    return ['total' => $total, 'discount' => null];
}

// Функция для получения общей суммы корзины
function getCartTotal() {
    $total = 0;
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }
    }
    return $total;
}

// Функция для очистки корзины
function clearCart() {
    $_SESSION['cart'] = [];
    unset($_SESSION['discount']);
}
?>