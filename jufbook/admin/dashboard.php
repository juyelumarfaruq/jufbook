<?php
$page_title = "ড্যাশবোর্ড | JUF Admin";
require_once 'includes/header.php';

$db = new Database();
$conn = $db->getConnection();

// Fetch quick statistics
$stats = [];

// Total Books
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM books");
$stmt->execute();
$stats['books'] = $stmt->fetch()['count'];

// Total Authors
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM authors");
$stmt->execute();
$stats['authors'] = $stmt->fetch()['count'];

// Total Users
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'reader'");
$stmt->execute();
$stats['users'] = $stmt->fetch()['count'];
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
    <h2>ওভারভিউ ড্যাশবোর্ড</h2>
    <span>হ্যালো, <?= sanitize_output($_SESSION['user_name']) ?> 👋</span>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 30px; margin-bottom: 50px;">
    <div class="stat-card">
        <div class="stat-number"><?= to_bengali_number($stats['books']) ?></div>
        <div class="stat-label">মোট বই</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-number"><?= to_bengali_number($stats['authors']) ?></div>
        <div class="stat-label">মোট লেখক</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-number"><?= to_bengali_number($stats['users']) ?></div>
        <div class="stat-label">মোট পাঠক</div>
    </div>
</div>

<div style="background: var(--card-bg); padding: 30px; border-radius: 8px; box-shadow: var(--shadow-soft);">
    <h3>অ্যাডমিন নির্দেশিকা</h3>
    <p style="color: var(--text-muted); margin-top: 15px; line-height: 1.8;">
        বাম দিকের মেনু ব্যবহার করে আপনি সম্পূর্ণ ওয়েবসাইট পরিচালনা করতে পারবেন।<br>
        - <strong>বই পরিচালনা:</strong> নতুন বই যুক্ত করা এবং অধ্যায় (Chapters) আপলোড করা।<br>
        - <strong>লেখক পরিচালনা:</strong> নতুন লেখকদের প্রোফাইল যুক্ত করা।<br>
        - <strong>সেটিংস:</strong> ওয়েবসাইটের বিভিন্ন অ্যাডভান্সড ফিচার (যেমন: অ্যান্টি-কপি, পেমেন্ট) চালু বা বন্ধ করা।
    </p>
</div>

<?php require_once 'includes/footer.php'; ?>