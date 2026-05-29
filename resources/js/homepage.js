
/*const sidebar = document.getElementById('sidebar');
const sidebarToggle = document.getElementById('sidebarToggle');

const topMenuBtn = document.getElementById('topMenuBtn');

if(topMenuBtn){
    topMenuBtn.addEventListener('click', () => {
        if(window.innerWidth <= 900){
            sidebar.classList.toggle('mobile-open');
        }
    });
}*/

const sidebar = document.getElementById("sidebar");
const sidebarToggle = document.getElementById("sidebarToggle");

/* TOGGLE SIDEBAR */
sidebarToggle.addEventListener("click", (e) => {

    e.stopPropagation();

    /* MOBILE */
    if(window.innerWidth <= 900){

        sidebar.classList.toggle("mobile-open");

    } 
    
    /* DESKTOP */
    else {

        sidebar.classList.toggle("collapsed");

    }

});

/* CLOSE MOBILE SIDEBAR WHEN CLICK OUTSIDE */
document.addEventListener("click", (e) => {

    if(
        window.innerWidth <= 900 &&
        sidebar.classList.contains("mobile-open") &&
        !sidebar.contains(e.target) &&
        !sidebarToggle.contains(e.target)
    ){

        sidebar.classList.remove("mobile-open");

    }

});

/* RESET ON RESIZE */
window.addEventListener("resize", () => {

    if(window.innerWidth > 900){

        sidebar.classList.remove("mobile-open");

    }

});

const tagsContainer = document.getElementById('tagsContainer');

const selectedCategoriesBox = document.getElementById('selectedCategories');
const selectedTagsBox = document.getElementById('selectedTags');


const ratingInputs = document.querySelectorAll('.rating-check input');

const cards = Array.from(document.querySelectorAll('.review-card'));
const cardOrder = new Map(cards.map((card, index) => [card, index]));

const pagination = document.getElementById('pagination');
const categoryCheckboxes = document.querySelectorAll('.category-check input');

const categorySearch = document.getElementById('categorySearch');

const tagSearch = document.getElementById('tagSearch');

const tagFilterBtn = document.getElementById('tagFilterBtn');
const tagFilterPanel = document.getElementById('tagFilterPanel');
const tagFilterCount = document.getElementById('tagFilterCount');
const tagFilterReset = document.getElementById('tagFilterReset');
const tagFilterCheckboxes = document.querySelectorAll('.tag-filter-check input');
const clearAllFiltersBtn = document.getElementById('clearAllFilters');
const resultsCount = document.getElementById('resultsCount');
const sortSelect = document.getElementById('sortSelect');

const mainContent = document.querySelector('.main-content');

function formatFilterLabel(value){

    const labels = window.homeCategoryLabels || {
        all: 'All',
        All: 'All'
    };

    return labels[value] || value;

}

function renderFilterEmptyState(container, text){

    if(container.children.length > 0){
        return;
    }

    const empty = document.createElement('span');
    empty.classList.add('filter-empty');
    empty.textContent = text;
    container.appendChild(empty);

}

function updateActiveFilterState(){

    const hasActiveFilters =
        selectedCategories.length > 0 ||
        selectedTags.length > 0 ||
        selectedRatings.length > 0;

    document
        .querySelector('.active-filters-wrapper')
        ?.classList.toggle('has-filters', hasActiveFilters);

}

function saveScrollPosition(){

    return {
        sidebar: sidebar ? sidebar.scrollTop : 0,
        main: mainContent ? mainContent.scrollTop : 0,
        windowX: window.scrollX,
        windowY: window.scrollY
    };

}

function restoreScrollPosition(position){

    if(sidebar){
        sidebar.scrollTop = position.sidebar;
    }

    if(mainContent){
        mainContent.scrollTop = position.main;
    }

    window.scrollTo(position.windowX, position.windowY);

}

function restoreScrollAfterLayout(position){

    restoreScrollPosition(position);

    requestAnimationFrame(() => {
        restoreScrollPosition(position);
    });

}

/* CATEGORY SEARCH */
categorySearch.addEventListener('input', () => {

    filterCategories();

});

categorySearch.addEventListener('focus', () => {

    categorySearch.parentElement.classList.add('active');

});

categorySearch.addEventListener('blur', () => {

    categorySearch.parentElement.classList.remove('active');

});

/* TAG SEARCH */
tagSearch.addEventListener('input', () => {

    renderTags();

});

/* FOCUS EFFECT */
tagSearch.addEventListener('focus', () => {

    tagSearch.parentElement.classList.add('active');

});

