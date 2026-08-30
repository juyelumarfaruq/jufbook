<?php
declare(strict_types=1);

// ------------------------------------------------------------------
// ডাটাবেস ক্রেডেনশিয়াল লোডিং
// প্রোডাকশনে config/db_credentials.php ফাইলে আসল (root নয়, লিমিটেড-প্রিভিলেজ)
// ইউজার/পাসওয়ার্ড রাখুন। ফাইলটি ভার্সন কন্ট্রোলের বাইরে (.gitignore) ও
// ওয়েব-অ্যাক্সেসের বাইরে (.htaccess) থাকবে। না থাকলে নিচের লোকাল ডিফল্ট
// (ডেভেলপমেন্ট) ব্যবহৃত হবে। নমুনার জন্য: config/db_credentials.sample.php
// ------------------------------------------------------------------
$__db_cred_file = __DIR__ . '/db_credentials.php';
if (is_readable($__db_cred_file)) {
    require $__db_cred_file;
}

if (!defined('DB_HOST'))    define('DB_HOST', 'localhost');
if (!defined('DB_NAME'))    define('DB_NAME', 'jufbook_db');
if (!defined('DB_USER'))    define('DB_USER', 'root');   // ⚠ শুধু লোকাল ডেভ; প্রোডাকশনে ওভাররাইড করুন
if (!defined('DB_PASS'))    define('DB_PASS', '');       // ⚠ শুধু লোকাল ডেভ; প্রোডাকশনে ওভাররাইড করুন
if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("DB Connection Error: " . $e->getMessage());
            die("Database service temporarily unavailable.");
        }
    }
    return $pdo;
}