<?php
declare(strict_types=1);
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = getDB();
$csrf_token = generateCSRFToken();

$categories = $pdo->query("SELECT * FROM categories ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

// পেজিনেশন হ্যান্ডলার
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$totalBooks = (int)$pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
$totalPages = max(1, (int)ceil($totalBooks / $limit));

$showcaseStmt = $pdo->prepare("
    SELECT b.*, a.name_bn AS author_name 
    FROM books b 
    LEFT JOIN authors a ON b.author_id = a.id 
    ORDER BY b.id ASC 
    LIMIT :limit OFFSET :offset
");
$showcaseStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$showcaseStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$showcaseStmt->execute();
$showcaseBooks = $showcaseStmt->fetchAll(PDO::FETCH_ASSOC);

$audiobooks = $pdo->query("SELECT b.*, a.name_bn AS author_name FROM books b LEFT JOIN authors a ON b.author_id = a.id WHERE b.is_audiobook = 1 ORDER BY b.id DESC LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);

// সদ্য প্রকাশিত
$newReleases = $pdo->query("SELECT b.*, a.name_bn AS author_name FROM books b LEFT JOIN authors a ON b.author_id = a.id WHERE b.is_new_release = 1 ORDER BY b.id DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
if (count($newReleases) < 10) {
    $needed = 10 - count($newReleases);
    $existingIds = !empty($newReleases) ? array_map('intval', array_column($newReleases, 'id')) : [];
    
    if (!empty($existingIds)) {
        $placeholders = implode(',', array_fill(0, count($existingIds), '?'));
        $extraStmt = $pdo->prepare("SELECT b.*, a.name_bn AS author_name FROM books b LEFT JOIN authors a ON b.author_id = a.id WHERE b.id NOT IN ($placeholders) ORDER BY b.id DESC LIMIT ?");
        foreach ($existingIds as $k => $id) {
            $extraStmt->bindValue($k + 1, $id, PDO::PARAM_INT);
        }
        $extraStmt->bindValue(count($existingIds) + 1, (int)$needed, PDO::PARAM_INT);
    } else {
        $extraStmt = $pdo->prepare("SELECT b.*, a.name_bn AS author_name FROM books b LEFT JOIN authors a ON b.author_id = a.id ORDER BY b.id DESC LIMIT ?");
        $extraStmt->bindValue(1, (int)$needed, PDO::PARAM_INT);
    }
    $extraStmt->execute();
    $newReleases = array_merge($newReleases, $extraStmt->fetchAll(PDO::FETCH_ASSOC));
}

$allAuthors = $pdo->query("SELECT * FROM authors ORDER BY id ASC LIMIT 15")->fetchAll(PDO::FETCH_ASSOC);

// পাঠকের রিভিউ
$testimonials = [];
try {
    $testimonials = $pdo->query("SELECT * FROM testimonials WHERE is_active = 1 ORDER BY id DESC LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $testimonials = [];
}

$heroBooks = array_slice($showcaseBooks, 0, 5);

function getBengaliInitialChar(string $str): string {
    return mb_substr(trim(strip_tags($str)), 0, 1, 'UTF-8');
}

$page_title = "JUFbook - বাংলা ই-বুক ও অডিওবুক";
require_once __DIR__ . '/includes/header.php';
?>

        <!-- 2. Cinematic Expanding Cards Hero Carousel -->
        <section class="hero-cinematic-stage" id="hero-stage">
            <div class="hero-cinematic-slider" id="hero-cinematic-slider">
                <?php foreach ($heroBooks as $idx => $hb): 
                    $isActive = ($idx === 0) ? 'active' : '';
                    $bgColor = htmlspecialchars($hb['theme_color'] ?? '#243447', ENT_QUOTES, 'UTF-8');
                ?>
                    <div class="hero-slide-item <?= $isActive; ?>" data-theme="<?= $bgColor; ?>" style="--slide-theme: <?= $bgColor; ?>;">
                        <div class="hero-bg-art-layer">
                            <div class="hero-art-mesh-gradient"></div>
                            <div class="hero-art-blob-1"></div>
                            <div class="hero-art-blob-2"></div>
                            <div class="hero-art-grid-lines"></div>
                        </div>

                        <div class="hero-slide-info">
                            <span class="hero-slide-badge">— <?= sanitizeOutput($hb['genre_tag_bn'] ?? 'বিশেষ সংকলন'); ?> —</span>
                            <h1 class="hero-slide-title"><?= sanitizeOutput($hb['title_bn']); ?></h1>
                            <div class="hero-slide-author">লেখক: <strong><?= sanitizeOutput($hb['author_name'] ?? 'অজ্ঞাত'); ?></strong></div>
                            <p class="hero-slide-desc">
                                <?= sanitizeOutput($hb['short_desc_bn'] ?? 'বাঙালির অমর সাহিত্য সম্ভারের এক কালজয়ী সৃষ্টি। ক্লাসিক সাহিত্য পাঠের প্রিমিয়াম ডিজিটাল অভিজ্ঞতা উপভোগ করুন।'); ?>
                            </p>
                            <div class="hero-slide-actions">
                                <a href="details.php?id=<?= (int)$hb['id']; ?>" class="btn-hero-cinematic-primary">পড়তে শুরু করুন</a>
                                <a href="details.php?id=<?= (int)$hb['id']; ?>#audiobook" class="btn-hero-cinematic-secondary">অডিওবুক শুনুন</a>
                            </div>
                        </div>

                        <div class="hero-slide-visual">
                            <div class="juf-book-jacket hero-jacket" style="background-color: <?= $bgColor; ?>; padding: 0; overflow: hidden;">
                                <?php if (!empty($hb['cover_image']) && file_exists(__DIR__ . '/uploads/covers/' . $hb['cover_image'])): ?>
                                    <img src="uploads/covers/<?= htmlspecialchars($hb['cover_image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= sanitizeOutput($hb['title_bn']); ?>" style="width:100%; height:100%; object-fit:cover; display:block;">
                                <?php else: ?>
                                    <div style="padding: 16px 14px; height: 100%; display: flex; flex-direction: column; justify-content: flex-end; position: relative;">
                                        <svg style="position:absolute; inset:0; width:100%; height:100%; pointer-events:none; opacity:0.18;" viewBox="0 0 200 280">
                                            <circle cx="160" cy="50" r="70" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-dasharray="4 3" />
                                            <line x1="25" y1="120" x2="175" y2="120" stroke="#FFFFFF" stroke-width="1.5" />
                                        </svg>
                                        <span class="juf-jacket-genre"><?= sanitizeOutput($hb['genre_tag_bn'] ?? 'উপন্যাস'); ?></span>
                                        <div class="juf-jacket-title"><?= sanitizeOutput($hb['title_bn']); ?></div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="hero-thumb-queue" id="hero-thumb-queue">
                <?php foreach ($heroBooks as $idx => $hb): ?>
                    <div class="hero-thumb-card <?= ($idx === 0) ? 'current-thumb' : ''; ?>" data-slide-index="<?= $idx; ?>" style="--thumb-bg: <?= htmlspecialchars($hb['theme_color'] ?? '#243447', ENT_QUOTES, 'UTF-8'); ?>; overflow:hidden;">
                        <?php if (!empty($hb['cover_image']) && file_exists(__DIR__ . '/uploads/covers/' . $hb['cover_image'])): ?>
                            <img src="uploads/covers/<?= htmlspecialchars($hb['cover_image'], ENT_QUOTES, 'UTF-8'); ?>" alt="" style="width:100%; height:100%; object-fit:cover;">
                        <?php else: ?>
                            <div class="thumb-mini-jacket">
                                <span><?= sanitizeOutput($hb['title_bn']); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="hero-cinematic-arrows">
                <button class="hero-cinematic-arrow-btn" id="hero-cine-prev" aria-label="পূর্ববর্তী বই">‹</button>
                <button class="hero-cinematic-arrow-btn" id="hero-cine-next" aria-label="পরবর্তী বই">›</button>
            </div>
        </section>

        <!-- 3. Search Bar Row -->
        <section class="search-pill-row">
            <form action="search.php" method="GET" class="search-input-box" style="margin: 0;">
                <span style="cursor: pointer;" onclick="this.closest('form').submit();">🔍</span>
                <input type="text" name="q" placeholder="বই, লেখক বা জনরা খুঁজুন..." required>
            </form>
            <div class="search-tags-nav">
                <a href="#showcase">Books</a>
                <a href="#authors">Authors</a>
                <a href="#genres">Genres</a>
                <a href="search.php">Lists</a>
            </div>
        </section>

        <!-- 4. Authors Pastel Cards Slider -->
        <section style="margin-bottom: 40px;">
            <div class="section-title-wrap-flex">
                <h2 class="page-section-title" style="margin-bottom: 0;">Authors</h2>
                <div class="slider-nav-ctrl">
                    <button class="slider-ctrl-btn" onclick="scrollTrack('pastel-track', -340)" aria-label="Previous">‹</button>
                    <button class="slider-ctrl-btn" onclick="scrollTrack('pastel-track', 340)" aria-label="Next">›</button>
                </div>
            </div>

            <div class="sub-tabs-row" id="author-filter-tabs">
                <button class="sub-tab-item active" onclick="filterPastel('all', this)">Top Authors</button>
                <button class="sub-tab-item" onclick="filterPastel('indic', this)">Favourite Indic Authors</button>
                <button class="sub-tab-item" onclick="filterPastel('female', this)">Female Authors</button>
                <button class="sub-tab-item" onclick="filterPastel('greek', this)">Greek Originals</button>
                <button class="sub-tab-item" onclick="filterPastel('epic', this)">Epic Authors</button>
            </div>

            <div class="pastel-authors-slider-track" id="pastel-track">
                <a href="search.php?q=রবীন্দ্রনাথ" class="pastel-card pastel-card-mint anim-card" data-category="indic all" style="text-decoration:none;">
                    <h3>Top Authors</h3>
                    <p>রবীন্দ্রনাথ, শরৎচন্দ্র ও শীর্ষ কথাসাহিত্যিকবৃন্দ</p>
                </a>
                <a href="search.php?q=মহাশ্বেতা" class="pastel-card pastel-card-peach anim-card" data-category="female all" style="text-decoration:none;">
                    <h3>Female Authors</h3>
                    <p>মহাশ্বেতা দেবী, আশাপূর্ণা দেবী ও প্রথিতযশা লেখিকাবৃন্দ</p>
                </a>
                <a href="search.php?q=হোমার" class="pastel-card pastel-card-coral anim-card" data-category="greek all" style="text-decoration:none;">
                    <h3>Greek Originals</h3>
                    <p>কালজয়ী ঐতিহাসিক সাহিত্য ও অনুবাদ সমগ্র</p>
                </a>
                <a href="search.php?q=মাইকেল" class="pastel-card pastel-card-lavender anim-card" data-category="epic all" style="text-decoration:none;">
                    <h3>Epic Authors</h3>
                    <p>মহাকাব্য ও কালজয়ী সাহিত্যের দিকপালগণ</p>
                </a>
                <a href="authors.php" class="pastel-card anim-card" data-category="all" style="background:#EAE4D6; text-decoration:none; text-align:center; justify-content:center; align-items:center;">
                    <h3 style="color:var(--accent-rust);">View All →</h3>
                    <p>সকল লেখকের প্রোফাইল দেখুন</p>
                </a>
            </div>
        </section>

        <!-- 5. Audiobook Slider -->
        <section id="audiobook" style="margin-bottom: 40px;">
            <div class="section-title-wrap-flex">
                <h2 class="page-section-title" style="margin-bottom: 0;">Audiobook</h2>
                <div class="slider-nav-ctrl">
                    <button class="slider-ctrl-btn" onclick="scrollTrack('audio-track', -300)" aria-label="Previous">‹</button>
                    <button class="slider-ctrl-btn" onclick="scrollTrack('audio-track', 300)" aria-label="Next">›</button>
                </div>
            </div>
            <div class="audiobook-slider-track" id="audio-track">
                <?php foreach ($audiobooks as $ab): ?>
                    <a href="details.php?id=<?= (int)$ab['id']; ?>" class="audio-pill-card anim-card" style="text-decoration: none;">
                        <div class="audio-cover-sq" style="background: <?= htmlspecialchars($ab['theme_color'] ?? '#243447', ENT_QUOTES, 'UTF-8'); ?>; overflow: hidden;">
                            <?php if (!empty($ab['cover_image']) && file_exists(__DIR__ . '/uploads/covers/' . $ab['cover_image'])): ?>
                                <img src="uploads/covers/<?= htmlspecialchars($ab['cover_image'], ENT_QUOTES, 'UTF-8'); ?>" alt="" loading="lazy" style="width:100%; height:100%; object-fit:cover;">
                            <?php else: ?>
                                <?= getBengaliInitialChar($ab['title_bn']); ?>
                            <?php endif; ?>
                        </div>
                        <div class="audio-details">
                            <h4><?= sanitizeOutput($ab['title_bn']); ?></h4>
                            <p><?= sanitizeOutput($ab['author_name'] ?? 'অজ্ঞাত'); ?> • <?= sanitizeOutput($ab['audio_duration_bn'] ?? '৪৫ মি.'); ?></p>
                        </div>
                        <div class="audio-play-circle">▶</div>
                    </a>
                <?php endforeach; ?>
                <a href="search.php?type=audio" class="audio-pill-card anim-card" style="text-decoration:none; background:var(--accent-rust); color:#fff; justify-content:center; text-align:center;">
                    <div style="font-weight:700; font-size:14px; color:#fff;">All Audiobooks →</div>
                </a>
            </div>
        </section>

        <!-- 6. Authors by Genres -->
        <section id="genres" style="margin-bottom: 40px;">
            <div class="section-title-wrap-flex">
                <h2 class="page-section-title" style="margin-bottom: 0;">Authors by Genres</h2>
                <div class="slider-nav-ctrl">
                    <button class="slider-ctrl-btn" onclick="scrollTrack('genre-track', -200)" aria-label="Previous">‹</button>
                    <button class="slider-ctrl-btn" onclick="scrollTrack('genre-track', 200)" aria-label="Next">›</button>
                </div>
            </div>
            <div class="genres-slider-track" id="genre-track">
                <a href="search.php?genre=উপন্যাস" class="genre-dark-box anim-card" style="text-decoration:none;"><h4>উপন্যাস</h4><span>১২০+ লেখক</span></a>
                <a href="search.php?genre=কবিতা" class="genre-dark-box anim-card" style="text-decoration:none;"><h4>কবিতা</h4><span>৮৫+ কবি</span></a>
                <a href="search.php?genre=গোয়েন্দা" class="genre-dark-box anim-card" style="text-decoration:none;"><h4>গোয়েন্দা</h4><span>৪০+ লেখক</span></a>
                <a href="search.php?genre=ইতিহাস" class="genre-dark-box anim-card" style="text-decoration:none;"><h4>ইতিহাস</h4><span>৩০+ গবেষক</span></a>
                <a href="search.php?genre=সায়েন্স ফিকশন" class="genre-dark-box anim-card" style="text-decoration:none;"><h4>সায়েন্স ফিকশন</h4><span>২৫+ লেখক</span></a>
                <a href="search.php?genre=প্রবন্ধ" class="genre-dark-box anim-card" style="text-decoration:none;"><h4>প্রবন্ধ</h4><span>৫০+ লেখক</span></a>
                <a href="search.php" class="genre-dark-box anim-card" style="text-decoration:none; background:var(--accent-rust);"><h4>View All →</h4><span>সব জনরা</span></a>
            </div>
        </section>

        <!-- 7. Books Showcase (5x4 Grid - 20 Books + Pagination) -->
        <section id="showcase" class="books-showcase-wrapper">
            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 24px;">
                <div>
                    <div class="section-overline-label">— লাইব্রেরি কালেকশন —</div>
                    <h2 class="page-section-title" style="margin-bottom: 0;">Books Showcase</h2>
                </div>
                <div style="font-size: 13px; color: var(--text-muted); font-family: var(--font-ui-system);">
                    মোট <strong style="color: var(--accent-rust);"><?= $totalBooks; ?></strong> টি বই
                </div>
            </div>
            
            <div id="showcase-grid-container" class="grid-5-col">
                <?php foreach ($showcaseBooks as $b): ?>
                    <a href="details.php?id=<?= (int)$b['id']; ?>" class="showcase-card-item anim-card">
                        <div class="juf-book-jacket" style="background-color: <?= htmlspecialchars($b['theme_color'] ?? '#2B3A42', ENT_QUOTES, 'UTF-8'); ?>; padding: 0; overflow: hidden;">
                            <?php if (!empty($b['cover_image']) && file_exists(__DIR__ . '/uploads/covers/' . $b['cover_image'])): ?>
                                <img src="uploads/covers/<?= htmlspecialchars($b['cover_image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= sanitizeOutput($b['title_bn']); ?>" loading="lazy" style="width:100%; height:100%; object-fit:cover; display:block;">
                            <?php else: ?>
                                <div style="padding: 16px 14px; height: 100%; display: flex; flex-direction: column; justify-content: flex-end; position: relative;">
                                    <svg style="position:absolute; inset:0; width:100%; height:100%; pointer-events:none; opacity:0.18;" viewBox="0 0 200 280">
                                        <circle cx="150" cy="50" r="60" fill="none" stroke="#FFFFFF" stroke-width="1.8" stroke-dasharray="3 3" />
                                        <line x1="20" y1="120" x2="180" y2="120" stroke="#FFFFFF" stroke-width="1.5" />
                                    </svg>
                                    <span class="juf-jacket-genre"><?= sanitizeOutput($b['genre_tag_bn'] ?? 'উপন্যাস'); ?></span>
                                    <div class="juf-jacket-title"><?= sanitizeOutput($b['title_bn']); ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="book-under-meta">
                            <div class="book-under-title"><?= sanitizeOutput($b['title_bn']); ?></div>
                            <div class="book-under-author"><?= sanitizeOutput($b['author_name'] ?? 'অজ্ঞাত'); ?></div>
                            <div class="book-rating-gold">★★★★★</div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Showcase Pagination Navigation -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination-numbers">
                    <?php if ($page > 1): ?>
                        <a href="index.php?page=<?= $page - 1; ?>#showcase" class="page-num-btn" style="text-decoration:none;">« Prev</a>
                    <?php endif; ?>

                    <?php 
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    for ($i = $startPage; $i <= $endPage; $i++): 
                    ?>
                        <a href="index.php?page=<?= $i; ?>#showcase" class="page-num-btn <?= $i === $page ? 'active' : ''; ?>" style="text-decoration:none;"><?= $i; ?></a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="index.php?page=<?= $page + 1; ?>#showcase" class="page-num-btn" style="text-decoration:none;">Next »</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- 8. সদ্য প্রকাশিত -->
        <section style="margin-bottom: 50px;">
            <div class="section-overline-label">— নতুন সংযোজন —</div>
            <h2 class="page-section-title" style="margin-bottom: 20px;">সদ্য প্রকাশিত</h2>
            <div class="grid-5-col">
                <?php foreach ($newReleases as $nb): ?>
                    <a href="details.php?id=<?= (int)$nb['id']; ?>" class="showcase-card-item anim-card">
                        <div class="juf-book-jacket" style="background-color: <?= htmlspecialchars($nb['theme_color'] ?? '#2B3A42', ENT_QUOTES, 'UTF-8'); ?>; padding: 0; overflow: hidden;">
                            <?php if (!empty($nb['cover_image']) && file_exists(__DIR__ . '/uploads/covers/' . $nb['cover_image'])): ?>
                                <img src="uploads/covers/<?= htmlspecialchars($nb['cover_image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= sanitizeOutput($nb['title_bn']); ?>" loading="lazy" style="width:100%; height:100%; object-fit:cover; display:block;">
                            <?php else: ?>
                                <div style="padding: 16px 14px; height: 100%; display: flex; flex-direction: column; justify-content: flex-end; position: relative;">
                                    <svg style="position:absolute; inset:0; width:100%; height:100%; pointer-events:none; opacity:0.18;" viewBox="0 0 200 280">
                                        <circle cx="150" cy="50" r="60" fill="none" stroke="#FFFFFF" stroke-width="1.8" stroke-dasharray="3 3" />
                                        <line x1="20" y1="120" x2="180" y2="120" stroke="#FFFFFF" stroke-width="1.5" />
                                    </svg>
                                    <span class="juf-jacket-genre"><?= sanitizeOutput($nb['genre_tag_bn'] ?? 'গল্প'); ?></span>
                                    <div class="juf-jacket-title"><?= sanitizeOutput($nb['title_bn']); ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="book-under-meta">
                            <div class="book-under-title"><?= sanitizeOutput($nb['title_bn']); ?></div>
                            <div class="book-under-author"><?= sanitizeOutput($nb['author_name'] ?? 'অজ্ঞাত'); ?></div>
                            <div class="book-rating-gold">★★★★★</div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- 9. প্রিয় লেখকদের অনুসরণ করুন -->
        <section id="authors" class="text-center" style="margin-bottom: 50px;">
            <div class="section-overline-label">— লেখকদের জগৎ —</div>
            <h2 class="page-section-title" style="margin-bottom: 6px;">প্রিয় লেখকদের অনুসরণ করুন</h2>
            <p style="color: #666; font-size: 14px; margin-bottom: 24px; font-family: var(--font-ui-system);">নতুন বই প্রকাশ হলেই জানতে পারবেন আপনার প্রিয় লেখকের কাছ থেকে।</p>
            
            <div class="authors-premium-slider-wrapper">
                <button class="author-nav-arrow arrow-left" onclick="scrollTrack('authors-circle-track', -250)" aria-label="Previous Authors">‹</button>
                <div class="authors-circle-slider-track" id="authors-circle-track">
                    <?php foreach ($allAuthors as $a): ?>
                        <a href="authors.php?id=<?= (int)$a['id']; ?>" class="author-circle-unit anim-card" style="text-decoration:none;">
                            <div class="author-circle-disc" style="background: <?= ['#2B3D4F', '#4A2E2B', '#1F3F35', '#4A3B32', '#34253A', '#1E3A4C'][abs(crc32($a['name_bn'])) % 6]; ?>; overflow:hidden;">
                                <?php if (!empty($a['photo']) && file_exists(__DIR__ . '/uploads/authors/' . $a['photo'])): ?>
                                    <img src="uploads/authors/<?= htmlspecialchars($a['photo'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= sanitizeOutput($a['name_bn']); ?>" loading="lazy" style="width:100%; height:100%; object-fit:cover; display:block;">
                                <?php else: ?>
                                    <span class="author-initial-char"><?= getBengaliInitialChar($a['name_bn']); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="author-circle-name"><?= sanitizeOutput($a['name_bn']); ?></div>
                            <div class="author-circle-title"><?= sanitizeOutput($a['designation_bn'] ?? 'সাহিত্যিক'); ?></div>
                        </a>
                    <?php endforeach; ?>
                    <a href="authors.php" class="author-circle-unit anim-card" style="text-decoration:none;">
                        <div class="author-circle-disc" style="background:var(--accent-rust); display:flex; align-items:center; justify-content:center;">
                            <span style="color:#fff; font-size:20px; font-weight:700;">→</span>
                        </div>
                        <div class="author-circle-name" style="color:var(--accent-rust);">View All</div>
                        <div class="author-circle-title">সকল লেখক</div>
                    </a>
                </div>
                <button class="author-nav-arrow arrow-right" onclick="scrollTrack('authors-circle-track', 250)" aria-label="Next Authors">›</button>
            </div>
        </section>

        <!-- 10. Readers Review -->
        <?php if (!empty($testimonials)): ?>
        <section style="margin-bottom: 50px;">
            <div class="section-overline-label text-center">— পাঠকের মতামত —</div>
            <h2 class="page-section-title text-center" style="margin-bottom: 20px;">যারা প্রতিদিন JUF-এ পড়েন</h2>
            
            <div class="testimonials-slider-box">
                <div class="testimonials-track" id="testimonial-track">
                    <?php foreach ($testimonials as $t): ?>
                        <div class="testimonial-card-white anim-card">
                            <div class="quote-mark">“</div>
                            <p style="font-size:14px; color:#444; margin-bottom:14px;"><?= sanitizeOutput($t['review_bn']); ?></p>
                            <div>
                                <h5 style="font-size: 14px; font-weight: 700; margin: 0; color: #1C1917;"><?= sanitizeOutput($t['user_name_bn']); ?></h5>
                                <span style="font-size: 12px; color: #888;"><?= sanitizeOutput($t['user_location_bn'] ?? 'পাঠক'); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- 11. Newsletter -->
        <section class="newsletter-premium-wrapper">
            <h2 class="page-section-title">নতুন বই আর বিশেষ অফার সবার আগে পান</h2>
            <p style="font-size:14px; color:#665C54; margin-bottom:24px;">প্রতি সপ্তাহে নির্বাচিত কালজয়ী ক্লাসিক বইয়ের আপডেট পান।</p>
            
            <form id="newsletter-pill-form" class="newsletter-luxury-pill" action="api/subscribe.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">
                <div class="pill-input-wrap">
                    <input type="email" name="email" id="newsletter-email" class="newsletter-luxury-input" placeholder="আপনার ইমেইল ঠিকানা লিখুন..." required>
                </div>
                <button type="submit" class="newsletter-luxury-btn">সাবস্ক্রাইব</button>
            </form>
        </section>

<script>
// ১. হরিজোন্টাল ট্র্যাক স্ক্রল
function scrollTrack(trackId, offset) {
    const track = document.getElementById(trackId);
    if (track) {
        track.scrollBy({ left: offset, behavior: 'smooth' });
    }
}

// ২. পেস্টেল ক্যাটাগরি ফিল্টার
function filterPastel(category, btn) {
    document.querySelectorAll('#author-filter-tabs .sub-tab-item').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');

    const cards = document.querySelectorAll('#pastel-track .pastel-card');
    cards.forEach(card => {
        const cat = card.getAttribute('data-category') || '';
        if (category === 'all' || cat.includes(category)) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
}

// ৩. হিরো সিনেমাটিক স্লাইডার
document.addEventListener('DOMContentLoaded', () => {
    const slides = document.querySelectorAll('.hero-slide-item');
    const thumbs = document.querySelectorAll('.hero-thumb-card');
    let currentSlide = 0;
    let slideInterval = null;

    function goToSlide(index) {
        if (!slides.length) return;
        slides.forEach(s => s.classList.remove('active'));
        thumbs.forEach(t => t.classList.remove('current-thumb'));

        currentSlide = (index + slides.length) % slides.length;
        slides[currentSlide].classList.add('active');
        if (thumbs[currentSlide]) {
            thumbs[currentSlide].classList.add('current-thumb');
        }
    }

    document.getElementById('hero-cine-next')?.addEventListener('click', () => {
        goToSlide(currentSlide + 1);
        resetAutoSlide();
    });

    document.getElementById('hero-cine-prev')?.addEventListener('click', () => {
        goToSlide(currentSlide - 1);
        resetAutoSlide();
    });

    thumbs.forEach(thumb => {
        thumb.addEventListener('click', () => {
            const idx = parseInt(thumb.getAttribute('data-slide-index') || '0', 10);
            goToSlide(idx);
            resetAutoSlide();
        });
    });

    function startAutoSlide() {
        if (slides.length > 1) {
            slideInterval = setInterval(() => goToSlide(currentSlide + 1), 6000);
        }
    }

    function resetAutoSlide() {
        clearInterval(slideInterval);
        startAutoSlide();
    }
    startAutoSlide();

    // ৪. স্ক্রল ফেড অ্যানিমেশন (Intersection Observer)
    const animCards = document.querySelectorAll('.anim-card');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('in-view');
            }
        });
    }, { threshold: 0.1 });
    animCards.forEach(card => observer.observe(card));

    // ৫. টেস্টমোনিয়াল অটো-স্ক্রল (যদি থাকে)
    const tTrack = document.getElementById('testimonial-track');
    if (tTrack && tTrack.children.length > 2) {
        let tIndex = 0;
        setInterval(() => {
            tIndex = (tIndex + 1) % (tTrack.children.length - 1);
            tTrack.style.transform = `translateX(-${tIndex * 50}%)`;
        }, 5000);
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>