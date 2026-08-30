<?php
declare(strict_types=1);
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = getDB();

$search_query = isset($_GET['q']) ? trim(sanitizeInput($_GET['q'])) : '';
$genre_filter = isset($_GET['genre']) ? trim(sanitizeInput($_GET['genre'])) : '';
$type_filter  = isset($_GET['type']) ? trim(sanitizeInput($_GET['type'])) : '';

$sql = "SELECT b.*, a.name_bn AS author_name 
        FROM books b 
        LEFT JOIN authors a ON b.author_id = a.id 
        WHERE 1=1 ";
$params = [];

if (!empty($search_query)) {
    $sql .= " AND (b.title_bn LIKE :q1 OR a.name_bn LIKE :q2 OR b.genre_tag_bn LIKE :q3) ";
    $params['q1'] = '%' . $search_query . '%';
    $params['q2'] = '%' . $search_query . '%';
    $params['q3'] = '%' . $search_query . '%';
}
if (!empty($genre_filter)) {
    $sql .= " AND b.genre_tag_bn LIKE :genre ";
    $params['genre'] = '%' . $genre_filter . '%';
}
if ($type_filter === 'audio') {
    $sql .= " AND b.is_audiobook = 1 ";
}

$sql .= " ORDER BY b.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = (!empty($search_query) ? sanitizeOutput($search_query) . " — " : "") . "বই খুঁজুন | JUFbook";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="min-height: 65vh; margin-top: 35px; margin-bottom: 60px;">
    
    <div style="max-width: 680px; margin: 0 auto 35px; text-align: center;">
        <h1 class="page-section-title" style="margin-bottom: 15px;">Search Library</h1>
        <form method="GET" action="search.php" class="search-input-box" style="width: 100%; margin: 0 auto; box-sizing: border-box;">
            <span>🔍</span>
            <input type="text" name="q" value="<?= sanitizeOutput($search_query) ?>" placeholder="বই, লেখক বা জনরা খুঁজুন..." style="font-size: 15px;">
        </form>
    </div>

    <?php if (!empty($search_query) || !empty($genre_filter) || !empty($type_filter)): ?>
        <div style="font-size: 15px; color: var(--text-muted); margin-bottom: 20px; font-weight: 500;">
            মোট <strong style="color: var(--accent-rust);"><?= to_bengali_number(count($books)) ?></strong> টি বই পাওয়া গেছে
        </div>
    <?php endif; ?>

    <?php if (empty($books)): ?>
        <div style="background: #FFFFFF; padding: 45px; border-radius: 16px; text-align: center; border: 1px solid #ECE5D8;">
            <p style="color: var(--text-muted); font-size: 15px;">দুঃখিত, কোনো ফলাফল পাওয়া যায়নি। অন্য কিছু দিয়ে অনুসন্ধান করুন।</p>
        </div>
    <?php else: ?>
        <div class="grid-5-col">
            <?php foreach ($books as $b): ?>
                <a href="details.php?id=<?= (int)$b['id'] ?>" class="showcase-card-item anim-card">
                    <div class="juf-book-jacket" style="background-color: <?= htmlspecialchars($b['theme_color'] ?? '#2B3A42', ENT_QUOTES, 'UTF-8') ?>; padding: 0; overflow: hidden;">
                        <?php if (!empty($b['cover_image']) && file_exists(__DIR__ . '/uploads/covers/' . $b['cover_image'])): ?>
                            <img src="uploads/covers/<?= htmlspecialchars($b['cover_image'], ENT_QUOTES, 'UTF-8') ?>" alt="" style="width:100%; height:100%; object-fit:cover;">
                        <?php else: ?>
                            <div style="padding: 16px 14px; height: 100%; display: flex; flex-direction: column; justify-content: flex-end;">
                                <span class="juf-jacket-genre"><?= sanitizeOutput($b['genre_tag_bn'] ?? 'উপন্যাস') ?></span>
                                <div class="juf-jacket-title"><?= sanitizeOutput($b['title_bn']) ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="book-under-meta">
                        <div class="book-under-title"><?= sanitizeOutput($b['title_bn']) ?></div>
                        <div class="book-under-author"><?= sanitizeOutput($b['author_name'] ?? 'অজ্ঞাত') ?></div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>