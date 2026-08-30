<?php
declare(strict_types=1);
require_once __DIR__ . '/security.php';

class GraphicEngine {

    public static function getFirstBengaliChar(string $name): string {
        $name = trim($name);
        if (empty($name)) return 'অ';
        return mb_substr($name, 0, 1, 'UTF-8');
    }

    public static function renderBookCover(string $title, string $author, string $genre = 'উপন্যাস', ?string $color = '#1A2930'): string {
        $safeColor = htmlspecialchars($color ?? '#1A2930', ENT_QUOTES, 'UTF-8');
        $safeTitle = sanitizeOutput($title);
        $safeAuthor = sanitizeOutput($author);
        $safeGenre = sanitizeOutput($genre);

        return '
        <div class="juf-book-jacket" style="background: ' . $safeColor . ';">
            <div class="juf-jacket-spine"></div>
            <span class="juf-jacket-genre">' . $safeGenre . '</span>
            <div class="juf-jacket-title">' . $safeTitle . '</div>
        </div>';
    }

    public static function renderAuthorAvatar(string $name, ?string $photo = null): string {
        $char = self::getFirstBengaliChar($name);
        $palette = ['#2B3D4F', '#4A2E2B', '#1F3F35', '#4A3B32', '#34253A', '#1E3A4C'];
        $bg = $palette[abs(crc32($name)) % count($palette)];

        return '
        <div class="author-circle-disc" style="background: ' . $bg . ';">
            <span class="author-initial-char">' . htmlspecialchars($char, ENT_QUOTES, 'UTF-8') . '</span>
        </div>';
    }
}

function generate_dynamic_cover(string $title, string $author, string $genre = 'উপন্যাস', ?string $color = '#1A2930'): string {
    return GraphicEngine::renderBookCover($title, $author, $genre, $color);
}

function get_avatar_initial(string $name): string {
    return GraphicEngine::renderAuthorAvatar($name);
}

function to_bengali_number(int|string $number): string {
    $eng = ['0','1','2','3','4','5','6','7','8','9'];
    $ben = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
    return str_replace($eng, $ben, (string)$number);
/**
 * ওয়েবসাইট পেজ ভিউ এবং ভিজিটর ট্র্যাক করার ফাংশন
 */
function trackSiteVisit(PDO $pdo): void {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $url = $_SERVER['REQUEST_URI'] ?? '/';
    $agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $today = date('Y-m-d');

    // একই IP থেকে প্রতি পেজে দিনে একবারের বেশি স্প্যাম এন্ট্রি বন্ধ রাখা
    try {
        $stmt = $pdo->prepare("
            INSERT INTO site_analytics (page_url, ip_address, user_agent, visit_date)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$url, $ip, $agent, $today]);
    } catch (Throwable $e) {
        // সাইলেন্ট ফেইল যাতে মূল সাইটে কোনো বিঘ্ন না ঘটে
    }
}

/**
 * পাঠক যখন কোনো নির্দিষ্ট বই পড়েন তখন ভিউ বাড়ানোর ফাংশন
 */
function incrementBookView(PDO $pdo, int $bookId): void {
    try {
        $stmt = $pdo->prepare("UPDATE books SET views_count = views_count + 1 WHERE id = ?");
        $stmt->execute([$bookId]);
    } catch (Throwable $e) {
        // Ignore error
    }
}
}