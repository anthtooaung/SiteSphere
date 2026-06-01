<div class="sitesphere-loader" data-page-loader aria-hidden="true" role="status" aria-live="polite">
    <span class="sitesphere-loader__label">Loading SiteSphere</span>

    <div class="sitesphere-loader__stage" aria-hidden="true">
        <svg class="sitesphere-loader__icon sitesphere-loader__draw sitesphere-loader__glow" viewBox="0 0 88.5 99.5" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path class="sitesphere-loader__draw-a" pathLength="100" d="M44.5 28.75L28.75 37.25L28.75 38.75L63.25 58.5L66.5 60.75L65.75 62.5L43.75 74.25L9.75 54.25L7.75 53.25L6 54L6.25 72L43.75 93.5L46 93.25L82.5 71.75L82 50Z"/>
            <path class="sitesphere-loader__draw-b" pathLength="100" d="M43.25 6L6.25 27.75L6.25 49L41 69.25L46.25 69.75L60.25 61.5L56.25 58L22 39L22 37.75L45 25.25L82 46.25L82.5 27.75L60.5 14L45.5 6Z"/>
        </svg>
    </div>
</div>

@once
    <style>
        .sitesphere-loader {
            --sitesphere-loader-accent: var(--accent-color, #6c5ce7);
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: grid;
            place-items: center;
            background: color-mix(in srgb, var(--background-color, #ffffff) 86%, transparent);
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity 180ms ease, visibility 180ms ease;
            backdrop-filter: blur(6px);
        }

        .sitesphere-loader.is-active {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        .sitesphere-loader__label {
            position: absolute;
            width: 1px;
            height: 1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
        }

        .sitesphere-loader__stage {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sitesphere-loader__icon {
            width: 150px;
            height: auto;
        }

        .sitesphere-loader__glow {
            filter: drop-shadow(0 0 14px color-mix(in srgb, var(--sitesphere-loader-accent) 55%, transparent));
        }

        .sitesphere-loader__draw path {
            fill: var(--sitesphere-loader-accent);
            stroke: var(--sitesphere-loader-accent);
            stroke-width: 2.4;
            stroke-linejoin: round;
            stroke-dasharray: 100;
            stroke-dashoffset: 100;
            fill-opacity: 0;
            stroke-opacity: 1;
        }

        .sitesphere-loader.is-active .sitesphere-loader__draw-a {
            animation: sitesphereLoaderDraw 1.2s ease-in-out infinite;
            animation-fill-mode: both;
        }

        .sitesphere-loader.is-active .sitesphere-loader__draw-b {
            animation: sitesphereLoaderDraw 1.2s ease-in-out infinite;
            animation-delay: 0.25s;
            animation-fill-mode: both;
        }

        @keyframes sitesphereLoaderDraw {
            0% { stroke-dashoffset: 100; fill-opacity: 0; stroke-opacity: 1; }
            38% { stroke-dashoffset: 0; fill-opacity: 0; stroke-opacity: 1; }
            52% { stroke-dashoffset: 0; fill-opacity: 1; stroke-opacity: 1; }
            80% { stroke-dashoffset: 0; fill-opacity: 1; stroke-opacity: 1; }
            96% { stroke-dashoffset: 0; fill-opacity: 0; stroke-opacity: 0; }
            100% { stroke-dashoffset: 0; fill-opacity: 0; stroke-opacity: 0; }
        }

        @media (prefers-reduced-motion: reduce) {
            .sitesphere-loader,
            .sitesphere-loader.is-active .sitesphere-loader__draw-a,
            .sitesphere-loader.is-active .sitesphere-loader__draw-b {
                transition: none;
                animation: none;
            }
        }
    </style>

    <script>
        (() => {
            if (window.siteSpherePageLoaderInitialized) {
                return;
            }

            window.siteSpherePageLoaderInitialized = true;

            const loader = document.querySelector('[data-page-loader]');

            if (! loader) {
                return;
            }

            const minimumVisibleMilliseconds = 1000;
            let shownAt = 0;
            let hideTimeout = null;

            const showLoader = () => {
                if (hideTimeout) {
                    clearTimeout(hideTimeout);
                    hideTimeout = null;
                }

                shownAt = Date.now();
                loader.classList.remove('is-active');
                void loader.offsetWidth;
                loader.classList.add('is-active');
                loader.setAttribute('aria-hidden', 'false');
            };

            const hideLoader = () => {
                const deactivateLoader = () => {
                    loader.classList.remove('is-active');
                    loader.setAttribute('aria-hidden', 'true');
                    hideTimeout = null;
                };

                if (! shownAt) {
                    deactivateLoader();

                    return;
                }

                const elapsedMilliseconds = Date.now() - shownAt;
                const remainingMilliseconds = minimumVisibleMilliseconds - elapsedMilliseconds;

                if (remainingMilliseconds <= 0) {
                    deactivateLoader();

                    return;
                }

                hideTimeout = setTimeout(deactivateLoader, remainingMilliseconds);
            };

            const navigationEntry = performance.getEntriesByType('navigation')[0];

            const isModifiedClick = (event) => event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0;

            const isEligibleLink = (link, event) => {
                if (! link || event.defaultPrevented || isModifiedClick(event) || link.closest('[data-no-loader]')) {
                    return false;
                }

                if (link.hasAttribute('download') || (link.target && link.target !== '_self')) {
                    return false;
                }

                const href = link.getAttribute('href');

                if (! href || href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:') || href.startsWith('javascript:')) {
                    return false;
                }

                const destination = new URL(link.href, window.location.href);
                const current = new URL(window.location.href);
                const onlyHashChanges = destination.origin === current.origin
                    && destination.pathname === current.pathname
                    && destination.search === current.search
                    && destination.hash;

                return destination.origin === current.origin && ! onlyHashChanges;
            };

            document.addEventListener('click', (event) => {
                const link = event.target.closest('a[href]');

                if (isEligibleLink(link, event)) {
                    showLoader();
                }
            });

            document.addEventListener('submit', (event) => {
                if (! event.defaultPrevented && ! event.target.closest('[data-no-loader]')) {
                    showLoader();
                }
            });

            if (navigationEntry?.type === 'reload') {
                showLoader();
            }

            window.addEventListener('beforeunload', showLoader);
            window.addEventListener('pagehide', showLoader);
            window.addEventListener('load', hideLoader);
            window.addEventListener('pageshow', hideLoader);
        })();
    </script>
@endonce
