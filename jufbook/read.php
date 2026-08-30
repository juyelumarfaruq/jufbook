<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = getDB();

$book_id = isset($_GET['book']) ? (int)$_GET['book'] : (int)($_GET['book_id'] ?? 0);
$chapter_num = isset($_GET['chapter']) ? (int)$_GET['chapter'] : (int)($_GET['chapter_id'] ?? 1);

if ($book_id <= 0) {
    header("Location: index.php");
    exit;
}

// ১. বই ও লেখক ডেটা
$book_stmt = $pdo->prepare("
    SELECT b.*, a.name_bn AS author_name, a.designation_bn AS author_designation 
    FROM books b 
    LEFT JOIN authors a ON b.author_id = a.id 
    WHERE b.id = :id
");
$book_stmt->execute([':id' => $book_id]);
$book = $book_stmt->fetch(PDO::FETCH_ASSOC);

if (!$book) {
    header("Location: index.php");
    exit;
}

// ২. বর্তমান অধ্যায় লোড
$chap_stmt = $pdo->prepare("
    SELECT * FROM chapters 
    WHERE book_id = :book_id AND (chapter_number = :c_num OR id = :c_num2) AND status = 'published' 
    LIMIT 1
");
$chap_stmt->execute([':book_id' => $book_id, ':c_num' => $chapter_num, ':c_num2' => $chapter_num]);
$chapter = $chap_stmt->fetch(PDO::FETCH_ASSOC);

// ৩. নেভিগেশন (Previous / Next)
$all_chaps_stmt = $pdo->prepare("SELECT id, chapter_number, chapter_title_bn FROM chapters WHERE book_id = :book_id AND status = 'published' ORDER BY chapter_number ASC");
$all_chaps_stmt->execute([':book_id' => $book_id]);
$all_chapters = $all_chaps_stmt->fetchAll(PDO::FETCH_ASSOC);

$prev_chap = null;
$next_chap = null;
if ($chapter) {
    foreach ($all_chapters as $idx => $c) {
        if ($c['id'] == $chapter['id']) {
            $prev_chap = $all_chapters[$idx - 1] ?? null;
            $next_chap = $all_chapters[$idx + 1] ?? null;
            break;
        }
    }
}

// ভিউ কাউন্টার বৃদ্ধি
try {
    $pdo->prepare("UPDATE books SET views_count = views_count + 1 WHERE id = :id")->execute([':id' => $book_id]);
} catch (Throwable $e) {}

$page_title = ($chapter ? sanitizeOutput($chapter['chapter_title_bn']) . " — " : "") . sanitizeOutput($book['title_bn']) . " | JUFbook Zen Reader";
require_once __DIR__ . '/includes/header.php';
?>

<style>
    .zen-reader-container { max-width: 740px; margin: 20px auto 60px; padding: 0 15px; }
    .zen-reader-paper { background: #FCFAF5; border: 1px solid #E6DEC9; border-radius: 16px; padding: 50px 45px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); }
    .zen-reader-body { font-family: var(--font-heading); font-size: 19px; line-height: 2.1; color: #23201D; }
    .zen-reader-body p { margin-bottom: 1.5em; text-align: justify; }
    .zen-nav-bar { display: flex; justify-content: space-between; align-items: center; margin-top: 40px; padding-top: 25px; border-top: 1px solid #EAE0CE; }
    .zen-nav-btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 18px; border-radius: 999px; background: #FAF5ED; border: 1px solid #DCD0B9; color: var(--accent-rust); text-decoration: none; font-weight: 600; font-size: 14px; }
    .zen-nav-btn:hover { background: var(--accent-rust); color: #FFF; border-color: var(--accent-rust); }
</style>

<div class="zen-reader-container">
    <div class="zen-reader-paper">
        <div style="text-align: center; margin-bottom: 35px; border-bottom: 1px dashed #DDD2BD; padding-bottom: 25px;">
            <span style="font-size: 13px; color: var(--accent-rust); font-weight: 600; font-family: var(--font-ui);">
                <?= sanitizeOutput($book['title_bn']) ?> • <?= sanitizeOutput($book['author_name'] ?? '') ?>
            </span>
            <h1 style="font-family: var(--font-heading); font-size: 32px; color: #1C1917; margin: 8px 0 0 0;">
                <?= $chapter ? sanitizeOutput($chapter['chapter_title_bn']) : 'কোনো অধ্যায় পাওয়া যায়নি' ?>
            </h1>
        </div>

        <div class="zen-reader-body">
            <?php if ($chapter && !empty($chapter['content'])): ?>
                <?= sanitizeRichText($chapter['content']); ?>
            <?php else: ?>
                <p style="text-align: center; color: #777;">এই বইটির ডিজিটাল কন্টেন্ট বর্তমানে প্রস্তুত হচ্ছে। খুব শীঘ্রই প্রকাশিত হবে।</p>
            <?php endif; ?>
        </div>

        <div class="zen-nav-bar">
            <?php if ($prev_chap): ?>
                <a href="read.php?book=<?= $book_id ?>&chapter=<?= $prev_chap['chapter_number'] ?>" class="zen-nav-btn">← পূর্ববর্তী অধ্যায়</a>
            <?php else: ?>
                <a href="details.php?id=<?= $book_id ?>" class="zen-nav-btn">← সূচিপত্র</a>
            <?php endif; ?>

            <span style="font-size: 13px; color: #888; font-family: var(--font-ui);">
                অধ্যায় <?= $chapter ? (int)$chapter['chapter_number'] : 0 ?> / <?= count($all_chapters) ?>
            </span>

            <?php if ($next_chap): ?>
                <a href="read.php?book=<?= $book_id ?>&chapter=<?= $next_chap['chapter_number'] ?>" class="zen-nav-btn">পরবর্তী অধ্যায় →</a>
            <?php else: ?>
                <a href="details.php?id=<?= $book_id ?>" class="zen-nav-btn">বইয়ের বিবরণ →</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>