tagSearch.addEventListener('blur', () => {

    tagSearch.parentElement.classList.remove('active');

});



let selectedCategories = [];

let selectedTags = [];

let selectedTagFilters = ['All'];

let selectedRatings = [];
let currentPage = 1;

const cardsPerPage = 10;

let filteredCards = [...cards];

function sortFilteredCards(){

    const sortValue = sortSelect ? sortSelect.value : 'best';

    filteredCards.sort((a, b) => {

        if(sortValue === 'rating'){
            const ratingDiff =
                parseInt(b.dataset.rating) - parseInt(a.dataset.rating);

            if(ratingDiff !== 0){
                return ratingDiff;
            }
        }

        if(sortValue === 'newest'){
            return cardOrder.get(b) - cardOrder.get(a);
        }

        return cardOrder.get(a) - cardOrder.get(b);

    });

}

function clearAllSelectedFilters(){

    categoryCheckboxes.forEach(cb => {
        cb.checked = false;
    });

    ratingInputs.forEach(cb => {
        cb.checked = false;
    });

    document.querySelectorAll('.tag-check input').forEach(cb => {
        cb.checked = false;
    });

    selectedCategories = [];
    selectedTags = [];
    selectedRatings = [];
    selectedTagFilters = ['All'];

    updateSelectedCategories();
    updateSelectedTags();
    updateSelectedRating();
    renderTags();
    filterCards();

}

if(clearAllFiltersBtn){
    clearAllFiltersBtn.addEventListener('click', clearAllSelectedFilters);
}

/* MOBILE SIDEBAR */

if(window.innerWidth <= 900){

    sidebar.classList.remove('mobile-open');

}else{

    sidebar.classList.remove('collapsed');
    sidebar.classList.add('desktop-open');

}

function closeSidebarOnOutsideClick(event){

    if(
        window.innerWidth <= 900 &&
        sidebar.classList.contains('mobile-open') &&
        !sidebar.contains(event.target) &&
        !sidebarToggle.contains(event.target)
    ){
        sidebar.classList.remove('mobile-open');
    }

}

document.addEventListener('click', closeSidebarOnOutsideClick);

// ensure mobile sidebar closes when resizing to desktop
window.addEventListener('resize', () => {
    if(window.innerWidth > 900){
        if(sidebar){
            sidebar.classList.remove('mobile-open');
            sidebar.classList.remove('collapsed');
            sidebar.classList.add('desktop-open');
        }
    }
});
/* CATEGORY TAGS DATA */
const categoryTags = window.homeCategoryTags || { all: [] };
  
categoryCheckboxes.forEach(box => {

    box.addEventListener('change', () => {

        const value = box.value;

        const allBox =
        document.querySelector('.category-check input[value="All"]');

        // CLICKED ALL
        if(value === 'All'){

            if(box.checked){

                // REMOVE OTHER CHECKS
                categoryCheckboxes.forEach(cb => {

                    if(cb.value !== 'All'){
                        cb.checked = false;
                    }

                });

                selectedCategories = ['All'];

            }else{

                selectedCategories = [];

            }

        }else{

            // REMOVE ALL CHECK
            allBox.checked = false;

            selectedCategories =
            [...categoryCheckboxes]
            .filter(cb =>
                cb.checked && cb.value !== 'All'
            )
            .map(cb => cb.value);

        }

        renderTags();

        updateSelectedCategories();

        filterCards();

    });

});
    
/* Categories Show More button*/
const showCategoryBtn =
document.getElementById('showCategoryBtn');

const extraCategories =
document.getElementById('extraCategories');

function filterCategories(){

    const searchValue = categorySearch.value.trim().toLowerCase();
    let extraCategoryMatch = false;

    document.querySelectorAll('.category-check').forEach(label => {

        const categoryName = label.textContent.trim().toLowerCase();
        const isMatch = categoryName.includes(searchValue);
        const isExtraCategory = label.closest('#extraCategories');

        label.style.display = isMatch ? 'flex' : 'none';

        if(isExtraCategory && isMatch){
            extraCategoryMatch = true;
        }

    });

    if(searchValue){
        extraCategories.classList.toggle('show', extraCategoryMatch);
        showCategoryBtn.style.display = 'none';
    }else{
        extraCategories.classList.remove('show');
        showCategoryBtn.style.display = 'block';
        showCategoryBtn.innerText = 'Show More Categories';
    }

}

