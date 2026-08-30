CREATE DATABASE IF NOT EXISTS `jufbook_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `jufbook_db`;

-- ১. অ্যাডমিন ও ইউজার টেবিল
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('super_admin', 'admin', 'editor', 'user') DEFAULT 'user',
  `status` ENUM('active', 'suspended', 'pending') DEFAULT 'active',
  `failed_logins` INT DEFAULT 0,
  `lockout_until` DATETIME NULL,
  `last_login_at` DATETIME NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ২. ক্যাটাগরি টেবিল
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name_bn` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `description_bn` TEXT NULL,
  `icon` VARCHAR(50) DEFAULT 'book',
  `theme_color` VARCHAR(20) DEFAULT '#243447',
  `sort_order` INT DEFAULT 0,
  `status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ৩. লেখক টেবিল
CREATE TABLE IF NOT EXISTS `authors` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name_bn` VARCHAR(150) NOT NULL,
  `designation_bn` VARCHAR(100) DEFAULT 'সাহিত্যিক',
  `biography_bn` TEXT NULL,
  `birth_date` VARCHAR(50) NULL,
  `birth_place_bn` VARCHAR(100) NULL,
  `death_date` VARCHAR(50) NULL,
  `literary_era_bn` VARCHAR(100) NULL,
  `avatar_url` VARCHAR(255) NULL,
  `status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ৪. বই টেবিল
CREATE TABLE IF NOT EXISTS `books` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title_bn` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(200) NULL,
  `author_id` INT NOT NULL,
  `category_id` INT NOT NULL,
  `genre_tag_bn` VARCHAR(50) DEFAULT 'উপন্যাস',
  `short_desc_bn` TEXT NULL,
  `theme_color` VARCHAR(20) DEFAULT '#2B3D4F',
  `cover_image` VARCHAR(255) NULL,
  `font_family` VARCHAR(50) DEFAULT 'font-tiro',
  `is_audiobook` TINYINT(1) DEFAULT 0,
  `audio_duration_bn` VARCHAR(50) DEFAULT '৪৫ মি.',
  `is_new_release` TINYINT(1) DEFAULT 0,
  `is_featured` TINYINT(1) DEFAULT 0,
  `views_count` INT UNSIGNED DEFAULT 0,
  `status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`author_id`) REFERENCES `authors`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE RESTRICT,
  INDEX `idx_books_author` (`author_id`),
  INDEX `idx_books_category` (`category_id`),
  INDEX `idx_books_views` (`views_count`)
) ENGINE=InnoDB;

-- ৫. অধ্যায় টেবিল (Canonical naming: chapter_title_bn)
CREATE TABLE IF NOT EXISTS `chapters` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `book_id` INT NOT NULL,
  `chapter_number` INT NOT NULL,
  `chapter_title_bn` VARCHAR(200) NOT NULL,
  `content` LONGTEXT NOT NULL,
  `audio_file_url` VARCHAR(255) NULL,
  `status` ENUM('published', 'draft') DEFAULT 'published',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`book_id`) REFERENCES `books`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_book_chapter` (`book_id`, `chapter_number`),
  INDEX `idx_chapters_book` (`book_id`)
) ENGINE=InnoDB;

-- ৬. বুক রিভিউ টেবিল (সঠিক বুক রিলেশন ও মডারেশন)
CREATE TABLE IF NOT EXISTS `testimonials` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `book_id` INT NULL,
  `user_name_bn` VARCHAR(100) NOT NULL,
  `user_location_bn` VARCHAR(100) DEFAULT 'পাঠক',
  `review_bn` TEXT NOT NULL,
  `rating` TINYINT UNSIGNED DEFAULT 5,
  `is_active` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`book_id`) REFERENCES `books`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ৭. ভিজিটর ও সাইট অ্যানালিটিক্স
CREATE TABLE IF NOT EXISTS `site_analytics` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` TEXT NULL,
  `visit_date` DATE NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_visit_date` (`visit_date`)
) ENGINE=InnoDB;