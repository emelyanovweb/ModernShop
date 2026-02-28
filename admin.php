<?php

require_once 'config.php';

// Простая аутентификация (в реальном проекте используйте нормальную систему)
session_start();
$isAdmin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// Обработка логина
if (isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // В демо-версии используем простые credentials
    if ($username === 'admin' && $password === 'admin') {
        $_SESSION['admin_logged_in'] = true;
        $isAdmin = true;
    } else {
        $loginError = 'Неверное имя пользователя или пароль';
    }
}

// Обработка выхода
if (isset($_GET['logout'])) {
    unset($_SESSION['admin_logged_in']);
    header('Location: admin.php');
    exit;
}

// Если не админ, показываем форму входа
if (!$isAdmin) {
    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Вход в админ-панель</title>
        <link rel="stylesheet" href="style.css">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <style>
            :root {
                --primary-color: #4a90e2;
                --primary-light: #6ba5e8;
                --primary-dark: #3a7bc8;
                --border-radius: 12px;
                --shadow-hover: 0 10px 15px rgba(0,0,0,0.1);
                --text-color: #2c3e50;
                --text-light: #7f8c8d;
            }
            
            .login-container {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
                padding: 20px;
            }
            
            .login-box {
                background: white;
                padding: 40px;
                border-radius: var(--border-radius);
                box-shadow: var(--shadow-hover);
                max-width: 400px;
                width: 100%;
            }
            
            .login-box h1 {
                text-align: center;
                margin-bottom: 30px;
                color: var(--text-color);
                font-size: 1.8rem;
            }
            
            .login-box h1 i {
                color: var(--primary-color);
                margin-right: 10px;
            }
            
            .login-form .form-group {
                margin-bottom: 20px;
            }
            
            .login-form label {
                display: block;
                margin-bottom: 5px;
                color: var(--text-color);
                font-weight: 500;
            }
            
            .login-form input {
                width: 100%;
                padding: 12px;
                border: 2px solid #e2e8f0;
                border-radius: 8px;
                font-size: 1rem;
                transition: border-color 0.3s;
            }
            
            .login-form input:focus {
                outline: none;
                border-color: var(--primary-color);
            }
            
            .login-form button {
                width: 100%;
                padding: 14px;
                background: var(--primary-color);
                color: white;
                border: none;
                border-radius: 8px;
                font-size: 1.1rem;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s;
            }
            
            .login-form button:hover {
                background: var(--primary-dark);
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(74, 144, 226, 0.3);
            }
            
            .error-message {
                background: #fee2e2;
                color: #e53e3e;
                padding: 12px;
                border-radius: 8px;
                margin-bottom: 20px;
                text-align: center;
                border: 1px solid #feb2b2;
            }
            
            .demo-credentials {
                margin-top: 20px;
                padding: 15px;
                background: #f7f9fc;
                border-radius: 8px;
                text-align: center;
                font-size: 0.9rem;
                color: var(--text-light);
            }
            
            .demo-credentials p {
                margin: 5px 0;
            }
            
            .demo-credentials strong {
                color: var(--primary-color);
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <div class="login-box">
                <h1>
                    <i class="fas fa-lock"></i>
                    Админ-панель
                </h1>
                
                <?php if (isset($loginError)): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $loginError; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="login-form">
                    <div class="form-group">
                        <label>
                            <i class="fas fa-user"></i>
                            Имя пользователя
                        </label>
                        <input type="text" name="username" required placeholder="admin">
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <i class="fas fa-key"></i>
                            Пароль
                        </label>
                        <input type="password" name="password" required placeholder="••••••••">
                    </div>
                    
                    <button type="submit" name="login">
                        <i class="fas fa-sign-in-alt"></i>
                        Войти
                    </button>
                </form>
                
                <div class="demo-credentials">
                    <p><i class="fas fa-info-circle"></i> Демо-доступ:</p>
                    <p><strong>Логин:</strong> admin</p>
                    <p><strong>Пароль:</strong> admin</p>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Обработка действий админки
$message = '';
$messageType = '';

// Сохранение настроек кастомизации
if (isset($_POST['save_settings'])) {
    foreach ($_POST['settings'] as $key => $value) {
        updateSetting($key, $value);
    }
    saveCustomCSS();
    $message = 'Настройки сохранены';
    $messageType = 'success';
}

// Сохранение пользовательского CSS
if (isset($_POST['save_custom_css'])) {
    $css_code = $_POST['custom_css'];
    
    // Проверяем, существует ли запись
    $stmt = $pdo->query("SELECT id FROM custom_css LIMIT 1");
    if ($stmt->fetch()) {
        $stmt = $pdo->prepare("UPDATE custom_css SET css_code = ? WHERE is_active = true");
    } else {
        $stmt = $pdo->prepare("INSERT INTO custom_css (css_code, is_active) VALUES (?, true)");
    }
    $stmt->execute([$css_code]);
    
    saveCustomCSS();
    $message = 'CSS сохранен';
    $messageType = 'success';
}

// Сброс настроек
if (isset($_POST['reset_settings'])) {
    // Очищаем таблицы настроек
    $pdo->exec("DELETE FROM settings");
    $pdo->exec("DELETE FROM custom_css");
    
    // Пересоздаем настройки через функцию из config.php
    createTables($pdo);
    
    $message = 'Настройки сброшены к значениям по умолчанию';
    $messageType = 'success';
}

// Обновление порядка сортировки категорий
if (isset($_POST['update_category_order'])) {
    $id = (int)$_POST['id'];
    $sort_order = (int)$_POST['sort_order'];
    
    $stmt = $pdo->prepare("UPDATE categories SET sort_order = ? WHERE id = ?");
    $stmt->execute([$sort_order, $id]);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}

// Обновление статуса категории
if (isset($_POST['toggle_category_status'])) {
    $id = (int)$_POST['id'];
    $is_active = (int)$_POST['is_active'];
    
    $stmt = $pdo->prepare("UPDATE categories SET is_active = ? WHERE id = ?");
    $stmt->execute([$is_active, $id]);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}

// Получаем все настройки для отображения
$allSettings = getSettings();
$settingsByGroup = [];
foreach ($allSettings as $key => $setting) {
    $group = $setting['setting_group'];
    if (!isset($settingsByGroup[$group])) {
        $settingsByGroup[$group] = [];
    }
    $settingsByGroup[$group][] = $setting;
}

// Получаем пользовательский CSS
$stmt = $pdo->query("SELECT css_code FROM custom_css WHERE is_active = true ORDER BY id DESC LIMIT 1");
$customCSS = $stmt->fetch(PDO::FETCH_ASSOC);
$customCSS = $customCSS ? $customCSS['css_code'] : '/* Пользовательские стили */';

// Добавление товара
if (isset($_POST['add_product'])) {
    try {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = (float)$_POST['price'];
        $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
        
        if (empty($name)) {
            throw new Exception('Название товара обязательно');
        }
        if ($price <= 0) {
            throw new Exception('Цена должна быть больше 0');
        }
        
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $maxSize = 5 * 1024 * 1024;
            
            if (!in_array($_FILES['image']['type'], $allowedTypes)) {
                throw new Exception('Допустимые форматы: JPG, PNG, GIF, WEBP');
            }
            
            if ($_FILES['image']['size'] > $maxSize) {
                throw new Exception('Максимальный размер файла: 5MB');
            }
            
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image = time() . '_' . uniqid() . '.' . $extension;
            $uploadPath = $uploadDir . $image;
            
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                throw new Exception('Ошибка при загрузке файла');
            }
        }
        
        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, category_id, image) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $price, $category_id, $image]);
        
        $message = 'Товар успешно добавлен';
        $messageType = 'success';
    } catch (Exception $e) {
        $message = 'Ошибка: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Редактирование товара
if (isset($_POST['edit_product'])) {
    try {
        $id = (int)$_POST['id'];
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = (float)$_POST['price'];
        $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
        
        if (empty($name)) {
            throw new Exception('Название товара обязательно');
        }
        if ($price <= 0) {
            throw new Exception('Цена должна быть больше 0');
        }
        
        $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $currentProduct = $stmt->fetch(PDO::FETCH_ASSOC);
        $image = $currentProduct['image'];
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $maxSize = 5 * 1024 * 1024;
            
            if (!in_array($_FILES['image']['type'], $allowedTypes)) {
                throw new Exception('Допустимые форматы: JPG, PNG, GIF, WEBP');
            }
            
            if ($_FILES['image']['size'] > $maxSize) {
                throw new Exception('Максимальный размер файла: 5MB');
            }
            
            $uploadDir = 'uploads/';
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $newImage = time() . '_' . uniqid() . '.' . $extension;
            $uploadPath = $uploadDir . $newImage;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                if ($image && file_exists($uploadDir . $image)) {
                    unlink($uploadDir . $image);
                }
                $image = $newImage;
            }
        }
        
        $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, category_id = ?, image = ? WHERE id = ?");
        $stmt->execute([$name, $description, $price, $category_id, $image, $id]);
        
        $message = 'Товар успешно обновлен';
        $messageType = 'success';
    } catch (Exception $e) {
        $message = 'Ошибка: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Удаление товара
if (isset($_POST['delete_product'])) {
    try {
        $id = (int)$_POST['id'];
        
        $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product && $product['image'] && file_exists('uploads/' . $product['image'])) {
            unlink('uploads/' . $product['image']);
        }
        
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        
        $message = 'Товар успешно удален';
        $messageType = 'success';
    } catch (Exception $e) {
        $message = 'Ошибка при удалении: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Добавление категории
if (isset($_POST['add_category'])) {
    try {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        
        if (empty($name)) {
            throw new Exception('Название категории обязательно');
        }
        
        // Получаем максимальный порядок сортировки
        $stmt = $pdo->query("SELECT MAX(sort_order) FROM categories");
        $maxOrder = $stmt->fetchColumn();
        $sort_order = $maxOrder ? $maxOrder + 1 : 0;
        
        $stmt = $pdo->prepare("INSERT INTO categories (name, description, sort_order, is_active) VALUES (?, ?, ?, 1)");
        $stmt->execute([$name, $description, $sort_order]);
        
        $message = 'Категория успешно добавлена';
        $messageType = 'success';
    } catch (Exception $e) {
        $message = 'Ошибка: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Редактирование категории
if (isset($_POST['edit_category'])) {
    try {
        $id = (int)$_POST['id'];
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        
        if (empty($name)) {
            throw new Exception('Название категории обязательно');
        }
        
        $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
        $stmt->execute([$name, $description, $id]);
        
        $message = 'Категория успешно обновлена';
        $messageType = 'success';
    } catch (Exception $e) {
        $message = 'Ошибка: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Удаление категории
if (isset($_POST['delete_category'])) {
    try {
        $id = (int)$_POST['id'];
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
        $stmt->execute([$id]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            throw new Exception('Нельзя удалить категорию, в которой есть товары');
        }
        
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        
        $message = 'Категория успешно удалена';
        $messageType = 'success';
    } catch (Exception $e) {
        $message = 'Ошибка: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Добавление скидки
if (isset($_POST['add_discount'])) {
    try {
        $code = strtoupper(trim($_POST['code']));
        $type = $_POST['type'];
        $value = (float)$_POST['value'];
        $valid_until = $_POST['valid_until'];
        $max_uses = (int)$_POST['max_uses'];
        
        if (empty($code)) {
            throw new Exception('Введите код скидки');
        }
        if ($value <= 0) {
            throw new Exception('Значение скидки должно быть больше 0');
        }
        if ($type == 'percentage' && $value > 100) {
            throw new Exception('Процентная скидка не может быть больше 100%');
        }
        
        $stmt = $pdo->prepare("SELECT id FROM discounts WHERE code = ?");
        $stmt->execute([$code]);
        if ($stmt->fetch()) {
            throw new Exception('Код скидки уже существует');
        }
        
        $stmt = $pdo->prepare("INSERT INTO discounts (code, type, value, valid_until, max_uses) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$code, $type, $value, $valid_until, $max_uses]);
        
        $message = 'Промокод успешно добавлен';
        $messageType = 'success';
    } catch (Exception $e) {
        $message = 'Ошибка: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Редактирование скидки
if (isset($_POST['edit_discount'])) {
    try {
        $id = (int)$_POST['id'];
        $code = strtoupper(trim($_POST['code']));
        $type = $_POST['type'];
        $value = (float)$_POST['value'];
        $valid_until = $_POST['valid_until'];
        $max_uses = (int)$_POST['max_uses'];
        
        if (empty($code)) {
            throw new Exception('Введите код скидки');
        }
        if ($value <= 0) {
            throw new Exception('Значение скидки должно быть больше 0');
        }
        if ($type == 'percentage' && $value > 100) {
            throw new Exception('Процентная скидка не может быть больше 100%');
        }
        
        $stmt = $pdo->prepare("SELECT id FROM discounts WHERE code = ? AND id != ?");
        $stmt->execute([$code, $id]);
        if ($stmt->fetch()) {
            throw new Exception('Код скидки уже существует');
        }
        
        $stmt = $pdo->prepare("UPDATE discounts SET code = ?, type = ?, value = ?, valid_until = ?, max_uses = ? WHERE id = ?");
        $stmt->execute([$code, $type, $value, $valid_until, $max_uses, $id]);
        
        $message = 'Промокод успешно обновлен';
        $messageType = 'success';
    } catch (Exception $e) {
        $message = 'Ошибка: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Удаление скидки
if (isset($_POST['delete_discount'])) {
    try {
        $id = (int)$_POST['id'];
        
        $stmt = $pdo->prepare("DELETE FROM discounts WHERE id = ?");
        $stmt->execute([$id]);
        
        $message = 'Промокод успешно удален';
        $messageType = 'success';
    } catch (Exception $e) {
        $message = 'Ошибка: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Получение данных для отображения
$products = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC")->fetchAll(PDO::FETCH_ASSOC);
$categories = $pdo->query("SELECT * FROM categories ORDER BY sort_order ASC, name ASC")->fetchAll(PDO::FETCH_ASSOC);
$discounts = $pdo->query("SELECT *, 
    CASE 
        WHEN valid_until < NOW() THEN 'expired'
        WHEN used >= max_uses THEN 'used_up'
        ELSE 'active'
    END as status 
    FROM discounts 
    ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// Статистика
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalCategories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$totalDiscounts = $pdo->query("SELECT COUNT(*) FROM discounts")->fetchColumn();
$activeDiscounts = $pdo->query("SELECT COUNT(*) FROM discounts WHERE valid_until >= NOW() AND used < max_uses")->fetchColumn();

// Проверяем количество категорий для меню
$activeCategories = $pdo->query("SELECT COUNT(*) FROM categories WHERE is_active = true")->fetchColumn();
$menuType = $activeCategories > 5 ? 'burger' : 'desktop';

include 'includes/header.php';
?>

<div class="container">
    <div class="admin-header">
        <h1 class="page-title">
            <i class="fas fa-cog"></i>
            Панель управления
        </h1>
        <a href="?logout=1" class="btn btn-secondary" onclick="return confirm('Выйти из админ-панели?')">
            <i class="fas fa-sign-out-alt"></i>
            Выйти
        </a>
    </div>
    
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <i class="fas fa-<?php echo $messageType == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <!-- Статистика -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #4a90e2, #6ba5e8);">
                <i class="fas fa-box"></i>
            </div>
            <div class="stat-info">
                <span class="stat-value"><?php echo $totalProducts; ?></span>
                <span class="stat-label">Товаров</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #38a169, #48bb78);">
                <i class="fas fa-folder"></i>
            </div>
            <div class="stat-info">
                <span class="stat-value"><?php echo $totalCategories; ?></span>
                <span class="stat-label">Категорий</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #d69e2e, #ecc94b);">
                <i class="fas fa-tag"></i>
            </div>
            <div class="stat-info">
                <span class="stat-value"><?php echo $totalDiscounts; ?></span>
                <span class="stat-label">Промокодов</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #9f7aea, #b794f4);">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-info">
                <span class="stat-value"><?php echo $activeDiscounts; ?></span>
                <span class="stat-label">Активных</span>
            </div>
        </div>
    </div>
    
    <!-- Табы -->
    <div class="admin-tabs">
        <button class="tab-btn active" data-tab="products">
            <i class="fas fa-box"></i>
            Товары
        </button>
        <button class="tab-btn" data-tab="categories">
            <i class="fas fa-folder"></i>
            Категории
        </button>
        <button class="tab-btn" data-tab="discounts">
            <i class="fas fa-tag"></i>
            Промокоды
        </button>
        <button class="tab-btn" data-tab="customize">
            <i class="fas fa-paint-brush"></i>
            Кастомизация
        </button>
        <button class="tab-btn" data-tab="css">
            <i class="fas fa-code"></i>
            CSS редактор
        </button>
        <button class="tab-btn" data-tab="footer">
            <i class="fas fa-shoe-prints"></i>
            Подвал
        </button>
        <button class="tab-btn" data-tab="settings">
            <i class="fas fa-sliders-h"></i>
            Настройки
        </button>
    </div>
    
    <!-- Вкладка Товары -->
    <div class="tab-content active" id="tab-products">
        <div class="admin-section">
            <div class="section-header">
                <h2>
                    <i class="fas fa-plus-circle"></i>
                    Добавить товар
                </h2>
            </div>
            
            <form method="POST" enctype="multipart/form-data" class="admin-form">
                <div class="form-row">
                    <div class="form-group">
                        <label>Название товара *</label>
                        <input type="text" name="name" required placeholder="Введите название товара">
                    </div>
                    
                    <div class="form-group">
                        <label>Категория</label>
                        <select name="category_id">
                            <option value="">Без категории</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Описание</label>
                    <textarea name="description" rows="4" placeholder="Подробное описание товара"></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Цена (₽) *</label>
                        <input type="number" name="price" step="0.01" min="0.01" required placeholder="0.00">
                    </div>
                    
                    <div class="form-group">
                        <label>Изображение</label>
                        <div class="file-input-wrapper">
                            <input type="file" name="image" accept="image/*" id="product-image">
                            <label for="product-image" class="file-input-label">
                                <i class="fas fa-upload"></i>
                                Выберите файл
                            </label>
                            <span class="file-name">Файл не выбран</span>
                        </div>
                        <small class="form-text">Допустимые форматы: JPG, PNG, GIF, WEBP. Макс. размер: 5MB</small>
                    </div>
                </div>
                
                <button type="submit" name="add_product" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Добавить товар
                </button>
            </form>
        </div>
        
        <div class="admin-section">
            <div class="section-header">
                <h2>
                    <i class="fas fa-list"></i>
                    Список товаров (<?php echo count($products); ?>)
                </h2>
            </div>
            
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Изображение</th>
                            <th>Название</th>
                            <th>Категория</th>
                            <th>Цена</th>
                            <th>Дата</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                        <tr>
                            <td>#<?php echo $product['id']; ?></td>
                            <td>
                                <?php if ($product['image'] && file_exists('uploads/' . $product['image'])): ?>
                                    <img src="uploads/<?php echo $product['image']; ?>" alt="" class="table-image">
                                <?php else: ?>
                                    <div class="no-image-small">
                                        <i class="fas fa-image"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                            </td>
                            <td>
                                <span class="category-badge">
                                    <?php echo htmlspecialchars($product['category_name'] ?? 'Без категории'); ?>
                                </span>
                            </td>
                            <td>
                                <span class="price-badge">
                                    <?php echo number_format($product['price'], 0, '', ' '); ?> ₽
                                </span>
                            </td>
                            <td><?php echo date('d.m.Y', strtotime($product['created_at'])); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon btn-edit" onclick="editProduct(<?php echo htmlspecialchars(json_encode($product)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" onsubmit="return confirm('Удалить товар?');" style="display: inline;">
                                        <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                        <button type="submit" name="delete_product" class="btn-icon btn-delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="7" class="text-center">Нет товаров</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Вкладка Категории -->
    <div class="tab-content" id="tab-categories">
        <div class="admin-section">
            <div class="section-header">
                <h2>
                    <i class="fas fa-plus-circle"></i>
                    Добавить категорию
                </h2>
            </div>
            
            <form method="POST" class="admin-form">
                <div class="form-group">
                    <label>Название категории *</label>
                    <input type="text" name="name" required placeholder="Например: Электроника">
                </div>
                
                <div class="form-group">
                    <label>Описание</label>
                    <textarea name="description" rows="3" placeholder="Описание категории"></textarea>
                </div>
                
                <button type="submit" name="add_category" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Добавить категорию
                </button>
            </form>
        </div>
        
        <div class="admin-section">
            <div class="section-header">
                <h2>
                    <i class="fas fa-list"></i>
                    Список категорий (<?php echo count($categories); ?>)
                </h2>
                <div class="menu-info">
                    <i class="fas fa-<?php echo $menuType == 'burger' ? 'bars' : 'arrow-right'; ?>"></i>
                    <span>
                        <?php if ($activeCategories > 5): ?>
                            В шапке будет <strong>бургер-меню</strong> (активных: <?php echo $activeCategories; ?>)
                        <?php else: ?>
                            Категории в шапке (активных: <?php echo $activeCategories; ?>/5)
                        <?php endif; ?>
                    </span>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Название</th>
                            <th>Описание</th>
                            <th>Товаров</th>
                            <th>Порядок</th>
                            <th>Статус</th>
                            <th>Дата</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): 
                            $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
                            $stmt->execute([$category['id']]);
                            $productsCount = $stmt->fetchColumn();
                        ?>
                        <tr>
                            <td>#<?php echo $category['id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($category['name']); ?></strong>
                            </td>
                            <td><?php echo htmlspecialchars($category['description']); ?></td>
                            <td>
                                <span class="count-badge"><?php echo $productsCount; ?></span>
                            </td>
                            <td>
                                <input type="number" 
                                       class="sort-order-input" 
                                       value="<?php echo $category['sort_order'] ?? 0; ?>" 
                                       data-category-id="<?php echo $category['id']; ?>"
                                       min="0"
                                       style="width: 60px; padding: 5px; border: 1px solid #e2e8f0; border-radius: 4px;">
                            </td>
                            <td>
                                <label class="switch">
                                    <input type="checkbox" 
                                           class="status-toggle" 
                                           data-category-id="<?php echo $category['id']; ?>"
                                           <?php echo ($category['is_active'] ?? 1) ? 'checked' : ''; ?>>
                                    <span class="slider round"></span>
                                </label>
                            </td>
                            <td><?php echo date('d.m.Y', strtotime($category['created_at'])); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon btn-edit" onclick="editCategory(<?php echo htmlspecialchars(json_encode($category)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if ($productsCount == 0): ?>
                                    <form method="POST" onsubmit="return confirm('Удалить категорию?');" style="display: inline;">
                                        <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                                        <button type="submit" name="delete_category" class="btn-icon btn-delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    <?php else: ?>
                                    <button class="btn-icon btn-delete disabled" title="Нельзя удалить категорию с товарами" disabled>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="8" class="text-center">Нет категорий</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Вкладка Промокоды -->
    <div class="tab-content" id="tab-discounts">
        <div class="admin-section">
            <div class="section-header">
                <h2>
                    <i class="fas fa-plus-circle"></i>
                    Добавить промокод
                </h2>
            </div>
            
            <form method="POST" class="admin-form">
                <div class="form-row">
                    <div class="form-group">
                        <label>Код *</label>
                        <input type="text" name="code" required placeholder="Например: WELCOME10" style="text-transform: uppercase;">
                    </div>
                    
                    <div class="form-group">
                        <label>Тип скидки</label>
                        <select name="type" id="discount-type">
                            <option value="percentage">Процентная (%)</option>
                            <option value="fixed">Фиксированная (₽)</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Значение *</label>
                        <input type="number" name="value" step="0.01" min="0.01" required placeholder="0.00">
                    </div>
                    
                    <div class="form-group">
                        <label>Действителен до</label>
                        <input type="datetime-local" name="valid_until" required value="<?php echo date('Y-m-d\TH:i', strtotime('+30 days')); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Максимальное количество использований</label>
                    <input type="number" name="max_uses" min="1" value="1" required>
                </div>
                
                <button type="submit" name="add_discount" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Добавить промокод
                </button>
            </form>
        </div>
        
        <div class="admin-section">
            <div class="section-header">
                <h2>
                    <i class="fas fa-list"></i>
                    Активные промокоды (<?php echo count($discounts); ?>)
                </h2>
            </div>
            
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Код</th>
                            <th>Тип</th>
                            <th>Значение</th>
                            <th>Действителен до</th>
                            <th>Использовано</th>
                            <th>Статус</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($discounts as $discount): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($discount['code']); ?></strong>
                            </td>
                            <td>
                                <?php if ($discount['type'] == 'percentage'): ?>
                                    <span class="badge badge-info">Процент</span>
                                <?php else: ?>
                                    <span class="badge badge-success">Фикс</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($discount['type'] == 'percentage'): ?>
                                    <?php echo $discount['value']; ?>%
                                <?php else: ?>
                                    <?php echo number_format($discount['value'], 0, '', ' '); ?> ₽
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d.m.Y H:i', strtotime($discount['valid_until'])); ?></td>
                            <td>
                                <div class="usage-progress">
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?php echo min(100, ($discount['used'] / $discount['max_uses']) * 100); ?>%"></div>
                                    </div>
                                    <span><?php echo $discount['used']; ?>/<?php echo $discount['max_uses']; ?></span>
                                </div>
                            </td>
                            <td>
                                <?php if ($discount['status'] == 'expired'): ?>
                                    <span class="badge badge-error">Просрочен</span>
                                <?php elseif ($discount['status'] == 'used_up'): ?>
                                    <span class="badge badge-warning">Использован</span>
                                <?php else: ?>
                                    <span class="badge badge-success">Активен</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon btn-edit" onclick="editDiscount(<?php echo htmlspecialchars(json_encode($discount)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" onsubmit="return confirm('Удалить промокод?');" style="display: inline;">
                                        <input type="hidden" name="id" value="<?php echo $discount['id']; ?>">
                                        <button type="submit" name="delete_discount" class="btn-icon btn-delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($discounts)): ?>
                        <tr>
                            <td colspan="7" class="text-center">Нет промокодов</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Вкладка Кастомизация -->
    <div class="tab-content" id="tab-customize">
        <div class="customize-header">
            <h2>
                <i class="fas fa-palette"></i>
                Настройка внешнего вида
            </h2>
            <div class="customize-actions">
                <button type="button" class="btn btn-secondary" onclick="resetSettings()">
                    <i class="fas fa-undo"></i>
                    Сбросить
                </button>
                <button type="button" class="btn btn-primary" onclick="saveSettings()">
                    <i class="fas fa-save"></i>
                    Сохранить все
                </button>
            </div>
        </div>
        
        <div class="customize-tabs">
            <button class="customize-tab-btn active" data-group="general">
                <i class="fas fa-globe"></i>
                Общие
            </button>
            <button class="customize-tab-btn" data-group="colors">
                <i class="fas fa-palette"></i>
                Цвета
            </button>
            <button class="customize-tab-btn" data-group="buttons">
                <i class="fas fa-square"></i>
                Кнопки
            </button>
            <button class="customize-tab-btn" data-group="layout">
                <i class="fas fa-columns"></i>
                Расположение
            </button>
            <button class="customize-tab-btn" data-group="fonts">
                <i class="fas fa-font"></i>
                Шрифты
            </button>
            <button class="customize-tab-btn" data-group="floating">
                <i class="fas fa-hand-pointer"></i>
                Плавающая кнопка
            </button>
            <button class="customize-tab-btn" data-group="cart">
                <i class="fas fa-shopping-cart"></i>
                Корзина
            </button>
            <button class="customize-tab-btn" data-group="products">
                <i class="fas fa-box"></i>
                Товары
            </button>
            <button class="customize-tab-btn" data-group="categories">
                <i class="fas fa-folder"></i>
                Категории
            </button>
        </div>
        
        <form id="settings-form" method="POST">
            <?php foreach ($settingsByGroup as $group => $settings): ?>
            <div class="customize-group" id="group-<?php echo $group; ?>" style="<?php echo $group != 'general' ? 'display: none;' : ''; ?>">
                <h3><?php echo ucfirst($group); ?></h3>
                
                <div class="settings-grid">
                    <?php foreach ($settings as $setting): ?>
                    <div class="setting-item">
                        <label for="<?php echo $setting['setting_key']; ?>">
                            <?php 
                            $label = str_replace('_', ' ', $setting['setting_key']);
                            $label = ucfirst($label);
                            echo $label;
                            ?>
                        </label>
                        
                        <?php if ($setting['setting_type'] == 'color'): ?>
                            <div class="color-picker-wrapper">
                                <input type="color" 
                                       name="settings[<?php echo $setting['setting_key']; ?>]" 
                                       id="<?php echo $setting['setting_key']; ?>"
                                       value="<?php echo htmlspecialchars($setting['setting_value']); ?>"
                                       class="color-picker">
                                <input type="text" 
                                       class="color-input" 
                                       value="<?php echo htmlspecialchars($setting['setting_value']); ?>"
                                       onchange="this.previousElementSibling.value = this.value">
                            </div>
                        
                        <?php elseif ($setting['setting_type'] == 'select'): ?>
                            <select name="settings[<?php echo $setting['setting_key']; ?>]" 
                                    id="<?php echo $setting['setting_key']; ?>">
                                <?php
                                $options = [];
                                switch($setting['setting_key']) {
                                    case 'header_position':
                                        $options = ['sticky' => 'Липкий', 'fixed' => 'Фиксированный', 'static' => 'Статичный'];
                                        break;
                                    case 'logo_position':
                                    case 'nav_position':
                                    case 'cart_position':
                                        $options = ['left' => 'Слева', 'center' => 'По центру', 'right' => 'Справа'];
                                        break;
                                    case 'floating_button_enabled':
                                        $options = ['true' => 'Включено', 'false' => 'Отключено'];
                                        break;
                                    case 'floating_button_animation':
                                        $options = ['float' => 'Парение', 'pulse' => 'Пульсация', 'none' => 'Без анимации'];
                                        break;
                                    default:
                                        $options = [$setting['setting_value'] => $setting['setting_value']];
                                }
                                foreach ($options as $value => $label):
                                ?>
                                    <option value="<?php echo $value; ?>" <?php echo $setting['setting_value'] == $value ? 'selected' : ''; ?>>
                                        <?php echo $label; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        
                        <?php elseif ($setting['setting_type'] == 'textarea'): ?>
                            <textarea name="settings[<?php echo $setting['setting_key']; ?>]" 
                                      id="<?php echo $setting['setting_key']; ?>"
                                      rows="3"><?php echo htmlspecialchars($setting['setting_value']); ?></textarea>
                        
                        <?php elseif ($setting['setting_type'] == 'image'): ?>
                            <div class="image-upload-wrapper">
                                <input type="file" 
                                       id="<?php echo $setting['setting_key']; ?>_file"
                                       accept="image/*"
                                       style="display: none;">
                                <div class="image-preview" id="<?php echo $setting['setting_key']; ?>_preview">
                                    <?php if (!empty($setting['setting_value']) && file_exists('uploads/' . $setting['setting_value'])): ?>
                                        <img src="uploads/<?php echo $setting['setting_value']; ?>" alt="">
                                    <?php else: ?>
                                        <i class="fas fa-image"></i>
                                    <?php endif; ?>
                                </div>
                                <button type="button" class="btn btn-secondary" onclick="document.getElementById('<?php echo $setting['setting_key']; ?>_file').click()">
                                    <i class="fas fa-upload"></i>
                                    Загрузить
                                </button>
                                <input type="hidden" 
                                       name="settings[<?php echo $setting['setting_key']; ?>]" 
                                       id="<?php echo $setting['setting_key']; ?>"
                                       value="<?php echo htmlspecialchars($setting['setting_value']); ?>">
                            </div>
                        
                        <?php else: ?>
                            <input type="<?php echo $setting['setting_type'] == 'number' ? 'number' : 'text'; ?>" 
                                   name="settings[<?php echo $setting['setting_key']; ?>]" 
                                   id="<?php echo $setting['setting_key']; ?>"
                                   value="<?php echo htmlspecialchars($setting['setting_value']); ?>"
                                   <?php echo $setting['setting_type'] == 'number' ? 'step="1"' : ''; ?>>
                        <?php endif; ?>
                        
                        <small class="setting-description">
                            <?php echo getSettingDescription($setting['setting_key']); ?>
                        </small>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
            
            <input type="hidden" name="save_settings" value="1">
        </form>
    </div>
    
    <!-- Вкладка CSS редактор -->
    <div class="tab-content" id="tab-css">
        <div class="customize-header">
            <h2>
                <i class="fas fa-code"></i>
                Редактор CSS
            </h2>
            <div class="customize-actions">
                <button type="button" class="btn btn-primary" onclick="saveCSS()">
                    <i class="fas fa-save"></i>
                    Сохранить CSS
                </button>
            </div>
        </div>
        
        <form id="css-form" method="POST">
            <div class="css-editor">
                <div class="css-editor-header">
                    <span>custom.css</span>
                    <span class="css-status">Автосохранение выключено</span>
                </div>
                <textarea name="custom_css" id="custom_css" rows="20" spellcheck="false"><?php echo htmlspecialchars($customCSS); ?></textarea>
            </div>
            <input type="hidden" name="save_custom_css" value="1">
        </form>
        
        <div class="css-help">
            <h4>CSS переменные, которые можно использовать:</h4>
            <pre>
--primary-color        /* Основной цвет */
--primary-light        /* Светлый вариант */
--primary-dark         /* Темный вариант */
--secondary-color      /* Вторичный цвет */
--text-color          /* Цвет текста */
--text-light          /* Светлый текст */
--background          /* Цвет фона */
--card-background     /* Цвет карточек */
--button-radius       /* Радиус кнопок */
--header-height       /* Высота шапки */
--font-family         /* Шрифт */
            </pre>
        </div>
    </div>
    
    <!-- Вкладка Подвал -->
    <div class="tab-content" id="tab-footer">
        <div class="customize-header">
            <h2>
                <i class="fas fa-shoe-prints"></i>
                Настройка подвала
            </h2>
            <button type="button" class="btn btn-primary" onclick="saveFooterSettings()">
                <i class="fas fa-save"></i>
                Сохранить настройки подвала
            </button>
        </div>
        
        <form id="footer-settings-form">
            <div class="customize-group">
                <h3>Основная информация</h3>
                <div class="settings-grid">
                    <div class="setting-item">
                        <label>Название магазина</label>
                        <input type="text" name="site_name" value="<?php echo getSetting('site_name', 'ModernShop'); ?>">
                    </div>
                    
                    <div class="setting-item">
                        <label>Описание</label>
                        <textarea name="site_description" rows="3"><?php echo getSetting('site_description', 'Лучшие товары по лучшим ценам'); ?></textarea>
                    </div>
                    
                    <div class="setting-item">
                        <label>Email для контактов</label>
                        <input type="email" name="contact_email" value="<?php echo getSetting('contact_email', 'info@modernshop.ru'); ?>">
                    </div>
                    
                    <div class="setting-item">
                        <label>Телефон</label>
                        <input type="text" name="contact_phone" value="<?php echo getSetting('contact_phone', '+7 (999) 123-45-67'); ?>">
                    </div>
                    
                    <div class="setting-item">
                        <label>Адрес</label>
                        <input type="text" name="contact_address" value="<?php echo getSetting('contact_address', 'г. Москва, ул. Примерная, д. 1'); ?>">
                    </div>
                    
                    <div class="setting-item">
                        <label>Копирайт</label>
                        <input type="text" name="copyright" value="<?php echo getSetting('copyright', '© 2026 ModernShop. Все права защищены.'); ?>">
                    </div>
                </div>
            </div>
            
            <div class="customize-group">
                <h3>Социальные сети</h3>
                <div class="settings-grid">
                    <div class="setting-item">
                        <label>Telegram</label>
                        <input type="url" name="social_telegram" value="<?php echo getSetting('social_telegram', '#'); ?>" placeholder="https://t.me/...">
                    </div>
                    
                    <div class="setting-item">
                        <label>VK</label>
                        <input type="url" name="social_vk" value="<?php echo getSetting('social_vk', '#'); ?>" placeholder="https://vk.com/...">
                    </div>
                    
                    <div class="setting-item">
                        <label>Instagram</label>
                        <input type="url" name="social_instagram" value="<?php echo getSetting('social_instagram', '#'); ?>" placeholder="https://instagram.com/...">
                    </div>
                    
                    <div class="setting-item">
                        <label>YouTube</label>
                        <input type="url" name="social_youtube" value="<?php echo getSetting('social_youtube', '#'); ?>" placeholder="https://youtube.com/...">
                    </div>
                    
                    <div class="setting-item">
                        <label>WhatsApp</label>
                        <input type="url" name="social_whatsapp" value="<?php echo getSetting('social_whatsapp', '#'); ?>" placeholder="https://wa.me/...">
                    </div>
                </div>
            </div>
            
            <div class="customize-group">
                <h3>Дизайн подвала</h3>
                <div class="settings-grid">
                    <div class="setting-item">
                        <label>Цвет фона</label>
                        <div class="color-picker-wrapper">
                            <input type="color" name="footer_background" value="<?php echo getSetting('footer_background', '#ffffff'); ?>">
                            <input type="text" value="<?php echo getSetting('footer_background', '#ffffff'); ?>" onchange="this.previousElementSibling.value = this.value">
                        </div>
                    </div>
                    
                    <div class="setting-item">
                        <label>Цвет текста</label>
                        <div class="color-picker-wrapper">
                            <input type="color" name="footer_text_color" value="<?php echo getSetting('footer_text_color', '#2c3e50'); ?>">
                            <input type="text" value="<?php echo getSetting('footer_text_color', '#2c3e50'); ?>" onchange="this.previousElementSibling.value = this.value">
                        </div>
                    </div>
                    
                    <div class="setting-item">
                        <label>Цвет ссылок</label>
                        <div class="color-picker-wrapper">
                            <input type="color" name="footer_link_color" value="<?php echo getSetting('footer_link_color', '#4a90e2'); ?>">
                            <input type="text" value="<?php echo getSetting('footer_link_color', '#4a90e2'); ?>" onchange="this.previousElementSibling.value = this.value">
                        </div>
                    </div>
                    
                    <div class="setting-item">
                        <label>Отступы</label>
                        <input type="text" name="footer_padding" value="<?php echo getSetting('footer_padding', '60px 0 20px'); ?>" placeholder="60px 0 20px">
                        <small class="setting-description">Формат: верх право низ лево</small>
                    </div>
                    
                    <div class="setting-item">
                        <label>Количество колонок</label>
                        <select name="footer_columns">
                            <option value="1" <?php echo getSetting('footer_columns', '3') == '1' ? 'selected' : ''; ?>>1 колонка</option>
                            <option value="2" <?php echo getSetting('footer_columns', '3') == '2' ? 'selected' : ''; ?>>2 колонки</option>
                            <option value="3" <?php echo getSetting('footer_columns', '3') == '3' ? 'selected' : ''; ?>>3 колонки</option>
                            <option value="4" <?php echo getSetting('footer_columns', '3') == '4' ? 'selected' : ''; ?>>4 колонки</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="customize-group">
                <h3>Дополнительные функции</h3>
                <div class="settings-grid">
                    <div class="setting-item">
                        <label>Показывать карту</label>
                        <select name="footer_show_map">
                            <option value="true" <?php echo getSetting('footer_show_map', 'false') == 'true' ? 'selected' : ''; ?>>Да</option>
                            <option value="false" <?php echo getSetting('footer_show_map', 'false') == 'false' ? 'selected' : ''; ?>>Нет</option>
                        </select>
                    </div>
                    
                    <div class="setting-item">
                        <label>URL карты</label>
                        <input type="url" name="footer_map_url" value="<?php echo getSetting('footer_map_url', ''); ?>" placeholder="https://maps.google.com/...">
                    </div>
                    
                    <div class="setting-item">
                        <label>Новостная рассылка</label>
                        <select name="footer_show_newsletter">
                            <option value="true" <?php echo getSetting('footer_show_newsletter', 'false') == 'true' ? 'selected' : ''; ?>>Да</option>
                            <option value="false" <?php echo getSetting('footer_show_newsletter', 'false') == 'false' ? 'selected' : ''; ?>>Нет</option>
                        </select>
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Вкладка Настройки -->
    <div class="tab-content" id="tab-settings">
        <div class="admin-section">
            <div class="section-header">
                <h2>
                    <i class="fas fa-cog"></i>
                    Общие настройки
                </h2>
            </div>
            
            <form method="POST" class="admin-form" id="general-settings-form">
                <div class="form-group">
                    <label>Название магазина</label>
                    <input type="text" name="site_name" value="<?php echo getSetting('site_name', 'ModernShop'); ?>" placeholder="Введите название магазина">
                </div>
                
                <div class="form-group">
                    <label>Email для уведомлений</label>
                    <input type="email" name="admin_email" value="<?php echo getSetting('admin_email', 'admin@modernshop.ru'); ?>" placeholder="admin@modernshop.ru">
                </div>
                
                <div class="form-group">
                    <label>Телефон</label>
                    <input type="tel" name="site_phone" value="<?php echo getSetting('site_phone', '+7 (999) 123-45-67'); ?>" placeholder="+7 (999) 123-45-67">
                </div>
                
                <div class="form-group">
                    <label>Валюта</label>
                    <select name="currency">
                        <option value="RUB" <?php echo getSetting('currency', 'RUB') == 'RUB' ? 'selected' : ''; ?>>Российский рубль (₽)</option>
                        <option value="USD" <?php echo getSetting('currency', 'RUB') == 'USD' ? 'selected' : ''; ?>>Доллар США ($)</option>
                        <option value="EUR" <?php echo getSetting('currency', 'RUB') == 'EUR' ? 'selected' : ''; ?>>Евро (€)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Количество товаров на странице</label>
                    <input type="number" name="products_per_page" value="<?php echo getSetting('products_per_page', '12'); ?>" min="1" max="100">
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="enable_discounts" value="true" <?php echo getSetting('enable_discounts', 'true') == 'true' ? 'checked' : ''; ?>>
                        Включить систему скидок
                    </label>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="enable_reviews" value="true" <?php echo getSetting('enable_reviews', 'true') == 'true' ? 'checked' : ''; ?>>
                        Включить отзывы на товары
                    </label>
                </div>
                
                <button type="button" class="btn btn-primary" onclick="saveGeneralSettings()">
                    <i class="fas fa-save"></i>
                    Сохранить настройки
                </button>
            </form>
        </div>
        
        <div class="admin-section">
            <div class="section-header">
                <h2>
                    <i class="fas fa-database"></i>
                    Управление базой данных
                </h2>
            </div>
            
            <div class="settings-actions">
                <button class="btn btn-secondary" onclick="showNotification('Резервная копия создана', 'success')">
                    <i class="fas fa-download"></i>
                    Создать резервную копию
                </button>
                
                <button class="btn btn-secondary" onclick="showNotification('Данные восстановлены', 'success')">
                    <i class="fas fa-upload"></i>
                    Восстановить из копии
                </button>
                
                <button class="btn btn-secondary" onclick="if(confirm('Очистить все данные?')) showNotification('Данные очищены', 'warning')">
                    <i class="fas fa-trash"></i>
                    Очистить данные
                </button>
            </div>
        </div>
        
        <div class="admin-section">
            <div class="section-header">
                <h2>
                    <i class="fas fa-shield-alt"></i>
                    Безопасность
                </h2>
            </div>
            
            <form class="admin-form" id="password-form">
                <div class="form-group">
                    <label>Текущий пароль</label>
                    <input type="password" name="current_password" placeholder="••••••••">
                </div>
                
                <div class="form-group">
                    <label>Новый пароль</label>
                    <input type="password" name="new_password" placeholder="Минимум 8 символов">
                </div>
                
                <div class="form-group">
                    <label>Подтверждение пароля</label>
                    <input type="password" name="confirm_password" placeholder="Повторите пароль">
                </div>
                
                <button type="button" class="btn btn-primary" onclick="changePassword()">
                    <i class="fas fa-key"></i>
                    Изменить пароль
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Модальное окно редактирования товара -->
<div id="editProductModal" class="modal">
    <div class="modal-content">
        <button class="modal-close">&times;</button>
        <h2>Редактировать товар</h2>
        
        <form method="POST" enctype="multipart/form-data" id="editProductForm">
            <input type="hidden" name="id" id="edit_product_id">
            
            <div class="form-group">
                <label>Название товара *</label>
                <input type="text" name="name" id="edit_product_name" required>
            </div>
            
            <div class="form-group">
                <label>Категория</label>
                <select name="category_id" id="edit_product_category">
                    <option value="">Без категории</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Описание</label>
                <textarea name="description" id="edit_product_description" rows="4"></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Цена (₽) *</label>
                    <input type="number" name="price" step="0.01" id="edit_product_price" required>
                </div>
                
                <div class="form-group">
                    <label>Текущее изображение</label>
                    <div id="current_image_container"></div>
                </div>
            </div>
            
            <div class="form-group">
                <label>Новое изображение</label>
                <input type="file" name="image" accept="image/*">
                <small class="form-text">Оставьте пустым, чтобы не менять изображение</small>
            </div>
            
            <button type="submit" name="edit_product" class="btn btn-primary">
                <i class="fas fa-save"></i>
                Сохранить изменения
            </button>
        </form>
    </div>
</div>

<!-- Модальное окно редактирования категории -->
<div id="editCategoryModal" class="modal">
    <div class="modal-content">
        <button class="modal-close">&times;</button>
        <h2>Редактировать категорию</h2>
        
        <form method="POST" id="editCategoryForm">
            <input type="hidden" name="id" id="edit_category_id">
            
            <div class="form-group">
                <label>Название категории *</label>
                <input type="text" name="name" id="edit_category_name" required>
            </div>
            
            <div class="form-group">
                <label>Описание</label>
                <textarea name="description" id="edit_category_description" rows="3"></textarea>
            </div>
            
            <button type="submit" name="edit_category" class="btn btn-primary">
                <i class="fas fa-save"></i>
                Сохранить изменения
            </button>
        </form>
    </div>
</div>

<!-- Модальное окно редактирования промокода -->
<div id="editDiscountModal" class="modal">
    <div class="modal-content">
        <button class="modal-close">&times;</button>
        <h2>Редактировать промокод</h2>
        
        <form method="POST" id="editDiscountForm">
            <input type="hidden" name="id" id="edit_discount_id">
            
            <div class="form-row">
                <div class="form-group">
                    <label>Код *</label>
                    <input type="text" name="code" id="edit_discount_code" required style="text-transform: uppercase;">
                </div>
                
                <div class="form-group">
                    <label>Тип скидки</label>
                    <select name="type" id="edit_discount_type">
                        <option value="percentage">Процентная (%)</option>
                        <option value="fixed">Фиксированная (₽)</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Значение *</label>
                    <input type="number" name="value" step="0.01" id="edit_discount_value" required>
                </div>
                
                <div class="form-group">
                    <label>Действителен до</label>
                    <input type="datetime-local" name="valid_until" id="edit_discount_valid_until" required>
                </div>
            </div>
            
            <div class="form-group">
                <label>Максимальное количество использований</label>
                <input type="number" name="max_uses" id="edit_discount_max_uses" min="1" required>
            </div>
            
            <button type="submit" name="edit_discount" class="btn btn-primary">
                <i class="fas fa-save"></i>
                Сохранить изменения
            </button>
        </form>
    </div>
</div>

<style>
/* Дополнительные стили для админки */
.admin-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 20px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: var(--card-background);
    border-radius: var(--border-radius);
    padding: 20px;
    box-shadow: var(--shadow);
    display: flex;
    align-items: center;
    gap: 20px;
    transition: transform 0.3s;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-hover);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.8rem;
}

.stat-info {
    flex: 1;
}

.stat-value {
    display: block;
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--text-color);
    line-height: 1.2;
}

.stat-label {
    color: var(--text-light);
    font-size: 0.9rem;
}

.alert {
    padding: 15px 20px;
    border-radius: var(--border-radius);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    animation: slideIn 0.3s ease;
}

.alert-success {
    background: #c6f6d5;
    color: #22543d;
    border: 1px solid #9ae6b4;
}

.alert-error {
    background: #fed7d7;
    color: #742a2a;
    border: 1px solid #fc8181;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.section-header {
    margin-bottom: 20px;
    border-bottom: 2px solid #edf2f7;
    padding-bottom: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.section-header h2 {
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--text-color);
}

.menu-info {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 15px;
    background: #f0f4f8;
    border-radius: 8px;
    font-size: 0.9rem;
    color: var(--text-color);
}

.menu-info i {
    color: var(--primary-color);
    font-size: 1.1rem;
}

.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 900px;
}

.admin-table th {
    text-align: left;
    padding: 15px;
    background: #f7f9fc;
    color: var(--text-color);
    font-weight: 600;
    font-size: 0.9rem;
    border-bottom: 2px solid #e2e8f0;
}

.admin-table td {
    padding: 15px;
    border-bottom: 1px solid #edf2f7;
    vertical-align: middle;
}

.admin-table tr:hover {
    background: #f7f9fc;
}

.table-image {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 8px;
}

.no-image-small {
    width: 50px;
    height: 50px;
    background: #f0f4f8;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-light);
}

.category-badge {
    background: #e2e8f0;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.85rem;
    color: var(--text-color);
    white-space: nowrap;
}

.price-badge {
    background: var(--primary-color);
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-weight: 600;
    white-space: nowrap;
}

.count-badge {
    background: #9f7aea;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.85rem;
}

.badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.85rem;
    font-weight: 500;
    white-space: nowrap;
}

.badge-success {
    background: #c6f6d5;
    color: #22543d;
}

.badge-info {
    background: #bee3f8;
    color: #2c5282;
}

.badge-warning {
    background: #feebc8;
    color: #7b341e;
}

.badge-error {
    background: #fed7d7;
    color: #742a2a;
}

.action-buttons {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
}

.btn-icon {
    width: 35px;
    height: 35px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}

.btn-edit {
    background: #e2e8f0;
    color: #4a5568;
}

.btn-edit:hover {
    background: var(--primary-color);
    color: white;
}

.btn-delete {
    background: #fed7d7;
    color: #e53e3e;
}

.btn-delete:hover:not(.disabled) {
    background: #e53e3e;
    color: white;
}

.btn-delete.disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Toggle Switch */
.switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 24px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .3s;
}

.slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .3s;
}

input:checked + .slider {
    background-color: var(--primary-color);
}

input:checked + .slider:before {
    transform: translateX(26px);
}

.slider.round {
    border-radius: 24px;
}

.slider.round:before {
    border-radius: 50%;
}

.file-input-wrapper {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.file-input-wrapper input[type="file"] {
    display: none;
}

.file-input-label {
    padding: 10px 15px;
    background: #f7f9fc;
    border: 2px dashed #e2e8f0;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.file-input-label:hover {
    border-color: var(--primary-color);
    background: #edf2f7;
}

.file-name {
    color: var(--text-light);
    font-size: 0.9rem;
}

.form-text {
    display: block;
    margin-top: 5px;
    color: var(--text-light);
    font-size: 0.85rem;
}

.usage-progress {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 100px;
}

.progress-bar {
    flex: 1;
    height: 6px;
    background: #e2e8f0;
    border-radius: 3px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: var(--primary-color);
    transition: width 0.3s;
}

.settings-actions {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.text-center {
    text-align: center;
}

#current_image_container {
    width: 100px;
    height: 100px;
    border-radius: 8px;
    overflow: hidden;
    background: #f0f4f8;
}

#current_image_container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Стили для кастомизации */
.customize-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 20px;
}

.customize-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 30px;
    overflow-x: auto;
    padding: 10px 0;
    background: var(--card-background);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
}

.customize-tab-btn {
    padding: 12px 20px;
    border: none;
    background: none;
    color: var(--text-color);
    font-size: 0.95rem;
    font-weight: 500;
    cursor: pointer;
    border-radius: var(--button-radius);
    transition: all 0.3s;
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 8px;
}

.customize-tab-btn:hover {
    background: #f0f4f8;
}

.customize-tab-btn.active {
    background: var(--primary-color);
    color: white;
}

.customize-tab-btn i {
    font-size: 1.1rem;
}

.customize-group {
    background: var(--card-background);
    border-radius: var(--border-radius);
    padding: 30px;
    box-shadow: var(--shadow);
    margin-bottom: 30px;
}

.customize-group h3 {
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid #edf2f7;
    font-size: 1.3rem;
    color: var(--primary-color);
    text-transform: capitalize;
}

.settings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 25px;
}

.setting-item {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.setting-item label {
    font-weight: 600;
    color: var(--text-color);
    font-size: 0.95rem;
    text-transform: capitalize;
}

.setting-item input[type="text"],
.setting-item input[type="number"],
.setting-item select,
.setting-item textarea {
    padding: 10px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.95rem;
    transition: all 0.3s;
    background: white;
}

.setting-item input:focus,
.setting-item select:focus,
.setting-item textarea:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
}

.color-picker-wrapper {
    display: flex;
    gap: 10px;
    align-items: center;
}

.color-picker {
    width: 50px;
    height: 40px;
    padding: 0;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    cursor: pointer;
}

.color-input {
    flex: 1;
    padding: 10px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.95rem;
}

.image-upload-wrapper {
    display: flex;
    gap: 15px;
    align-items: center;
    flex-wrap: wrap;
}

.image-preview {
    width: 80px;
    height: 80px;
    border: 2px dashed #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f7f9fc;
}

.image-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.image-preview i {
    font-size: 2rem;
    color: #cbd5e0;
}

.setting-description {
    color: var(--text-light);
    font-size: 0.85rem;
    margin-top: 4px;
}

/* CSS редактор */
.css-editor {
    background: #1e1e1e;
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 30px;
}

.css-editor-header {
    background: #2d2d2d;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: #fff;
    font-family: monospace;
}

.css-status {
    color: #98c379;
    font-size: 0.9rem;
}

.css-editor textarea {
    width: 100%;
    padding: 20px;
    background: #1e1e1e;
    color: #d4d4d4;
    border: none;
    font-family: 'Fira Code', 'Courier New', monospace;
    font-size: 14px;
    line-height: 1.6;
    resize: vertical;
    white-space: pre;
    overflow-x: auto;
}

.css-editor textarea:focus {
    outline: none;
}

.css-help {
    background: var(--card-background);
    padding: 20px;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
}

.css-help h4 {
    margin-bottom: 15px;
    color: var(--text-color);
}

.css-help pre {
    background: #f7f9fc;
    padding: 15px;
    border-radius: 8px;
    overflow-x: auto;
    font-family: monospace;
    color: var(--text-color);
    border: 1px solid #e2e8f0;
}

/* Live preview */
.live-preview {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: var(--primary-color);
    color: white;
    padding: 10px 20px;
    border-radius: 50px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.2);
    display: flex;
    align-items: center;
    gap: 10px;
    z-index: 10000;
    cursor: pointer;
    transition: all 0.3s;
}

.live-preview:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.3);
}

.live-preview i {
    font-size: 1.2rem;
}

/* Адаптивность */
@media (max-width: 768px) {
    .settings-grid {
        grid-template-columns: 1fr;
    }
    
    .customize-tabs {
        flex-wrap: nowrap;
    }
    
    .customize-tab-btn {
        padding: 8px 12px;
        font-size: 0.85rem;
    }
    
    .customize-tab-btn i {
        font-size: 0.9rem;
    }
    
    .image-upload-wrapper {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .stats-grid {
        grid-template-columns: 1fr 1fr;
    }
    
    .form-row {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .stat-card {
        padding: 15px;
    }
    
    .stat-icon {
        width: 50px;
        height: 50px;
        font-size: 1.5rem;
    }
    
    .stat-value {
        font-size: 1.5rem;
    }
    
    .admin-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .settings-actions {
        flex-direction: column;
    }
    
    .settings-actions .btn {
        width: 100%;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .action-buttons {
        justify-content: flex-start;
    }
    
    .customize-group {
        padding: 20px;
    }
    
    .color-picker-wrapper {
        flex-wrap: wrap;
    }
    
    .color-picker {
        width: 100%;
        height: 50px;
    }
    
    .menu-info {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
// Функции для редактирования
function editProduct(product) {
    document.getElementById('edit_product_id').value = product.id;
    document.getElementById('edit_product_name').value = product.name;
    document.getElementById('edit_product_description').value = product.description || '';
    document.getElementById('edit_product_price').value = product.price;
    document.getElementById('edit_product_category').value = product.category_id || '';
    
    const container = document.getElementById('current_image_container');
    if (product.image && product.image !== '') {
        container.innerHTML = `<img src="uploads/${product.image}" alt="${product.name}">`;
    } else {
        container.innerHTML = '<div class="no-image-small" style="width:100px;height:100px;"><i class="fas fa-image"></i></div>';
    }
    
    document.getElementById('editProductModal').classList.add('active');
}

function editCategory(category) {
    document.getElementById('edit_category_id').value = category.id;
    document.getElementById('edit_category_name').value = category.name;
    document.getElementById('edit_category_description').value = category.description || '';
    
    document.getElementById('editCategoryModal').classList.add('active');
}

function editDiscount(discount) {
    document.getElementById('edit_discount_id').value = discount.id;
    document.getElementById('edit_discount_code').value = discount.code;
    document.getElementById('edit_discount_type').value = discount.type;
    document.getElementById('edit_discount_value').value = discount.value;
    
    // Форматируем дату для input datetime-local
    const date = new Date(discount.valid_until);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    document.getElementById('edit_discount_valid_until').value = `${year}-${month}-${day}T${hours}:${minutes}`;
    
    document.getElementById('edit_discount_max_uses').value = discount.max_uses;
    
    document.getElementById('editDiscountModal').classList.add('active');
}

// Закрытие модальных окон
document.querySelectorAll('.modal-close').forEach(btn => {
    btn.addEventListener('click', function() {
        this.closest('.modal').classList.remove('active');
    });
});

// Закрытие по клику вне модального окна
document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('active');
        }
    });
});

// Отображение имени файла при выборе
document.getElementById('product-image')?.addEventListener('change', function() {
    const fileName = this.files[0]?.name || 'Файл не выбран';
    const fileNameSpan = document.querySelector('.file-name');
    if (fileNameSpan) {
        fileNameSpan.textContent = fileName;
    }
});

// Табы
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const tabId = this.dataset.tab;
        
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        
        this.classList.add('active');
        const tabContent = document.getElementById(`tab-${tabId}`);
        if (tabContent) {
            tabContent.classList.add('active');
        }
        
        // Сохраняем активный таб в localStorage
        localStorage.setItem('adminActiveTab', tabId);
    });
});

// Восстанавливаем активный таб
const savedTab = localStorage.getItem('adminActiveTab');
if (savedTab) {
    const tabBtn = document.querySelector(`.tab-btn[data-tab="${savedTab}"]`);
    if (tabBtn) {
        setTimeout(() => {
            tabBtn.click();
        }, 100);
    }
}

// Подтверждение действий
document.querySelectorAll('form[onsubmit]').forEach(form => {
    const originalSubmit = form.onsubmit;
    form.onsubmit = function(e) {
        if (!confirm('Вы уверены?')) {
            e.preventDefault();
            return false;
        }
        return true;
    };
});

// Переключение групп настроек
document.querySelectorAll('.customize-tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const group = this.dataset.group;
        
        document.querySelectorAll('.customize-tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.customize-group').forEach(g => g.style.display = 'none');
        
        this.classList.add('active');
        const groupElement = document.getElementById(`group-${group}`);
        if (groupElement) {
            groupElement.style.display = 'block';
        }
    });
});

// Сохранение настроек
function saveSettings() {
    const form = document.getElementById('settings-form');
    const formData = new FormData(form);
    
    showNotification('Сохранение...', 'info');
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(() => {
        showNotification('Настройки сохранены!', 'success');
        updateLivePreview();
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Ошибка при сохранении', 'error');
    });
}

// Сохранение CSS
function saveCSS() {
    const form = document.getElementById('css-form');
    const formData = new FormData(form);
    
    showNotification('Сохранение CSS...', 'info');
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(() => {
        showNotification('CSS сохранен!', 'success');
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Ошибка при сохранении CSS', 'error');
    });
}

// Сброс настроек
function resetSettings() {
    if (confirm('Сбросить все настройки к значениям по умолчанию?')) {
        const formData = new FormData();
        formData.append('reset_settings', '1');
        
        showNotification('Сброс настроек...', 'info');
        
        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(() => {
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Ошибка при сбросе', 'error');
        });
    }
}

// Обновление live preview
function updateLivePreview() {
    const links = document.querySelectorAll('link[rel="stylesheet"]');
    links.forEach(link => {
        if (link.href.includes('custom.css')) {
            const newHref = 'custom.css.php?' + new Date().getTime();
            link.href = newHref;
        }
    });
}

// Предпросмотр изображений
document.querySelectorAll('input[type="file"]').forEach(input => {
    input.addEventListener('change', function() {
        const previewId = this.id.replace('_file', '_preview');
        const preview = document.getElementById(previewId);
        const hiddenInput = document.getElementById(this.id.replace('_file', ''));
        
        if (this.files && this.files[0] && preview) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
            };
            
            reader.readAsDataURL(this.files[0]);
        }
    });
});

