/**
 * Legacy sidebar JS logic removed in favor of AlpineJS implementation.
 * See: resources/views/components/layout/home-aside.blade.php
 */

document.addEventListener('DOMContentLoaded', () => {
    const searchForm = document.getElementById('searchForm');
    if (!searchForm) return;

    searchForm.addEventListener('submit', (e) => {
        e.preventDefault();

        const searchInput = document.getElementById('search');
        const homePage = document.querySelector('[x-data*="homeController"]');
        if (!homePage || !searchInput) return;

        const alpineData = Alpine.$data(homePage);
        alpineData.filters.search = searchInput.value.trim();
        alpineData.updateResults();
    });
});
