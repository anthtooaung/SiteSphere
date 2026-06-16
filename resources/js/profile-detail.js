const revealItems = document.querySelectorAll(
    ".profile-card, .stat-grid, .expansion-container"
);

const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
        if(entry.isIntersecting){
            entry.target.classList.add("show");
            revealObserver.unobserve(entry.target);
        }
    });
}, {
    threshold:.15
});

revealItems.forEach((item, index) => {
    item.classList.add("scroll-reveal");
    item.style.transitionDelay = `${index * 80}ms`;
    revealObserver.observe(item);
});

console.log("Profile Dashboard Loaded");
