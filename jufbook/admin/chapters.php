<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/includes/security.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';

require_admin();
$pdo = getDB();
$message = '';

$book_id = isset($_GET['book_id']) ? (int)$_GET['book_id'] : 0;
if ($book_id <= 0) {
    header("Location: books.php");
    exit;
}

// বইয়ের ক্যানোনিকাল শিরোনাম লোড
$book_stmt = $pdo->prepare("SELECT id, title_bn FROM books WHERE id = :id");
$book_stmt->execute([':id' => $book_id]);
$book_data = $book_stmt->fetch(PDO::FETCH_ASSOC);

if (!$book_data) {
    header("Location: books.php");
    exit;
}
$book_title = sanitizeOutput($book_data['title_bn']);

// POST-Based Secure Deletion (Roadmap Phase 1 Security Rule)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = '<div class="alert-box alert-danger">Security verification failed!</div>';
    } else {
        $delete_id = (int)($_POST['chapter_id'] ?? 0);
        $del_stmt = $pdo->prepare("DELETE FROM chapters WHERE id = :id AND book_id = :book_id");
        if ($del_stmt->execute([':id' => $delete_id, ':book_id' => $book_id])) {
            $message = '<div class="alert-box alert-success">অধ্যায় সফলভাবে মুছে ফেলা হয়েছে।</div>';
        }
    }
}

// Add / Update Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['action']) || $_POST['action'] !== 'delete')) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = '<div class="alert-box alert-danger">Security verification failed!</div>';
    } else {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $chapter_title = sanitizeInput($_POST['chapter_title_bn'] ?? '');
        $chapter_number = (int)($_POST['chapter_number'] ?? 1);
        $audio_file_url = sanitizeInput($_POST['audio_file_url'] ?? '');
        $status = ($_POST['status'] ?? '') === 'draft' ? 'draft' : 'published';
        $content = sanitizeRichText($_POST['content'] ?? ''); // স্ট্রিক্ট স্যানিটাইজেশন

        if ($chapter_title === '' || $content === '') {
            $message = '<div class="alert-box alert-danger">শিরোনাম ও অধ্যায়ের মূল লেখা আবশ্যক।</div>';
        } else {
            try {
                if ($id > 0) {
                    $sql = "UPDATE chapters SET chapter_title_bn = :title, chapter_number = :num, content = :content, audio_file_url = :audio, status = :status WHERE id = :id AND book_id = :book_id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ':title'   => $chapter_title,
                        ':num'     => $chapter_number,
                        ':content' => $content,
                        ':audio'   => $audio_file_url,
                        ':status'  => $status,
                        ':id'      => $id,
                        ':book_id' => $book_id
                    ]);
                    $message = '<div class="alert-box alert-success">অধ্যায় সফলভাবে আপডেট করা হয়েছে।</div>';
                } else {
                    $sql = "INSERT INTO chapters (book_id, chapter_title_bn, chapter_number, content, audio_file_url, status) VALUES (:book_id, :title, :num, :content, :audio, :status)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ':book_id' => $book_id,
                        ':title'   => $chapter_title,
                        ':num'     => $chapter_number,
                        ':content' => $content,
                        ':audio'   => $audio_file_url,
                        ':status'  => $status
                    ]);
                    $message = '<div class="alert-box alert-success">নতুন অধ্যায় যুক্ত হয়েছে।</div>';
                }
            } catch (PDOException $e) {
                $message = '<div class="alert-box alert-danger">ডেটাবেস ত্রুটি: ' . sanitizeOutput($e->getMessage()) . '</div>';
            }
        }
    }
}

// অধ্যায় তালিকা ফেচিং
$chap_stmt = $pdo->prepare("SELECT id, chapter_title_bn, chapter_number, content, audio_file_url, status FROM chapters WHERE book_id = :book_id ORDER BY chapter_number ASC");
$chap_stmt->execute([':book_id' => $book_id]);
$chapters = $chap_stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

$page_title = "Manage Chapters — JUFbook Admin";
require_once __DIR__ . '/includes/header.php';
?>

