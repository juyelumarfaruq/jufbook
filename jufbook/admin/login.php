<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/security.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// অ্যাডমিন ইতিমধ্যে লগইন থাকলে সরাসরি ড্যাশবোর্ডে পাঠাবে
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: index.php");
    exit;
}

$pdo = getDB();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "নিরাপত্তা টোকেন যাচাই ব্যর্থ হয়েছে!";
    } else {
        $username = sanitizeInput($_POST['username'] ?? '');
        $password = (string)($_POST['password'] ?? '');

        if (empty($username) || empty($password)) {
            $error = "ইউজারনেম এবং পাসওয়ার্ড উভয়ই আবশ্যক।";
        } else {
            // আলাদাকৃত প্যারামিটার বাইন্ডিং (:username এবং :email)
            $stmt = $pdo->prepare("SELECT id, username, email, password_hash FROM admins WHERE username = :username OR email = :email LIMIT 1");
            $stmt->execute([
                'username' => $username,
                'email'    => $username
            ]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['role'] = 'admin';

                header("Location: index.php");
                exit;
            } else {
                $error = "ভুল অ্যাডমিন ক্রেডেনশিয়াল!";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>অ্যাডমিন লগইন | JUFbook</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { background: #1C1917; display: flex; align-items: center; justify-content: center; min-height: 100vh; font-family: var(--font-ui); }
        .admin-login-box { background: #FFFFFF; width: 100%; max-width: 420px; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
    </style>
</head>
<body>

<div class="admin-login-box">
    <div style="text-align: center; margin-bottom: 25px;">
        <span class="juf-brand" style="font-size: 28px;">
            <span class="brand-rust">JUF</span><span class="brand-dark">book</span>
        </span>
        <p style="font-size: 13px; color: var(--text-muted); margin-top: 4px;">অ্যাডমিন কন্ট্রোল গেটওয়ে</p>
    </div>

    <?php if ($error): ?>
        <div style="background: #FDE8E8; color: #B91C1C; padding: 10px 14px; border-radius: 8px; margin-bottom: 18px; font-size: 13px; text-align: center;">
            <?= $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">
        
        <div style="margin-bottom: 16px;">
            <label style="display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600;">অ্যাডমিন ইউজারনেম বা ইমেইল</label>
            <input type="text" name="username" required style="width: 100%; padding: 11px 14px; border: 1px solid #DCD5C8; border-radius: 8px; outline: none;">
        </div>

        <div style="margin-bottom: 24px;">
            <label style="display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600;">অ্যাডমিন পাসওয়ার্ড</label>
            <input type="password" name="password" required style="width: 100%; padding: 11px 14px; border: 1px solid #DCD5C8; border-radius: 8px; outline: none;">
        </div>

        <button type="submit" class="btn-hero-cinematic-primary" style="width: 100%; border: none; padding: 12px; font-size: 15px; cursor: pointer;">লগইন করুন</button>
    </form>
</div>

</body>
</html>