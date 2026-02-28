// script.js
document.addEventListener('DOMContentLoaded', function() {
    // Элементы для уведомлений
    const notificationContainer = createNotificationContainer();
    
    // Добавление в корзину
    const addToCartButtons = document.querySelectorAll('.add-to-cart-btn, .add-to-cart-floating');
    
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const productId = this.dataset.productId;
            const productName = this.dataset.productName;
            const productPrice = this.dataset.productPrice;
            const quantity = this.dataset.quantity || 1;
            
            // Блокируем кнопку на время запроса
            this.disabled = true;
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Добавление...';
            
            fetch('cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=add&product_id=${productId}&product_name=${encodeURIComponent(productName)}&product_price=${productPrice}&quantity=${quantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Обновляем счетчик корзины
                    updateCartCount(data.count);
                    
                    // Показываем уведомление по центру сверху
                    showCenteredNotification('Товар добавлен в корзину!', 'success');
                    
                    // Анимация кнопки
                    animateButton(this);
                    
                    // Добавляем эффект пульсации на иконку корзины
                    const cartIcon = document.querySelector('.cart-icon');
                    if (cartIcon) {
                        cartIcon.classList.add('cart-bounce');
                        setTimeout(() => {
                            cartIcon.classList.remove('cart-bounce');
                        }, 500);
                    }
                } else {
                    showCenteredNotification('Ошибка при добавлении', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showCenteredNotification('Ошибка соединения', 'error');
            })
            .finally(() => {
                // Возвращаем кнопку в исходное состояние
                this.disabled = false;
                this.innerHTML = originalText;
            });
        });
    });
    
    // Обновление количества в корзине
    const quantityInputs = document.querySelectorAll('.quantity-input');
    const minusButtons = document.querySelectorAll('.quantity-btn.minus');
    const plusButtons = document.querySelectorAll('.quantity-btn.plus');
    
    // Функция обновления количества
    function updateQuantity(productId, newQuantity) {
        const input = document.querySelector(`.quantity-input[data-product-id="${productId}"]`);
        if (input) {
            input.value = newQuantity;
        }
        
        // Отправляем запрос на обновление
        fetch('cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=update&product_id=${productId}&quantity=${newQuantity}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Обновляем общую сумму
                updateCartTotal(data.total);
                
                // Обновляем сумму для конкретного товара
                const cartItem = document.querySelector(`.cart-item[data-product-id="${productId}"]`);
                if (cartItem) {
                    const price = parseFloat(cartItem.querySelector('.cart-item-price').textContent.replace(/[^\d]/g, ''));
                    const totalElement = cartItem.querySelector('.cart-item-total');
                    if (totalElement) {
                        totalElement.textContent = formatPrice(price * newQuantity) + ' ₽';
                    }
                }
                
                // Обновляем общее количество товаров в корзине
                const totalQuantity = Array.from(document.querySelectorAll('.quantity-input'))
                    .reduce((sum, input) => sum + parseInt(input.value), 0);
                updateCartCount(totalQuantity);
            }
        });
    }
    
    // Обработчики для инпутов
    quantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            const productId = this.dataset.productId;
            let quantity = parseInt(this.value);
            
            if (isNaN(quantity) || quantity < 1) {
                quantity = 1;
            }
            
            updateQuantity(productId, quantity);
        });
    });
    
    // Обработчики для кнопок минус
    minusButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const input = document.querySelector(`.quantity-input[data-product-id="${productId}"]`);
            let quantity = parseInt(input.value) - 1;
            
            if (quantity >= 1) {
                updateQuantity(productId, quantity);
            }
        });
    });
    
    // Обработчики для кнопок плюс
    plusButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const input = document.querySelector(`.quantity-input[data-product-id="${productId}"]`);
            let quantity = parseInt(input.value) + 1;
            
            updateQuantity(productId, quantity);
        });
    });
    
    // Удаление из корзины
    const removeButtons = document.querySelectorAll('.remove-item');
    
    removeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            
            if (confirm('Удалить товар из корзины?')) {
                // Показываем индикатор загрузки
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                this.disabled = true;
                
                fetch('cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=remove&product_id=${productId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Удаляем элемент из DOM с анимацией
                        const cartItem = document.querySelector(`.cart-item[data-product-id="${productId}"]`);
                        if (cartItem) {
                            cartItem.style.transition = 'all 0.3s ease';
                            cartItem.style.opacity = '0';
                            cartItem.style.transform = 'translateX(-20px)';
                            
                            setTimeout(() => {
                                cartItem.remove();
                                
                                // Обновляем общую сумму
                                updateCartTotal(data.total);
                                
                                // Обновляем счетчик корзины
                                const totalQuantity = Array.from(document.querySelectorAll('.quantity-input'))
                                    .reduce((sum, input) => sum + parseInt(input.value), 0);
                                updateCartCount(totalQuantity);
                                
                                // Проверяем, пуста ли корзина
                                checkEmptyCart();
                                
                                showCenteredNotification('Товар удален из корзины', 'success');
                            }, 300);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showCenteredNotification('Ошибка при удалении', 'error');
                });
            }
        });
    });
    
    // Применение скидки
    const applyDiscountBtn = document.getElementById('apply-discount');
    const discountCodeInput = document.getElementById('discount-code');
    
    if (applyDiscountBtn && discountCodeInput) {
        applyDiscountBtn.addEventListener('click', function() {
            const code = discountCodeInput.value.trim();
            
            if (!code) {
                showCenteredNotification('Введите промокод', 'error');
                return;
            }
            
            // Блокируем кнопку
            this.disabled = true;
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Проверка...';
            
            fetch('cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=apply_discount&code=${encodeURIComponent(code)}`
            })
            .then(response => response.json())
            .then(data => {
                const messageDiv = document.getElementById('discount-message');
                
                if (data.success) {
                    messageDiv.innerHTML = '<span class="discount-success"><i class="fas fa-check-circle"></i> Промокод применен!</span>';
                    updateCartTotal(data.total);
                    showCenteredNotification('Промокод успешно применен!', 'success');
                    
                    // Добавляем информацию о скидке
                    addDiscountInfo(data.discount);
                } else {
                    messageDiv.innerHTML = '<span class="discount-error"><i class="fas fa-exclamation-circle"></i> ' + (data.message || 'Недействительный код') + '</span>';
                    showCenteredNotification('' + (data.message || 'Недействительный код'), 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showCenteredNotification('Ошибка проверки промокода', 'error');
            })
            .finally(() => {
                this.disabled = false;
                this.innerHTML = originalText;
            });
        });
        
        // Добавляем обработчик на Enter
        discountCodeInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                applyDiscountBtn.click();
            }
        });
    }
    
    // Оформление заказа (демо-оплата)
    const checkoutBtn = document.querySelector('.checkout-btn');
    
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', function() {
            showPaymentModal();
        });
    }
    
    // Табы в админке
    const tabBtns = document.querySelectorAll('.tab-btn');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const tabId = this.dataset.tab;
            
            // Убираем активный класс у всех кнопок и контента
            tabBtns.forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            
            // Добавляем активный класс текущей кнопке и контенту
            this.classList.add('active');
            const tabContent = document.getElementById(`tab-${tabId}`);
            if (tabContent) {
                tabContent.classList.add('active');
            }
        });
    });
    
    // Функция создания контейнера для уведомлений
    function createNotificationContainer() {
        let container = document.querySelector('.notification-center');
        
        if (!container) {
            container = document.createElement('div');
            container.className = 'notification-center';
            container.style.cssText = `
                position: fixed;
                top: 20px;
                left: 50%;
                transform: translateX(-50%);
                z-index: 9999;
                pointer-events: none;
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 10px;
                width: 100%;
                max-width: 400px;
            `;
            document.body.appendChild(container);
        }
        
        return container;
    }
    
    // Функция показа центрированного уведомления
    function showCenteredNotification(message, type = 'info') {
        const container = notificationContainer || createNotificationContainer();
        
        const notification = document.createElement('div');
        notification.className = `notification-center-item ${type}`;
        notification.style.cssText = `
            background: ${type === 'success' ? '#38a169' : type === 'error' ? '#e53e3e' : type === 'warning' ? '#d69e2e' : '#4a90e2'};
            color: white;
            padding: 15px 25px;
            border-radius: 50px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideDown 0.3s ease forwards;
            margin-bottom: 10px;
            pointer-events: auto;
            max-width: 90%;
            text-align: center;
            justify-content: center;
            border: 2px solid rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
        `;
        
        // Добавляем иконку в зависимости от типа
        let icon = '';
        switch(type) {
            case 'success':
                icon = '<i class="fas fa-check-circle"></i>';
                break;
            case 'error':
                icon = '<i class="fas fa-exclamation-circle"></i>';
                break;
            case 'warning':
                icon = '<i class="fas fa-exclamation-triangle"></i>';
                break;
            default:
                icon = '<i class="fas fa-info-circle"></i>';
        }
        
        notification.innerHTML = `${icon} ${message}`;
        container.appendChild(notification);
        
        // Анимация исчезновения
        setTimeout(() => {
            notification.style.animation = 'slideUp 0.3s ease forwards';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 300);
        }, 3000);
    }
    
    // Функция обновления счетчика корзины
    function updateCartCount(count) {
        const cartIcon = document.querySelector('.cart-icon');
        
        if (!cartIcon) return;
        
        let cartCount = cartIcon.querySelector('.cart-count');
        
        if (count > 0) {
            if (cartCount) {
                cartCount.textContent = count;
            } else {
                cartCount = document.createElement('span');
                cartCount.className = 'cart-count';
                cartCount.textContent = count;
                cartIcon.appendChild(cartCount);
            }
        } else {
            if (cartCount) {
                cartCount.remove();
            }
        }
    }
    
    // Функция обновления общей суммы
    function updateCartTotal(total) {
        const totalElements = document.querySelectorAll('.summary-row.total span:last-child, .cart-summary .total span:last-child');
        totalElements.forEach(el => {
            el.textContent = formatPrice(total) + ' ₽';
        });
        
        // Обновляем также в модальном окне оплаты
        const modalTotal = document.getElementById('modal-total-amount');
        if (modalTotal) {
            modalTotal.textContent = formatPrice(total) + ' ₽';
        }
    }
    
    // Функция форматирования цены
    function formatPrice(price) {
        return Math.round(price).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    }
    
    // Функция проверки пустой корзины
    function checkEmptyCart() {
        const cartItems = document.querySelectorAll('.cart-item');
        const cartContent = document.querySelector('.cart-content');
        const emptyCartDiv = document.querySelector('.empty-cart');
        
        if (cartItems.length === 0 && cartContent) {
            // Плавно скрываем содержимое корзины
            cartContent.style.opacity = '0';
            
            setTimeout(() => {
                if (emptyCartDiv) {
                    emptyCartDiv.style.display = 'block';
                    cartContent.remove();
                } else {
                    location.reload();
                }
            }, 300);
        }
    }
    
    // Функция анимации кнопки
    function animateButton(button) {
        button.style.transform = 'scale(0.95)';
        button.style.transition = 'transform 0.2s ease';
        
        setTimeout(() => {
            button.style.transform = '';
        }, 200);
    }
    
    // Функция добавления информации о скидке
    function addDiscountInfo(discount) {
        const cartSidebar = document.querySelector('.cart-sidebar');
        if (!cartSidebar) return;
        
        // Удаляем старую информацию о скидке
        const oldDiscountRow = document.querySelector('.summary-row.discount');
        if (oldDiscountRow) {
            oldDiscountRow.remove();
        }
        
        // Создаем новую строку со скидкой
        const summaryRows = document.querySelectorAll('.summary-row');
        const totalRow = document.querySelector('.summary-row.total');
        
        if (summaryRows.length > 0 && totalRow) {
            const discountRow = document.createElement('div');
            discountRow.className = 'summary-row discount';
            
            let discountText = '';
            if (discount.type === 'percentage') {
                discountText = `-${discount.value}%`;
            } else {
                discountText = `-${formatPrice(discount.value)} ₽`;
            }
            
            discountRow.innerHTML = `
                <span>Скидка:</span>
                <span>${discountText}</span>
            `;
            
            totalRow.parentNode.insertBefore(discountRow, totalRow);
        }
    }
    
    // Функция показа модального окна оплаты
    function showPaymentModal() {
        const total = document.querySelector('.summary-row.total span:last-child')?.textContent || '0 ₽';
        
        // Создаем модальное окно
        const modal = document.createElement('div');
        modal.className = 'payment-modal';
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(5px);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease;
        `;
        
        modal.innerHTML = `
            <div class="payment-modal-content" style="
                background: white;
                border-radius: 20px;
                padding: 30px;
                max-width: 500px;
                width: 90%;
                max-height: 90vh;
                overflow-y: auto;
                position: relative;
                animation: slideUpModal 0.3s ease;
            ">
                <button class="modal-close" style="
                    position: absolute;
                    top: 15px;
                    right: 15px;
                    background: none;
                    border: none;
                    font-size: 1.5rem;
                    cursor: pointer;
                    color: #7f8c8d;
                    transition: color 0.3s;
                ">&times;</button>
                
                <h2 style="
                    margin-bottom: 20px;
                    color: #2c3e50;
                    font-size: 1.8rem;
                ">Оформление заказа</h2>
                
                <div class="payment-summary" style="
                    background: #f7f9fc;
                    padding: 20px;
                    border-radius: 12px;
                    margin-bottom: 25px;
                ">
                    <p style="display: flex; justify-content: space-between; margin: 10px 0;">
                        <span>Сумма заказа:</span>
                        <span style="font-weight: 600;">${total}</span>
                    </p>
                    <p style="display: flex; justify-content: space-between; margin: 10px 0;">
                        <span>Доставка:</span>
                        <span style="color: #38a169;">Бесплатно</span>
                    </p>
                    <p style="display: flex; justify-content: space-between; margin: 15px 0 0; padding-top: 15px; border-top: 2px solid #e2e8f0; font-weight: 700; font-size: 1.2rem;">
                        <span>ИТОГО:</span>
                        <span style="color: #4a90e2;">${total}</span>
                    </p>
                </div>
                
                <h3 style="margin-bottom: 15px;">Способ оплаты</h3>
                
                <div class="payment-methods" style="
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
                    gap: 15px;
                    margin-bottom: 25px;
                ">
                    <label class="payment-method" style="
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                        gap: 10px;
                        padding: 15px;
                        border: 2px solid #e2e8f0;
                        border-radius: 12px;
                        cursor: pointer;
                        transition: all 0.3s;
                    ">
                        <input type="radio" name="payment" value="card" checked style="display: none;">
                        <i class="fas fa-credit-card" style="font-size: 2rem; color: #4a90e2;"></i>
                        <span>Банковская карта</span>
                    </label>
                    
                    <label class="payment-method" style="
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                        gap: 10px;
                        padding: 15px;
                        border: 2px solid #e2e8f0;
                        border-radius: 12px;
                        cursor: pointer;
                        transition: all 0.3s;
                    ">
                        <input type="radio" name="payment" value="cash">
                        <i class="fas fa-money-bill" style="font-size: 2rem; color: #38a169;"></i>
                        <span>Наличные</span>
                    </label>
                    
                    <label class="payment-method" style="
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                        gap: 10px;
                        padding: 15px;
                        border: 2px solid #e2e8f0;
                        border-radius: 12px;
                        cursor: pointer;
                        transition: all 0.3s;
                    ">
                        <input type="radio" name="payment" value="online">
                        <i class="fas fa-mobile-alt" style="font-size: 2rem; color: #9f7aea;"></i>
                        <span>Онлайн кошелек</span>
                    </label>
                </div>
                
                <div class="card-details" style="
                    margin-bottom: 25px;
                    padding: 20px;
                    background: #f7f9fc;
                    border-radius: 12px;
                ">
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 500;">Номер карты</label>
                        <input type="text" placeholder="1234 5678 9012 3456" maxlength="19" style="
                            width: 100%;
                            padding: 12px;
                            border: 2px solid #e2e8f0;
                            border-radius: 8px;
                            font-size: 1rem;
                        ">
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Срок</label>
                            <input type="text" placeholder="MM/ГГ" maxlength="5" style="
                                width: 100%;
                                padding: 12px;
                                border: 2px solid #e2e8f0;
                                border-radius: 8px;
                                font-size: 1rem;
                            ">
                        </div>
                        
                        <div class="form-group">
                            <label style="display: block; margin-bottom: 5px; font-weight: 500;">CVV</label>
                            <input type="password" placeholder="123" maxlength="3" style="
                                width: 100%;
                                padding: 12px;
                                border: 2px solid #e2e8f0;
                                border-radius: 8px;
                                font-size: 1rem;
                            ">
                        </div>
                    </div>
                </div>
                
                <div style="display: flex; gap: 15px;">
                    <button class="btn btn-secondary" style="flex: 1;" onclick="this.closest('.payment-modal').remove()">
                        Отмена
                    </button>
                    <button class="btn btn-primary" style="flex: 2;" onclick="processPayment(this)">
                        <i class="fas fa-lock"></i>
                        Оплатить ${total}
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Обработчики для методов оплаты
        const paymentMethods = modal.querySelectorAll('.payment-method');
        paymentMethods.forEach(method => {
            method.addEventListener('click', function() {
                paymentMethods.forEach(m => {
                    m.style.borderColor = '#e2e8f0';
                    m.style.background = 'white';
                });
                this.style.borderColor = '#4a90e2';
                this.style.background = '#ebf4ff';
                
                const radio = this.querySelector('input[type="radio"]');
                if (radio) radio.checked = true;
                
                // Показываем/скрываем детали карты
                const cardDetails = modal.querySelector('.card-details');
                if (radio.value === 'card') {
                    cardDetails.style.display = 'block';
                } else {
                    cardDetails.style.display = 'none';
                }
            });
        });
        
        // Закрытие модального окна
        const closeBtn = modal.querySelector('.modal-close');
        closeBtn.addEventListener('click', () => modal.remove());
        
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.remove();
            }
        });
    }
    
    // Функция обработки платежа (демо)
    window.processPayment = function(button) {
        const modal = button.closest('.payment-modal');
        const total = button.textContent.match(/[\d\s]+/)?.[0] || '0';
        
        // Блокируем кнопку
        button.disabled = true;
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Обработка...';
        
        // Имитация обработки платежа
        setTimeout(() => {
            modal.remove();
            
            // Показываем уведомление об успехе
            showCenteredNotification(`Оплата прошла успешно!`, 'success');
            
            // Очищаем корзину (в демо-режиме)
            setTimeout(() => {
                if (confirm('Очистить корзину? (демо-режим)')) {
                    // Отправляем запрос на очистку корзины
                    fetch('cart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'action=clear'
                    })
                    .then(() => {
                        location.reload();
                    });
                }
            }, 1000);
        }, 2000);
    };
    
    // Обработка выбора файлов в админке
    const fileInput = document.getElementById('product-image');
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            const fileName = this.files[0]?.name || 'Файл не выбран';
            const fileNameSpan = document.querySelector('.file-name');
            if (fileNameSpan) {
                fileNameSpan.textContent = fileName;
            }
        });
    }
    
    // Добавляем стили для анимаций
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideDown {
            from {
                transform: translateY(-100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        @keyframes slideUp {
            from {
                transform: translateY(0);
                opacity: 1;
            }
            to {
                transform: translateY(-100%);
                opacity: 0;
            }
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        
        @keyframes slideUpModal {
            from {
                transform: translateY(50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        @keyframes cartBounce {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.2);
            }
        }
        
        .cart-bounce {
            animation: cartBounce 0.5s ease;
        }
        
        .notification-center {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            pointer-events: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            width: 100%;
            max-width: 400px;
        }
        
        .notification-center-item {
            animation: slideDown 0.3s ease forwards;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            border: 2px solid rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
        }
        
        .discount-success {
            color: #38a169;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .discount-error {
            color: #e53e3e;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        /* Стили для плавающей кнопки - исправление дерганья */
        .floating-cart-btn {
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            width: auto;
            max-width: 90%;
            animation: float 3s ease-in-out infinite;
            pointer-events: none;
        }
        
        .add-to-cart-floating {
            pointer-events: auto;
            position: relative;
            will-change: transform;
            backface-visibility: hidden;
            transform: translateZ(0);
        }
        
        .floating-cart-btn:hover {
            animation-play-state: paused;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateX(-50%) translateY(0);
            }
            50% {
                transform: translateX(-50%) translateY(-10px);
            }
        }
        
        /* Стили для модального окна оплаты */
        .payment-method {
            transition: all 0.3s ease;
        }
        
        .payment-method:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .payment-method.selected {
            border-color: #4a90e2;
            background: #ebf4ff;
        }
        
        .payment-modal-content {
            max-height: 90vh;
            overflow-y: auto;
            scrollbar-width: thin;
        }
        
        .payment-modal-content::-webkit-scrollbar {
            width: 8px;
        }
        
        .payment-modal-content::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .payment-modal-content::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }
        
        .payment-modal-content::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    `;
    
    document.head.appendChild(style);
});

// Глобальная функция для показа уведомлений (для вызова из HTML)
window.showNotification = function(message, type = 'info') {
    const event = new CustomEvent('showNotification', { detail: { message, type } });
    document.dispatchEvent(event);
};