<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/includes/security.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';

require_admin();
$pdo = getDB();
$message = '';

// ১. ডিলিট অপারেশন (Secure POST + CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = '<div class="alert-box alert-danger">নিরাপত্তা টোকেন যাচাই ব্যর্থ হয়েছে!</div>';
    } else {
        $del_id = (int)($_POST['book_id'] ?? 0);
        try {
            $pdo->beginTransaction();
            $pdo->prepare("DELETE FROM chapters WHERE book_id = :id")->execute([':id' => $del_id]);
            $pdo->prepare("DELETE FROM books WHERE id = :id")->execute([':id' => $del_id]);
            $pdo->commit();
            $message = '<div class="alert-box alert-success">বই এবং এর সমস্ত অধ্যায় সফলভাবে মুছে ফেলা হয়েছে।</div>';
        } catch (Throwable $e) {
            $pdo->rollBack();
            $message = '<div class="alert-box alert-danger">ত্রুটি: ' . sanitizeOutput($e->getMessage()) . '</div>';
        }
    }
}

// ২. অ্যাড / এডিট অপারেশন
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['action']) || $_POST['action'] !== 'delete')) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = '<div class="alert-box alert-danger">নিরাপত্তা টোকেন যাচাই ব্যর্থ হয়েছে!</div>';
    } else {
        $id = (int)($_POST['id'] ?? 0);
        $title_bn = sanitizeInput($_POST['title_bn'] ?? '');
        $author_id = (int)($_POST['author_id'] ?? 0);
        $category_id = (int)($_POST['category_id'] ?? 0);
        $genre_tag_bn = sanitizeInput($_POST['genre_tag_bn'] ?? '');
        $theme_color = sanitizeInput($_POST['theme_color'] ?? '#243447');
        $summary_bn = sanitizeInput($_POST['summary_bn'] ?? '');
        $is_audiobook = isset($_POST['is_audiobook']) ? 1 : 0;
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $cover_image = sanitizeInput($_POST['cover_image'] ?? '');

        if ($title_bn === '' || $author_id <= 0) {
            $message = '<div class="alert-box alert-danger">বইয়ের নাম এবং লেখক নির্বাচন করা আবশ্যক।</div>';
        } else {
            try {
                if ($id > 0) {
                    $sql = "UPDATE books SET title_bn = :title, author_id = :author_id, category_id = :category_id, 
                            genre_tag_bn = :genre, theme_color = :color, summary_bn = :summary, 
                            is_audiobook = :audio, is_featured = :feat, cover_image = :cover WHERE id = :id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ':title'       => $title_bn,
                        ':author_id'   => $author_id,
                        ':category_id' => $category_id > 0 ? $category_id : null,
                        ':genre'       => $genre_tag_bn,
                        ':color'       => $theme_color,
                        ':summary'     => $summary_bn,
                        ':audio'       => $is_audiobook,
                        ':feat'        => $is_featured,
                        ':cover'       => $cover_image,
                        ':id'          => $id
                    ]);
                    $message = '<div class="alert-box alert-success">বইয়ের বিবরণ সফলভাবে আপডেট করা হয়েছে।</div>';
                } else {
                    $sql = "INSERT INTO books (title_bn, author_id, category_id, genre_tag_bn, theme_color, summary_bn, is_audiobook, is_featured, cover_image) 
                            VALUES (:title, :author_id, :category_id, :genre, :color, :summary, :audio, :feat, :cover)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ':title'       => $title_bn,
                        ':author_id'   => $author_id,
                        ':category_id' => $category_id > 0 ? $category_id : null,
                        ':genre'       => $genre_tag_bn,
                        ':color'       => $theme_color,
                        ':summary'     => $summary_bn,
                        ':audio'       => $is_audiobook,
                        ':feat'        => $is_featured,
                        ':cover'       => $cover_image
                    ]);
                    $message = '<div class="alert-box alert-success">নতুন বই সফলভাবে ক্যাটালগে যুক্ত হয়েছে।</div>';
                }
            } catch (Throwable $e) {
                $message = '<div class="alert-box alert-danger">ডেটাবেস ত্রুটি: ' . sanitizeOutput($e->getMessage()) . '</div>';
            }
        }
    }
}

