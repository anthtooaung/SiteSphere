function initWelcomePage() {
    const welcomeMain = document.querySelector('.welcome-main');

    if (! welcomeMain) {
        return;
    }

    const scrollTrigger = welcomeMain.querySelector('[data-welcome-scroll]');
    const reviewsSection = welcomeMain.querySelector('#reviews-section');

    scrollTrigger?.addEventListener('click', (event) => {
        event.preventDefault();
        reviewsSection?.scrollIntoView({ behavior: 'smooth' });
    });

    const connectSection = welcomeMain.querySelector('#welcome-connect-section');
    const urlParameters = new URLSearchParams(window.location.search);

    if (urlParameters.get('scroll') === 'contact') {
        window.requestAnimationFrame(() => {
            connectSection?.scrollIntoView({ behavior: 'smooth' });
        });
    }

    document.querySelectorAll('[data-welcome-connect-scroll]').forEach((link) => {
        link.addEventListener('click', (event) => {
            event.preventDefault();
            connectSection?.scrollIntoView({ behavior: 'smooth' });
        });
    });

    const revealElements = welcomeMain.querySelectorAll('.welcome-reveal');

    const activateReveal = (element) => {
        element.classList.add('is-active');

        const progressBar = element.querySelector('.welcome-score-fill');

        if (progressBar) {
            progressBar.style.width = progressBar.dataset.width || '0';
        }
    };

    const resetReveal = (element) => {
        element.classList.remove('is-active');

        const progressBar = element.querySelector('.welcome-score-fill');

        if (progressBar) {
            progressBar.style.width = '0';
        }
    };

    if ('IntersectionObserver' in window) {
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    activateReveal(entry.target);
                } else {
                    resetReveal(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px',
        });

        revealElements.forEach((element) => revealObserver.observe(element));
    } else {
        revealElements.forEach(activateReveal);
    }

    const swapBoxes = welcomeMain.querySelectorAll('.welcome-swap-box');

    swapBoxes.forEach((box) => {
        const firstWord = box.querySelector('[data-word-state="a"]');

        firstWord?.classList.add('is-visible');
    });

    window.setInterval(() => {
        swapBoxes.forEach((box) => {
            const firstWord = box.querySelector('[data-word-state="a"]');
            const secondWord = box.querySelector('[data-word-state="b"]');

            if (! firstWord || ! secondWord) {
                return;
            }

            firstWord.classList.toggle('is-visible');
            secondWord.classList.toggle('is-visible');
        });
    }, 3000);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initWelcomePage);
} else {
    initWelcomePage();
}
