USE `jufbook_db`;

-- কলাম আপডেট (যদি আগের টেবিলে না থাকে)
ALTER TABLE `books` 
ADD COLUMN IF NOT EXISTS `is_audiobook` TINYINT(1) DEFAULT 0,
ADD COLUMN IF NOT EXISTS `audio_duration_bn` VARCHAR(50) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `audio_file_url` VARCHAR(255) DEFAULT NULL;

ALTER TABLE `authors` 
ADD COLUMN IF NOT EXISTS `is_indic` TINYINT(1) DEFAULT 0,
ADD COLUMN IF NOT EXISTS `is_epic` TINYINT(1) DEFAULT 0;

-- হিরো স্লাইডার ব্যানার টেবিল
CREATE TABLE IF NOT EXISTS `hero_banners` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `badge_text_bn` VARCHAR(100) NOT NULL,
    `title_bn` VARCHAR(255) NOT NULL,
    `desc_bn` TEXT NOT NULL,
    `btn_text_bn` VARCHAR(100) NOT NULL,
    `btn_url` VARCHAR(255) NOT NULL,
    `bg_gradient` VARCHAR(255) NOT NULL,
    `book_title_bn` VARCHAR(255) NOT NULL,
    `author_name_bn` VARCHAR(255) NOT NULL,
    `genre_tag_bn` VARCHAR(100) NOT NULL,
    `theme_color` VARCHAR(50) NOT NULL,
    `sort_order` INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- হিরো ব্যানার ডেটা
INSERT INTO `hero_banners` (`id`, `badge_text_bn`, `title_bn`, `desc_bn`, `btn_text_bn`, `btn_url`, `bg_gradient`, `book_title_bn`, `author_name_bn`, `genre_tag_bn`, `theme_color`, `sort_order`) VALUES
(1, '— বিশেষ সংকলন —', 'বাঙালির অমর সাহিত্য সম্ভার', 'হাজারো কালজয়ী উপন্যাস, কবিতা ও অডিওবুক পড়ুন সম্পূর্ণ প্রিমিয়াম ও বিজ্ঞাপনহীন পরিবেশে।', 'পড়তে শুরু করুন', '#books-showcase-section', 'linear-gradient(135deg, #2B231D 0%, #171310 100%)', 'গীতাঞ্জলি', 'রবীন্দ্রনাথ ঠাকুর', 'কাব্যগ্রন্থ', '#8C3820', 1),
(2, '— অডিও লাইব্রেরি —', 'কানের ভেতর সাহিত্যের সুর', 'সেরা বাচিকশিল্পীদের কণ্ঠে শুনুন বাংলা সাহিত্যের কালজয়ী অডিওবুক ও রোমাঞ্চকর গল্প।', 'অডিওবুক শুনুন', '#audiobooks-section', 'linear-gradient(135deg, #1A2930 0%, #0D161A 100%)', 'পথের পাঁচালী', 'বিভূতিভূষণ বন্দ্যোপাধ্যায়', 'অডিওবুক', '#1F3F35', 2)
ON DUPLICATE KEY UPDATE `title_bn` = VALUES(`title_bn`);

-- লেখক তালিকা আপডেট
INSERT INTO `authors` (`id`, `name_bn`, `designation_bn`, `is_top_author`, `is_indic`, `is_epic`) VALUES
(1, 'রবীন্দ্রনাথ ঠাকুর', 'বিশ্বকবি ও নোবেল বিজয়ী', 1, 1, 1),
(2, 'কাজী নজরুল ইসলাম', 'জাতীয় কবি ও বিদ্রোহী সাহিত্যিক', 1, 1, 1),
(3, 'শরৎচন্দ্র চট্টোপাধ্যায়', 'অপরাজেয় কথাশিল্পী', 1, 1, 0),
(4, 'বিভূতিভূষণ বন্দ্যোপাধ্যায়', 'প্রকৃতির কথাকার', 1, 1, 0),
(5, 'হুমায়ূন আহমেদ', 'জনপ্রিয় কথাসাহিত্যিক', 1, 0, 0),
(6, 'সুনীল গঙ্গোপাধ্যায়', 'আধুনিক কবি ও ঔপন্যাসিক', 1, 0, 1),
(7, 'মানিক বন্দ্যোপাধ্যায়', 'বাস্তববাদী কথাসাহিত্যিক', 0, 1, 0),
(8, 'তারাশঙ্কর বন্দ্যোপাধ্যায়', 'রাঢ় অঞ্চলের সাহিত্যসাধক', 0, 1, 1),
(9, 'জীবনানন্দ দাশ', 'রূপসী বাংলার কবি', 1, 1, 0),
(10, 'মাইকেল মধুসূদন দত্ত', 'মহাকবি ও নাট্যকার', 1, 1, 1)
ON DUPLICATE KEY UPDATE `name_bn` = VALUES(`name_bn`), `is_indic` = VALUES(`is_indic`), `is_epic` = VALUES(`is_epic`);

