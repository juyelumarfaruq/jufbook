-- Core Configuration
CREATE DATABASE juf_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE juf_platform;

CREATE TABLE settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value BOOLEAN DEFAULT 0
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('reader', 'admin') DEFAULT 'reader',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    cover_color VARCHAR(50) NOT NULL -- Hex code for dynamic covers
);

CREATE TABLE authors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    profession VARCHAR(100),
    bio TEXT,
    avatar_initial CHAR(1)
);

CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    category_id INT NOT NULL,
    author_id INT NOT NULL,
    description TEXT,
    is_premium BOOLEAN DEFAULT 0,
    price DECIMAL(10,2) DEFAULT 0.00,
    has_audio BOOLEAN DEFAULT 0,
    status ENUM('published', 'draft') DEFAULT 'published',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE chapters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    chapter_title VARCHAR(255) NOT NULL,
    chapter_number INT NOT NULL,
    content LONGTEXT NOT NULL,
    audio_file_url VARCHAR(255) DEFAULT NULL,
    status ENUM('published', 'draft') DEFAULT 'published'
);

CREATE TABLE ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    user_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK(rating >= 1 AND rating <= 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY(book_id, user_id)
);

CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    user_id INT NOT NULL,
    review_text TEXT NOT NULL,
    status ENUM('approved', 'pending') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE purchases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    payment_status VARCHAR(50),
    transaction_id VARCHAR(100),
    purchased_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE reading_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    last_chapter_id INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY(user_id, book_id)
);