document.addEventListener('DOMContentLoaded', () => {

    // 1. Universal Dynamic Viewport Reveal
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

    function attachAllObservers() {
        document.querySelectorAll('.anim-card, .showcase-card-item, .pastel-card, .audio-pill-card').forEach(el => {
            universalObserver.observe(el);
        });
    }
    attachAllObservers();

    // 2. Cinematic Hero Slider Engine (Auto Slide + Fade Morphing)
    const heroSlides = document.querySelectorAll('.hero-slide-item');
    const heroThumbs = document.querySelectorAll('.hero-thumb-card');
    const heroPrevBtn = document.getElementById('hero-cine-prev');
    const heroNextBtn = document.getElementById('hero-cine-next');
    let currentHeroIndex = 0;
    let heroAutoTimer = null;
    const heroIntervalTime = 5500;

    function goToHeroSlide(index) {
        if (!heroSlides.length) return;

        heroSlides[currentHeroIndex].classList.remove('active');
        if (heroThumbs[currentHeroIndex]) {
            heroThumbs[currentHeroIndex].classList.remove('current-thumb');
        }

        currentHeroIndex = (index + heroSlides.length) % heroSlides.length;

        heroSlides[currentHeroIndex].classList.add('active');
        if (heroThumbs[currentHeroIndex]) {
            heroThumbs[currentHeroIndex].classList.add('current-thumb');
        }
    }

    if (heroSlides.length > 0) {
        // Force First Slide Active
        goToHeroSlide(0);

        heroNextBtn?.addEventListener('click', () => {
            goToHeroSlide(currentHeroIndex + 1);
            resetHeroAutoTimer();
        });

        heroPrevBtn?.addEventListener('click', () => {
            goToHeroSlide(currentHeroIndex - 1);
            resetHeroAutoTimer();
        });

        heroThumbs.forEach((thumb) => {
            thumb.addEventListener('click', () => {
                const targetIdx = parseInt(thumb.getAttribute('data-slide-index') || '0');
                goToHeroSlide(targetIdx);
                resetHeroAutoTimer();
            });
        });

        function startHeroAutoTimer() {
            heroAutoTimer = setInterval(() => {
                goToHeroSlide(currentHeroIndex + 1);
            }, heroIntervalTime);
        }

        function resetHeroAutoTimer() {
            clearInterval(heroAutoTimer);
            startHeroAutoTimer();
        }

        const stage = document.getElementById('hero-stage');
        stage?.addEventListener('mouseenter', () => clearInterval(heroAutoTimer));
        stage?.addEventListener('mouseleave', () => resetHeroAutoTimer());

        startHeroAutoTimer();
    }

    // 3. Authors Pastel Cards Smooth Scrolling
    const pastelTrack = document.getElementById('pastel-track');
    document.getElementById('pastel-prev')?.addEventListener('click', () => {
        pastelTrack?.scrollBy({ left: -320, behavior: 'smooth' });
    });
    document.getElementById('pastel-next')?.addEventListener('click', () => {
        pastelTrack?.scrollBy({ left: 320, behavior: 'smooth' });
    });

    // 4. Authors Category Tabs
    const filterTabs = document.querySelectorAll('#author-filter-tabs .sub-tab-item');
    const pastelCards = document.querySelectorAll('#pastel-track .pastel-card');

    filterTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            filterTabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            const filter = tab.getAttribute('data-filter');

            pastelCards.forEach(card => {
                const categories = card.getAttribute('data-category') || '';
                if (filter === 'all' || categories.includes(filter)) {
                    card.style.display = 'flex';
                    card.classList.add('in-view');
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

    // 5. Audiobook Slider Scroll
    const audioTrack = document.getElementById('audio-track');
    document.getElementById('audio-prev')?.addEventListener('click', () => {
        audioTrack?.scrollBy({ left: -300, behavior: 'smooth' });
    });
    document.getElementById('audio-next')?.addEventListener('click', () => {
        audioTrack?.scrollBy({ left: 300, behavior: 'smooth' });
    });

    // 6. Authors Circle Scroll
    const authorTrack = document.getElementById('authors-circle-track');
    document.getElementById('author-scroll-left')?.addEventListener('click', () => {
        authorTrack?.scrollBy({ left: -260, behavior: 'smooth' });
    });
    document.getElementById('author-scroll-right')?.addEventListener('click', () => {
        authorTrack?.scrollBy({ left: 260, behavior: 'smooth' });
    });

    // 7. Testimonials Auto-scroll
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
});
// স্লাইডার বাটন ইভেন্ট হ্যান্ডলার
document.addEventListener('DOMContentLoaded', () => {
    // Authors Pastel Slider
    const pastelTrack = document.getElementById('pastel-track');
    document.getElementById('pastel-prev')?.addEventListener('click', () => {
        pastelTrack?.scrollBy({ left: -340, behavior: 'smooth' });
    });
    document.getElementById('pastel-next')?.addEventListener('click', () => {
        pastelTrack?.scrollBy({ left: 340, behavior: 'smooth' });
    });

    // Audiobook Slider
    const audioTrack = document.getElementById('audio-track');
    document.getElementById('audio-prev')?.addEventListener('click', () => {
        audioTrack?.scrollBy({ left: -320, behavior: 'smooth' });
    });
    document.getElementById('audio-next')?.addEventListener('click', () => {
        audioTrack?.scrollBy({ left: 320, behavior: 'smooth' });
    });

    // Genres Slider
    const genreTrack = document.getElementById('genre-track');
    document.getElementById('genre-prev')?.addEventListener('click', () => {
        genreTrack?.scrollBy({ left: -200, behavior: 'smooth' });
    });
    document.getElementById('genre-next')?.addEventListener('click', () => {
        genreTrack?.scrollBy({ left: 200, behavior: 'smooth' });
    });
});