<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    .ql-toolbar.ql-snow { border-radius: 8px 8px 0 0; border-color: #E2E8F0; background: #FFF; }
    .ql-container.ql-snow { border-radius: 0 0 8px 8px; border-color: #E2E8F0; background: #FFF; }
    .ql-editor { font-family: 'Noto Serif Bengali', serif; font-size: 16px; min-height: 250px; line-height: 1.8; }
</style>

<?= $message ?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
    <div>
        <span style="font-size:13px; color:var(--adm-text-muted);">Book Index & Chapter Management</span>
        <h2 class="bn-font" style="font-size:22px; font-weight:800; color:var(--adm-text-main); margin:0;"><?= $book_title ?></h2>
    </div>
    <div style="display:flex; gap:10px;">
        <a href="books.php" class="btn-adm-secondary">← Back to Books</a>
        <a href="import_chapters.php?book_id=<?= $book_id ?>" class="btn-adm-primary" style="background:#059669;">📥 Import via CSV</a>
    </div>
</div>

<div class="adm-form-grid" style="align-items:start;">
    <!-- Add/Edit Form -->
    <div class="adm-card-panel">
        <div class="adm-panel-head">
            <h3 id="formHeading">Add Chapter</h3>
        </div>

        <form method="POST" id="chapterForm">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            <input type="hidden" name="id" id="chapter_id" value="0">
            
            <div style="display:grid; grid-template-columns: 1fr 3fr; gap:14px; margin-bottom:14px;">
                <div class="form-group" style="margin-bottom:0;">
                    <label>Chapter No. *</label>
                    <input type="number" name="chapter_number" id="chapter_number" value="<?= count($chapters) + 1 ?>" required class="form-control">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>Chapter Title (বাংলায়) *</label>
                    <input type="text" name="chapter_title_bn" id="chapter_title_bn" required class="form-control bn-font" placeholder="যেমন: প্রথম পরিচ্ছেদ">
                </div>
            </div>

            <div class="form-group">
                <label>Reading Content (বইয়ের মূল গল্প/লেখা) *</label>
                <div id="editor-container"></div>
                <input type="hidden" name="content" id="hiddenContent">
            </div>

            <div class="form-group">
                <label>Audio URL (Optional)</label>
                <input type="url" name="audio_file_url" id="audio_file_url" class="form-control" placeholder="https://domain.com/audio.mp3">
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status" id="status" class="form-control">
                    <option value="published">Published</option>
                    <option value="draft">Draft</option>
                </select>
            </div>

            <button type="submit" id="submitChapterBtn" class="btn-adm-primary" style="width:100%; justify-content:center;">Save Chapter</button>
            <button type="button" id="cancelChapterBtn" onclick="resetChapterForm()" class="btn-adm-secondary" style="width:100%; justify-content:center; margin-top:8px; display:none;">Cancel Edit</button>
        </form>
    </div>

    <!-- Chapter Directory Table -->
    <div class="adm-card-panel">
        <div class="adm-panel-head">
            <h3>Chapter Index (<?= count($chapters); ?>)</h3>
        </div>

        <div class="adm-table-wrap">
            <table class="adm-table">
                <thead>
                    <tr>
                        <th style="width:60px;">No.</th>
                        <th>Chapter Title</th>
                        <th>Status</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($chapters)): ?>
                        <tr><td colspan="4" style="text-align:center; color:var(--adm-text-muted); padding:24px;">No chapters found. Add the first chapter on the left.</td></tr>
                    <?php else: ?>
                        <?php foreach($chapters as $c): ?>
                        <tr>
                            <td><span class="rank-badge">#<?= (int)$c['chapter_number'] ?></span></td>
                            <td>
                                <strong class="bn-font" style="font-size:14.5px; color:var(--adm-text-main); display:block;"><?= sanitizeOutput($c['chapter_title_bn']) ?></strong>
                                <small style="color:var(--adm-text-subtle);"><?= mb_strlen(strip_tags($c['content'] ?? '')) ?> characters</small>
                            </td>
                            <td>
                                <span class="adm-badge-status <?= $c['status'] === 'published' ? 'status-active' : 'status-draft' ?>">
                                    <?= ucfirst($c['status'] ?? 'published') ?>
                                </span>
                            </td>
                            <td style="text-align:right; display:flex; justify-content:flex-end; gap:6px;">
                                <button type="button" onclick='editChapter(<?= json_encode($c, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)' class="btn-adm-secondary" style="padding:4px 8px; font-size:11.5px; cursor:pointer;">Edit</button>
                                
                                <form method="POST" style="margin:0; display:inline;" onsubmit="return confirm('অধ্যায়টি নিশ্চিতভাবে মুছে ফেলতে চান?');">
                                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="chapter_id" value="<?= (int)$c['id'] ?>">
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

<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
    var quill = new Quill('#editor-container', {
        theme: 'snow',
        placeholder: 'এখানে অধ্যায়ের মূল গল্প বা লেখা পেস্ট করুন...',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline'],
                [{ 'header': [2, 3, false] }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['blockquote'],
                ['clean']
            ]
        }
    });

    document.getElementById('chapterForm').onsubmit = function() {
        document.getElementById('hiddenContent').value = quill.root.innerHTML;
        return true;
    };

    function editChapter(chap) {
        document.getElementById('chapter_id').value = chap.id;
        document.getElementById('chapter_number').value = chap.chapter_number;
        document.getElementById('chapter_title_bn').value = chap.chapter_title_bn;
        document.getElementById('audio_file_url').value = chap.audio_file_url || '';
        document.getElementById('status').value = chap.status || 'published';
        
        quill.root.innerHTML = chap.content || '';

        document.getElementById('formHeading').innerText = 'Edit Chapter';
        document.getElementById('submitChapterBtn').innerText = 'Update Chapter';
        document.getElementById('cancelChapterBtn').style.display = 'inline-flex';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function resetChapterForm() {
        document.getElementById('chapterForm').reset();
        document.getElementById('chapter_id').value = '0';
        quill.root.innerHTML = '';
        document.getElementById('formHeading').innerText = 'Add Chapter';
        document.getElementById('submitChapterBtn').innerText = 'Save Chapter';
        document.getElementById('cancelChapterBtn').style.display = 'none';
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>