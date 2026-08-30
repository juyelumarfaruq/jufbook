<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

require_login();

$pdo = getDB();
$user_id = $_SESSION['user_id'];

// Fetch user data
$query = "SELECT name, email, created_at FROM users WHERE id = :id LIMIT 1";
$stmt = $pdo->prepare($query);
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch();

// Fetch reading history / books
$history_query = "SELECT b.id as book_id, b.title_bn, b.genre_tag_bn, b.theme_color, a.name_bn as author_name
                  FROM books b
                  LEFT JOIN authors a ON b.author_id = a.id
                  ORDER BY b.id DESC LIMIT 4";
$history_books = $pdo->query($history_query)->fetchAll();

$page_title = "আমার প্রোফাইল | JUFbook";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="margin-top: 40px; margin-bottom: 70px;">
    <div style="display: flex; flex-wrap: wrap; gap: 35px;">
        
        <!-- Left Sidebar: Profile Details -->
        <div style="flex: 0 0 280px; max-width: 320px;">
            <div style="background: #FFFFFF; padding: 30px 20px; border-radius: 20px; border: 1px solid #ECE5D8; text-align: center;">
                <div style="width: 75px; height: 75px; background: rgba(186, 78, 44, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px auto; font-family: var(--font-heading); font-size: 28px; color: var(--accent-rust); font-weight: 700;">
                    <?= mb_substr(sanitizeOutput($user['name'] ?? 'প'), 0, 1, 'UTF-8') ?>
                </div>
                <h3 style="margin: 0 0 4px 0; font-family: var(--font-heading); font-size: 18px;"><?= sanitizeOutput($user['name'] ?? 'সম্মানিত পাঠক') ?></h3>
                <p style="color: var(--text-muted); font-size: 13px; margin: 0 0 20px 0;"><?= sanitizeOutput($user['email'] ?? '') ?></p>
                
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="<?= BASE_URL ?>/admin/index.php" class="btn-hero-cinematic-primary" style="display: block; text-decoration: none; margin-bottom: 10px; font-size: 13.5px;">অ্যাডমিন ড্যাশবোর্ড</a>
                <?php endif; ?>
                
                <a href="<?= BASE_URL ?>/logout.php" style="display: block; color: #EF4444; font-size: 13.5px; font-weight: 600; text-decoration: none; margin-top: 15px;">🚪 লগআউট করুন</a>
            </div>
        </div>

        <!-- Right Content: Reading Shelf -->
        <div style="flex: 1; min-width: 300px;">
            <div class="section-overline-label">— আপনার লাইব্রেরি —</div>
            <h2 class="page-section-title" style="margin-bottom: 25px;">জনপ্রিয় বইসমূহ</h2>

            <div class="grid-4-col">
                <?php foreach ($history_books as $book): ?>
                    <a href="details.php?id=<?= (int)$book['book_id'] ?>" class="showcase-card-item anim-card" style="text-decoration: none;">
                        <div class="juf-book-jacket" style="background: <?= $book['theme_color'] ?? '#243447' ?>;">
                            <div class="juf-jacket-spine"></div>
                            <span class="juf-jacket-genre"><?= sanitizeOutput($book['genre_tag_bn'] ?? 'উপন্যাস') ?></span>
                            <div class="juf-jacket-title"><?= sanitizeOutput($book['title_bn']) ?></div>
                        </div>
                        <div class="book-under-meta">
                            <div class="book-under-title"><?= sanitizeOutput($book['title_bn']) ?></div>
                            <div class="book-under-author"><?= sanitizeOutput($book['author_name']) ?></div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>