-- phpMyAdmin SQL Dump
-- version 4.9.7
-- https://www.phpmyadmin.net/
--
-- Хост: localhost
-- Время создания: Фев 28 2026 г., 14:42
-- Версия сервера: 8.0.34-26-beget-1-1
-- Версия PHP: 5.6.40

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `sql`
--

-- --------------------------------------------------------

--
-- Структура таблицы `categories`
--
-- Создание: Фев 28 2026 г., 11:12
-- Последнее обновление: Фев 28 2026 г., 11:13
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `description` text NOT NULL,
  `sort_order` varchar(112) NOT NULL,
  `is_active` enum('0','1','true','false') NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `created_at`, `description`, `sort_order`, `is_active`) VALUES
(1, 'Электроника', 'electronics', '2026-02-28 08:38:15', '', '', '1'),
(2, 'Одежда', 'clothing', '2026-02-28 08:38:15', '', '', '1'),
(3, 'Книги', 'books', '2026-02-28 08:38:15', '', '1', '1'),
(4, 'Магазин', '', '2026-02-28 10:35:04', '13', '', '1');

-- --------------------------------------------------------

--
-- Структура таблицы `custom_css`
--
-- Создание: Фев 28 2026 г., 09:55
-- Последнее обновление: Фев 28 2026 г., 10:51
--

