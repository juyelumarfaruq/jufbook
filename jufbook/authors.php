<?php
declare(strict_types=1);
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = getDB();
$author_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// =========================================================================
// মোড ১: নির্দিষ্ট কোনো লেখকের একক প্রোফাইল ভিউ (?id=...)
// =========================================================================
if ($author_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM authors WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $author_id]);
    $author = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($author) {
        $bStmt = $pdo->prepare("SELECT * FROM books WHERE author_id = :aid ORDER BY id DESC");
        $bStmt->execute(['aid' => $author_id]);
        $books = $bStmt->fetchAll(PDO::FETCH_ASSOC);

        $page_title = sanitizeOutput($author['name_bn']) . " — প্রোফাইল ও গ্রন্থতালিকা | JUFbook";
        require_once __DIR__ . '/includes/header.php';
        ?>
        <div class="container" style="min-height: 65vh; margin-top: 35px; margin-bottom: 60px;">
            <div style="background: #FFFFFF; border-radius: 20px; padding: 35px; border: 1px solid #ECE5D8; display: flex; gap: 30px; align-items: center; flex-wrap: wrap; margin-bottom: 40px;">
                <div style="width: 120px; height: 140px; border-radius: 50% / 40%; overflow: hidden; background: #2B3D4F; border: 3px solid #DED6C7; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                    <?php if (!empty($author['photo']) && file_exists(__DIR__ . '/uploads/authors/' . $author['photo'])): ?>
                        <img src="uploads/authors/<?= htmlspecialchars($author['photo'], ENT_QUOTES, 'UTF-8') ?>" alt="" style="width:100%; height:100%; object-fit:cover;">
                    <?php else: ?>
                        <span style="color:#fff; font-size:38px; font-weight:700;"><?= mb_substr($author['name_bn'], 0, 1, 'UTF-8') ?></span>
                    <?php endif; ?>
                </div>
                <div style="flex: 1; min-width: 280px;">
                    <div class="section-overline-label">— সাহিত্যিক প্রোফাইল —</div>
                    <h1 class="page-section-title" style="margin: 4px 0 6px;"><?= sanitizeOutput($author['name_bn']) ?></h1>
                    <p style="color: var(--accent-rust); font-weight: 600; font-size: 14px; margin-bottom: 10px;"><?= sanitizeOutput($author['designation_bn'] ?? 'লেখক ও সাহিত্যিক') ?></p>
                    <p style="color: #555; font-size: 14px; line-height: 1.68;">
                        <?= sanitizeOutput($author['bio_bn'] ?? 'বাঙালির অমর সাহিত্য সম্ভারে অনন্য অবদানের জন্য চিরস্মরণীয় কথাশিল্পী।') ?>
                    </p>
                </div>
            </div>

            <h2 class="page-section-title" style="margin-bottom: 20px; font-size: 24px;">প্রকাশিত গ্রন্থাবলী</h2>
            
            <?php if (empty($books)): ?>
                <p style="color: var(--text-muted);">এই লেখকের কোনো বই এখনো যুক্ত করা হয়নি।</p>
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
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        require_once __DIR__ . '/includes/footer.php';
        exit;
    }
}

// =========================================================================
// মোড ২: সকল লেখকের ডিরেক্টরি তালিকা ভিউ (View All)
// =========================================================================
try {
    $stmt = $pdo->query("SELECT * FROM authors ORDER BY name_bn ASC");
    $authors = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $authors = [];
}

$page_title = "সকল লেখক | JUFbook";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="min-height: 65vh; margin-top: 40px; margin-bottom: 60px;">
    <section class="section-header" style="margin-bottom: 30px; text-align: center;">
        <div class="section-overline-label">— লেখকদের জগৎ —</div>
        <h1 class="page-section-title" style="margin-top: 4px;">আমাদের সকল লেখক</h1>
    </section>

    <?php if (empty($authors)): ?>
        <p style="color: var(--text-muted); text-align: center;">এখনো কোনো লেখকের প্রোফাইল যুক্ত করা হয়নি।</p>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 24px; text-align: center;">
            <?php foreach ($authors as $a): 
                $initial = mb_substr($a['name_bn'], 0, 1, 'UTF-8');
                $themeColor = ['#2B3D4F', '#4A2E2B', '#1F3F35', '#4A3B32', '#34253A', '#1E3A4C'][abs(crc32($a['name_bn'])) % 6];
            ?>
                <a href="authors.php?id=<?= (int)$a['id']; ?>" class="author-card" style="background: #FFFFFF; padding: 22px 14px; border-radius: 12px; border: 1px solid #ECE5D8; text-decoration: none; display: block; transition: transform 0.25s ease;">
                    <div style="width: 95px; height: 110px; margin: 0 auto 14px; border-radius: 50% / 40%; overflow: hidden; background: <?= $themeColor ?>; border: 2px solid #DED6C7; display: flex; align-items: center; justify-content: center;">
                        <?php if (!empty($a['photo']) && file_exists(__DIR__ . '/uploads/authors/' . $a['photo'])): ?>
                            <img src="uploads/authors/<?= htmlspecialchars($a['photo'], ENT_QUOTES, 'UTF-8') ?>" alt="" style="width:100%; height:100%; object-fit:cover;">
                        <?php else: ?>
                            <span style="color: #FFFFFF; font-size: 32px; font-weight: 700;"><?= $initial ?></span>
                        <?php endif; ?>
                    </div>
                    <h4 style="font-size: 16px; font-weight: 700; color: #1C1917; margin-bottom: 4px;"><?= sanitizeOutput($a['name_bn']); ?></h4>
                    <p style="font-size: 12px; color: #666;"><?= sanitizeOutput($a['designation_bn'] ?? 'সাহিত্যিক'); ?></p>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>