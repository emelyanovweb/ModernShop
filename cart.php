<?php
require_once 'config.php';

$message = '';
$discountMessage = '';

// Обработка добавления в корзину через AJAX
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] == 'add') {
        $productId = (int)$_POST['product_id'];
        $productName = $_POST['product_name'];
        $productPrice = (float)$_POST['product_price'];
        $quantity = (int)($_POST['quantity'] ?? 1);
        
        // Проверяем, есть ли уже такой товар в корзине
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $productId) {
                $item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            $_SESSION['cart'][] = [
                'id' => $productId,
                'name' => $productName,
                'price' => $productPrice,
                'quantity' => $quantity
            ];
        }
        
        echo json_encode(['success' => true, 'count' => array_sum(array_column($_SESSION['cart'], 'quantity'))]);
        exit;
    }
    
    if ($_POST['action'] == 'update') {
        $productId = (int)$_POST['product_id'];
        $quantity = (int)$_POST['quantity'];
        
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $productId) {
                if ($quantity <= 0) {
                    $item['quantity'] = 0;
                } else {
                    $item['quantity'] = $quantity;
                }
                break;
            }
        }
        
        // Удаляем товары с нулевым количеством
        $_SESSION['cart'] = array_filter($_SESSION['cart'], function($item) {
            return $item['quantity'] > 0;
        });
        
        echo json_encode(['success' => true, 'total' => getCartTotal()]);
        exit;
    }
    
    if ($_POST['action'] == 'remove') {
        $productId = (int)$_POST['product_id'];
        
        $_SESSION['cart'] = array_filter($_SESSION['cart'], function($item) use ($productId) {
            return $item['id'] != $productId;
        });
        
        echo json_encode(['success' => true, 'total' => getCartTotal()]);
        exit;
    }
    
    if ($_POST['action'] == 'apply_discount') {
        $code = $_POST['code'];
        $total = getCartTotal();
        $result = applyDiscount($code, $total);
        
        if ($result['discount']) {
            $_SESSION['discount'] = $result['discount']['code'];
            echo json_encode(['success' => true, 'total' => $result['total'], 'discount' => $result['discount']]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Недействительный код скидки']);
        }
        exit;
    }
}

include 'includes/header.php';

$cartItems = $_SESSION['cart'];
$total = getCartTotal();
$discount = null;

// Применяем скидку, если она есть в сессии
if (isset($_SESSION['discount'])) {
    $result = applyDiscount($_SESSION['discount'], $total);
    $total = $result['total'];
    $discount = $result['discount'];
}
?>

<div class="container">
    <h1 class="page-title">Корзина</h1>
    
    <?php if (empty($cartItems)): ?>
        <div class="empty-cart">
            <i class="fas fa-shopping-cart"></i>
            <h2>Корзина пуста</h2>
            <p>Добавьте товары, чтобы оформить заказ</p>
            <a href="index.php" class="btn btn-primary">Перейти к покупкам</a>
        </div>
    <?php else: ?>
        <div class="cart-content">
            <div class="cart-items">
                <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item" data-product-id="<?php echo $item['id']; ?>">
                        <div class="cart-item-info">
                            <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                            <div class="cart-item-price"><?php echo number_format($item['price'], 0, '', ' '); ?> ₽</div>
                        </div>
                        
                        <div class="cart-item-actions">
                            <div class="quantity-control">
                                <button class="quantity-btn minus" data-product-id="<?php echo $item['id']; ?>">-</button>
                                <input type="number" class="quantity-input" value="<?php echo $item['quantity']; ?>" min="1" data-product-id="<?php echo $item['id']; ?>">
                                <button class="quantity-btn plus" data-product-id="<?php echo $item['id']; ?>">+</button>
                            </div>
                            
                            <div class="cart-item-total">
                                <?php echo number_format($item['price'] * $item['quantity'], 0, '', ' '); ?> ₽
                            </div>
                            
                            <button class="remove-item" data-product-id="<?php echo $item['id']; ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="cart-sidebar">
                <div class="cart-summary">
                    <h3>Итого</h3>
                    
                    <div class="summary-row">
                        <span>Товары:</span>
                        <span><?php echo number_format(getCartTotal(), 0, '', ' '); ?> ₽</span>
                    </div>
                    
                    <?php if ($discount): ?>
                        <div class="summary-row discount">
                            <span>Скидка:</span>
                            <span>-<?php echo $discount['type'] == 'percentage' ? $discount['value'] . '%' : number_format($discount['value'], 0, '', ' ') . ' ₽'; ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="summary-row total">
                        <span>К оплате:</span>
                        <span><?php echo number_format($total, 0, '', ' '); ?> ₽</span>
                    </div>
                    
                    <div class="discount-section">
                        <h4>Промокод</h4>
                        <div class="discount-input">
                            <input type="text" id="discount-code" placeholder="Введите промокод">
                            <button id="apply-discount" class="btn btn-secondary">Применить</button>
                        </div>
                        <div id="discount-message"></div>
                    </div>
                    
                    <button class="btn btn-primary checkout-btn">Оформить заказ</button>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>