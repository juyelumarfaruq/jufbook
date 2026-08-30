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

// ১. লেখক ডিলিট (Secure POST + CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = '<div class="alert-box alert-danger">নিরাপত্তা টোকেন যাচাই ব্যর্থ হয়েছে!</div>';
    } else {
        $del_id = (int)($_POST['author_id'] ?? 0);
        try {
            $stmt = $pdo->prepare("DELETE FROM authors WHERE id = :id");
            $stmt->execute([':id' => $del_id]);
            $message = '<div class="alert-box alert-success">লেখক সফলভাবে মুছে ফেলা হয়েছে।</div>';
        } catch (Throwable $e) {
            $message = '<div class="alert-box alert-danger">এই লেখকের অধীনে প্রকাশিত বই থাকায় মুছে ফেলা সম্ভব নয়।</div>';
        }
    }
}

// ২. লেখক অ্যাড / এডিট
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['action']) || $_POST['action'] !== 'delete')) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = '<div class="alert-box alert-danger">নিরাপত্তা টোকেন যাচাই ব্যর্থ হয়েছে!</div>';
    } else {
        $id = (int)($_POST['id'] ?? 0);
        $name_bn = sanitizeInput($_POST['name_bn'] ?? '');
        $title_tag_bn = sanitizeInput($_POST['title_tag_bn'] ?? '');
        $bio_bn = sanitizeInput($_POST['bio_bn'] ?? '');
        $theme_color = sanitizeInput($_POST['theme_color'] ?? '#BA4E2C');
        $photo_url = sanitizeInput($_POST['photo_url'] ?? '');

        if ($name_bn === '') {
            $message = '<div class="alert-box alert-danger">লেখকের নাম আবশ্যক।</div>';
        } else {
            try {
                if ($id > 0) {
                    $sql = "UPDATE authors SET name_bn = :name, title_tag_bn = :title_tag, bio_bn = :bio, theme_color = :color, photo_url = :photo WHERE id = :id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ':name'      => $name_bn,
                        ':title_tag' => $title_tag_bn,
                        ':bio'       => $bio_bn,
                        ':color'     => $theme_color,
                        ':photo'     => $photo_url,
                        ':id'        => $id
                    ]);
                    $message = '<div class="alert-box alert-success">লেখকের তথ্য আপডেট করা হয়েছে।</div>';
                } else {
                    $sql = "INSERT INTO authors (name_bn, title_tag_bn, bio_bn, theme_color, photo_url) VALUES (:name, :title_tag, :bio, :color, :photo)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ':name'      => $name_bn,
                        ':title_tag' => $title_tag_bn,
                        ':bio'       => $bio_bn,
                        ':color'     => $theme_color,
                        ':photo'     => $photo_url
                    ]);
                    $message = '<div class="alert-box alert-success">নতুন লেখক সফলভাবে যুক্ত হয়েছে।</div>';
                }
            } catch (Throwable $e) {
                $message = '<div class="alert-box alert-danger">ত্রুটি: ' . sanitizeOutput($e->getMessage()) . '</div>';
            }
        }
    }
}

