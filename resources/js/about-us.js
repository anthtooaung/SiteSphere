function initAboutUsPage() {
    const aboutusMain = document.querySelector('.aboutus-main');

    if (! aboutusMain) {
        return;
    }

    // --- 1. SCROLL REVEAL LOGIC ---
    const revealElements = aboutusMain.querySelectorAll('.aboutus-scroll-reveal');

    if ('IntersectionObserver' in window) {
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                }
            });
        }, {
            threshold: 0.12,
            rootMargin: '0px 0px -50px 0px',
        });

        revealElements.forEach((element) => revealObserver.observe(element));
    } else {
        revealElements.forEach((element) => element.classList.add('active'));
    }

    // --- 2. 3D CAROUSEL LOGIC ---
    const cards = aboutusMain.querySelectorAll('.aboutus-carousel-card');
    const prevBtn = aboutusMain.querySelector('.aboutus-prev-btn');
    const nextBtn = aboutusMain.querySelector('.aboutus-next-btn');
    let currentIndex = 0;

    const updateCarousel = () => {
        if (cards.length === 0) {
            return;
        }

        cards.forEach((card, index) => {
            card.classList.remove('active', 'prev', 'next', 'hidden');

            if (index === currentIndex) {
                card.classList.add('active');
            } else if (index === (currentIndex - 1 + cards.length) % cards.length) {
                card.classList.add('prev');
            } else if (index === (currentIndex + 1) % cards.length) {
                card.classList.add('next');
            } else {
                card.classList.add('hidden');
            }
        });
    };

    updateCarousel();

    prevBtn.addEventListener('click', () => {
        currentIndex = (currentIndex - 1 + cards.length) % cards.length;
        updateCarousel();
    });

    nextBtn.addEventListener('click', () => {
        currentIndex = (currentIndex + 1) % cards.length;
        updateCarousel();
    });

    // --- 3. SMOOTH SCROLL FOR ANCHOR LINKS ---
    aboutusMain.querySelectorAll('a[href^="#"]').forEach((anchor) => {
        anchor.addEventListener('click', (event) => {
            const targetId = anchor.getAttribute('href');

            if (targetId === '#') {
                return;
            }

            const targetElement = aboutusMain.querySelector(targetId);

            if (targetElement) {
                event.preventDefault();
                targetElement.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAboutUsPage);
} else {
    initAboutUsPage();
}
