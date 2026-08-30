<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/security.php';

header('Content-Type: application/json; charset=utf-8');

$q = trim($_GET['q'] ?? '');
if (mb_strlen($q, 'UTF-8') < 2) {
    echo json_encode(['results' => []]);
    exit;
}

$pdo = getDB();
$results = [];

try {
    // Search in Books
    $stmt = $pdo->prepare("SELECT id, title_bn FROM books WHERE title_bn LIKE ? LIMIT 5");
    $stmt->execute(["%{$q}%"]);
    while ($row = $stmt->fetch()) {
        $results[] = [
            'title' => $row['title_bn'],
            'type' => 'বই',
            'url' => 'book.php?id=' . (int)$row['id']
        ];
    }

    // Search in Authors
    $stmtAuth = $pdo->prepare("SELECT id, name_bn FROM authors WHERE name_bn LIKE ? LIMIT 3");
    $stmtAuth->execute(["%{$q}%"]);
    while ($rowAuth = $stmtAuth->fetch()) {
        $results[] = [
            'title' => $rowAuth['name_bn'],
            'type' => 'লেখক',
            'url' => 'author.php?id=' . (int)$rowAuth['id']
        ];
    }

    echo json_encode(['results' => $results]);
} catch (Exception $e) {
    echo json_encode(['results' => []]);
}