<!-- Chapter Index Accordion -->
    <div style="margin-top: 60px; max-width: 800px; margin-left: auto; margin-right: auto;">
        <h2 style="text-align: center; margin-bottom: 30px;">সূচিপত্র</h2>
        
        <div style="background: var(--card-bg); border-radius: 8px; box-shadow: var(--shadow-soft); overflow: hidden;">
            <button id="toggleChaptersBtn" style="width: 100%; text-align: left; background: none; border: none; padding: 20px 30px; font-size: 18px; font-family: var(--font-sans); font-weight: 600; color: var(--text-charcoal); cursor: pointer; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-light);">
                <span>সবগুলো পর্ব দেখুন</span>
                <span id="accordionIcon" style="transition: transform 0.3s ease;">▼</span>
            </button>
            
            <ul id="chapterList" style="list-style: none; padding: 0; margin: 0; display: none; background: #faf9f5;">
                <?php if (empty($chapters)): ?>
                    <li style="padding: 20px 30px; color: var(--text-muted);">কোনো পর্ব এখনো যুক্ত করা হয়নি।</li>
                <?php else: ?>
                    <?php foreach ($chapters as $index => $chap): ?>
                        <li style="border-bottom: 1px solid var(--border-light);">
                            <a href="<?= BASE_URL ?>/read.php?book=<?= $book['id'] ?>&chapter=<?= $chap['id'] ?>" style="display: block; padding: 15px 30px; color: var(--text-charcoal); transition: background 0.2s ease;">
                                <span style="color: var(--text-muted); margin-right: 15px;">পর্ব <?= to_bengali_number($chap['chapter_number']) ?>:</span> 
                                <?= sanitize_output($chap['chapter_title']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>