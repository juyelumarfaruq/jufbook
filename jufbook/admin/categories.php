<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/header.php';

$message = '';

// Delete Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = '<div class="alert-box alert-danger">Security token verification failed!</div>';
    } else {
        $del_id = (int)($_POST['category_id'] ?? 0);
        try {
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = :id");
            $stmt->execute([':id' => $del_id]);
            $message = '<div class="alert-box alert-success">Category successfully deleted.</div>';
        } catch (Throwable $e) {
            $message = '<div class="alert-box alert-danger">Cannot delete: This category is used by existing books.</div>';
        }
    }
}

// Add / Update Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['action']) || $_POST['action'] !== 'delete')) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = '<div class="alert-box alert-danger">Security token verification failed!</div>';
    } else {
        $id = (int)($_POST['id'] ?? 0);
        $name_bn = sanitizeInput($_POST['name_bn'] ?? '');
        $slug = sanitizeInput($_POST['slug'] ?? '');
        $description_bn = sanitizeInput($_POST['description_bn'] ?? '');
        $theme_color = sanitizeInput($_POST['theme_color'] ?? '#243447');

        if ($slug === '') {
            $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower($name_bn)) ?: 'cat-' . time();
        }

        if ($name_bn === '') {
            $message = '<div class="alert-box alert-danger">Category Name is required.</div>';
        } else {
            try {
                if ($id > 0) {
                    $stmt = $pdo->prepare("UPDATE categories SET name_bn = :name, slug = :slug, description_bn = :desc, theme_color = :color WHERE id = :id");
                    $stmt->execute([':name' => $name_bn, ':slug' => $slug, ':desc' => $description_bn, ':color' => $theme_color, ':id' => $id]);
                    $message = '<div class="alert-box alert-success">Category updated successfully.</div>';
                } else {
                    $stmt = $pdo->prepare("INSERT INTO categories (name_bn, slug, description_bn, theme_color) VALUES (:name, :slug, :desc, :color)");
                    $stmt->execute([':name' => $name_bn, ':slug' => $slug, ':desc' => $description_bn, ':color' => $theme_color]);
                    $message = '<div class="alert-box alert-success">New category created successfully.</div>';
                }
            } catch (Throwable $e) {
                $message = '<div class="alert-box alert-danger">Error: ' . sanitizeOutput($e->getMessage()) . '</div>';
            }
        }
    }
}

$categories = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM books WHERE category_id = c.id) AS book_count FROM categories c ORDER BY c.id DESC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
$page_title = "Categories & Genres — JUFbook Admin";
?>

<?= $message ?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
    <div>
        <h2 style="font-size:22px; font-weight:800; color:var(--adm-ink); margin:0;">Categories & Genres</h2>
        <span style="font-size:13px; color:var(--adm-text-muted);">Manage literature categories and 2D theme palettes</span>
    </div>
</div>

<div class="adm-form-grid" style="align-items:start;">
    <!-- Add/Edit Form -->
    <div class="adm-card-panel">
        <div class="adm-panel-head">
            <h3 id="formTitle">Create New Category</h3>
        </div>

        <form method="POST" id="catForm">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            <input type="hidden" name="id" id="cat_id" value="0">

            <div class="form-group">
                <label>Category Name (বাংলায়) *</label>
                <input type="text" name="name_bn" id="name_bn" class="form-control bn-font" placeholder="যেমন: উপন্যাস, কবিতা, গল্প" required>
            </div>

            <div class="form-group">
                <label>Category Slug (URL)</label>
                <input type="text" name="slug" id="slug" class="form-control" placeholder="যেমন: uponyas, kobita">
            </div>

            <div class="form-group">
                <label>2D Theme Color</label>
                <input type="color" name="theme_color" id="theme_color" class="form-control" style="height:42px; padding:4px;" value="#243447">
            </div>

            <div class="form-group">
                <label>Short Description (ঐচ্ছিক)</label>
                <textarea name="description_bn" id="description_bn" class="form-control bn-font" rows="3" placeholder="ক্যাটাগরির বিবরণ..."></textarea>
            </div>

            <button type="submit" id="submitBtn" class="btn-adm-primary" style="width:100%; justify-content:center;">Save Category</button>
            <button type="button" id="cancelBtn" onclick="resetCatForm()" class="btn-adm-secondary" style="width:100%; justify-content:center; margin-top:8px; display:none;">Cancel Edit</button>
        </form>
    </div>

    <!-- Category List Table -->
    <div class="adm-card-panel">
        <div class="adm-panel-head">
            <h3>Active Categories (<?= count($categories); ?>)</h3>
        </div>

        <div class="adm-table-wrap">
            <table class="adm-table">
                <thead>
                    <tr>
                        <th>Color</th>
                        <th>Category Name</th>
                        <th>Books</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                        <tr><td colspan="4" style="text-align:center; color:var(--adm-text-muted); padding:24px;">No categories available.</td></tr>
                    <?php else: ?>
                        <?php foreach ($categories as $c): ?>
                            <tr>
                                <td>
                                    <div style="width:24px; height:24px; border-radius:6px; background:<?= htmlspecialchars($c['theme_color'] ?? '#243447', ENT_QUOTES, 'UTF-8') ?>; border:1px solid rgba(0,0,0,0.15);"></div>
                                </td>
                                <td>
                                    <strong class="bn-font" style="font-size:14.5px; color:var(--adm-ink);"><?= sanitizeOutput($c['name_bn']) ?></strong>
                                    <small style="display:block; color:var(--adm-text-muted);"><?= sanitizeOutput($c['slug']) ?></small>
                                </td>
                                <td>
                                    <span class="genre-pill" style="background:var(--adm-primary-soft); color:var(--adm-primary-deep); border:1px solid var(--adm-primary);">
                                        <?= (int)$c['book_count'] ?> Books
                                    </span>
                                </td>
                                <td style="text-align:right; display:flex; justify-content:flex-end; gap:6px;">
                                    <button type="button" onclick='editCategory(<?= json_encode($c, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)' class="btn-adm-secondary" style="padding:4px 8px; font-size:11.5px; cursor:pointer;">Edit</button>
                                    
                                    <form method="POST" style="margin:0; display:inline;" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="category_id" value="<?= (int)$c['id'] ?>">
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
</div>

<script>
function editCategory(cat) {
    document.getElementById('cat_id').value = cat.id;
    document.getElementById('name_bn').value = cat.name_bn;
    document.getElementById('slug').value = cat.slug;
    document.getElementById('theme_color').value = cat.theme_color || '#243447';
    document.getElementById('description_bn').value = cat.description_bn || '';

    document.getElementById('formTitle').innerText = 'Edit Category';
    document.getElementById('submitBtn').innerText = 'Update Category';
    document.getElementById('cancelBtn').style.display = 'inline-flex';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function resetCatForm() {
    document.getElementById('catForm').reset();
    document.getElementById('cat_id').value = '0';
    document.getElementById('formTitle').innerText = 'Create New Category';
    document.getElementById('submitBtn').innerText = 'Save Category';
    document.getElementById('cancelBtn').style.display = 'none';
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>