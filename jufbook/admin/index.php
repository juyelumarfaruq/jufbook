<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/includes/security.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';

require_admin();

$pdo = getDB();

// 1. Live Tracking & Metrics
$totalViews = 0;
$uniqueVisitors = 0;
$todayViews = 0;

try {
    $totalViews = (int)$pdo->query("SELECT COUNT(*) FROM site_analytics")->fetchColumn();
    $uniqueVisitors = (int)$pdo->query("SELECT COUNT(DISTINCT ip_address) FROM site_analytics")->fetchColumn();
    $todayViews = (int)$pdo->query("SELECT COUNT(*) FROM site_analytics WHERE visit_date = CURDATE() OR DATE(created_at) = CURDATE()")->fetchColumn();
} catch (Throwable $e) {}

$totalBooks = 0;
$totalAuthors = 0;
$totalAudiobooks = 0;
$totalReads = 0;

try {
    $totalBooks = (int)$pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
    $totalAuthors = (int)$pdo->query("SELECT COUNT(*) FROM authors")->fetchColumn();
    $totalAudiobooks = (int)$pdo->query("SELECT COUNT(*) FROM books WHERE is_audiobook = 1")->fetchColumn();
    $totalReads = (int)$pdo->query("SELECT COALESCE(SUM(views_count), 0) FROM books")->fetchColumn();
} catch (Throwable $e) {}

