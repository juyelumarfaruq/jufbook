<?php
$page_title = "Contact Us | JUF";
require_once 'config/database.php';
require_once 'includes/header.php';
?>

<div class="container" style="max-width: 800px; margin-top: 50px; margin-bottom: 80px; background: var(--card-bg); padding: 40px; border-radius: 12px; box-shadow: var(--shadow-soft);">
    <h1 style="margin-bottom: 10px; text-align: center;">যোগাযোগ করুন</h1>
    <p style="text-align: center; color: var(--text-muted); margin-bottom: 30px;">যেকোনো সমস্যা, পরামর্শ বা অভিযোগ জানাতে আমাদের সাথে যোগাযোগ করুন।</p>
    
    <form method="POST" action="contact.php" style="max-width: 500px; margin: 0 auto;">
        <!-- Demo Form (Does not send real email on localhost without SMTP config) -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-size: 14px; color: var(--text-muted);">আপনার নাম</label>
            <input type="text" name="name" required style="width: 100%; padding: 12px; border: 1px solid var(--border-light); border-radius: 4px; font-family: var(--font-sans);">
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-size: 14px; color: var(--text-muted);">ইমেইল ঠিকানা</label>
            <input type="email" name="email" required style="width: 100%; padding: 12px; border: 1px solid var(--border-light); border-radius: 4px; font-family: var(--font-sans);">
        </div>

        <div style="margin-bottom: 25px;">
            <label style="display: block; margin-bottom: 8px; font-size: 14px; color: var(--text-muted);">আপনার বার্তা</label>
            <textarea name="message" rows="5" required style="width: 100%; padding: 12px; border: 1px solid var(--border-light); border-radius: 4px; font-family: var(--font-sans);"></textarea>
        </div>

        <button type="submit" class="btn-primary" style="width: 100%; font-size: 16px; padding: 14px;">মেসেজ পাঠান</button>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>