$authors = $pdo->query("
    SELECT a.*, (SELECT COUNT(*) FROM books WHERE author_id = a.id) AS book_count 
    FROM authors a 
    ORDER BY a.id DESC
")->fetchAll(PDO::FETCH_ASSOC) ?: [];

$page_title = "Manage Authors — JUFbook Admin";
require_once __DIR__ . '/includes/header.php';
?>

<?= $message ?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
    <div>
        <h2 style="font-size:22px; font-weight:800; color:var(--adm-ink); margin:0;">লেখক প্যানেল</h2>
        <span style="font-size:13px; color:var(--adm-text-muted);">বাংলা সাহিত্যের বরেণ্য লেখকবৃন্দের তালিকা</span>
    </div>
</div>

<div class="adm-form-grid" style="align-items:start;">
    <!-- Add/Edit Form -->
    <div class="adm-card-panel">
        <div class="adm-panel-head">
            <h3 id="formTitle">নতুন লেখক যুক্ত করুন</h3>
        </div>

        <form method="POST" id="authorForm">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            <input type="hidden" name="id" id="author_id" value="0">

            <div class="form-group">
                <label>লেখকের নাম (বাংলায়) *</label>
                <input type="text" name="name_bn" id="name_bn" class="form-control bn-font" placeholder="যেমন: রবীন্দ্রনাথ ঠাকুর" required>
            </div>

            <div class="adm-form-grid" style="grid-template-columns: 1.5fr 1fr; gap:14px; margin-bottom:14px;">
                <div class="form-group" style="margin-bottom:0;">
                    <label>উপাধি / পরিচিতি ট্যাগ</label>
                    <input type="text" name="title_tag_bn" id="title_tag_bn" class="form-control bn-font" placeholder="যেমন: বিশ্বকবি">
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label>2D পোর্ট্রেট কালার</label>
                    <input type="color" name="theme_color" id="theme_color" class="form-control" style="height:42px; padding:4px;" value="#BA4E2C">
                </div>
            </div>

            <div class="form-group">
                <label>পোর্ট্রেট ইমেজ URL (ঐচ্ছিক)</label>
                <input type="text" name="photo_url" id="photo_url" class="form-control" placeholder="assets/images/authors/rabindranath.webp">
            </div>

            <div class="form-group">
                <label>সংক্ষিপ্ত জীবনী</label>
                <textarea name="bio_bn" id="bio_bn" rows="4" class="form-control bn-font" placeholder="লেখকের সাহিত্যকর্ম ও জীবনকাল..."></textarea>
            </div>

            <button type="submit" id="submitBtn" class="btn-adm-primary" style="width:100%; justify-content:center;">সংরক্ষণ করুন</button>
            <button type="button" id="cancelBtn" onclick="resetAuthorForm()" class="btn-adm-secondary" style="width:100%; justify-content:center; margin-top:8px; display:none;">এডিট বাতিল করুন</button>
        </form>
    </div>

    <!-- Authors List Table -->
    <div class="adm-card-panel">
        <div class="adm-panel-head">
            <h3>নিবন্ধিত লেখকবৃন্দ (<?= count($authors); ?>)</h3>
        </div>

        <div class="adm-table-wrap">
            <table class="adm-table">
                <thead>
                    <tr>
                        <th>লেখক</th>
                        <th>উপাধি</th>
                        <th>বই সংখ্যা</th>
                        <th style="text-align:right;">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($authors)): ?>
                        <tr><td colspan="4" style="text-align:center; color:var(--adm-text-muted); padding:24px;">কোনো লেখক যুক্ত করা হয়নি।</td></tr>
                    <?php else: ?>
                        <?php foreach ($authors as $a): ?>
                            <tr>
                                <td>
                                    <strong class="bn-font" style="font-size:14.5px; color:var(--adm-ink); display:block;"><?= sanitizeOutput($a['name_bn']) ?></strong>
                                </td>
                                <td>
                                    <span class="genre-pill bn-font" style="background:<?= htmlspecialchars($a['theme_color'] ?? '#BA4E2C', ENT_QUOTES, 'UTF-8') ?>; font-size:11px;">
                                        <?= sanitizeOutput($a['title_tag_bn'] ?? 'লেখক') ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="rank-badge"><?= (int)$a['book_count'] ?> টি বই</span>
                                </td>
                                <td style="text-align:right; white-space:nowrap;">
                                    <button type="button" onclick='editAuthor(<?= json_encode($a, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)' class="btn-adm-secondary" style="padding:4px 8px; font-size:11.5px; cursor:pointer;">এডিট</button>
                                    
                                    <form method="POST" style="margin:0; display:inline;" onsubmit="return confirm('এই লেখককে মুছে ফেলতে চান?');">
                                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="author_id" value="<?= (int)$a['id'] ?>">
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
function editAuthor(a) {
    document.getElementById('author_id').value = a.id;
    document.getElementById('name_bn').value = a.name_bn;
    document.getElementById('title_tag_bn').value = a.title_tag_bn || '';
    document.getElementById('theme_color').value = a.theme_color || '#BA4E2C';
    document.getElementById('photo_url').value = a.photo_url || '';
    document.getElementById('bio_bn').value = a.bio_bn || '';

    document.getElementById('formTitle').innerText = 'লেখক সম্পাদনা করুন';
    document.getElementById('submitBtn').innerText = 'আপডেট সংরক্ষণ করুন';
    document.getElementById('cancelBtn').style.display = 'inline-flex';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function resetAuthorForm() {
    document.getElementById('authorForm').reset();
    document.getElementById('author_id').value = '0';
    document.getElementById('formTitle').innerText = 'নতুন লেখক যুক্ত করুন';
    document.getElementById('submitBtn').innerText = 'সংরক্ষণ করুন';
    document.getElementById('cancelBtn').style.display = 'none';
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>