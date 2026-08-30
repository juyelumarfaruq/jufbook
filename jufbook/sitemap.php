<?php
require_once 'config/database.php';
require_once 'config/constants.php';

$db = new Database();
$conn = $db->getConnection();

// Set header for XML output
header("Content-Type: text/xml; charset=utf-8");
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    
    <!-- Core Pages -->
    <url>
        <loc><?= BASE_URL ?>/</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc><?= BASE_URL ?>/authors.php</loc>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc><?= BASE_URL ?>/search.php</loc>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>

    <!-- Dynamic Book Pages -->
    <?php
    $book_stmt = $conn->prepare("SELECT slug, created_at FROM books WHERE status = 'published' ORDER BY created_at DESC");
    $book_stmt->execute();
    while ($book = $book_stmt->fetch(PDO::FETCH_ASSOC)) {
        $date = date('Y-m-d', strtotime($book['created_at']));
        echo "    <url>\n";
        echo "        <loc>" . BASE_URL . "/details.php?slug=" . htmlspecialchars($book['slug']) . "</loc>\n";
        echo "        <lastmod>" . $date . "</lastmod>\n";
        echo "        <changefreq>weekly</changefreq>\n";
        echo "        <priority>0.9</priority>\n";
        echo "    </url>\n";
    }
    ?>

</urlset>