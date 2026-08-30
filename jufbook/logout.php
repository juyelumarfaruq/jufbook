<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/functions.php';

$pdo_head = getDB();
$site_settings = [];
try {
    $site_settings = $pdo_head->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (Throwable $e) {
    $site_settings = [];
}

$meta_title   = $site_settings['meta_title'] ?? "JUFbook - বাংলা ই-বুক ও অডিওবুক";
$meta_desc    = $site_settings['meta_description'] ?? "বাঙালির অমর সাহিত্য সম্ভারের প্রিমিয়াম ডিজিটাল ই-বুক ও অডিওবুক সংগ্রহ।";
$page_title   = $page_title ?? $meta_title;
$css_version  = file_exists(__DIR__ . '/../assets/css/style.css') ? filemtime(__DIR__ . '/../assets/css/style.css') : time();
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitizeOutput($page_title) ?></title>
    <meta name="description" content="<?= sanitizeOutput($meta_desc) ?>">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Hind+Siliguri:wght@400;500;600;700&family=Noto+Sans+Bengali:wght@400;500;600&family=Noto+Serif+Bengali:wght@600;700&family=Tiro+Bangla:ital@0;1&display=swap">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css?v=<?= $css_version ?>">
</head>
<body>

    <header class="juf-header-modern">
        <div class="container juf-header-wrap">
            <a href="<?= BASE_URL ?>/index.php" class="juf-brand-logo-wrap">
                <div class="juf-logo-badge">
                    <span class="juf-text">JUF</span>
                </div>
                <span class="book-text">Book</span>
            </a>
            <nav class="juf-nav">
                <a href="<?= BASE_URL ?>/index.php">হোমপেজ</a>
                <a href="<?= BASE_URL ?>/search.php">বই খুঁজুন</a>
                <a href="<?= BASE_URL ?>/authors.php">লেখকবৃন্দ</a>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="<?= BASE_URL ?>/profile.php">প্রোফাইল</a>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <a href="<?= BASE_URL ?>/admin/index.php" style="color: var(--accent-rust); font-weight: 700;">অ্যাডমিন</a>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>/logout.php" class="juf-auth-btn" style="background: #4A2E2B !important;">লগআউট</a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/login.php" class="juf-auth-btn">লগইন করুন</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="site-main container">
        <!-- Content gets rendered here -->
    </main>

    <footer class="juf-footer-cream">
        <div class="container">
            <div class="footer-grid-cream">
                <div class="footer-col-cream">
                    <a href="<?= BASE_URL ?>/index.php" class="juf-brand-logo-wrap" style="margin-bottom: 12px;">
                        <div class="juf-logo-badge" style="border-width: 3px; border-radius: 12px; padding: 4px 10px;">
                            <span class="juf-text" style="font-size: 24px;">JUF</span>
                        </div>
                        <span class="book-text" style="font-size: 22px;">Book</span>
                    </a>
                    <p class="footer-tagline-text" style="margin-top: 10px;">
                        বাংলা সাহিত্যের কালজয়ী রচনাবলী ও ডিজিটাল অডিওবুককে বিশ্বমানের পড়ার অভিজ্ঞতায় পৌঁছে দেওয়ার এক অনন্য ডিজিটাল পাঠাগার।
                    </p>
                </div>
                <div class="footer-col-cream">
                    <h4>কুইক লিংক</h4>
                    <ul>
                        <li><a href="<?= BASE_URL ?>/index.php">হোমপেজ</a></li>
                        <li><a href="<?= BASE_URL ?>/search.php">বই ক্যাটালগ</a></li>
                        <li><a href="<?= BASE_URL ?>/authors.php">লেখকবৃন্দ</a></li>
                    </ul>
                </div>
                <div class="footer-col-cream">
                    <h4>নীতিমালা</h4>
                    <ul>
                        <li><a href="<?= BASE_URL ?>/privacy.php">গোপনীয়তা নীতি</a></li>
                        <li><a href="<?= BASE_URL ?>/disclaimer.php">দাবিত্যাগ</a></li>
                        <li><a href="<?= BASE_URL ?>/contact.php">যোগাযোগ</a></li>
                    </ul>
                </div>
                <div class="footer-col-cream">
                    <h4>যোগাযোগ</h4>
                    <p class="footer-contact-mail">ইমেইল: <?= sanitizeOutput($site_settings['contact_email'] ?? 'support@jufbook.com') ?></p>
                </div>
            </div>

            <div class="footer-copyright-cream">
                &copy; <?= date('Y') ?> JUFbook. সর্বস্বত্ব সংরক্ষিত।
            </div>
        </div>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        // Universal Viewport Reveal
        const observerOptions = { threshold: 0.05, rootMargin: "0px 0px -20px 0px" };
        const universalObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('in-view');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.anim-card, .showcase-card-item, .pastel-card, .audio-pill-card').forEach(el => {
            universalObserver.observe(el);
        });

        // Horizontal Slider Navigation
        window.scrollTrack = function(trackId, amount) {
            const track = document.getElementById(trackId);
            if (track) {
                track.scrollBy({ left: amount, behavior: 'smooth' });
            }
        };

        // Category Filter for Cards
        window.filterPastel = function(filterTag, btnElem) {
            const tabs = document.querySelectorAll('#author-filter-tabs .sub-tab-item');
            tabs.forEach(t => t.classList.remove('active'));
            if (btnElem) btnElem.classList.add('active');

            const cards = document.querySelectorAll('#pastel-track .pastel-card');
            cards.forEach(card => {
                const cat = card.getAttribute('data-category') || '';
                if (filterTag === 'all' || cat.includes(filterTag)) {
                    card.style.display = 'flex';
                    card.classList.add('in-view');
                } else {
                    card.style.display = 'none';
                }
            });
        };

        // Testimonial Autoscroll
        const reviewTrack = document.getElementById('testimonial-track');
        if (reviewTrack && reviewTrack.children.length > 1) {
            let revIdx = 0;
            const total = reviewTrack.children.length;
            setInterval(() => {
                const isMobile = window.innerWidth <= 992;
                const shift = isMobile ? 100 : 50;
                const max = isMobile ? total - 1 : Math.max(0, total - 2);
                revIdx = revIdx >= max ? 0 : revIdx + 1;
                reviewTrack.style.transform = `translateX(-${revIdx * shift}%)`;
            }, 5000);
        }

        // Hero Cinematic Slider
        let currentSlide = 0;
        const heroSlides = document.querySelectorAll('.hero-slide-item');
        const heroThumbs = document.querySelectorAll('.hero-thumb-card');

        function switchHeroSlide(idx) {
            if (!heroSlides.length) return;
            heroSlides[currentSlide].classList.remove('active');
            if (heroThumbs[currentSlide]) heroThumbs[currentSlide].classList.remove('current-thumb');
            
            currentSlide = (idx + heroSlides.length) % heroSlides.length;
            
            heroSlides[currentSlide].classList.add('active');
            if (heroThumbs[currentSlide]) heroThumbs[currentSlide].classList.add('current-thumb');
        }

        const prevBtn = document.getElementById('hero-cine-prev');
        const nextBtn = document.getElementById('hero-cine-next');

        if (prevBtn) prevBtn.addEventListener('click', () => switchHeroSlide(currentSlide - 1));
        if (nextBtn) nextBtn.addEventListener('click', () => switchHeroSlide(currentSlide + 1));
        heroThumbs.forEach((thumb, i) => thumb.addEventListener('click', () => switchHeroSlide(i)));

        if (heroSlides.length > 1) {
            setInterval(() => switchHeroSlide(currentSlide + 1), 6000);
        }
    });
    </script>
</body>
</html>