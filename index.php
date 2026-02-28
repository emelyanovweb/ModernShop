<?php
require_once 'config.php';

// Получение категорий
$categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);

// Получение товаров с фильтром по категории
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;
if ($categoryId) {
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p 
                           LEFT JOIN categories c ON p.category_id = c.id 
                           WHERE p.category_id = ?");
    $stmt->execute([$categoryId]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $products = $pdo->query("SELECT p.*, c.name as category_name FROM products p 
                             LEFT JOIN categories c ON p.category_id = c.id")->fetchAll(PDO::FETCH_ASSOC);
}

include 'includes/header.php';
?>

<div class="container">
    <section class="hero">
        <div class="hero-content">
            <h1>Добро пожаловать</h1>
            <p>Откройте для себя лучшие товары по выгодным ценам</p>
        </div>
    </section>
    
    <section class="categories-section">
        <h2 class="section-title">Категории</h2>
        <div class="categories-grid">
            <?php foreach ($categories as $category): ?>
                <a href="?category=<?php echo $category['id']; ?>" class="category-card">
                    <i class="fas fa-folder"></i>
                    <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                    <p><?php echo htmlspecialchars($category['description']); ?></p>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
    
    <section class="products-section">
        <h2 class="section-title">
            <?php echo $categoryId ? 'Товары в категории' : 'Все товары'; ?>
        </h2>
        
        <?php if (empty($products)): ?>
            <p class="no-products">Товары не найдены</p>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <a href="product.php?id=<?php echo $product['id']; ?>" class="product-link">
                            <div class="product-image">
                                <?php if ($product['image'] && file_exists('uploads/' . $product['image'])): ?>
                                    <img src="uploads/<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <?php else: ?>
                                    <div class="no-image">
                                        <i class="fas fa-image"></i>
                                        <span>Нет фото</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="product-info">
                                <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p class="product-category"><?php echo htmlspecialchars($product['category_name'] ?? 'Без категории'); ?></p>
                                <p class="product-description"><?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?></p>
                                <div class="product-price">
                                    <span class="price"><?php echo number_format($product['price'], 0, '', ' '); ?> ₽</span>
                                </div>
                            </div>
                        </a>
                        <button class="add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>" data-product-name="<?php echo htmlspecialchars($product['name']); ?>" data-product-price="<?php echo $product['price']; ?>">
                            <i class="fas fa-cart-plus"></i>
                            В корзину
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php include 'includes/footer.php'; ?>