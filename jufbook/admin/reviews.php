<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/header.php';

$message = '';

// Moderate Action (Approve / Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = '<div class="alert-box alert-danger">Security token verification failed!</div>';
    } else {
        $rev_id = (int)($_POST['review_id'] ?? 0);
        $action = $_POST['action'];

        if ($action === 'approve') {
            $stmt = $pdo->prepare("UPDATE testimonials SET is_active = 1 WHERE id = :id");
            $stmt->execute([':id' => $rev_id]);
            $message = '<div class="alert-box alert-success">Review approved and published to homepage.</div>';
        } elseif ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM testimonials WHERE id = :id");
            $stmt->execute([':id' => $rev_id]);
            $message = '<div class="alert-box alert-success">Review deleted.</div>';
        }
    }
}

$reviews = $pdo->query("SELECT * FROM testimonials ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
$page_title = "Reader Reviews — JUFbook Admin";
?>

<?= $message ?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
    <div>
        <h2 style="font-size:22px; font-weight:800; color:var(--adm-ink); margin:0;">Reader Reviews & Testimonials</h2>
        <span style="font-size:13px; color:var(--adm-text-muted);">Moderate reader feedback before publishing to homepage</span>
    </div>
</div>

<div class="adm-card-panel">
    <div class="adm-panel-head">
        <h3>All Feedback (<?= count($reviews); ?>)</h3>
    </div>

    <div class="adm-table-wrap">
        <table class="adm-table">
            <thead>
                <tr>
                    <th>Reader</th>
                    <th>Location / Role</th>
                    <th>Feedback Text</th>
                    <th>Status</th>
                    <th style="text-align:right;">Moderation</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reviews)): ?>
                    <tr><td colspan="5" style="text-align:center; color:var(--adm-text-muted); padding:24px;">No reviews submitted yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($reviews as $r): ?>
                        <tr>
                            <td><strong class="bn-font"><?= sanitizeOutput($r['user_name_bn']) ?></strong></td>
                            <td class="bn-font" style="color:var(--adm-text-muted);"><?= sanitizeOutput($r['user_location_bn'] ?? 'পাঠক') ?></td>
                            <td class="bn-font" style="max-width:350px; font-size:13px; line-height:1.6;">
                                <?= sanitizeOutput($r['review_bn']) ?>
                            </td>
                            <td>
                                <span class="adm-badge-status <?= $r['is_active'] == 1 ? 'status-active' : 'status-draft' ?>">
                                    <?= $r['is_active'] == 1 ? 'Approved' : 'Pending' ?>
                                </span>
                            </td>
                            <td style="text-align:right; display:flex; justify-content:flex-end; gap:6px;">
                                <?php if ($r['is_active'] == 0): ?>
                                    <form method="POST" style="margin:0; display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <input type="hidden" name="review_id" value="<?= (int)$r['id'] ?>">
                                        <button type="submit" class="btn-adm-primary" style="padding:4px 8px; font-size:11.5px;">Approve</button>
                                    </form>
                                <?php endif; ?>

                                <form method="POST" style="margin:0; display:inline;" onsubmit="return confirm('Delete this review?');">
                                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="review_id" value="<?= (int)$r['id'] ?>">
                                    <button type="submit" class="btn-adm-danger" style="padding:4px 8px; font-size:11.5px;">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>