</main>

    <footer class="juf-footer-cream">
        <div class="container">
            <div class="footer-grid-cream">
                <div class="footer-col-cream">
                    <a href="<?= BASE_URL ?>/index.php" class="juf-brand-logo-wrap" style="margin-bottom: 12px; display: inline-flex; height: 56px;">
                        <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="JUF Book" class="juf-site-logo" style="height: 56px; width: auto; object-fit: contain;">
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
                    <p class="footer-contact-mail">ইমেইল: support@jufbook.com</p>
                </div>
            </div>

            <div class="footer-copyright-cream">
                &copy; <?= date('Y') ?> JUFbook. সর্বস্বত্ব সংরক্ষিত।
            </div>
        </div>
    </footer>

    <!-- স্ক্রিপ্টস -->
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        // 1. Universal Dynamic Viewport Reveal (স্মুথ রিভিল অ্যানিমেশন)
        const observerOptions = {
            threshold: 0.05,
            rootMargin: "0px 0px -20px 0px"
        };

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

        // 2. স্লাইডার স্ক্রোলিং ফাংশন
        window.scrollTrack = function(trackId, amount) {
            const track = document.getElementById(trackId);
            if (track) {
                track.scrollBy({ left: amount, behavior: 'smooth' });
            }
        };

        // 3. Authors ফিল্টারিং লজিক
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

        // 4. মূল Testimonials Auto-scroll (২-কার্ড ৫০% শিফট অ্যানিমেশন)
        const reviewTrack = document.getElementById('testimonial-track');
        if (reviewTrack && reviewTrack.children.length > 1) {
            let revIdx = 0;
            const total = reviewTrack.children.length;
            setInterval(() => {
                const shift = window.innerWidth <= 992 ? 100 : 50;
                const max = window.innerWidth <= 992 ? total - 1 : total - 2;
                revIdx = revIdx >= max ? 0 : revIdx + 1;
                reviewTrack.style.transform = `translateX(-${revIdx * shift}%)`;
            }, 5000);
        }

        // 5. হিরো সিনেমাটিক স্লাইডার
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

        setInterval(() => switchHeroSlide(currentSlide + 1), 6000);
    });
    </script>
</body>
</html>