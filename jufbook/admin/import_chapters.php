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

$book_id = isset($_GET['book_id']) ? (int)$_GET['book_id'] : 0;
if ($book_id <= 0) {
    header("Location: books.php");
    exit;
}

$book_stmt = $pdo->prepare("SELECT title_bn FROM books WHERE id = :id");
$book_stmt->execute([':id' => $book_id]);
$book = $book_stmt->fetch(PDO::FETCH_ASSOC);

if (!$book) {
    header("Location: books.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = '<div class="alert-box alert-danger">Security token verification failed!</div>';
    } elseif ($_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        $message = '<div class="alert-box alert-danger">CSV ফাইল আপলোড করতে সমস্যা হয়েছে।</div>';
    } else {
        $filePath = $_FILES['csv_file']['tmp_name'];
        if (($handle = fopen($filePath, 'r')) !== false) {
            $inserted = 0;
            $header = fgetcsv($handle); // হেডার স্কিপ
            
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO chapters (book_id, chapter_number, chapter_title_bn, content, status) 
                    VALUES (:book_id, :num, :title, :content, 'published')
                    ON DUPLICATE KEY UPDATE chapter_title_bn = VALUES(chapter_title_bn), content = VALUES(content)
                ");

                while (($data = fgetcsv($handle, 0, ',')) !== false) {
                    if (isset($data[0], $data[1], $data[2])) {
                        $num = (int)$data[0];
                        $title = sanitizeInput($data[1]);
                        $content = sanitizeRichText($data[2]);

                        $stmt->execute([
                            ':book_id' => $book_id,
                            ':num'     => $num,
                            ':title'   => $title,
                            ':content' => $content
                        ]);
                        $inserted++;
                    }
                }
                $pdo->commit();
                $message = "<div class='alert-box alert-success'>মোট {$inserted} টি অধ্যায় সফলভাবে ইমপোর্ট হয়েছে।</div>";
            } catch (Throwable $e) {
                $pdo->rollBack();
                $message = "<div class='alert-box alert-danger'>ইমপোর্ট ত্রুটি: " . sanitizeOutput($e->getMessage()) . "</div>";
            }
            fclose($handle);
        }
    }
}

$page_title = "Bulk Import Chapters — JUFbook Admin";
require_once __DIR__ . '/includes/header.php';
?>

<?= $message ?>

<div class="adm-card-panel" style="max-width:650px; margin: 0 auto;">
    <div class="adm-panel-head">
        <div>
            <span style="font-size:12.5px; color:var(--adm-text-muted);">CSV Import for Book</span>
            <h3 class="bn-font" style="margin:2px 0;"><?= sanitizeOutput($book['title_bn']) ?></h3>
        </div>
        <a href="chapters.php?book_id=<?= $book_id ?>" class="btn-adm-secondary">← Back to Chapters</a>
    </div>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
        
        <div class="form-group">
            <label>Select CSV File (UTF-8 Encoded) *</label>
            <input type="file" name="csv_file" accept=".csv" class="form-control" required>
            <small style="color:var(--adm-text-muted); display:block; margin-top:6px;">
                কলাম ফরম্যাট: <code>Chapter_No, Chapter_Title_BN, Content_HTML</code>
            </small>
        </div>

        <button type="submit" class="btn-adm-primary" style="width:100%; justify-content:center;">📥 Upload & Process CSV</button>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>