// Живой предпросмотр изменений
document.querySelectorAll('.setting-item input, .setting-item select').forEach(element => {
    element.addEventListener('change', function() {
        const key = this.id;
        const value = this.value;
        
        if (this.type === 'color') {
            document.documentElement.style.setProperty(`--${key}`, value);
        }
    });
});

// Создание кнопки live preview (если её нет)
if (!document.querySelector('.live-preview')) {
    const livePreviewBtn = document.createElement('div');
    livePreviewBtn.className = 'live-preview';
    livePreviewBtn.innerHTML = '<i class="fas fa-eye"></i> Предпросмотр';
    livePreviewBtn.onclick = function() {
        window.open('/', '_blank', 'width=1200,height=800');
    };
    document.body.appendChild(livePreviewBtn);
}

// Функция показа уведомлений
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i> ${message}`;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

// Автосохранение CSS
let cssTimeout;
const cssEditor = document.getElementById('custom_css');
if (cssEditor) {
    cssEditor.addEventListener('input', function() {
        clearTimeout(cssTimeout);
        const statusEl = document.querySelector('.css-status');
        if (statusEl) {
            statusEl.textContent = 'Изменения не сохранены...';
            statusEl.style.color = '#e5c07b';
        }
        
        cssTimeout = setTimeout(() => {
            saveCSS();
            if (statusEl) {
                statusEl.textContent = 'Автосохранение...';
                statusEl.style.color = '#98c379';
            }
        }, 2000);
    });
}

// Сохранение общих настроек
function saveGeneralSettings() {
    const form = document.getElementById('general-settings-form');
    const formData = new FormData(form);
    formData.append('save_settings', '1');
    
    showNotification('Сохранение...', 'info');
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(() => {
        showNotification('Настройки сохранены!', 'success');
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Ошибка при сохранении', 'error');
    });
}

// Сохранение настроек подвала
function saveFooterSettings() {
    const form = document.getElementById('footer-settings-form');
    const formData = new FormData(form);
    formData.append('save_settings', '1');
    
    // Преобразуем FormData в объект для отправки
    const settings = {};
    formData.forEach((value, key) => {
        settings[key] = value;
    });
    
    // Создаем новый FormData с правильной структурой
    const saveData = new FormData();
    saveData.append('save_settings', '1');
    Object.keys(settings).forEach(key => {
        saveData.append(`settings[${key}]`, settings[key]);
    });
    
    showNotification('Сохранение...', 'info');
    
    fetch(window.location.href, {
        method: 'POST',
        body: saveData
    })
    .then(response => response.text())
    .then(() => {
        showNotification('Настройки подвала сохранены!', 'success');
        updateLivePreview();
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Ошибка при сохранении', 'error');
    });
}

// Смена пароля
function changePassword() {
    const form = document.getElementById('password-form');
    const currentPassword = form.querySelector('[name="current_password"]').value;
    const newPassword = form.querySelector('[name="new_password"]').value;
    const confirmPassword = form.querySelector('[name="confirm_password"]').value;
    
    if (!currentPassword || !newPassword || !confirmPassword) {
        showNotification('Заполните все поля', 'error');
        return;
    }
    
    if (newPassword !== confirmPassword) {
        showNotification('Пароли не совпадают', 'error');
        return;
    }
    
    if (newPassword.length < 8) {
        showNotification('Пароль должен быть минимум 8 символов', 'error');
        return;
    }
    
    // В демо-режиме просто показываем уведомление
    showNotification('В демо-режиме смена пароля недоступна', 'warning');
}

// Обработка сортировки категорий
document.querySelectorAll('.sort-order-input').forEach(input => {
    input.addEventListener('change', function() {
        const categoryId = this.dataset.categoryId;
        const sortOrder = this.value;
        
        fetch(window.location.href, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `update_category_order=1&id=${categoryId}&sort_order=${sortOrder}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Порядок сортировки сохранен', 'success');
            }
        });
    });
});

