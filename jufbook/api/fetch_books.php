<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/security.php';

header('Content-Type: application/json; charset=utf-8');

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$pdo = getDB();

try {
    $totalBooks = (int)$pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
    $totalPages = max(1, (int)ceil($totalBooks / $limit));

    $stmt = $pdo->prepare("
        SELECT b.*, a.name_bn AS author_name 
        FROM books b 
        JOIN authors a ON b.author_id = a.id 
        ORDER BY b.id ASC 
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $books = $stmt->fetchAll();

    if (empty($books)) {
        echo json_encode(['success' => false, 'message' => 'আর কোনো বই নেই।']);
        exit;
    }

    $html = '';
    foreach ($books as $b) {
        $genre = sanitizeOutput($b['genre_tag_bn'] ?? 'উপন্যাস');
        $title = sanitizeOutput($b['title_bn']);
        $author = sanitizeOutput($b['author_name']);
        $color = htmlspecialchars($b['theme_color'] ?? '#2B3A42', ENT_QUOTES, 'UTF-8');

        $html .= '<div class="showcase-card-item">';
        $html .= '<div class="juf-book-jacket" style="background: ' . $color . ';">';
        $html .= '<div class="juf-jacket-spine"></div>';
        $html .= '<span class="juf-jacket-genre">' . $genre . '</span>';
        $html .= '<div class="juf-jacket-title">' . $title . '</div>';
        $html .= '</div>';
        $html .= '<div class="book-under-meta">';
        $html .= '<div class="book-under-title">' . $title . '</div>';
        $html .= '<div class="book-under-author">' . $author . '</div>';
        $html .= '<div class="book-rating-gold">★★★★★</div>';
        $html .= '</div>';
        $html .= '</div>';
    }

    echo json_encode([
        'success' => true,
        'html' => $html,
        'page' => $page,
        'totalPages' => $totalPages
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'সার্ভার ত্রুটি।']);
}