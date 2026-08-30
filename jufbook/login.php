<?php
declare(strict_types=1);
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    header("Location: profile.php");
    exit;
}

$pdo = getDB();
$error = '';
$success = '';

$action = isset($_GET['action']) && $_GET['action'] === 'register' ? 'register' : 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "নিরাপত্তা ত্রুটি। অনুগ্রহ করে আবার চেষ্টা করুন।";
    } else {
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = (string)($_POST['password'] ?? '');

        if (isset($_POST['register'])) {
            $name = sanitizeInput($_POST['name'] ?? '');
            if (empty($name) || empty($email) || empty($password)) {
                $error = "সবগুলো ফিল্ড পূরণ করা আবশ্যক।";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "সঠিক ইমেইল ঠিকানা প্রদান করুন।";
            } else {
                $check_stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
                $check_stmt->execute(['email' => $email]);
                
                if ($check_stmt->fetch()) {
                    $error = "এই ইমেইলটি ইতিমধ্যে ব্যবহৃত হয়েছে।";
                } else {
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    $insert_stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (:name, :email, :hash, 'reader')");
                    
                    if ($insert_stmt->execute(['name' => $name, 'email' => $email, 'hash' => $hash])) {
                        $success = "অ্যাকাউন্ট তৈরি সফল হয়েছে! এখন লগইন করুন।";
                        $action = 'login';
                    } else {
                        $error = "অ্যাকাউন্ট তৈরি করতে সমস্যা হয়েছে।";
                    }
                }
            }
        } elseif (isset($_POST['login'])) {
            $throttle = loginThrottleStatus('reader_login', 5, 900);
            if ($throttle['locked']) {
                $mins  = (int)ceil($throttle['remaining'] / 60);
                $error = "অতিরিক্ত ব্যর্থ চেষ্টার কারণে সাময়িকভাবে ব্লক করা হয়েছে। প্রায় {$mins} মিনিট পর আবার চেষ্টা করুন।";
            } elseif (empty($email) || empty($password)) {
                $error = "ইমেইল এবং পাসওয়ার্ড আবশ্যক।";
            } else {
                $stmt = $pdo->prepare("SELECT id, name, password_hash, role FROM users WHERE email = :email LIMIT 1");
                $stmt->execute(['email' => $email]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password_hash'])) {
                    loginThrottleReset('reader_login');
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['role'] = $user['role'] ?? 'reader';
                    
                    header("Location: profile.php");
                    exit;
                } else {
                    loginThrottleFail('reader_login', 5, 900);
                    $error = "ইমেইল অথবা পাসওয়ার্ড সঠিক নয়।";
                }
            }
        }
    }
}

$page_title = ($action === 'register' ? 'পাঠক রেজিস্ট্রেশন' : 'পাঠক লগইন') . " | JUFbook";
require_once __DIR__ . '/includes/header.php';
$csrf_token = generateCSRFToken();
?>

<div class="container" style="min-height: 65vh; display: flex; align-items: center; justify-content: center; margin-top: 40px; margin-bottom: 50px;">
    <div style="background: #FFFFFF; padding: 40px; border-radius: 20px; border: 1px solid #ECE5D8; box-shadow: 0 4px 20px rgba(0,0,0,0.02); width: 100%; max-width: 440px;">
        
        <div class="section-overline-label text-center">— পাঠক পোর্টাল —</div>
        <h2 class="page-section-title text-center" style="font-size: 24px; margin-bottom: 20px;">
            <?= $action === 'register' ? 'নতুন পাঠক অ্যাকাউন্ট' : 'পাঠক অ্যাকাউন্টে প্রবেশ' ?>
        </h2>

        <?php if ($error): ?>
            <div style="background: #FDE8E8; color: #B91C1C; padding: 10px 14px; border-radius: 8px; margin-bottom: 18px; font-size: 13px; text-align: center; font-family: var(--font-ui-system);">
                <?= $error; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div style="background: #EAF6ED; color: #1F6E3B; padding: 10px 14px; border-radius: 8px; margin-bottom: 18px; font-size: 13px; text-align: center; font-family: var(--font-ui-system);">
                <?= $success; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php<?= $action === 'register' ? '?action=register' : '' ?>">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">
            
            <?php if ($action === 'register'): ?>
                <div style="margin-bottom: 16px;">
                    <label style="display: block; margin-bottom: 6px; font-size: 13px; font-family: var(--font-ui); font-weight: 600;">আপনার নাম</label>
                    <input type="text" name="name" required style="width: 100%; padding: 10px 14px; border: 1px solid #DCD5C8; border-radius: 8px; font-family: var(--font-ui-system); outline: none;">
                </div>
            <?php endif; ?>

            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 6px; font-size: 13px; font-family: var(--font-ui); font-weight: 600;">ইমেইল ঠিকানা</label>
                <input type="email" name="email" required style="width: 100%; padding: 10px 14px; border: 1px solid #DCD5C8; border-radius: 8px; font-family: var(--font-ui-system); outline: none;">
            </div>

            <div style="margin-bottom: 22px;">
                <label style="display: block; margin-bottom: 6px; font-size: 13px; font-family: var(--font-ui); font-weight: 600;">পাসওয়ার্ড</label>
                <input type="password" name="password" required style="width: 100%; padding: 10px 14px; border: 1px solid #DCD5C8; border-radius: 8px; font-family: var(--font-ui-system); outline: none;">
            </div>

            <?php if ($action === 'register'): ?>
                <button type="submit" name="register" class="btn-hero-cinematic-primary" style="width: 100%; border: none; padding: 12px; cursor: pointer; font-size: 15px;">অ্যাকাউন্ট তৈরি করুন</button>
                <p style="text-align: center; margin-top: 18px; font-size: 13px; color: var(--text-muted); font-family: var(--font-ui-system);">
                    আগে থেকেই অ্যাকাউন্ট আছে? <a href="login.php" style="color: var(--accent-rust); font-weight: 600;">লগইন করুন</a>
                </p>
            <?php else: ?>
                <button type="submit" name="login" class="btn-hero-cinematic-primary" style="width: 100%; border: none; padding: 12px; cursor: pointer; font-size: 15px;">লগইন করুন</button>
                <p style="text-align: center; margin-top: 18px; font-size: 13px; color: var(--text-muted); font-family: var(--font-ui-system);">
                    নতুন পাঠক? <a href="login.php?action=register" style="color: var(--accent-rust); font-weight: 600;">অ্যাকাউন্ট তৈরি করুন</a>
                </p>
            <?php endif; ?>
        </form>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>