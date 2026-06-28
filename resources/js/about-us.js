function initAboutUsPage() {
    const aboutusMain = document.querySelector('.aboutus-main');

    if (! aboutusMain) {
        return;
    }

    
    
    // --- 1. SCROLL REVEAL LOGIC ---
    const revealElements = aboutusMain.querySelectorAll('.aboutus-scroll-reveal, .scroll-reveal');

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
    const cards = aboutusMain.querySelectorAll('.carousel-card');
    const prevBtn = aboutusMain.querySelector('.prev-btn');
    const nextBtn = aboutusMain.querySelector('.next-btn');
    let currentIndex = 0;

    const updateCarousel = () => {
        if (cards.length === 0) {
            return;
        }

        cards.forEach((card, index) => {
            card.classList.remove('active', 'prev', 'next', 'far-prev', 'far-next', 'hidden');

            if (index === currentIndex) {
                card.classList.add('active');
            } else if (index === (currentIndex - 1 + cards.length) % cards.length) {
                card.classList.add('prev');
            } else if (index === (currentIndex + 1) % cards.length) {
                card.classList.add('next');
            } else if (index === (currentIndex - 2 + cards.length) % cards.length) {
                card.classList.add('far-prev');
            } else if (index === (currentIndex + 2) % cards.length) {
                card.classList.add('far-next');
            } else {
                card.classList.add('hidden');
            }
        });
    };

    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            currentIndex = (currentIndex - 1 + cards.length) % cards.length;
            updateCarousel();
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            currentIndex = (currentIndex + 1) % cards.length;
            updateCarousel();
        });
    }

    cards.forEach((card, index) => {
        card.addEventListener('click', () => {
            if (card.classList.contains('prev')) {
                currentIndex = (currentIndex - 1 + cards.length) % cards.length;
                updateCarousel();
            } else if (card.classList.contains('next')) {
                currentIndex = (currentIndex + 1) % cards.length;
                updateCarousel();
            }
        });
    });

    updateCarousel();

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