// ডেটা সংগ্রহ
$authors = $pdo->query("SELECT id, name_bn FROM authors ORDER BY name_bn ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
$categories = $pdo->query("SELECT id, name_bn FROM categories ORDER BY name_bn ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];

$booksQuery = "
    SELECT b.*, a.name_bn AS author_name, c.name_bn AS category_name,
           (SELECT COUNT(*) FROM chapters WHERE book_id = b.id) AS chapter_count
    FROM books b
    LEFT JOIN authors a ON b.author_id = a.id
    LEFT JOIN categories c ON b.category_id = c.id
    ORDER BY b.id DESC
";
$books = $pdo->query($booksQuery)->fetchAll(PDO::FETCH_ASSOC) ?: [];

$page_title = "Manage Books — JUFbook Admin";
require_once __DIR__ . '/includes/header.php';
?>

<?= $message ?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
    <div>
        <h2 style="font-size:22px; font-weight:800; color:var(--adm-ink); margin:0;">বই ও উপন্যাস ব্যবস্থাপনা</h2>
        <span style="font-size:13px; color:var(--adm-text-muted);">মোট প্রকাশিত বই: <?= count($books); ?> টি</span>
    </div>
    <a href="bulk_books.php" class="btn-adm-primary" style="background:#059669; border-color:#047857;">📥 বাল্ক CSV আপলোড</a>
</div>

<div class="adm-form-grid" style="align-items:start;">
    <!-- Add/Edit Form -->
    <div class="adm-card-panel">
        <div class="adm-panel-head">
            <h3 id="formTitle">নতুন বই প্রকাশ করুন</h3>
        </div>

        <form method="POST" id="bookForm">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            <input type="hidden" name="id" id="book_id" value="0">

            <div class="form-group">
                <label>বইয়ের শিরোনাম (বাংলায়) *</label>
                <input type="text" name="title_bn" id="title_bn" class="form-control bn-font" placeholder="যেমন: পথের পাঁচালী" required>
            </div>

            <div class="adm-form-grid" style="grid-template-columns: 1fr 1fr; gap:14px; margin-bottom:14px;">
                <div class="form-group" style="margin-bottom:0;">
                    <label>লেখক নির্বাচন *</label>
                    <select name="author_id" id="author_id" class="form-control bn-font" required>
                        <option value="">-- লেখক সিলেক্ট করুন --</option>
                        <?php foreach ($authors as $a): ?>
                            <option value="<?= (int)$a['id'] ?>"><?= sanitizeOutput($a['name_bn']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label>ক্যাটাগরি</label>
                    <select name="category_id" id="category_id" class="form-control bn-font">
                        <option value="0">-- সাধারণ / ক্যাটাগরি ছাড়া --</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= (int)$c['id'] ?>"><?= sanitizeOutput($c['name_bn']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="adm-form-grid" style="grid-template-columns: 1.5fr 1fr; gap:14px; margin-bottom:14px;">
                <div class="form-group" style="margin-bottom:0;">
                    <label>জনরা ট্যাগ (Genre Tag)</label>
                    <input type="text" name="genre_tag_bn" id="genre_tag_bn" class="form-control bn-font" placeholder="যেমন: কালজয়ী উপন্যাস">
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label>2D থিম কালার</label>
                    <input type="color" name="theme_color" id="theme_color" class="form-control" style="height:42px; padding:4px;" value="#243447">
                </div>
            </div>

            <div class="form-group">
                <label>কভার ছবি URL (ঐচ্ছিক)</label>
                <input type="text" name="cover_image" id="cover_image" class="form-control" placeholder="assets/images/covers/book.webp">
            </div>

            <div class="form-group">
                <label>সংক্ষিপ্ত সারসংক্ষেপ (Book Summary)</label>
                <textarea name="summary_bn" id="summary_bn" rows="3" class="form-control bn-font" placeholder="বইয়ের পটভূমি বা সারসংক্ষেপ..."></textarea>
            </div>

            <div style="display:flex; gap:18px; background:var(--adm-primary-tint); padding:12px; border-radius:var(--adm-radius-sm); border:1.5px solid var(--adm-border); margin-bottom:18px;">
                <label style="display:flex; align-items:center; gap:6px; cursor:pointer; font-size:13px; font-weight:700;">
                    <input type="checkbox" name="is_audiobook" id="is_audiobook" value="1">
                    <span>🎧 ডিজিটাল অডিওবুক</span>
                </label>
                <label style="display:flex; align-items:center; gap:6px; cursor:pointer; font-size:13px; font-weight:700;">
                    <input type="checkbox" name="is_featured" id="is_featured" value="1">
                    <span>⭐ ফিচার্ড বই (হোমপেজ)</span>
                </label>
            </div>

            <button type="submit" id="submitBtn" class="btn-adm-primary" style="width:100%; justify-content:center;">বই সংরক্ষণ করুন</button>
            <button type="button" id="cancelBtn" onclick="resetBookForm()" class="btn-adm-secondary" style="width:100%; justify-content:center; margin-top:8px; display:none;">এডিট বাতিল করুন</button>
        </form>
    </div>

    <!-- Books List Table -->
    <div class="adm-card-panel">
        <div class="adm-panel-head">
            <h3>ক্যাটালগ তালিকা (<?= count($books); ?>)</h3>
        </div>

        <div class="adm-table-wrap">
            <table class="adm-table">
                <thead>
                    <tr>
                        <th>বইয়ের নাম</th>
                        <th>লেখক</th>
                        <th>অধ্যায়</th>
                        <th style="text-align:right;">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($books)): ?>
                        <tr><td colspan="4" style="text-align:center; color:var(--adm-text-muted); padding:24px;">এখনও কোনো বই যোগ করা হয়নি।</td></tr>
                    <?php else: ?>
                        <?php foreach ($books as $b): ?>
                            <tr>
                                <td>
                                    <strong class="bn-font" style="font-size:14.5px; color:var(--adm-ink);"><?= sanitizeOutput($b['title_bn']) ?></strong>
                                    <div style="display:flex; gap:6px; align-items:center; margin-top:3px;">
                                        <span class="genre-pill bn-font" style="background:<?= htmlspecialchars($b['theme_color'] ?? '#243447', ENT_QUOTES, 'UTF-8') ?>; font-size:10.5px; padding:1px 6px;">
                                            <?= sanitizeOutput($b['genre_tag_bn'] ?? 'General') ?>
                                        </span>
                                        <?php if ($b['is_audiobook']): ?>
                                            <span style="font-size:11px; background:var(--adm-primary-soft); color:var(--adm-primary-deep); padding:1px 5px; border-radius:4px; font-weight:700;">🎧 অডিও</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="bn-font" style="color:var(--adm-text-muted);"><?= sanitizeOutput($b['author_name'] ?? 'অজানা') ?></td>
                                <td>
                                    <a href="chapters.php?book_id=<?= (int)$b['id'] ?>" class="btn-adm-secondary" style="padding:3px 8px; font-size:11.5px;">
                                        📖 <?= (int)$b['chapter_count'] ?> টি অধ্যায়
                                    </a>
                                </td>
                                <td style="text-align:right; white-space:nowrap;">
                                    <button type="button" onclick='editBook(<?= json_encode($b, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)' class="btn-adm-secondary" style="padding:4px 8px; font-size:11.5px; cursor:pointer;">এডিট</button>
                                    
                                    <form method="POST" style="margin:0; display:inline;" onsubmit="return confirm('এই বইটি এবং এর সমস্ত অধ্যায় মুছে ফেলতে চান?');">
                                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="book_id" value="<?= (int)$b['id'] ?>">
                                        <button type="submit" class="btn-adm-danger" style="padding:4px 8px; font-size:11.5px;">মুছুন</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function editBook(b) {
    document.getElementById('book_id').value = b.id;
    document.getElementById('title_bn').value = b.title_bn;
    document.getElementById('author_id').value = b.author_id;
    document.getElementById('category_id').value = b.category_id || '0';
    document.getElementById('genre_tag_bn').value = b.genre_tag_bn || '';
    document.getElementById('theme_color').value = b.theme_color || '#243447';
    document.getElementById('cover_image').value = b.cover_image || '';
    document.getElementById('summary_bn').value = b.summary_bn || '';
    document.getElementById('is_audiobook').checked = b.is_audiobook == 1;
    document.getElementById('is_featured').checked = b.is_featured == 1;

    document.getElementById('formTitle').innerText = 'বই সম্পাদনা করুন';
    document.getElementById('submitBtn').innerText = 'আপডেট সংরক্ষণ করুন';
    document.getElementById('cancelBtn').style.display = 'inline-flex';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function resetBookForm() {
    document.getElementById('bookForm').reset();
    document.getElementById('book_id').value = '0';
    document.getElementById('formTitle').innerText = 'নতুন বই প্রকাশ করুন';
    document.getElementById('submitBtn').innerText = 'বই সংরক্ষণ করুন';
    document.getElementById('cancelBtn').style.display = 'none';
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>