// 2. Top Performing Books
$topBooks = [];
try {
    $topBooksStmt = $pdo->query("
        SELECT b.id, b.title_bn, b.genre_tag_bn, b.theme_color, b.views_count, a.name_bn AS author_name 
        FROM books b 
        LEFT JOIN authors a ON b.author_id = a.id 
        ORDER BY b.views_count DESC, b.id DESC 
        LIMIT 5
    ");
    $topBooks = $topBooksStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (Throwable $e) {}

// 3. Recently Added Books
$recentBooks = [];
try {
    $recentBooksStmt = $pdo->query("
        SELECT b.id, b.title_bn, b.genre_tag_bn, b.theme_color, b.created_at, a.name_bn AS author_name 
        FROM books b 
        LEFT JOIN authors a ON b.author_id = a.id 
        ORDER BY b.id DESC 
        LIMIT 5
    ");
    $recentBooks = $recentBooksStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (Throwable $e) {}

$page_title = "Command Center — JUFbook Admin";
require_once __DIR__ . '/includes/header.php';
?>

<!-- 2D Hero Banner -->
<div class="adm-banner-card">
    <div class="adm-banner-blob"></div>
    <div class="adm-banner-content">
        <div class="adm-banner-tag">✦ 2D Minimalist Control Suite</div>
        <h2>Explore Redesigned JUFbook Admin</h2>
        <p>A unified, distraction-free environment to publish Bengali literature, manage chapters, audiobooks, and track live reading metrics.</p>
        <a href="books.php" class="adm-banner-btn">
            <span>+ Publish New Book</span>
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
        </a>
    </div>
</div>

<!-- 4-Stat 2D Cards Grid -->
<div class="adm-stats-grid">
    <div class="adm-stat-card">
        <div class="adm-stat-header">
            <div class="adm-stat-icon-wrap">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
            </div>
            <span style="font-size: 11px; font-weight: 800; color: var(--adm-text-subtle); letter-spacing: 0.5px;">BOOKS</span>
        </div>
        <div class="adm-stat-value"><?= number_format($totalBooks); ?></div>
        <div class="adm-stat-title">Published Books</div>
        <div class="adm-stat-trend">↗ Live in Catalog</div>
    </div>

    <div class="adm-stat-card">
        <div class="adm-stat-header">
            <div class="adm-stat-icon-wrap">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <span style="font-size: 11px; font-weight: 800; color: var(--adm-text-subtle); letter-spacing: 0.5px;">TODAY</span>
        </div>
        <div class="adm-stat-value"><?= number_format($todayViews); ?></div>
        <div class="adm-stat-title">Today's Page Views</div>
        <div class="adm-stat-trend">↗ Unique: <?= number_format($uniqueVisitors); ?></div>
    </div>

    <div class="adm-stat-card">
        <div class="adm-stat-header">
            <div class="adm-stat-icon-wrap">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </div>
            <span style="font-size: 11px; font-weight: 800; color: var(--adm-text-subtle); letter-spacing: 0.5px;">READS</span>
        </div>
        <div class="adm-stat-value"><?= number_format($totalReads); ?></div>
        <div class="adm-stat-title">Reader Impressions</div>
        <div class="adm-stat-trend">↗ Lifetime Views</div>
    </div>

    <div class="adm-stat-card">
        <div class="adm-stat-header">
            <div class="adm-stat-icon-wrap">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>
            </div>
            <span style="font-size: 11px; font-weight: 800; color: var(--adm-text-subtle); letter-spacing: 0.5px;">AUDIO</span>
        </div>
        <div class="adm-stat-value"><?= number_format($totalAudiobooks); ?></div>
        <div class="adm-stat-title">Audiobook Tracks</div>
        <div class="adm-stat-trend">↗ Streaming Active</div>
    </div>
</div>

<!-- 2-Column Split: Top Tables & Actions -->
<div class="adm-dashboard-grid">
    <div>
        <!-- Top Ranking Books Table -->
        <div class="adm-card-panel">
            <div class="adm-panel-head">
                <h3>🔥 Top Performing Books (Live Ranking)</h3>
                <span style="font-size: 11.5px; font-weight: 800; color: var(--adm-primary-deep); background: var(--adm-primary-soft); padding: 3px 8px; border-radius: 6px;">TOP 5</span>
            </div>

            <div class="adm-table-wrap">
                <table class="adm-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">Rank</th>
                            <th>Book Title</th>
                            <th>Author</th>
                            <th style="text-align: right;">Total Reads</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($topBooks)): ?>
                            <tr><td colspan="4" style="text-align: center; color: var(--adm-text-muted); padding: 24px;">No reading records found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($topBooks as $idx => $tb): ?>
                                <tr>
                                    <td><span class="rank-badge">#<?= $idx + 1 ?></span></td>
                                    <td class="bn-font" style="font-weight: 700; font-size: 14.5px;">
                                        <?= sanitizeOutput($tb['title_bn'] ?? ''); ?>
                                    </td>
                                    <td class="bn-font" style="color: var(--adm-text-muted); font-size: 13px;">
                                        <?= sanitizeOutput($tb['author_name'] ?? 'Unknown'); ?>
                                    </td>
                                    <td style="text-align: right; font-weight: 800; color: var(--adm-danger);">
                                        <?= number_format((int)($tb['views_count'] ?? 0)); ?> views
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recently Added Books -->
        <div class="adm-card-panel">
            <div class="adm-panel-head">
                <h3>Recently Published Books</h3>
                <a href="books.php" class="btn-adm-secondary" style="padding: 4px 10px; font-size: 12px;">View All →</a>
            </div>

            <div class="adm-table-wrap">
                <table class="adm-table">
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Author</th>
                            <th>Genre Tag</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentBooks)): ?>
                            <tr><td colspan="3" style="text-align: center; color: var(--adm-text-muted); padding: 24px;">No books available.</td></tr>
                        <?php else: ?>
                            <?php foreach ($recentBooks as $rb): ?>
                                <tr>
                                    <td class="bn-font" style="font-weight: 700; font-size: 14.5px;">
                                        <?= sanitizeOutput($rb['title_bn'] ?? ''); ?>
                                    </td>
                                    <td class="bn-font" style="color: var(--adm-text-muted); font-size: 13px;">
                                        <?= sanitizeOutput($rb['author_name'] ?? 'Unknown'); ?>
                                    </td>
                                    <td>
                                        <span class="genre-pill bn-font" style="background: <?= htmlspecialchars($rb['theme_color'] ?? '#243447', ENT_QUOTES, 'UTF-8'); ?>;">
                                            <?= sanitizeOutput($rb['genre_tag_bn'] ?? 'General'); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Quick Operations -->
    <div>
        <div class="adm-card-panel">
            <div class="adm-panel-head">
                <h3>Quick Operations</h3>
            </div>

            <a href="books.php" class="quick-action-link">
                <span>➕ Publish New Book</span>
                <span>→</span>
            </a>
            <a href="authors.php" class="quick-action-link">
                <span>✍️ Authors Directory</span>
                <span>→</span>
            </a>
            <a href="categories.php" class="quick-action-link">
                <span>🏷️ Manage Categories</span>
                <span>→</span>
            </a>

            <div style="background: var(--adm-primary-soft); border: 1.5px solid var(--adm-primary); padding: 16px; border-radius: var(--adm-radius-sm); margin-top: 14px;">
                <h4 style="font-size: 13px; color: var(--adm-primary-deep); font-weight: 800; margin-bottom: 4px;">✦ 2D Graphics Engine</h4>
                <p style="font-size: 12px; color: var(--adm-text-muted); line-height: 1.6;">
                    The system automatically generates balanced 2D book covers, spines, and author badges in the unified pastel theme.
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>