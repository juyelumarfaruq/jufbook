<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin();

$pdo = getDB();
$feedback = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $feedback = 'নিরাপত্তা টোকেন ব্যর্থ হয়েছে।';
    } else {
        $file = $_FILES['csv_file'];
        $ext  = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
        if ($file['error'] === UPLOAD_ERR_OK && $ext === 'csv') {
            $handle = fopen($file['tmp_name'], 'r');
            $row = 0;
            $successCount = 0;
            
            $pdo->beginTransaction();
            try {
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $row++;
                    if ($row === 1) continue; // Skip header row
                    
                    // Format: Title, Author ID, Category ID, Genre Tag BN, Theme Color
                    $title = $data[0] ?? '';
                    $authorId = (int)($data[1] ?? 1);
                    $catId = (int)($data[2] ?? 1);
                    $genre = $data[3] ?? 'উপন্যাস';
                    $color = $data[4] ?? '#1A2930';

                    if (!empty($title)) {
                        $stmt = $pdo->prepare("INSERT INTO books (title_bn, author_id, category_id, genre_tag_bn, theme_color) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$title, $authorId, $catId, $genre, $color]);
                        $successCount++;
                    }
                }
                $pdo->commit();
                fclose($handle);
                $feedback = "সফলভাবে {$successCount} টি বই বাল্ক আপলোড সম্পন্ন হয়েছে!";
            } catch (Throwable $e) {
                $pdo->rollBack();
                $feedback = "আপলোড ব্যর্থ হয়েছে: " . $e->getMessage();
            }
        }
    }
}
$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>Bulk Upload Books - JUFbook</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body style="background: #EFECE6; padding: 40px 0;">
    <div class="container" style="max-width: 600px; background: #FFF; padding: 30px; border-radius: 12px; border: 1px solid var(--border-light);">
        <h2 style="font-family: var(--font-bn-head); margin-bottom: 10px;">বাল্ক বই আপলোড (CSV)</h2>
        <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 20px;">CSV ফরম্যাট: <code>Title, AuthorID, CategoryID, GenreTag, ColorCode</code></p>
        
        <?php if ($feedback): ?>
            <div style="background: #dcfce7; color: #15803d; padding: 10px; border-radius: 6px; margin-bottom: 20px;"><?= sanitizeOutput($feedback); ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">
            <div style="margin-bottom: 20px;">
                <input type="file" name="csv_file" accept=".csv" required style="width: 100%;">
            </div>
            <button type="submit" class="juf-pill-btn" style="width: 100%;">CSV ফাইল আপলোড করুন</button>
        </form>
    </div>
</body>
</html>