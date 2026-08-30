<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/security.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'অবৈধ অনুরোধ।']);
    exit;
}

$csrfToken = $_POST['csrf_token'] ?? '';
if (!verifyCSRFToken($csrfToken)) {
    echo json_encode(['success' => false, 'message' => 'নিরাপত্তা টোকেন যাচাই ব্যর্থ হয়েছে।']);
    exit;
}

$email = trim($_POST['email'] ?? '');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'একটি সঠিক ইমেইল ঠিকানা প্রদান করুন।']);
    exit;
}

$pdo = getDB();

try {
    $checkStmt = $pdo->prepare("SELECT id FROM subscribers WHERE email = ?");
    $checkStmt->execute([$email]);

    if ($checkStmt->fetch()) {
        echo json_encode(['success' => true, 'message' => 'আপনি ইতিমধ্যে সাবস্ক্রাইব করে রেখেছেন।']);
        exit;
    }

    $insertStmt = $pdo->prepare("INSERT INTO subscribers (email, created_at) VALUES (?, NOW())");
    $insertStmt->execute([$email]);

    echo json_encode(['success' => true, 'message' => 'ধন্যবাদ! আপনার সাবস্ক্রিপশন সফল হয়েছে।']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'সার্ভার সমস্যা। পরে আবার চেষ্টা করুন।']);
}