-- বইয়ের তালিকা ও অডিওবুক ডেটা
INSERT INTO `books` (`id`, `title_bn`, `author_id`, `category_id`, `genre_tag_bn`, `rating`, `theme_color`, `is_new_release`, `is_audiobook`, `audio_duration_bn`) VALUES
(1, 'গীতাঞ্জলি', 1, 2, 'কাব্যগ্রন্থ', 5.0, '#8C3820', 1, 1, '৪ ঘণ্টা ৩০ মি.'),
(2, 'গোরা', 1, 1, 'উপন্যাস', 4.9, '#1A2930', 1, 0, NULL),
(3, 'চোখের বালি', 1, 1, 'সামাজিক উপন্যাস', 4.8, '#402E32', 1, 1, '৬ ঘণ্টা ১৫ মি.'),
(4, 'অগ্নিবীণা', 2, 2, 'কাব্যসংকলন', 5.0, '#8C3820', 1, 0, NULL),
(5, 'মৃত্যুক্ষুধা', 2, 1, 'উপন্যাস', 4.7, '#223843', 1, 0, NULL),
(6, 'দেবদাস', 3, 1, 'রোমান্টিক ক্লাসিক', 4.9, '#5C3D2E', 0, 1, '৩ ঘণ্টা ৪৫ মি.'),
(7, 'শ্রীকান্ত', 3, 1, 'ভ্রমণ-উপন্যাস', 5.0, '#1F3F35', 0, 0, NULL),
(8, 'পথের পাঁচালী', 4, 1, 'কালজয়ী ক্লাসিক', 5.0, '#1F3F35', 0, 1, '৮ ঘণ্টা ২০ মি.'),
(9, 'অপরাজিত', 4, 1, 'জীবনমুখী উপন্যাস', 4.9, '#223843', 0, 0, NULL),
(10, 'শঙ্খনীল কারাগার', 5, 1, 'পারিবারিক ড্রামা', 4.8, '#3D2B56', 0, 0, NULL),
(11, 'নন্দিত নরকে', 5, 1, 'মনস্তাত্ত্বিক', 4.8, '#402E32', 0, 1, '২ ঘণ্টা ৫০ মি.'),
(12, 'প্রথম আলো', 6, 3, 'ঐতিহাসিক', 4.9, '#1A2930', 0, 0, NULL),
(13, 'সেই সময়', 6, 3, 'ইতিহাস-ভিত্তিক', 5.0, '#8C3820', 0, 1, '১১ ঘণ্টা ১০ মি.'),
(14, 'পদ্মা নদীর মাঝি', 7, 1, 'জীবনবাদী', 4.8, '#223843', 0, 0, NULL),
(15, 'গণদেবতা', 8, 1, 'মহাকাব্যিক', 4.9, '#5C3D2E', 0, 0, NULL),
(16, 'বনলতা সেন', 9, 2, 'আধুনিক কবিতা', 5.0, '#1F3F35', 1, 0, NULL),
(17, 'মেঘনাদবধ কাব্য', 10, 2, 'মহাকাব্য', 4.9, '#402E32', 1, 1, '৫ ঘণ্টা ৪০ মি.'),
(18, 'চরিত্রহীন', 3, 1, 'সামাজিক ড্রামা', 4.7, '#223843', 0, 0, NULL),
(19, 'আরণ্যক', 4, 1, 'প্রকৃতি ক্লাসিক', 5.0, '#1F3F35', 0, 1, '৭ ঘণ্টা ১৫ মি.'),
(20, 'কড়ি ও কোমল', 1, 2, 'কাব্যসংকলন', 4.8, '#8C3820', 0, 0, NULL)
ON DUPLICATE KEY UPDATE `title_bn` = VALUES(`title_bn`), `is_audiobook` = VALUES(`is_audiobook`), `audio_duration_bn` = VALUES(`audio_duration_bn`);