showCategoryBtn.addEventListener('click', () => {

    extraCategories.classList.toggle('show');

    if(extraCategories.classList.contains('show')){

        showCategoryBtn.innerText =
        'Show Less Categories';

    }else{

        showCategoryBtn.innerText =
        'Show More Categories';
    }

});


/* UPDATE SELECTED CATEGORY UI */
function updateSelectedCategories(){

    selectedCategoriesBox.innerHTML = '';

    selectedCategories.forEach(category => {

        const box = document.createElement('div');

        box.classList.add('selected-box');

        box.innerHTML = `
            <i class="fas fa-layer-group"></i>
            <span class="selected-label">${formatFilterLabel(category)}</span>
            <span class="remove-btn">×</span>
        `;

        box.querySelector('.remove-btn').addEventListener('click', () => {

            document.querySelector(
                `.category-check input[value="${category}"]`
            ).checked = false;

            selectedCategories = selectedCategories.filter(
                c => c !== category
            );

            renderTags();

            updateSelectedCategories();

            filterCards();

        });

        selectedCategoriesBox.appendChild(box);

    });

    renderFilterEmptyState(selectedCategoriesBox, 'No category selected');
    updateActiveFilterState();

}

const showTagsBtn = document.getElementById('showTagsBtn');

let showAllTags = false;

function updateTagFilterButton(){

    if(!tagFilterBtn || !tagFilterCount){
        return;
    }

    const activeCount = selectedTagFilters.includes('All')
        ? 'All'
        : selectedTagFilters.length;

    tagFilterCount.innerText = activeCount || 'All';
    tagFilterBtn.classList.toggle(
        'has-filters',
        selectedTagFilters.length > 0 && !selectedTagFilters.includes('All')
    );

}

function positionTagFilterPanel(){

    if(!tagFilterPanel || !tagFilterBtn){
        return;
    }

    const sidebarRect = sidebar.getBoundingClientRect();

    const buttonRect = tagFilterBtn.getBoundingClientRect();

    tagFilterPanel.style.left =
        `${sidebarRect.right + 12}px`;

    tagFilterPanel.style.top =
        `${buttonRect.top}px`;
}

if(tagFilterBtn && tagFilterPanel){

tagFilterBtn.addEventListener('click', event => {

    event.stopPropagation();

    tagFilterPanel.classList.toggle('show');
    tagFilterBtn.classList.toggle(
        'active',
        tagFilterPanel.classList.contains('show')
    );

    if(tagFilterPanel.classList.contains('show')){
        positionTagFilterPanel();
    }

});

}

window.addEventListener('resize', () => {

    if(tagFilterPanel && tagFilterPanel.classList.contains('show')){
        positionTagFilterPanel();
    }

});

sidebar.addEventListener('scroll', () => {

    if(tagFilterPanel && tagFilterPanel.classList.contains('show')){
        positionTagFilterPanel();
    }

});

document.addEventListener('click', event => {

    if(!tagFilterBtn || !tagFilterPanel){
        return;
    }

    if(
        !tagFilterBtn.contains(event.target) &&
        !tagFilterPanel.contains(event.target)
    ){
        tagFilterPanel.classList.remove('show');
        tagFilterBtn.classList.remove('active');
    }

});

if(tagFilterReset){

tagFilterReset.addEventListener('click', () => {

    tagFilterCheckboxes.forEach(cb => {
        cb.checked = cb.value === 'All';
    });

    selectedTagFilters = ['All'];

    updateTagFilterButton();

    renderTags();

});

}

tagFilterCheckboxes.forEach(box => {

    box.addEventListener('change', () => {

        const value = box.value;
        const allBox =
            document.querySelector('.tag-filter-check input[value="All"]');

        if(value === 'All'){

            if(!box.checked){
                box.checked = true;
            }

            tagFilterCheckboxes.forEach(cb => {

                if(cb.value !== 'All'){
                    cb.checked = false;
                }

            });

            selectedTagFilters = ['All'];

        }else{

            allBox.checked = false;

            selectedTagFilters =
                [...tagFilterCheckboxes]
                .filter(cb =>
                    cb.checked && cb.value !== 'All'
                )
                .map(cb => cb.value);

            if(selectedTagFilters.length === 0){
                allBox.checked = true;
                selectedTagFilters = ['All'];
            }

        }

        updateTagFilterButton();

        renderTags();

    });

});

