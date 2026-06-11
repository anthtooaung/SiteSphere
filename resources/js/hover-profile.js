document.addEventListener('DOMContentLoaded', () => {
    // Check if the container already exists
    let container = document.getElementById('hoverProfileContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'hoverProfileContainer';
        container.style.display = 'none';
        container.style.position = 'absolute';
        document.body.appendChild(container);
    }

    const cache = {};
    let showTimer = null;
    let hideTimer = null;
    let activeTrigger = null;

    // Event delegation on document
    document.addEventListener('mouseover', (e) => {
        const trigger = e.target.closest('[data-hover-profile]');
        if (!trigger) {
            return;
        }

        const userId = trigger.getAttribute('data-hover-profile');
        if (!userId) {
            return;
        }

        // Mouse entered a trigger
        clearTimeout(hideTimer);

        if (activeTrigger === trigger) {
            return;
        }

        clearTimeout(showTimer);
        activeTrigger = trigger;

        showTimer = setTimeout(() => {
            showHoverCard(trigger, userId);
        }, 300);
    });

    document.addEventListener('mouseout', (e) => {
        const trigger = e.target.closest('[data-hover-profile]');
        if (!trigger) {
            return;
        }

        // Mouse left a trigger
        clearTimeout(showTimer);
        activeTrigger = null;

        startHideTimer();
    });

    // Handle hovering on the popup card container itself
    container.addEventListener('mouseenter', () => {
        clearTimeout(hideTimer);
    });

    container.addEventListener('mouseleave', () => {
        startHideTimer();
    });

    function startHideTimer() {
        clearTimeout(hideTimer);
        hideTimer = setTimeout(() => {
            hideHoverCard();
        }, 200);
    }

    function showHoverCard(trigger, userId) {
        if (cache[userId]) {
            renderCard(trigger, cache[userId]);
            return;
        }

        // Fetch user hover card from the backend
        fetch(`/users/${userId}/hover-card`)
            .then(res => {
                if (!res.ok) {
                    throw new Error('Failed to load user info');
                }
                return res.text();
            })
            .then(html => {
                cache[userId] = html;
                // Double check that we are still hovering this trigger before rendering
                if (activeTrigger === trigger) {
                    renderCard(trigger, html);
                }
            })
            .catch(err => {
                console.error(err);
            });
    }

    function renderCard(trigger, html) {
        container.innerHTML = html;
        container.style.display = 'block';

        // Bind message/profile button custom action if needed
        const messageBtn = container.querySelector('#messageBtn');
        if (messageBtn && messageBtn.getAttribute('href') === '#') {
            messageBtn.addEventListener('click', (e) => {
                e.preventDefault();
                if (window.Swal) {
                    window.Swal.fire({
                        title: 'Profile Unavailable',
                        text: 'Viewing profile of other users is not available.',
                        icon: 'info',
                        confirmButtonColor: 'var(--accent-color, #6c5ce7)',
                        background: 'var(--background-color, #ffffff)',
                        color: 'var(--text-color, #0d1b2a)',
                    });
                } else {
                    alert('Viewing profile of other users is not available.');
                }
            });
        }

        // Trigger reflow to apply transitions
        container.offsetHeight;
        container.classList.add('is-visible');

        // Position the card
        positionCard(trigger);
    }

    function hideHoverCard() {
        container.classList.remove('is-visible');
        // Wait for CSS transition to end
        setTimeout(() => {
            if (!container.classList.contains('is-visible')) {
                container.style.display = 'none';
            }
        }, 200);
    }

    function positionCard(trigger) {
        const rect = trigger.getBoundingClientRect();
        const containerRect = container.getBoundingClientRect();

        const triggerCenter = rect.left + rect.width / 2;
        let left = triggerCenter - containerRect.width / 2;

        // Ensure within window boundaries
        if (left < 10) {
            left = 10;
        } else if (left + containerRect.width > window.innerWidth - 10) {
            left = window.innerWidth - containerRect.width - 10;
        }

        // Determine if there is enough space above, otherwise place below
        const spaceAbove = rect.top;
        let top;
        if (spaceAbove > containerRect.height + 15) {
            top = rect.top - containerRect.height - 8;
        } else {
            top = rect.bottom + 8;
        }

        container.style.left = `${left + window.scrollX}px`;
        container.style.top = `${top + window.scrollY}px`;
    }
});
