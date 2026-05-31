
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
const dropdownAsideLocations = ['top', 'bottom'];

function isDropdownAsideDesktop(){

    return (
        window.innerWidth > 900 &&
        sidebar &&
        dropdownAsideLocations.includes(sidebar.dataset.menuBarLocation)
    );

}

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

function filterIconSvg(type){

    const icons = {
        category: '<svg class="selected-icon" viewBox="0 0 512 512" aria-hidden="true"><path fill="currentColor" d="M40 48C26.7 48 16 58.7 16 72v48c0 13.3 10.7 24 24 24h432c13.3 0 24-10.7 24-24V72c0-13.3-10.7-24-24-24H40zm0 160c-13.3 0-24 10.7-24 24v48c0 13.3 10.7 24 24 24h432c13.3 0 24-10.7 24-24v-48c0-13.3-10.7-24-24-24H40zm0 160c-13.3 0-24 10.7-24 24v48c0 13.3 10.7 24 24 24h432c13.3 0 24-10.7 24-24v-48c0-13.3-10.7-24-24-24H40z"/></svg>',
        rating: '<svg class="selected-icon" viewBox="0 0 576 512" aria-hidden="true"><path fill="currentColor" d="M316.9 18.6c-5.3-11-16.5-18.6-28.9-18.6s-23.6 7.6-28.9 18.6L195 150.3 51.4 171.5c-12 1.8-22 10.2-25.7 21.7s-.7 24.2 7.9 32.7L137.8 328 113.2 472.1c-2 12 3 24.2 12.9 31.3s23 8 33.8 2.3L288 437.9l128.1 67.8c10.8 5.7 23.9 4.8 33.8-2.3s14.9-19.3 12.9-31.3L438.2 328 542.4 225.9c8.6-8.5 11.7-21.2 7.9-32.7s-13.7-19.9-25.7-21.7L381 150.3 316.9 18.6z"/></svg>',
        tag: '<svg class="selected-icon" viewBox="0 0 448 512" aria-hidden="true"><path fill="currentColor" d="M0 80C0 53.5 21.5 32 48 32h145.9c17 0 33.3 6.7 45.3 18.7l176 176c25 25 25 65.5 0 90.5L241.2 491.3c-25 25-65.5 25-90.5 0l-132-132C6.7 347.3 0 331 0 314.1V80zm112 32a48 48 0 1 0 0 96 48 48 0 1 0 0-96z"/></svg>',
    };

    return icons[type] || '';

}

function fillSelectedBox(box, iconType, label){

    box.innerHTML = `${filterIconSvg(iconType)}<span class="selected-label"></span><span class="remove-btn">×</span>`;
    box.querySelector('.selected-label').textContent = label;

}

function fillSelectedTextBox(box, label){

    box.innerHTML = '<span class="selected-label"></span><span class="remove-btn">×</span>';
    box.querySelector('.selected-label').textContent = label;

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

const hasMoreCategories = Boolean(showCategoryBtn);

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
        if(showCategoryBtn){
            showCategoryBtn.style.display = 'none';
        }
    }else{
        extraCategories.classList.remove('show');
        if(showCategoryBtn){
            showCategoryBtn.style.display = hasMoreCategories ? 'block' : 'none';
            showCategoryBtn.innerText = 'Show More Categories';
        }
    }

}

if(showCategoryBtn){
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
}


/* UPDATE SELECTED CATEGORY UI */
function updateSelectedCategories(){

    selectedCategoriesBox.innerHTML = '';

    selectedCategories.forEach(category => {

        const box = document.createElement('div');

        box.classList.add('selected-box');

        fillSelectedTextBox(box, formatFilterLabel(category));

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
const tagFilterTemplate = document.getElementById('tagFilterTemplate');

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

    const allTags = Array.isArray(categoryTags.all)
        ? categoryTags.all
        : Object.entries(categoryTags)
            .filter(([category]) => category !== 'all')
            .flatMap(([, tags]) => tags || []);

    let realTags = [...new Set(allTags)];

    updateSelectedTags();

    if(
        selectedTagFilters.length > 0 &&
        !selectedTagFilters.includes('All')
    ){

        const filterTags = [];

        selectedTagFilters.forEach(category => {

            filterTags.push(...(categoryTags[category] || []));

        });

        const filterTagSet = new Set(filterTags);

        realTags = realTags.filter(tag => filterTagSet.has(tag));

    }

    /* SEARCH */
    const searchValue = tagSearch.value.toLowerCase();

    realTags = realTags.filter(tag =>
        tag.toLowerCase().includes(searchValue)
    );

    if(realTags.length <= 5){
        showAllTags = false;
    }

    showTagsBtn.style.display = realTags.length > 5 ? 'block' : 'none';
    showTagsBtn.innerText = showAllTags ? 'Show Less Tags' : 'Show More Tags';

    /* SHOW FIRST 5 */
    let visibleTags = [
        'All',
        ...(showAllTags
            ? realTags
            : realTags.slice(0, 5)),
    ];

    visibleTags.forEach(tag => {

        const label = createTagFilterItem(tag);

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

function closeAsideDropdowns(exceptItem = null){

    dropdowns.forEach(item => {

        if(item === exceptItem){
            return;
        }

        item.content.classList.add('collapsed');
        item.header.classList.remove('active');

    });

}

function createTagFilterItem(tag){

    const label = tagFilterTemplate
        ? tagFilterTemplate.content.firstElementChild.cloneNode(true)
        : document.createElement('label');

    label.classList.add('tag-check');

    const input = label.querySelector('input') || document.createElement('input');
    const text = label.querySelector('span') || document.createElement('span');

    input.type = 'checkbox';
    input.value = tag;
    input.checked = selectedTags.includes(tag);
    text.textContent = tag;

    if(!input.parentElement){
        label.appendChild(input);
    }

    if(!text.parentElement){
        label.appendChild(text);
    }

    return label;

}

function syncAsideDropdownState(){

    dropdowns.forEach(item => {

        if(isDropdownAsideDesktop()){
            item.content.classList.add('collapsed');
            item.header.classList.remove('active');

            return;
        }

        item.content.classList.remove('collapsed');
        item.header.classList.add('active');

    });

}

syncAsideDropdownState();

/* SIDEBAR DROPDOWN */

dropdowns.forEach(item => {

    item.header.addEventListener('click', () => {

        if(isDropdownAsideDesktop()){
            const isOpening = item.content.classList.contains('collapsed');

            closeAsideDropdowns(item);
            item.content.classList.toggle('collapsed', ! isOpening);
            item.header.classList.toggle('active', isOpening);

            return;
        }

        item.content.classList.toggle('collapsed');

        item.header.classList.toggle('active');

    });

});
document.addEventListener('click', (e) => {

    if(
        isDropdownAsideDesktop() &&
        !sidebar.contains(e.target)
    ){
        closeAsideDropdowns();
    }

    if(window.innerWidth <= 900){

        if(
            !sidebar.contains(e.target) &&
            !sidebarToggle.contains(e.target)
        ){

            sidebar.classList.remove('mobile-open');

        }

    }

});

window.addEventListener('resize', syncAsideDropdownState);

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

        fillSelectedTextBox(box, tag);

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

        fillSelectedBox(box, 'rating', rating === 'all' ? 'All Ratings' : rating + '+ Rating');

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

