<?php
declare(strict_types=1);

/**
 * JUFbook — ডাটাবেস ক্রেডেনশিয়াল টেমপ্লেট।
 *
 * ব্যবহার:
 *   1. এই ফাইলটি কপি করে নাম দিন: config/db_credentials.php
 *   2. প্রোডাকশনের আসল মান বসান (root নয় — শুধু এই একটি ডাটাবেসে
 *      SELECT/INSERT/UPDATE/DELETE অনুমতিসহ একটি সীমিত ইউজার তৈরি করুন)।
 *   3. db_credentials.php কখনো Git-এ কমিট করবেন না (.gitignore-এ যুক্ত আছে)
 *      এবং ওয়েব থেকে ডাউনলোডযোগ্য নয় তা .htaccess নিশ্চিত করে।
 *
 * এই ফাইলে ডিফাইন করা মানগুলো config/database.php-এর ডিফল্টকে ওভাররাইড করবে।
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'your_db_name');
define('DB_USER', 'your_limited_user');   // root ব্যবহার করবেন না
define('DB_PASS', 'your_strong_password');
define('DB_CHARSET', 'utf8mb4');
