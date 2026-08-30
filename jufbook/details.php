<?php
declare(strict_types=1);
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = getDB();
$book_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($book_id <= 0) {
    header("Location: index.php");
    exit;
}

if (function_exists('incrementBookView')) {
    incrementBookView($pdo, $book_id);
}

$stmt = $pdo->prepare("
    SELECT b.*, a.name_bn AS author_name 
    FROM books b 
    LEFT JOIN authors a ON b.author_id = a.id 
    WHERE b.id = :id LIMIT 1
");
$stmt->execute(['id' => $book_id]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$book) {
    header("Location: index.php");
    exit;
}

// chapters টেবিল না থাকলেও ক্র্যাশ না করার নিরাপদ মেকানিজম
$chapters = [];
try {
    $chap_stmt = $pdo->prepare("SELECT id, chapter_title_bn, chapter_number FROM chapters WHERE book_id = :bid ORDER BY chapter_number ASC");
    $chap_stmt->execute(['bid' => $book['id']]);
    $chapters = $chap_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $chapters = [];
}

$csrf_token = generateCSRFToken();
$page_title = sanitizeOutput($book['title_bn']) . " — " . sanitizeOutput($book['author_name'] ?? 'অজ্ঞাত') . " | JUFbook";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="margin-top: 40px; margin-bottom: 70px;">
    <div style="display: flex; flex-wrap: wrap; gap: 40px; background: #FFFFFF; padding: 40px; border-radius: 24px; border: 1px solid #ECE5D8;">
        
        <!-- Book Cover -->
        <div style="flex: 0 0 240px; max-width: 260px;">
            <div class="juf-book-jacket" style="background-color: <?= htmlspecialchars($book['theme_color'] ?? '#2B3A42', ENT_QUOTES, 'UTF-8') ?>; height: 320px; padding: 0; overflow: hidden; border-radius: 8px;">
                <?php if (!empty($book['cover_image']) && file_exists(__DIR__ . '/uploads/covers/' . $book['cover_image'])): ?>
                    <img src="uploads/covers/<?= htmlspecialchars($book['cover_image'], ENT_QUOTES, 'UTF-8') ?>" alt="" style="width:100%; height:100%; object-fit:cover;">
                <?php else: ?>
                    <div style="padding: 20px 16px; height: 100%; display: flex; flex-direction: column; justify-content: flex-end;">
                        <span class="juf-jacket-genre"><?= sanitizeOutput($book['genre_tag_bn'] ?? 'উপন্যাস') ?></span>
                        <div class="juf-jacket-title" style="font-size: 22px;"><?= sanitizeOutput($book['title_bn']) ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Details Info -->
        <div style="flex: 1; min-width: 300px;">
            <div class="section-overline-label">— <?= sanitizeOutput($book['genre_tag_bn'] ?? 'উপন্যাস') ?> —</div>
            <h1 class="page-section-title" style="margin: 4px 0 6px; font-size: 34px;"><?= sanitizeOutput($book['title_bn']) ?></h1>
            <h3 style="font-weight: 500; color: var(--text-muted); font-size: 17px; margin-bottom: 16px;">
                লেখক: <a href="authors.php?id=<?= (int)($book['author_id'] ?? 0) ?>" style="color:var(--text-main); text-decoration:underline;"><?= sanitizeOutput($book['author_name'] ?? 'অজ্ঞাত') ?></a>
            </h3>
            
            <p style="font-size: 14.5px; color: #555; line-height: 1.7; margin-bottom: 24px;">
                <?= sanitizeOutput($book['short_desc_bn'] ?? 'বাঙালির অমর সাহিত্য সম্ভারের এক কালজয়ী সৃষ্টি। ক্লাসিক সাহিত্য পাঠের প্রিমিয়াম ডিজিটাল অভিজ্ঞতা উপভোগ করুন।') ?>
            </p>

            <div style="margin-bottom: 24px; display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                <?php if (!empty($book['is_audiobook'])): ?>
                    <span style="background: var(--accent-blue); color: #fff; padding: 5px 16px; border-radius: 999px; font-size: 13px; font-weight: 600;">🔊 অডিওবুক (<?= sanitizeOutput($book['audio_duration_bn'] ?? 'অডিও') ?>)</span>
                <?php endif; ?>
                <span style="color: #777; font-size: 13.5px;">👁️ মোট পাঠ: <?= to_bengali_number((int)($book['views_count'] ?? 0)) ?> বার</span>
            </div>

            <?php if (!empty($chapters)): ?>
                <a href="read.php?book_id=<?= (int)$book['id'] ?>&chapter_id=<?= (int)$chapters[0]['id'] ?>" class="btn-hero-cinematic-primary" style="display: inline-block; padding: 12px 34px; font-size: 15px;">পড়া শুরু করুন</a>
            <?php else: ?>
                <button class="btn-hero-cinematic-secondary" style="background:#ECE5D8; color:#777; border-color:#DDD; cursor:default;">অনলাইন রিডিং মোড প্রস্তুত হচ্ছে</button>
            <?php endif; ?>
        </div>
    </div>

    <!-- সূচিপত্র সেকশন -->
    <div style="margin-top: 45px; max-width: 850px; margin-left: auto; margin-right: auto;">
        <h2 class="page-section-title" style="text-align: center; margin-bottom: 20px;">সূচিপত্র</h2>
        <div style="background: #FFFFFF; border-radius: 16px; border: 1px solid #ECE5D8; overflow: hidden;">
            <ul style="list-style: none; padding: 0; margin: 0;">
                <?php if (empty($chapters)): ?>
                    <li style="padding: 24px; text-align: center; color: #777; font-size: 14px;">বইটির ডিজিটাল অধ্যায়গুলো ডাটাবেসে প্রক্রিয়াকরণ চলছে।</li>
                <?php else: ?>
                    <?php foreach ($chapters as $idx => $chap): ?>
                        <li style="border-bottom: 1px solid #ECE5D8;">
                            <a href="read.php?book_id=<?= (int)$book['id'] ?>&chapter_id=<?= (int)$chap['id'] ?>" style="display: flex; justify-content: space-between; padding: 16px 24px; color: #1C1917; text-decoration: none; font-weight: 500;">
                                <span><strong style="color: var(--accent-rust); margin-right: 8px;">পর্ব <?= to_bengali_number($chap['chapter_number'] ?? ($idx + 1)) ?>:</strong> <?= sanitizeOutput($chap['chapter_title_bn']) ?></span>
                                <span style="color: var(--accent-rust);">পড়ুন →</span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>