function renderTags(){

    const previousScroll = saveScrollPosition();

    tagsContainer.innerHTML = '';

    /* SHOW TAGS BY SELECTED CATEGORY */
    let allTags = [];

    const categoryKeys = selectedCategories.length === 0 ||
        selectedCategories.includes('All')
        ? Object.keys(categoryTags)
        : selectedCategories.map(category => category.toLowerCase());

    categoryKeys.forEach(category => {

        const tags = categoryTags[category] || [];

        allTags.push(...tags);

    });

    allTags = [...new Set(allTags)];

    updateSelectedTags();

    /* ADD ALL FIRST */
    allTags.unshift('All');

    if(
        selectedTagFilters.length > 0 &&
        !selectedTagFilters.includes('All')
    ){

        const filterTags = [];

        selectedTagFilters.forEach(category => {

            filterTags.push(...(categoryTags[category] || []));

        });

        const filterTagSet = new Set(filterTags);

        allTags = allTags.filter(tag =>
            tag === 'All' || filterTagSet.has(tag)
        );

    }

    /* SEARCH */
    const searchValue = tagSearch.value.toLowerCase();

    allTags = allTags.filter(tag =>
        tag.toLowerCase().includes(searchValue)
    );

    showTagsBtn.style.visibility = allTags.length > 5 ? 'visible' : 'hidden';

    /* SHOW FIRST 5 */
    let visibleTags = showAllTags
        ? allTags
        : allTags.slice(0, 5);

    visibleTags.forEach(tag => {

        const label = document.createElement('label');

        label.classList.add('tag-check');

        label.innerHTML = `
            <input 
                type="checkbox"
                value="${tag}"
                ${selectedTags.includes(tag) ? 'checked' : ''}
            >

            <span>${tag}</span>
        `;

        const input = label.querySelector('input');

        input.addEventListener('change', () => {

            if(tag === 'All'){

    if(input.checked){

        selectedTags = ['All'];

        document
        .querySelectorAll('.tag-check input')
        .forEach(cb => {

            if(cb.value !== 'All'){
                cb.checked = false;
            }

        });

    }else{

        selectedTags = [];

    }

    updateSelectedTags();

    filterCards();

    return;
}
    // normal tag logic
    const allCheckbox =
document.querySelector('.tag-check input[value="All"]');

if(allCheckbox){
    allCheckbox.checked = false;
}

selectedTags =
[...document.querySelectorAll('.tag-check input')]
.filter(cb =>
    cb.checked && cb.value !== 'All'
)
.map(cb => cb.value);

if(selectedTags.length === 0){

    if(allCheckbox){
        allCheckbox.checked = false;
    }

    selectedTags = [];

}
    updateSelectedTags();
    filterCards();
});

        tagsContainer.appendChild(label);

    });

    restoreScrollAfterLayout(previousScroll);

}
/* ICON CLICK OPEN SIDEBAR */
const sectionIcons = document.querySelectorAll('.section-icon');

sectionIcons.forEach(icon => {

    icon.addEventListener('click', () => {

        sidebar.classList.remove('collapsed');

    });

});

/*SIDEBAR DROPDOWN */

const dropdowns = [
    {
        header: document.getElementById('categoryHeader'),
        content: document.getElementById('categoryContent')
    },

    {
        header: document.getElementById('tagsHeader'),
        content: document.getElementById('tagsContent')
    },

    {
        header: document.getElementById('ratingHeader'),
        content: document.getElementById('ratingContent')
    }
];

/* START OPEN */
dropdowns.forEach(item => {
    item.header.classList.add('active');
});

/* SIDEBAR DROPDOWN */

dropdowns.forEach(item => {

    item.header.addEventListener('click', () => {

        item.content.classList.toggle('collapsed');

        item.header.classList.toggle('active');

    });

});
dropdowns.forEach(item => {

    item.content.classList.remove('collapsed');

    item.header.classList.add('active');

});
document.addEventListener('click', (e) => {

    if(window.innerWidth <= 900){

        if(
            !sidebar.contains(e.target) &&
            !sidebarToggle.contains(e.target)
        ){

            sidebar.classList.remove('mobile-open');

        }

    }

});

/* SHOW MORE TAGS */
showTagsBtn.addEventListener('click', () => {

    showAllTags = !showAllTags;

    renderTags();

});



/* UPDATE TAG DISPLAY */
function updateSelectedTags(){

    selectedTagsBox.innerHTML = '';

    selectedTags.forEach(tag => {

        const box = document.createElement('div');

        box.classList.add('selected-box');

        box.innerHTML = `
            <i class="fas fa-tags"></i>
            <span class="selected-label">${tag}</span>
            <span class="remove-btn">×</span>
        `;

        box.querySelector('.remove-btn').addEventListener('click', () => {

            selectedTags = selectedTags.filter(t => t !== tag);

            updateSelectedTags();

            renderTags();

            filterCards();

        });

        selectedTagsBox.appendChild(box);

    });

    renderFilterEmptyState(selectedTagsBox, 'No tag selected');
    updateActiveFilterState();

}

