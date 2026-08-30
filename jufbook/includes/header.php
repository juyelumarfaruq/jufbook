<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/functions.php';

$pdo_head = getDB();
$site_settings = [];
try {
    $site_settings = $pdo_head->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (Throwable $e) {
    $site_settings = [];
}

$meta_title   = $site_settings['meta_title'] ?? "JUFbook - বাংলা ই-বুক ও অডিওবুক";
$meta_desc    = $site_settings['meta_description'] ?? "বাঙালির অমর সাহিত্য সম্ভারের প্রিমিয়াম ডিজিটাল ই-বুক ও অডিওবুক সংগ্রহ।";
$page_title   = $page_title ?? $meta_title;

// ডাইনামিক ফন্ট ভ্যারিয়েবলস (সেটিংস প্যানেল থেকে প্রাপ্ত)
$dyn_font_heading = $site_settings['font_heading'] ?? 'Noto Serif Bengali';
$dyn_font_body    = $site_settings['font_body'] ?? 'Hind Siliguri';
$dyn_font_reader  = $site_settings['font_reader'] ?? 'Tiro Bangla';
$dyn_font_english = $site_settings['font_english'] ?? 'Cinzel';
$dyn_reader_size  = $site_settings['reader_font_size'] ?? '18';
$header_scripts   = $site_settings['header_scripts'] ?? '';
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitizeOutput($page_title) ?></title>
    <meta name="description" content="<?= sanitizeOutput($meta_desc) ?>">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Alkatra&family=Anek+Bangla:wght@400;600;700&family=Atma:wght@500;700&family=Baloo+Da+2:wght@500;700&family=Cinzel:wght@600;700&family=Galada&family=Hind+Siliguri:wght@400;500;600;700&family=Inter:wght@400;600;700&family=Lora:ital,wght@0,600;1,400&family=Merriweather:wght@400;700&family=Mina:wght@400;700&family=Montserrat:wght@500;700&family=Noto+Sans+Bengali:wght@400;600&family=Noto+Serif+Bengali:wght@600;700&family=Outfit:wght@500;600;700&family=Playfair+Display:wght@600;700&family=Plus+Jakarta+Sans:wght@500;600;700&family=Tiro+Bangla:ital@0;1&display=swap">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css?v=<?= time(); ?>">
    
    <style>
        /* ডাইনামিকালি সেটিংস থেকে ফন্ট ইঞ্জেকশন */
        :root {
            --font-heading: '<?= $dyn_font_heading ?>', serif;
            --font-body: '<?= $dyn_font_body ?>', sans-serif;
            --font-reader: '<?= $dyn_font_reader ?>', serif;
            --font-english: '<?= $dyn_font_english ?>', sans-serif;
            --reader-fs: <?= (int)$dyn_reader_size ?>px;
        }

        body {
            font-family: var(--font-body);
        }

        h1, h2, h3, h4, .book-title-heading {
            font-family: var(--font-heading);
        }

        #reader-content-body {
            font-family: var(--font-reader) !important;
            font-size: var(--reader-fs) !important;
        }

        .juf-header-modern {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: rgba(248, 245, 238, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(222, 214, 199, 0.6);
            padding: 10px 0;
            margin-bottom: 25px;
        }
        .juf-header-wrap {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .juf-brand-logo-wrap {
            display: inline-flex;
            align-items: center;
            text-decoration: none;
            height: 56px;
        }
        .juf-site-logo {
            height: 56px;
            width: auto;
            object-fit: contain;
            display: block;
        }
        .juf-nav {
            display: flex;
            align-items: center;
            gap: 22px;
        }
        .juf-nav a {
            color: var(--text-main);
            text-decoration: none;
            font-size: 15px;
            font-weight: 500;
            transition: color 0.2s ease;
        }
        .juf-nav a:hover {
            color: var(--accent-rust);
        }
        .juf-auth-btn {
            background: var(--accent-rust) !important;
            color: #fff !important;
            padding: 8px 22px;
            border-radius: 999px;
            font-weight: 600 !important;
            box-shadow: 0 4px 12px rgba(186, 78, 44, 0.22);
        }
    </style>

    <?php if (!empty($header_scripts)): ?>
        <?= $header_scripts ?>
    <?php endif; ?>
</head>
<body>

    <header class="juf-header-modern">
        <div class="container juf-header-wrap">
            <a href="<?= BASE_URL ?>/index.php" class="juf-brand-logo-wrap" aria-label="JUFbook Home">
                <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="JUF Book" class="juf-site-logo">
            </a>
            <nav class="juf-nav">
                <a href="<?= BASE_URL ?>/index.php">হোমপেজ</a>
                <a href="<?= BASE_URL ?>/search.php">বই খুঁজুন</a>
                <a href="<?= BASE_URL ?>/authors.php">লেখকবৃন্দ</a>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="<?= BASE_URL ?>/profile.php">প্রোফাইল</a>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <a href="<?= BASE_URL ?>/admin/index.php" style="color: var(--accent-rust); font-weight:700;">অ্যাডমিন</a>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>/logout.php" class="juf-auth-btn" style="background:#4A2E2B !important;">লগআউট</a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/login.php" class="juf-auth-btn">লগইন করুন</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="site-main container">