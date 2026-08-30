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

// CSV আপলোড হ্যান্ডলার
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = '<div class="alert-box alert-danger">নিরাপত্তা টোকেন যাচাই ব্যর্থ হয়েছে!</div>';
    } elseif ($_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        $message = '<div class="alert-box alert-danger">CSV ফাইল আপলোড করতে সমস্যা হয়েছে।</div>';
    } else {
        $fileTmp = $_FILES['csv_file']['tmp_name'];
        if (($handle = fopen($fileTmp, 'r')) !== false) {
            $count = 0;
            fgetcsv($handle, 0, ','); // Skip header

            $pdo->beginTransaction();
            try {
                $authorStmt = $pdo->prepare("SELECT id FROM authors WHERE name_bn = :name LIMIT 1");
                $insertAuthor = $pdo->prepare("INSERT INTO authors (name_bn, theme_color) VALUES (:name, '#243447')");
                
                $bookStmt = $pdo->prepare("
                    INSERT INTO books (title_bn, author_id, genre_tag_bn, theme_color, summary_bn) 
                    VALUES (:title, :author_id, :genre, :color, :summary)
                ");

                while (($data = fgetcsv($handle, 0, ',')) !== false) {
                    if (isset($data[0], $data[1]) && trim((string)$data[0]) !== '') {
                        $title_bn   = sanitizeInput((string)$data[0]);
                        $author_bn  = sanitizeInput((string)$data[1]);
                        $genre_bn   = sanitizeInput((string)($data[2] ?? 'উপন্যাস'));
                        $theme_col  = sanitizeInput((string)($data[3] ?? '#243447'));
                        $summary_bn = sanitizeInput((string)($data[4] ?? ''));

                        // লেখক চেক/তৈরি
                        $authorStmt->execute([':name' => $author_bn]);
                        $author_id = $authorStmt->fetchColumn();
                        if (!$author_id) {
                            $insertAuthor->execute([':name' => $author_bn]);
                            $author_id = (int)$pdo->lastInsertId();
                        }

                        $bookStmt->execute([
                            ':title'     => $title_bn,
                            ':author_id' => (int)$author_id,
                            ':genre'     => $genre_bn,
                            ':color'     => $theme_col ?: '#243447',
                            ':summary'   => $summary_bn
                        ]);
                        $count++;
                    }
                }
                $pdo->commit();
                $message = '<div class="alert-box alert-success">সফলভাবে ' . $count . ' টি বই ক্যাটালগে ইমপোর্ট করা হয়েছে!</div>';
            } catch (Throwable $e) {
                $pdo->rollBack();
                $message = '<div class="alert-box alert-danger">ডেটাবেস ত্রুটি: ' . sanitizeOutput($e->getMessage()) . '</div>';
            }
            fclose($handle);
        }
    }
}

$page_title = "Bulk Books Import — JUFbook Admin";
require_once __DIR__ . '/includes/header.php';
?>

<?= $message ?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
    <div>
        <h2 style="font-size:22px; font-weight:800; color:var(--adm-ink); margin:0;">বাল্ক বই আপলোড (CSV)</h2>
        <span style="font-size:13px; color:var(--adm-text-muted);">এক ক্লিকে একাধিক বই ক্যাটালগে যুক্ত করুন</span>
    </div>
    <a href="books.php" class="btn-adm-secondary">← বই তালিকায় ফিরে যান</a>
</div>

<div class="adm-card-panel" style="max-width: 680px; margin: 0 auto;">
    <div class="adm-panel-head">
        <h3>📥 CSV ফাইল ফরম্যাট ও নির্দেশিকা</h3>
    </div>

    <div style="background:var(--adm-primary-tint); border:1.5px solid var(--adm-border); padding:16px 18px; border-radius:var(--adm-radius-sm); margin-bottom:20px; font-size:13.5px; line-height:1.7; color:var(--adm-ink);">
        <strong style="color:var(--adm-primary-deep);">ফাইলের কলাম বিন্যাস:</strong><br>
        ১ম কলাম: <b>বইয়ের শিরোনাম</b> (যেমন: <code>চোখের বালি</code>)<br>
        ২য় কলাম: <b>লেখকের নাম</b> (যেমন: <code>রবীন্দ্রনাথ ঠাকুর</code>)<br>
        ৩য় কলাম: <b>জনরা ট্যাগ</b> (যেমন: <code>সামাজিক উপন্যাস</code>)<br>
        ৪র্থ কলাম: <b>2D কালার কোড</b> (যেমন: <code>#BA4E2C</code>)<br>
        ৫ম কলাম: <b>সারসংক্ষেপ</b><br>
        <small style="color:var(--adm-text-muted); display:block; margin-top:4px;">* যদি ফাইলে উল্লেখিত লেখক ডেটাবেসে না থাকেন, তবে স্বয়ংক্রিয়ভাবে নতুন লেখক তৈরি হবে।</small>
    </div>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

        <div class="form-group">
            <label>CSV ফাইল নির্বাচন করুন (.csv UTF-8) *</label>
            <input type="file" name="csv_file" accept=".csv,text/csv" class="form-control" required style="padding: 9px 12px;">
        </div>

        <button type="submit" class="btn-adm-primary" style="width: 100%; justify-content: center; padding: 11px;">
            📥 বাল্ক বুক ইমপোর্ট শুরু করুন
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>