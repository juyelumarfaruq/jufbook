<?php
declare(strict_types=1);
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = getDB();

$page_title = "সকল অডিওবুক লাইব্রেরি | JUFbook Spotify Audio";
require_once __DIR__ . '/includes/header.php';

try {
    $stmt = $pdo->query("
        SELECT b.*, a.name_bn AS author_name 
        FROM books b 
        LEFT JOIN authors a ON b.author_id = a.id 
        WHERE b.is_audiobook = 1 
        ORDER BY b.id DESC
    ");
    $allAudiobooks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $allAudiobooks = [];
}

function getBengaliInitialChar(string $str): string {
    return mb_substr(trim(strip_tags($str)), 0, 1, 'UTF-8');
}
?>

<div class="container" style="min-height: 70vh; margin-top: 35px; margin-bottom: 70px;">
    <!-- Spotify Style Banner Header -->
    <div style="background: linear-gradient(135deg, #121212 0%, #1e392a 100%); border-radius: 20px; padding: 40px 35px; border: 1px solid rgba(255, 255, 255, 0.08); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px; margin-bottom: 40px; box-shadow: 0 12px 30px rgba(0,0,0,0.25);">
        <div>
            <div style="display: inline-flex; align-items: center; gap: 6px; background: rgba(29, 185, 84, 0.15); border: 1px solid #1DB954; color: #1DB954; font-size: 12px; font-weight: 700; padding: 4px 14px; border-radius: 999px; margin-bottom: 12px;">
                🎧 JUF DIGITAL AUDIO
            </div>
            <h1 style="font-family: var(--font-heading); font-size: 36px; font-weight: 700; color: #FFFFFF; margin-bottom: 8px;">
                অডিওবুক প্লেলিস্ট
            </h1>
            <p style="color: #B3B3B3; font-size: 14px; max-width: 600px; line-height: 1.6;">
                কালজয়ী বাংলা সাহিত্য ও ক্লাসিক উপন্যাস শুনুন পেশাদার বাচিক শিল্পী ও আকর্ষণীয় আবহসঙ্গীতের সাথে।
            </p>
        </div>
        <div style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 14px; padding: 18px 24px; text-align: center;">
            <div style="color: #1DB954; font-size: 26px; font-weight: 800;"><?= count($allAudiobooks); ?>+</div>
            <div style="color: #B3B3B3; font-size: 12px;">মোট অডিও ট্র্যাক</div>
        </div>
    </div>

    <!-- Audiobooks Grid -->
    <?php if (empty($allAudiobooks)): ?>
        <div style="text-align: center; padding: 50px 20px; background: #FFFFFF; border-radius: 16px; border: 1px solid #ECE5D8;">
            <p style="color: var(--text-muted); font-size: 15px;">বর্তমানে কোনো অডিওবুক যুক্ত নেই।</p>
        </div>
    <?php else: ?>
        <div class="spotify-grid-container">
            <?php foreach ($allAudiobooks as $ab): 
                $themeBg = htmlspecialchars($ab['theme_color'] ?? '#1F2937', ENT_QUOTES, 'UTF-8');
            ?>
                <div class="spotify-audio-card anim-card">
                    <div class="spotify-card-cover-wrap" style="background-color: <?= $themeBg; ?>;">
                        <?php if (!empty($ab['cover_image']) && file_exists(__DIR__ . '/uploads/covers/' . $ab['cover_image'])): ?>
                            <img src="uploads/covers/<?= htmlspecialchars($ab['cover_image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= sanitizeOutput($ab['title_bn']); ?>" class="spotify-cover-img" loading="lazy">
                        <?php else: ?>
                            <div class="spotify-cover-fallback">
                                <span><?= getBengaliInitialChar($ab['title_bn']); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Floating Green Play Button -->
                        <a href="details.php?id=<?= (int)$ab['id']; ?>#audiobook" class="spotify-floating-play-btn" aria-label="শুনুন">
                            <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor">
                                <path d="M8 5v14l11-7z"/>
                            </svg>
                        </a>

                        <span class="spotify-duration-badge">⏱ <?= sanitizeOutput($ab['audio_duration_bn'] ?? '৪৫ মি.'); ?></span>
                    </div>

                    <div class="spotify-card-body">
                        <a href="details.php?id=<?= (int)$ab['id']; ?>" class="spotify-audio-title" title="<?= sanitizeOutput($ab['title_bn']); ?>">
                            <?= sanitizeOutput($ab['title_bn']); ?>
                        </a>
                        <p class="spotify-audio-author"><?= sanitizeOutput($ab['author_name'] ?? 'অজ্ঞাত লেখক'); ?></p>
                        
                        <!-- Sound Wave Equalizer -->
                        <div class="spotify-mini-waveform">
                            <span class="bar b1"></span>
                            <span class="bar b2"></span>
                            <span class="bar b3"></span>
                            <span class="bar b4"></span>
                            <span class="bar b5"></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>