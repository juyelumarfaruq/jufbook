<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/config/constants.php';
require_once dirname(__DIR__, 2) . '/includes/security.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';

require_admin();
$pdo = getDB();

$currentScript = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitizeOutput($page_title ?? 'JUFbook Admin Suite'); ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Noto+Serif+Bengali:wght@600;700&display=swap">
    <link rel="stylesheet" href="assets/css/admin-theme.css?v=<?= time(); ?>">
    <style>
        .alert-box { padding: 12px 18px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; font-weight: 600; }
        .alert-success { background: var(--adm-primary-soft); color: var(--adm-primary-deep); border: 1.5px solid var(--adm-primary); }
        .alert-danger { background: var(--adm-danger-soft); color: var(--adm-danger); border: 1.5px solid #FCA5A5; }
    </style>
</head>
<body>

    <!-- 2D Sidebar Navigation -->
    <aside class="adm-sidebar" id="admSidebar">
        <a href="index.php" class="adm-logo-wrap">
            <div class="adm-logo-icon">J</div>
            <div class="adm-logo-text">JUF<span>book</span></div>
        </a>

        <div class="adm-user-card">
            <div class="adm-user-avatar">A</div>
            <div class="adm-user-meta">
                <div class="adm-user-name">Administrator</div>
                <div class="adm-user-role">Super Admin</div>
            </div>
        </div>

        <div class="adm-nav-group-title">Navigation</div>
        <ul class="adm-nav-list">
            <li class="adm-nav-item">
                <a href="index.php" class="adm-nav-link <?= $currentScript === 'index.php' ? 'active' : ''; ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    <span>Dashboard</span>
                    <span class="adm-nav-badge">Live</span>
                </a>
            </li>
        </ul>

        <div class="adm-nav-group-title">Library Management</div>
        <ul class="adm-nav-list">
            <li class="adm-nav-item">
                <a href="books.php" class="adm-nav-link <?= in_array($currentScript, ['books.php', 'chapters.php']) ? 'active' : ''; ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                    <span>Books & Chapters</span>
                </a>
            </li>
            <li class="adm-nav-item">
                <a href="bulk_books.php" class="adm-nav-link <?= in_array($currentScript, ['bulk_books.php', 'import_chapters.php']) ? 'active' : ''; ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    <span>Bulk CSV Upload</span>
                </a>
            </li>
            <li class="adm-nav-item">
                <a href="authors.php" class="adm-nav-link <?= $currentScript === 'authors.php' ? 'active' : ''; ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span>Authors</span>
                </a>
            </li>
            <li class="adm-nav-item">
                <a href="categories.php" class="adm-nav-link <?= $currentScript === 'categories.php' ? 'active' : ''; ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    <span>Categories & Genres</span>
                </a>
            </li>
            <li class="adm-nav-item">
                <a href="reviews.php" class="adm-nav-link <?= $currentScript === 'reviews.php' ? 'active' : ''; ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    <span>Reader Reviews</span>
                </a>
            </li>
        </ul>

        <div class="adm-nav-group-title">System & Control</div>
        <ul class="adm-nav-list">
            <li class="adm-nav-item">
                <a href="settings.php" class="adm-nav-link <?= $currentScript === 'settings.php' ? 'active' : ''; ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                    <span>Master Settings</span>
                </a>
            </li>
            <li class="adm-nav-item">
                <a href="../index.php" target="_blank" class="adm-nav-link">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    <span>Live Website</span>
                </a>
            </li>
            <li class="adm-nav-item">
                <a href="../logout.php" class="adm-nav-link" style="color: var(--adm-danger);">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Content Container -->
    <div class="adm-main-wrap">
        <header class="adm-topbar">
            <div style="display: flex; align-items: center; gap: 14px;">
                <button type="button" class="adm-icon-btn" id="sidebarToggle" onclick="document.getElementById('admSidebar').classList.toggle('show')">☰</button>
                <div class="adm-search-pill">
                    <span>🔍</span>
                    <input type="text" placeholder="Search books, authors, categories...">
                </div>
            </div>

            <div class="adm-top-actions">
                <a href="bulk_books.php" class="btn-adm-secondary" style="font-size: 12.5px;">📥 Bulk Upload</a>
                <a href="books.php" class="btn-adm-primary" style="padding: 7px 14px; font-size: 12.5px;">+ Publish Book</a>
            </div>
        </header>

        <main class="adm-content-body">