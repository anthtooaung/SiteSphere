
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
if (sidebarToggle) {
    sidebarToggle.addEventListener("click", (e) => {
        e.stopPropagation();
        if(window.innerWidth <= 900){
            sidebar.classList.toggle("mobile-open");
        } else {
            sidebar.classList.toggle("collapsed");
        }
    });
}

/* CLOSE MOBILE SIDEBAR WHEN CLICK OUTSIDE */
document.addEventListener("click", (e) => {
    if(
        window.innerWidth <= 900 &&
        sidebar &&
        sidebar.classList.contains("mobile-open") &&
        !sidebar.contains(e.target) &&
        !sidebarToggle.contains(e.target)
    ){
        sidebar.classList.remove("mobile-open");
    }
});

/* RESET ON RESIZE */
window.addEventListener("resize", () => {
    if(window.innerWidth > 900 && sidebar){
        sidebar.classList.remove("mobile-open");
    }
});

/* ICON CLICK OPEN SIDEBAR */
const sectionIcons = document.querySelectorAll('.section-icon');
sectionIcons.forEach(icon => {
    icon.addEventListener('click', () => {
        if (sidebar) sidebar.classList.remove('collapsed');
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
        if(item === exceptItem || !item.header || !item.content) return;
        item.content.classList.add('collapsed');
        item.header.classList.remove('active');
    });
}

function syncAsideDropdownState(){
    dropdowns.forEach(item => {
        if(!item.header || !item.content) return;
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

dropdowns.forEach(item => {
    if(!item.header || !item.content) return;
    item.header.addEventListener('click', () => {
        if(isDropdownAsideDesktop()){
            const isOpening = item.content.classList.contains('collapsed');
            closeAsideDropdowns(item);
            item.content.classList.toggle('collapsed', !isOpening);
            item.header.classList.toggle('active', isOpening);
            return;
        }
        item.content.classList.toggle('collapsed');
        item.header.classList.toggle('active');
    });
});

document.addEventListener('click', (e) => {
    if(isDropdownAsideDesktop() && sidebar && !sidebar.contains(e.target)){
        closeAsideDropdowns();
    }
});

window.addEventListener('resize', syncAsideDropdownState);
