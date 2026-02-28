<?php
require_once 'config.php';

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Получение товара
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p 
                       LEFT JOIN categories c ON p.category_id = c.id 
                       WHERE p.id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header('Location: index.php');
    exit;
}

include 'includes/header.php';
?>

<div class="container">
    <div class="product-page">
        <div class="product-details">
            <div class="product-gallery">
                <div class="main-image">
                    <?php if ($product['image'] && file_exists('uploads/' . $product['image'])): ?>
                        <img src="uploads/<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <?php else: ?>
                        <div class="no-image-large">
                            <i class="fas fa-image"></i>
                            <span>Нет фото</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="product-info-detailed">
                <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                
                <div class="product-meta">
                    <span class="product-category">
                        <i class="fas fa-tag"></i>
                        <?php echo htmlspecialchars($product['category_name'] ?? 'Без категории'); ?>
                    </span>
                </div>
                
                <div class="product-description-full">
                    <h3>Описание</h3>
                    <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                </div>
                
                <div class="product-price-block">
                    <span class="current-price"><?php echo number_format($product['price'], 0, '', ' '); ?> ₽</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Плавающая кнопка добавления в корзину -->
<div class="floating-cart-btn">
    <button class="add-to-cart-floating" data-product-id="<?php echo $product['id']; ?>" data-product-name="<?php echo htmlspecialchars($product['name']); ?>" data-product-price="<?php echo $product['price']; ?>">
        <i class="fas fa-cart-plus"></i>
        Добавить в корзину - <?php echo number_format($product['price'], 0, '', ' '); ?> ₽
    </button>
</div>

<?php include 'includes/footer.php'; ?>