/* RATING */
document.querySelectorAll('.rating-check input')
.forEach(input => {

    input.addEventListener('change', () => {

        const value = input.value;

        const allRating =
        document.querySelector(
            '.rating-check input[value="all"]'
        );

        // CLICKED ALL
        if(value === 'all'){

            if(input.checked){

                document
                .querySelectorAll('.rating-check input')
                .forEach(cb => {

                    if(cb.value !== 'all'){
                        cb.checked = false;
                    }

                });

                selectedRatings = ['all'];

            }else{

                selectedRatings = [];

            }

        }else{

            // REMOVE ALL
            allRating.checked = false;

            selectedRatings =
            [...document.querySelectorAll('.rating-check input')]
            .filter(cb =>
                cb.checked && cb.value !== 'all'
            )
            .map(cb => cb.value);

        }

        updateSelectedRating();

        filterCards();

    });

});

/* FILTER */
function filterCards(){

    const previousScroll = saveScrollPosition();

    filteredCards = cards.filter(card => {

        const category = card.dataset.category;
        const rating = parseInt(card.dataset.rating);

        const cardTags = card.dataset.tags
            ? card.dataset.tags.split(',')
            : [];

       const matchCategory =
    selectedCategories.includes('All') ||
    selectedCategories.length === 0 ||
    selectedCategories.includes(category);

const matchTags =
    selectedTags.includes('All') ||
    selectedTags.length === 0 ||
    selectedTags.some(tag => cardTags.includes(tag));

const matchRating =
    selectedRatings.includes('all') ||
    selectedRatings.length === 0 ||
    selectedRatings.some(r => rating >= parseInt(r));

        return matchCategory && matchTags && matchRating;
    });

    currentPage = 1;

    if(resultsCount){
        resultsCount.textContent = filteredCards.length;
    }

    sortFilteredCards();

    displayCards();
    setupPagination();
    restoreScrollAfterLayout(previousScroll);
}

/*Rating UI */
const selectedRatingsBox = document.getElementById('selectedRatings');

function updateSelectedRating(){

    selectedRatingsBox.innerHTML = '';

    selectedRatings.forEach(rating => {

        const box = document.createElement('div');

        box.classList.add('selected-box');

        box.innerHTML = `
            <i class="fas fa-star"></i>
            <span class="selected-label">${rating === 'all' ? 'All Ratings' : rating + '+ Rating'}</span>
            <span class="remove-btn">×</span>
        `;

        const removeBtn = box.querySelector('.remove-btn');

        removeBtn.addEventListener('click', () => {

            document.querySelector(
                `.rating-check input[value="${rating}"]`
            ).checked = false;

            selectedRatings = selectedRatings.filter(
                r => r !== rating
            );

            updateSelectedRating();

            filterCards();

        });

        selectedRatingsBox.appendChild(box);

    });

    renderFilterEmptyState(selectedRatingsBox, 'No rating selected');
    updateActiveFilterState();

}

updateSelectedRating();

/* DISPLAY CARDS */
function displayCards(){

    cards.forEach(card => {
        card.style.display = 'none';
    });

    const start = (currentPage - 1) * cardsPerPage;

    const end = start + cardsPerPage;

    filteredCards.slice(start, end).forEach(card => {

        card.style.order = String(filteredCards.indexOf(card));
        card.style.display = 'block';

    });

}


/* PAGINATION */
function setupPagination(){

    pagination.innerHTML = '';

    const pageCount = Math.ceil(filteredCards.length / cardsPerPage);

    for(let i = 1; i <= pageCount; i++){

        const btn = document.createElement('button');

        btn.innerText = i;

        if(i === currentPage){
            btn.classList.add('active');
        }

        btn.addEventListener('click', () => {

            currentPage = i;

            displayCards();

            setupPagination();

        });

        pagination.appendChild(btn);

    }

}

if(sortSelect){

    sortSelect.addEventListener('change', () => {

        currentPage = 1;
        sortFilteredCards();
        displayCards();
        setupPagination();

    });

}

/* INITIAL */
renderTags();

updateTagFilterButton();

updateSelectedCategories();

updateSelectedTags();

filterCards();

