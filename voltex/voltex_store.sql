-- ============================================================
--  VOLTEX Electronics — Database Schema
--  Database: voltex_store
-- ============================================================

CREATE DATABASE IF NOT EXISTS voltex_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE voltex_store;

-- Categories
CREATE TABLE IF NOT EXISTS categories (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    slug        VARCHAR(100) NOT NULL UNIQUE,
    icon        VARCHAR(10)  NOT NULL DEFAULT '📦',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products
CREATE TABLE IF NOT EXISTS products (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id   INT UNSIGNED NOT NULL,
    name          VARCHAR(200) NOT NULL,
    slug          VARCHAR(200) NOT NULL UNIQUE,
    description   TEXT,
    price         DECIMAL(10,2) NOT NULL,
    original_price DECIMAL(10,2) DEFAULT NULL,
    stock         INT UNSIGNED NOT NULL DEFAULT 0,
    image_url     VARCHAR(500) DEFAULT NULL,
    badge         VARCHAR(50)  DEFAULT NULL,
    rating        DECIMAL(2,1) DEFAULT 4.5,
    reviews_count INT UNSIGNED DEFAULT 0,
    featured      TINYINT(1)   DEFAULT 0,
    created_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Users
CREATE TABLE IF NOT EXISTS users (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(100) NOT NULL,
    email        VARCHAR(150) NOT NULL UNIQUE,
    password     VARCHAR(255) NOT NULL,
    phone        VARCHAR(20)  DEFAULT NULL,
    address      TEXT         DEFAULT NULL,
    city         VARCHAR(100) DEFAULT NULL,
    created_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);

-- Cart
CREATE TABLE IF NOT EXISTS cart (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    quantity   INT UNSIGNED NOT NULL DEFAULT 1,
    added_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_cart (user_id, product_id),
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Wishlist
CREATE TABLE IF NOT EXISTS wishlist (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    added_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_wish (user_id, product_id),
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Orders
CREATE TABLE IF NOT EXISTS orders (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id        INT UNSIGNED NOT NULL,
    total_amount   DECIMAL(10,2) NOT NULL,
    status         ENUM('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
    payment_method VARCHAR(50)  DEFAULT 'cod',
    name           VARCHAR(100) NOT NULL,
    email          VARCHAR(150) NOT NULL,
    phone          VARCHAR(20)  NOT NULL,
    address        TEXT         NOT NULL,
    city           VARCHAR(100) NOT NULL,
    notes          TEXT         DEFAULT NULL,
    created_at     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Order Items
CREATE TABLE IF NOT EXISTS order_items (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id   INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    name       VARCHAR(200) NOT NULL,
    price      DECIMAL(10,2) NOT NULL,
    quantity   INT UNSIGNED NOT NULL,
    FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Admin
CREATE TABLE IF NOT EXISTS admins (
    id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username  VARCHAR(50)  NOT NULL UNIQUE,
    password  VARCHAR(255) NOT NULL,
    created_at TIMESTAMP   DEFAULT CURRENT_TIMESTAMP
);

-- ── Seed Data ────────────────────────────────────────────────

INSERT INTO categories (name, slug, icon) VALUES
('Smartphones',    'smartphones',    '📱'),
('Laptops',        'laptops',        '💻'),
('Headphones',     'headphones',     '🎧'),
('Smart Watches',  'smart-watches',  '⌚'),
('Gaming',         'gaming',         '🎮'),
('Cameras',        'cameras',        '📷'),
('Tablets',        'tablets',        '📲'),
('Accessories',    'accessories',    '🔌');

INSERT INTO products (category_id, name, slug, description, price, original_price, stock, image_url, badge, rating, reviews_count, featured) VALUES
(1, 'VOLTEX ProMax X1', 'voltex-promax-x1', 'The ultimate flagship smartphone with 200MP camera, 6.8" AMOLED display, and 5000mAh battery. Experience the future today.', 129999, 149999, 25, 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=600', 'Best Seller', 4.8, 1240, 1),
(1, 'NovaSpark Z9 Pro', 'novaspark-z9-pro', 'Slim design meets powerhouse performance. 6.6" display, 108MP camera, and all-day battery life.', 89999, 99999, 40, 'https://images.unsplash.com/photo-1580910051074-3eb694886505?w=600', 'Hot', 4.6, 876, 1),
(1, 'ArcLight Mini 5', 'arclight-mini-5', 'Compact powerhouse. 5.4" Super Retina display, triple camera system, and blazing fast chip.', 74999, NULL, 60, 'https://images.unsplash.com/photo-1592899677977-9c10ca588bbd?w=600', NULL, 4.5, 543, 0),
(2, 'VOLTEX UltraBook Pro 16', 'voltex-ultrabook-pro-16', '16" OLED display, 32GB RAM, 1TB SSD, RTX 4090 GPU. The workstation that goes anywhere.', 299999, 349999, 15, 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=600', 'New', 4.9, 432, 1),
(2, 'NexCore Slim 14', 'nexcore-slim-14', 'Ultra-thin 14" laptop. 12-hour battery, 16GB RAM, perfect for professionals on the move.', 149999, 169999, 30, 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=600', 'Popular', 4.7, 654, 1),
(2, 'CipherBook Air', 'cipherbook-air', 'Featherlight 13" ultrabook. All-day battery, stunning display, and whisper-quiet operation.', 109999, NULL, 45, 'https://images.unsplash.com/photo-1541807084-5c52b6b3adef?w=600', NULL, 4.5, 321, 0),
(3, 'SonicPulse X Pro', 'sonicpulse-x-pro', 'Industry-leading noise cancellation, 40-hour battery, Hi-Res Audio certified. Pure sound perfection.', 34999, 39999, 80, 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600', 'Top Rated', 4.9, 2100, 1),
(3, 'AuraBeats Studio', 'aurabeats-studio', 'Over-ear studio headphones with spatial audio and premium leather cushions.', 24999, 29999, 100, 'https://images.unsplash.com/photo-1484704849700-f032a568e944?w=600', NULL, 4.7, 987, 0),
(3, 'PulseGo Wireless', 'pulsego-wireless', 'True wireless earbuds with 8hr battery + 24hr case. IPX5 water resistant.', 12999, 14999, 150, 'https://images.unsplash.com/photo-1572569511254-d8f925fe2cbb?w=600', 'Sale', 4.5, 1543, 0),
(4, 'VOLTEX Watch Ultra', 'voltex-watch-ultra', '49mm titanium case, always-on display, advanced health monitoring, 60-hour battery.', 69999, 79999, 35, 'https://images.unsplash.com/photo-1546868871-7041f2a55e12?w=600', 'Premium', 4.8, 765, 1),
(4, 'FitPulse Pro 5', 'fitpulse-pro-5', 'AMOLED display, SpO2 sensor, 100+ workout modes, 14-day battery life.', 29999, 34999, 70, 'https://images.unsplash.com/photo-1579586337278-3befd40fd17a?w=600', 'New', 4.6, 432, 1),
(5, 'HyperX Nexus Controller', 'hyperx-nexus-controller', 'Pro gaming controller with haptic feedback, adaptive triggers, and 40-hour battery.', 19999, 22999, 90, 'https://images.unsplash.com/photo-1592840496694-26d035b52b48?w=600', 'Gaming', 4.8, 1876, 1),
(5, 'VOLTEX GamePad Elite', 'voltex-gamepad-elite', 'Hall-effect joysticks, RGB lighting, multi-platform compatible gaming controller.', 14999, NULL, 120, 'https://images.unsplash.com/photo-1486572788966-cfd3df1f5b42?w=600', NULL, 4.6, 654, 0),
(6, 'LensMax Alpha 9', 'lensmax-alpha-9', '50MP full-frame mirrorless camera. 8K video, 30fps continuous shooting, weather sealed.', 349999, 399999, 10, 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=600', 'Pro', 4.9, 234, 1),
(7, 'VOLTEX Tab Pro 12', 'voltex-tab-pro-12', '12.9" Liquid Retina display, M2 chip, 5G connectivity. The tablet that replaces your laptop.', 189999, 209999, 20, 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=600', 'Featured', 4.8, 543, 1),
(8, 'MagCharge Pad 15W', 'magcharge-pad-15w', 'Magnetic wireless charger. 15W fast charging, compatible with all Qi devices.', 4999, 5999, 200, 'https://images.unsplash.com/photo-1583394838336-acd977736f90?w=600', NULL, 4.4, 876, 0);

-- Default admin (password: admin123)
INSERT INTO admins (username, password) VALUES
('admin', '$2y$10$FrStkEjfGuWf2NpWtfcod.qD/uPqkHdQWDlt5B.RVY8wv/p.aJiQi');