// Обработка статуса категории
document.querySelectorAll('.status-toggle').forEach(toggle => {
    toggle.addEventListener('change', function() {
        const categoryId = this.dataset.categoryId;
        const isActive = this.checked ? 1 : 0;
        
        fetch(window.location.href, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `toggle_category_status=1&id=${categoryId}&is_active=${isActive}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Статус категории обновлен', 'success');
                // Обновляем информацию о меню через 1 секунду
                setTimeout(() => {
                    location.reload();
                }, 1000);
            }
        });
    });
});
</script>

<?php
// Функция для описания настроек
function getSettingDescription($key) {
    $descriptions = [
        'primary_color' => 'Основной цвет магазина',
        'primary_light' => 'Светлый вариант основного цвета',
        'primary_dark' => 'Темный вариант основного цвета',
        'secondary_color' => 'Вторичный цвет (обычно белый)',
        'text_color' => 'Цвет основного текста',
        'text_light' => 'Цвет второстепенного текста',
        'background_color' => 'Цвет фона сайта',
        'card_background' => 'Цвет фона карточек',
        'success_color' => 'Цвет успешных операций',
        'error_color' => 'Цвет ошибок',
        'warning_color' => 'Цвет предупреждений',
        'button_radius' => 'Радиус скругления кнопок (например: 4%, 8px)',
        'button_padding' => 'Внутренние отступы кнопок',
        'button_font_size' => 'Размер шрифта на кнопках',
        'button_font_weight' => 'Жирность шрифта на кнопках',
        'button_shadow' => 'Тень кнопок',
        'button_hover_scale' => 'Масштабирование при наведении',
        'header_position' => 'Положение шапки на странице',
        'header_height' => 'Высота шапки',
        'logo_position' => 'Расположение логотипа',
        'nav_position' => 'Расположение навигации',
        'cart_position' => 'Расположение корзины',
        'products_per_row_desktop' => 'Количество товаров в ряду на десктопе',
        'products_per_row_tablet' => 'Количество товаров в ряду на планшете',
        'products_per_row_mobile' => 'Количество товаров в ряду на телефоне',
        'font_family' => 'Основной шрифт сайта',
        'font_size_base' => 'Базовый размер шрифта',
        'heading_font_weight' => 'Жирность заголовков',
        'floating_button_enabled' => 'Показать плавающую кнопку на странице товара',
        'floating_button_animation' => 'Анимация плавающей кнопки',
        'floating_button_bottom' => 'Отступ от нижнего края',
        'cart_icon_color' => 'Цвет иконки корзины',
        'cart_count_bg' => 'Фон счетчика корзины',
        'cart_count_color' => 'Цвет счетчика корзины',
        'product_image_height' => 'Высота изображения товара',
        'product_border_radius' => 'Радиус скругления карточки товара',
        'product_shadow' => 'Тень карточки товара',
        'product_shadow_hover' => 'Тень при наведении',
        'category_image_height' => 'Высота изображения категории',
        'category_border_radius' => 'Радиус скругления карточки категории',
        'site_name' => 'Название магазина в подвале',
        'site_description' => 'Описание магазина в подвале',
        'contact_email' => 'Email для контактов',
        'contact_phone' => 'Телефон для контактов',
        'contact_address' => 'Физический адрес магазина',
        'copyright' => 'Текст копирайта',
        'social_telegram' => 'Ссылка на Telegram',
        'social_vk' => 'Ссылка на VK',
        'social_instagram' => 'Ссылка на Instagram',
        'social_youtube' => 'Ссылка на YouTube',
        'social_whatsapp' => 'Ссылка на WhatsApp',
        'footer_background' => 'Цвет фона подвала',
        'footer_text_color' => 'Цвет текста в подвале',
        'footer_link_color' => 'Цвет ссылок в подвале',
        'footer_padding' => 'Отступы в подвале',
        'footer_columns' => 'Количество колонок в подвале',
        'footer_show_map' => 'Показывать карту в подвале',
        'footer_map_url' => 'URL для встраивания карты',
        'footer_show_newsletter' => 'Показывать форму подписки',
    ];
    
    return $descriptions[$key] ?? 'Настройка внешнего вида';
}
?>

<?php include 'includes/footer.php'; ?>