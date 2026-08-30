<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ((int)($_SERVER['SERVER_PORT'] ?? 0) === 443);
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function sendSecurityHeaders(): void {
    if (!headers_sent()) {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
    }
}
sendSecurityHeaders();

function generateCSRFToken(): string {
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken(?string $token): bool {
    if (!is_string($token) || $token === '' || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals((string)$_SESSION['csrf_token'], $token);
}

function sanitizeOutput(?string $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function sanitizeInput(?string $value): string {
    $value = trim((string)$value);
    return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? '';
}

/**
 * বইয়ের অধ্যায়ের জন্য নিরাপদ রিচ-টেক্সট স্যানিটাইজার (XSS Protection)
 */
function sanitizeRichText(string $html): string {
    // শুধুমাত্র পড়ার প্রয়োজনীয় নিরাপদ ট্যাগ অনুমোদন
    $allowedTags = '<p><br><strong><b><em><i><u><h2><h3><h4><blockquote><ul><ol><li><a>';
    $clean = strip_tags($html, $allowedTags);
    // ক্ষতিকর ইভেন্ট হ্যান্ডলার ও জাভাস্ক্রিপ্ট প্রোটোকল দূরীকরণ
    $clean = preg_replace('/<([a-z][a-z0-9]*)[^>]*?(\bon[a-z]+\s*=|javascript:)[^>]*?>/i', '<$1>', $clean);
    return $clean ?? '';
}