DROP TABLE IF EXISTS `custom_css`;
CREATE TABLE `custom_css` (
  `id` int NOT NULL,
  `css_code` text,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `custom_css`
--

INSERT INTO `custom_css` (`id`, `css_code`, `is_active`, `created_at`) VALUES
(2, '/* Пользовательские стили */', 1, '2026-02-28 10:51:52');

-- --------------------------------------------------------

--
-- Структура таблицы `discounts`
--
-- Создание: Фев 28 2026 г., 09:02
-- Последнее обновление: Фев 28 2026 г., 11:37
--

DROP TABLE IF EXISTS `discounts`;
CREATE TABLE `discounts` (
  `id` int NOT NULL,
  `code` varchar(50) NOT NULL,
  `type` enum('percentage','fixed') NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `valid_until` datetime DEFAULT NULL,
  `max_uses` int DEFAULT '1',
  `used` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `discounts`
--

INSERT INTO `discounts` (`id`, `code`, `type`, `value`, `valid_until`, `max_uses`, `used`, `created_at`) VALUES
(1, 'TEST', 'percentage', '10.00', '2026-03-30 12:15:00', 1000, 41, '2026-02-28 09:16:25'),
(2, 'TEST2', 'fixed', '10.00', '2026-03-30 13:33:00', 1, 1, '2026-02-28 10:33:51');

-- --------------------------------------------------------

--
-- Структура таблицы `discount_codes`
--
-- Создание: Фев 28 2026 г., 08:38
--

DROP TABLE IF EXISTS `discount_codes`;
CREATE TABLE `discount_codes` (
  `id` int NOT NULL,
  `code` varchar(50) NOT NULL,
  `discount_percent` int NOT NULL,
  `max_uses` int DEFAULT '1',
  `used_count` int DEFAULT '0',
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `discount_codes`
--

INSERT INTO `discount_codes` (`id`, `code`, `discount_percent`, `max_uses`, `used_count`, `expires_at`, `created_at`) VALUES
(1, 'WELCOME10', 10, 100, 0, '2026-03-30 08:38:15', '2026-02-28 08:38:15'),
(2, 'SALE20', 20, 50, 0, '2026-03-07 08:38:15', '2026-02-28 08:38:15');

-- --------------------------------------------------------

--
-- Структура таблицы `products`
--
-- Создание: Фев 28 2026 г., 08:38
-- Последнее обновление: Фев 28 2026 г., 10:35
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` int NOT NULL,
  `category_id` int DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `price`, `image`, `created_at`) VALUES
(1, 1, 'Смартфон XYZ', 'Современный смартфон с отличной камерой', '59990.00', 'phone.jpg', '2026-02-28 08:38:15'),
(2, 1, 'Ноутбук Pro', 'Мощный ноутбук для работы и игр', '89990.00', 'laptop.jpg', '2026-02-28 08:38:15'),
(3, 2, 'Футболка Classic', 'Хлопковая футболка', '1990.00', 'tshirt.jpg', '2026-02-28 08:38:15'),
(4, 3, 'Программирование на PHP', 'Книга для начинающих', '1290.00', 'book.jpg', '2026-02-28 08:38:15'),
(5, 2, 'test', 'test2', '100.00', '1772270245_69a2b2a51b861.png', '2026-02-28 09:17:25'),
(6, 4, '123', '32131', '2000.00', '1772274928_69a2c4f034ee0.png', '2026-02-28 10:35:28');

-- --------------------------------------------------------

--
-- Структура таблицы `settings`
--
-- Создание: Фев 28 2026 г., 09:55
-- Последнее обновление: Фев 28 2026 г., 11:24
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` int NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `setting_type` enum('text','color','number','select','image','textarea') DEFAULT 'text',
  `setting_group` varchar(50) DEFAULT 'general',
  `setting_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Дамп данных таблицы `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `setting_group`, `setting_order`, `created_at`) VALUES
(51, 'site_name', 'Shop', 'text', 'general', 1, '2026-02-28 10:51:52'),
(52, 'site_description', 'Интернет-магазин', 'textarea', 'general', 2, '2026-02-28 10:51:52'),
(53, 'site_logo', '', 'image', 'general', 3, '2026-02-28 10:51:52'),
(54, 'favicon', '', 'image', 'general', 4, '2026-02-28 10:51:52'),
(55, 'admin_email', 'admin@shop.ru', 'text', 'general', 5, '2026-02-28 10:51:52'),
(56, 'site_phone', '+7 (999) 123-45-67', 'text', 'general', 6, '2026-02-28 10:51:52'),
(57, 'currency', 'RUB', 'select', 'general', 7, '2026-02-28 10:51:52'),
(58, 'products_per_page', '12', 'number', 'general', 8, '2026-02-28 10:51:52'),
(59, 'enable_discounts', 'true', 'select', 'general', 9, '2026-02-28 10:51:52'),
(60, 'enable_reviews', 'true', 'select', 'general', 10, '2026-02-28 10:51:52'),
(61, 'primary_color', '#4a90e2', 'color', 'colors', 10, '2026-02-28 10:51:52'),
(62, 'primary_light', '#6ba5e8', 'color', 'colors', 11, '2026-02-28 10:51:52'),
(63, 'primary_dark', '#3a7bc8', 'color', 'colors', 12, '2026-02-28 10:51:52'),
(64, 'secondary_color', '#ffffff', 'color', 'colors', 13, '2026-02-28 10:51:52'),
(65, 'text_color', '#2c3e50', 'color', 'colors', 14, '2026-02-28 10:51:52'),
(66, 'text_light', '#7f8c8d', 'color', 'colors', 15, '2026-02-28 10:51:52'),
(67, 'background_color', '#f8fafc', 'color', 'colors', 16, '2026-02-28 10:51:52'),
(68, 'card_background', '#ffffff', 'color', 'colors', 17, '2026-02-28 10:51:52'),
(69, 'success_color', '#38a169', 'color', 'colors', 18, '2026-02-28 10:51:52'),
(70, 'error_color', '#e53e3e', 'color', 'colors', 19, '2026-02-28 10:51:52'),
(71, 'warning_color', '#d69e2e', 'color', 'colors', 20, '2026-02-28 10:51:52'),
(72, 'button_radius', '4%', 'text', 'buttons', 30, '2026-02-28 10:51:52'),
(73, 'button_padding', '12px 24px', 'text', 'buttons', 31, '2026-02-28 10:51:52'),
(74, 'button_font_size', '1rem', 'text', 'buttons', 32, '2026-02-28 10:51:52'),
(75, 'button_font_weight', '600', 'text', 'buttons', 33, '2026-02-28 10:51:52'),
(76, 'button_shadow', '0 4px 6px rgba(0,0,0,0.1)', 'text', 'buttons', 34, '2026-02-28 10:51:52'),
(77, 'button_hover_scale', '1.02', 'text', 'buttons', 35, '2026-02-28 10:51:52'),
(78, 'header_position', 'sticky', 'select', 'layout', 40, '2026-02-28 10:51:52'),
(79, 'header_height', '70px', 'text', 'layout', 41, '2026-02-28 10:51:52'),
(80, 'logo_position', 'left', 'select', 'layout', 42, '2026-02-28 10:51:52'),
(81, 'nav_position', 'right', 'select', 'layout', 43, '2026-02-28 10:51:52'),
(82, 'cart_position', 'right', 'select', 'layout', 44, '2026-02-28 10:51:52'),
(83, 'products_per_row_desktop', '4', 'number', 'layout', 45, '2026-02-28 10:51:52'),
(84, 'products_per_row_tablet', '2', 'number', 'layout', 46, '2026-02-28 10:51:52'),
(85, 'products_per_row_mobile', '1', 'number', 'layout', 47, '2026-02-28 10:51:52'),
(86, 'font_family', 'Inter', 'text', 'fonts', 50, '2026-02-28 10:51:52'),
(87, 'font_size_base', '16px', 'text', 'fonts', 51, '2026-02-28 10:51:52'),
(88, 'heading_font_weight', '700', 'text', 'fonts', 52, '2026-02-28 10:51:52'),
(89, 'floating_button_enabled', 'true', 'select', 'floating', 60, '2026-02-28 10:51:52'),
(90, 'floating_button_animation', 'float', 'select', 'floating', 61, '2026-02-28 10:51:52'),
(91, 'floating_button_bottom', '30px', 'text', 'floating', 62, '2026-02-28 10:51:52'),
(92, 'cart_icon_color', '#3a88fe', 'color', 'cart', 70, '2026-02-28 10:51:52'),
(93, 'cart_count_bg', '#b51a00', 'color', 'cart', 71, '2026-02-28 10:51:52'),
(94, 'cart_count_color', '#ffffff', 'color', 'cart', 72, '2026-02-28 10:51:52'),
(95, 'product_image_height', '200px', 'text', 'products', 80, '2026-02-28 10:51:52'),
(96, 'product_border_radius', '12px', 'text', 'products', 81, '2026-02-28 10:51:52'),
(97, 'product_shadow', '0 4px 6px rgba(0,0,0,0.05)', 'text', 'products', 82, '2026-02-28 10:51:52'),
(98, 'product_shadow_hover', '0 10px 15px rgba(0,0,0,0.1)', 'text', 'products', 83, '2026-02-28 10:51:52'),
(99, 'category_image_height', '150px', 'text', 'categories', 90, '2026-02-28 10:51:52'),
(100, 'category_border_radius', '12px', 'text', 'categories', 91, '2026-02-28 10:51:52'),
(101, 'contact_email', 'info@shop.ru', 'text', 'footer', 102, '2026-02-28 10:58:53'),
(102, 'contact_phone', '+7 (999) 123-45-67', 'text', 'footer', 103, '2026-02-28 10:58:53'),
(103, 'contact_address', 'г. Москва, ул. Примерная, д. 1', 'text', 'footer', 104, '2026-02-28 10:58:53'),
(104, 'copyright', '© 2026 ModernShop. Все права защищены.', 'text', 'footer', 105, '2026-02-28 10:58:53'),
(105, 'social_telegram', '#', 'text', 'footer', 110, '2026-02-28 10:58:53'),
(106, 'social_vk', '#', 'text', 'footer', 111, '2026-02-28 10:58:53'),
(107, 'social_instagram', '#', 'text', 'footer', 112, '2026-02-28 10:58:53'),
(108, 'social_youtube', '#', 'text', 'footer', 113, '2026-02-28 10:58:53'),
(109, 'social_whatsapp', '#', 'text', 'footer', 114, '2026-02-28 10:58:53'),
(110, 'footer_background', '#2c3e50', 'color', 'footer', 120, '2026-02-28 10:58:53'),
(111, 'footer_text_color', '#ffffff', 'color', 'footer', 121, '2026-02-28 10:58:53'),
(112, 'footer_link_color', '#4a90e2', 'color', 'footer', 122, '2026-02-28 10:58:53'),
(113, 'footer_padding', '60px 0 20px', 'text', 'footer', 123, '2026-02-28 10:58:53'),
(114, 'footer_columns', '4', 'number', 'footer', 124, '2026-02-28 10:58:53'),
(115, 'footer_show_map', 'false', 'select', 'footer', 130, '2026-02-28 10:58:53'),
(116, 'footer_map_url', '', 'text', 'footer', 131, '2026-02-28 10:58:53'),
(117, 'footer_show_newsletter', 'true', 'select', 'footer', 132, '2026-02-28 10:58:53');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Индексы таблицы `custom_css`
--
ALTER TABLE `custom_css`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `discounts`
--
ALTER TABLE `discounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Индексы таблицы `discount_codes`
--
ALTER TABLE `discount_codes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Индексы таблицы `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Индексы таблицы `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `custom_css`
--
ALTER TABLE `custom_css`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `discounts`
--
ALTER TABLE `discounts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `discount_codes`
--
ALTER TABLE `discount_codes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
