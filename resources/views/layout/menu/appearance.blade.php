@extends('dashboard')

@section('title')
    Appearance
@endsection

@push('styles')
    @vite('resources/css/appearance.css')
@endpush

@section('content')
    @php
        $dashboardMenuLocation = in_array($menuBarLocation ?? 'left', ['top', 'right', 'bottom', 'left'], true)
            ? $menuBarLocation
            : 'left';
        $selectedThemeId = (int) old('theme_id', $appearanceSettings->theme_id);
        $usesCustomTheme = old('use_custom_theme', $appearanceSettings->use_custom_theme ? '1' : '0') === '1';
        $customTheme = $customTheme ?? $appearanceSettings->customTheme;
        $customBackground = old('background_color', $customTheme?->background_color ?? ($themeColors['background'] ?? '#ffffff'));
        $customText = old('text_color', $customTheme?->text_color ?? ($themeColors['text'] ?? '#0d1b2a'));
        $customAccent = old('accent_color', $customTheme?->accent_color ?? ($themeColors['accent'] ?? '#6c5ce7'));
        $selectedFontId = (int) old('font_id', $currentFontId ?: $fonts->firstWhere('is_default', true)?->id);
        $selectedMenuLocation = old('menuBar_location', $appearanceSettings->menuBar_location ?? 'left');
        $selectedToastPosition = old('noti_location', $appearanceSettings->noti_location ?? 'top-end');
        $selectedDarkMode = old('dark_mode', $appearanceSettings->dark_mode ? '1' : '0') === '1';
        $presetThemeOptions = collect($presetThemes)
            ->map(
                fn ($theme) => [
                    'id' => (int) $theme['id'],
                    'name' => $theme['name'],
                    'accent' => $theme['accent_color'],
                ],
            )
            ->values();
        $fontOptions = $fonts
            ->map(
                fn ($font) => [
                    'id' => (int) $font->id,
                    'name' => $font->display_name ?? $font->font_family,
                    'family' => $font->font_family,
                ],
            )
            ->values();
        $menuLayouts = [
            'left' => ['title' => 'Left Sidebar', 'description' => 'Classic account navigation beside content.'],
            'right' => ['title' => 'Right Sidebar', 'description' => 'Navigation follows the reading edge.'],
            'top' => ['title' => 'Top Bar', 'description' => 'Compact horizontal account menu.'],
            'bottom' => ['title' => 'Bottom Bar', 'description' => 'Horizontal menu anchored below content.'],
        ];
        $toastPositions = [
            'top-start' => ['title' => 'Top Left', 'description' => 'Alerts appear near the upper start corner.'],
            'top-end' => ['title' => 'Top Right', 'description' => 'Alerts appear near the upper end corner.'],
            'bottom-start' => ['title' => 'Bottom Left', 'description' => 'Alerts appear near the lower start corner.'],
            'bottom-end' => ['title' => 'Bottom Right', 'description' => 'Alerts appear near the lower end corner.'],
        ];
    @endphp

    <x-layout.nav />

    <div class="dashboard-page dashboard-page--{{ $dashboardMenuLocation }} appearance-page">
        <x-layout.menu :menu-bar-location="$dashboardMenuLocation" />

        <main class="dashboard-content appearance-content" aria-labelledby="appearanceTitle">
            <form method="POST" action="{{ route('appearance.update') }}" class="appearance-shell"
                x-data="{
                    useCustomTheme: {{ $usesCustomTheme ? 'true' : 'false' }},
                    darkMode: '{{ $selectedDarkMode ? '1' : '0' }}',
                    selectedThemeId: {{ $selectedThemeId }},
                    selectedFontId: {{ $selectedFontId }},
                    customBackground: '{{ $customBackground }}',
                    customText: '{{ $customText }}',
                    customAccent: '{{ $customAccent }}',
                    fontSearch: '',
                    fontResultsOpen: false,
                    themes: @js($presetThemeOptions),
                    fonts: @js($fontOptions),
                    isSubmitting: false,
                    initialMenuLocation: '{{ $selectedMenuLocation }}',
                    selectedTheme() {
                        return this.themes.find((theme) => theme.id === Number(this.selectedThemeId)) || this.themes[0];
                    },
                    selectedFont() {
                        return this.fonts.find((font) => font.id === Number(this.selectedFontId)) || this.fonts[0];
                    },
                    selectedFontFamily() {
                        return this.selectedFont()?.family || 'Figtree, sans-serif';
                    },
                    filteredFonts() {
                        const query = this.fontSearch.trim().toLowerCase();

                        if (! query) {
                            return this.fonts.slice(0, 6);
                        }

                        return this.fonts.filter((font) => font.name.toLowerCase().includes(query));
                    },
                    chooseFont(font) {
                        this.selectedFontId = font.id;
                        this.fontResultsOpen = false;
                    },
                    previewBackground() {
                        if (this.useCustomTheme) {
                            return this.customBackground;
                        }

                        return this.darkMode === '1' ? '#000000' : '#ffffff';
                    },
                    previewText() {
                        if (this.useCustomTheme) {
                            return this.customText;
                        }

                        return this.darkMode === '1' ? '#ffffff' : '#0d1b2a';
                    },
                    previewAccent() {
                        if (this.useCustomTheme) {
                            return this.customAccent;
                        }

                        return this.selectedTheme()?.accent || '#6c5ce7';
                    },
                    applyRootTheme() {
                        const root = document.documentElement;
                        root.style.setProperty('--accent-color', this.previewAccent());
                        root.style.setProperty('--background-color', this.previewBackground());
                        root.style.setProperty('--text-color', this.previewText());
                        root.style.setProperty('--font-family', this.selectedFontFamily());
                        document.body.style.fontFamily = 'var(--font-family)';
                    },
                    async submitForm(formElement) {
                        if (this.isSubmitting) return;
                        this.isSubmitting = true;

                        const formData = new FormData(formElement);

                        try {
                            const response = await fetch(formElement.action, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '',
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: formData
                            });

                            const data = await response.json();

                            if (response.ok) {
                                const locationChanged = formData.get('menuBar_location') !== this.initialMenuLocation;
                                Swal.fire({
                                    toast: true,
                                    position: formData.get('noti_location') || 'top-end',
                                    showConfirmButton: false,
                                    timer: locationChanged ? 1500 : 3000,
                                    timerProgressBar: true,
                                    icon: 'success',
                                    title: data.message || 'Appearance settings saved.',
                                    didOpen: (toast) => {
                                        toast.onmouseenter = Swal.stopTimer;
                                        toast.onmouseleave = Swal.resumeTimer;
                                    },
                                    ...(locationChanged ? {
                                        willClose: () => {
                                            location.reload();
                                        }
                                    } : {})
                                });
                            } else {
                                let errorText = 'An error occurred.';
                                if (data.errors) {
                                    errorText = Object.values(data.errors).flat().join(' ');
                                } else if (data.message) {
                                    errorText = data.message;
                                }

                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000,
                                    timerProgressBar: true,
                                    icon: 'error',
                                    title: errorText
                                });
                            }
                        } catch (error) {
                            console.error(error);
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true,
                                icon: 'error',
                                title: 'Could not save appearance settings. Please try again.'
                            });
                        } finally {
                            this.isSubmitting = false;
                        }
                    }
                }"
                x-init="
                    applyRootTheme();
                    $watch('useCustomTheme', () => applyRootTheme());
                    $watch('darkMode', () => applyRootTheme());
                    $watch('selectedThemeId', () => applyRootTheme());
                    $watch('customBackground', () => applyRootTheme());
                    $watch('customText', () => applyRootTheme());
                    $watch('customAccent', () => applyRootTheme());
                    $watch('selectedFontId', () => applyRootTheme());
                "
                @submit.prevent="submitForm($el)"
                data-appearance-page>
                @csrf
                @method('PATCH')

                <header class="appearance-header">
                    <div>
                        <p class="dashboard-kicker">Personal Workspace</p>
                        <h1 id="appearanceTitle">Appearance Settings</h1>
                        <p>Adjust the color system, typography, account menu layout, and alert placement for your SiteSphere workspace.</p>
                    </div>
                </header>

                @if ($errors->any())
                    <div class="appearance-errors" role="alert" data-appearance-errors>
                        <strong>Some appearance settings need attention.</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <section class="appearance-card" aria-labelledby="themeModeTitle">
                    <div class="appearance-section-heading">
                        <h2 id="themeModeTitle">Theme Mode</h2>
                        <p>Choose the base contrast for your workspace.</p>
                    </div>

                    <div class="appearance-choice-grid two" :class="{ 'is-disabled': useCustomTheme }">
                        <label class="appearance-mode-option">
                            <input type="radio" name="dark_mode" value="0" x-model="darkMode" :disabled="useCustomTheme" @checked(! $selectedDarkMode)>
                            <span class="appearance-mode-preview light">
                                <span></span><span></span><span></span>
                            </span>
                            <strong>Light</strong>
                            <small>Bright surfaces with dark text.</small>
                        </label>

                        <label class="appearance-mode-option">
                            <input type="radio" name="dark_mode" value="1" x-model="darkMode" :disabled="useCustomTheme" @checked($selectedDarkMode)>
                            <span class="appearance-mode-preview dark">
                                <span></span><span></span><span></span>
                            </span>
                            <strong>Dark</strong>
                            <small>Dark surfaces with light text.</small>
                        </label>
                    </div>
                </section>

                <section class="appearance-card" aria-labelledby="presetThemeTitle">
                    <div class="appearance-section-heading with-toggle">
                        <div>
                            <h2 id="presetThemeTitle">Color Theme</h2>
                            <p>Select one of the default colors or switch on custom colors.</p>
                        </div>

                        <label class="appearance-toggle">
                            <input type="hidden" name="use_custom_theme" value="0">
                            <input type="checkbox" name="use_custom_theme" value="1" x-model="useCustomTheme"
                                @checked($usesCustomTheme) data-appearance-custom-toggle>
                            <span aria-hidden="true"></span>
                            <strong>Custom theme</strong>
                        </label>
                    </div>

                    <div class="appearance-theme-grid" :class="{ 'is-disabled': useCustomTheme }">
                        @foreach ($presetThemes as $presetTheme)
                            <label class="appearance-theme-option">
                                <input type="radio" name="theme_id" value="{{ $presetTheme['id'] }}"
                                    @checked($selectedThemeId === $presetTheme['id'])
                                    x-model.number="selectedThemeId"
                                    :disabled="useCustomTheme"
                                    data-appearance-preset-theme>

                                <span class="appearance-swatch" style="--theme-swatch: {{ $presetTheme['accent_color'] }}"></span>
                                <strong>{{ $presetTheme['name'] }}</strong>
                                <small>{{ $presetTheme['accent_color'] }}</small>
                            </label>
                        @endforeach
                    </div>

                    <div class="appearance-custom-panel" :class="{ 'is-disabled': ! useCustomTheme }"
                        x-transition.opacity.duration.160ms
                        data-appearance-custom-panel data-appearance-stable-panel>
                        <label>
                            <span>Background</span>
                            <input type="color" name="background_color" value="{{ $customBackground }}"
                                x-model="customBackground"
                                :disabled="! useCustomTheme" data-appearance-background-color>
                        </label>
                        <label>
                            <span>Text</span>
                            <input type="color" name="text_color" value="{{ $customText }}"
                                x-model="customText"
                                :disabled="! useCustomTheme" data-appearance-text-color>
                        </label>
                        <label>
                            <span>Accent</span>
                            <input type="color" name="accent_color" value="{{ $customAccent }}"
                                x-model="customAccent"
                                :disabled="! useCustomTheme" data-appearance-accent-color>
                        </label>
                    </div>
                </section>

                <section class="appearance-card" aria-labelledby="fontTitle">
                    <div class="appearance-section-heading">
                        <h2 id="fontTitle">Font</h2>
                        <p>Choose from the curated database font catalog.</p>
                    </div>

                    <div data-appearance-fonts>
                        <div class="font-search-wrapper" @click.outside="fontResultsOpen = false">
                            <label class="sr-only" for="appearanceFontSearch">Search font style</label>
                            <input type="search" class="font-search" id="appearanceFontSearch"
                                placeholder="Search font style..." x-model="fontSearch"
                                @focus="fontResultsOpen = true"
                                @input="fontResultsOpen = true"
                                @keydown.escape="fontResultsOpen = false"
                                autocomplete="off" data-appearance-font-search>
                            <div class="font-search-results" :class="{ 'show': fontResultsOpen }"
                                data-appearance-font-results>
                                <template x-for="font in filteredFonts()" :key="font.id">
                                    <button type="button" class="font-search-option"
                                        @click="chooseFont(font)"
                                        x-text="font.name"></button>
                                </template>
                                <div class="font-search-empty" x-show="filteredFonts().length === 0">
                                    No matching fonts
                                </div>
                            </div>
                        </div>

                        <div class="font-row">
                            <div class="font-select-wrapper">
                                <label class="sr-only" for="appearanceFontSelect">Font family</label>
                                <select class="font-select" id="appearanceFontSelect" name="font_id"
                                    x-model.number="selectedFontId" data-appearance-font-select>
                                    @foreach ($fonts as $font)
                                        <option value="{{ $font->id }}" @selected($selectedFontId === $font->id)>
                                            {{ $font->display_name ?? $font->font_family }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="font-preview-box" x-bind:style="{ fontFamily: selectedFontFamily() }"
                                data-appearance-font-preview>
                                The quick brown fox jumps over the lazy dog.
                            </div>
                        </div>
                    </div>
                </section>

                <section class="appearance-card" aria-labelledby="layoutTitle">
                    <div class="appearance-section-heading">
                        <h2 id="layoutTitle">Sidebar Layout</h2>
                        <p>Control where the account menu appears on dashboard pages.</p>
                    </div>

                    <div class="appearance-choice-grid four" data-appearance-menu-layouts>
                        @foreach ($menuLayouts as $layout => $copy)
                            <label class="appearance-layout-option">
                                <input type="radio" name="menuBar_location" value="{{ $layout }}"
                                    @checked($selectedMenuLocation === $layout) data-appearance-menu-location>

                                <span class="appearance-layout-preview {{ $layout }}" aria-hidden="true">
                                    <i></i><b></b>
                                </span>
                                <strong>{{ $copy['title'] }}</strong>
                                <small>{{ $copy['description'] }}</small>
                            </label>
                        @endforeach
                    </div>
                </section>

                <section class="appearance-card" aria-labelledby="alertsTitle">
                    <div class="appearance-section-heading">
                        <h2 id="alertsTitle">Alert Box</h2>
                        <p>Choose where success and warning messages appear.</p>
                    </div>

                    <div class="appearance-choice-grid four" data-appearance-toast-positions>
                        @foreach ($toastPositions as $position => $copy)
                            <label class="appearance-layout-option">
                                <input type="radio" name="noti_location" value="{{ $position }}"
                                    @checked($selectedToastPosition === $position) data-appearance-toast-position>

                                <span class="appearance-alert-preview {{ $position }}" aria-hidden="true">
                                    <i></i>
                                </span>
                                <strong>{{ $copy['title'] }}</strong>
                                <small>{{ $copy['description'] }}</small>
                            </label>
                        @endforeach
                    </div>
                </section>

                @if (session('success'))
                    <script>
                        document.addEventListener('DOMContentLoaded', () => {
                            Swal.fire({
                                toast: true,
                                position: '{{ $selectedToastPosition }}',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true,
                                icon: 'success',
                                title: '{{ session('success') }}',
                                didOpen: (toast) => {
                                    toast.onmouseenter = Swal.stopTimer;
                                    toast.onmouseleave = Swal.resumeTimer;
                                }
                            });
                        });
                    </script>
                @endif

                <div class="appearance-actions">
                    <button type="submit" class="save-btn" data-appearance-save :class="{ 'is-loading': isSubmitting }" :disabled="isSubmitting">
                        <span class="button-label">
                            <svg xmlns="http://www.w3.org/2000/svg" class="save-btn-icon" viewBox="0 0 16 16"
                                fill="currentColor" aria-hidden="true">
                                <path
                                    d="M8.5 1.5A1.5 1.5 0 0 1 10 3v1.5A1.5 1.5 0 0 1 8.5 6h-3A1.5 1.5 0 0 1 4 4.5v-3h4.5Z" />
                                <path
                                    d="M2 1.5A1.5 1.5 0 0 1 3.5 0h7.086a1.5 1.5 0 0 1 1.061.44l3.914 3.913A1.5 1.5 0 0 1 16 5.414V14.5a1.5 1.5 0 0 1-1.5 1.5H14v-5a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v5h-.5A1.5 1.5 0 0 1 0 14.5v-13A1.5 1.5 0 0 1 1.5 0H2v1.5Zm3 9A1 1 0 0 0 4 11.5V16h8v-4.5a1 1 0 0 0-1-1H5Z" />
                            </svg>
                            <span>Save Changes</span>
                        </span>
                        <span class="button-loader" aria-hidden="true">
                            <i></i><i></i><i></i>
                        </span>
                    </button>
                </div>
            </form>
        </main>
    </div>
@endsection
