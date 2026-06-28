@extends('dashboard')

@section('title')
    Edit Tag
@endsection

@push('styles')
    @vite('resources/css/edit-tag.css')
@endpush

@section('content')
    @php
        $dashboardMenuLocation = in_array($menuBarLocation ?? 'left', ['top', 'right', 'bottom', 'left'], true)
            ? $menuBarLocation
            : 'left';
    @endphp

    <script>
        window.editTagPage = function (initialState) {
            return {
                isAdmin: Boolean(initialState.isAdmin),
                isSubmitting: false,
                categories: (initialState.taxonomy || []).map((category) => ({
                    ...category,
                    uid: category.id || `new-${crypto.randomUUID()}`,
                    tags: (category.tags || []).map((tag) => ({
                        ...tag,
                        uid: tag.id || `new-${crypto.randomUUID()}`,
                    })),
                })),
                search: '',
                filter: 'all',
                openCategory: null,
                editingCategory: null,
                payload() {
                    return JSON.stringify(this.categories.map((category) => ({
                        id: category.id || null,
                        name: category.name,
                        color: category.color,
                        tags: category.tags.map((tag) => ({
                            id: tag.id || null,
                            name: tag.name,
                            color: tag.color,
                        })),
                    })));
                },
                async confirmSubmit(title, text) {
                    if (this.isSubmitting) return;

                    const result = await window.sitesphereSwal.confirm({
                        title: title,
                        text: text
                    });

                    if (result.isConfirmed) {
                        this.isSubmitting = true;
                        document.querySelector('[data-edit-tag-form]').submit();
                    }
                },
                async confirmReset(formElement) {
                    const result = await window.sitesphereSwal.confirm({
                        title: 'Reset to Defaults?',
                        text: 'Are you sure you want to revert all tag styles to system defaults? This cannot be undone.',
                        icon: 'warning',
                        confirmButtonText: 'Yes, reset them!'
                    });

                    if (result.isConfirmed) {
                        formElement.submit();
                    }
                },
                filteredCategories() {
                    const query = this.search.trim().toLowerCase();

                    return this.categories.filter((category) => {
                        if (query === '') {
                            return true;
                        }

                        const categoryMatches = String(category.name || '').toLowerCase().includes(query);
                        const tagMatches = (category.tags || []).some((tag) => String(tag.name || '').toLowerCase().includes(query));

                        return this.filter === 'categories' ? categoryMatches : categoryMatches || tagMatches;
                    });
                },
                visibleTags(category) {
                    const query = this.search.trim().toLowerCase();

                    if (query === '' || this.filter === 'categories') {
                        return category.tags || [];
                    }

                    return (category.tags || []).filter((tag) => String(tag.name || '').toLowerCase().includes(query));
                },
                toggleCategory(category) {
                    this.openCategory = this.openCategory === category.uid ? null : category.uid;
                    if (this.openCategory === null) {
                        this.editingCategory = null;
                    }
                },
                toggleFilter() {
                    this.filter = this.filter === 'all' ? 'categories' : 'all';
                },
                startEditing(category) {
                    this.openCategory = category.uid;
                    this.editingCategory = this.editingCategory === category.uid ? null : category.uid;
                },
                isEditing(category) {
                    return this.editingCategory === category.uid;
                },
                addCategory() {
                    if (! this.isAdmin) {
                        return;
                    }

                    const category = {
                        id: null,
                        uid: `new-${crypto.randomUUID()}`,
                        name: 'New Category',
                        color: getComputedStyle(document.documentElement).getPropertyValue('--accent-color').trim() || '#6c5ce7',
                        tags: [],
                    };

                    this.categories.push(category);
                    this.openCategory = category.uid;
                    this.editingCategory = category.uid;
                },
                async removeCategory(category) {
                    if (! this.isAdmin) {
                        return;
                    }

                    const result = await window.sitesphereSwal.confirm({
                        title: 'Delete Category?',
                        text: `Are you sure you want to delete "${category.name}"? This action cannot be undone.`,
                        icon: 'warning',
                        confirmButtonText: 'Yes, delete it!'
                    });

                    if (result.isConfirmed) {
                        this.categories = this.categories.filter((item) => item.uid !== category.uid);
                        this.$nextTick(() => {
                            const formElement = document.querySelector('[data-edit-tag-form]');
                            if (formElement) {
                                formElement.submit();
                            }
                        });
                    }
                },
                addTag(category) {
                    if (! this.isAdmin) {
                        return;
                    }

                    category.tags.push({
                        id: null,
                        uid: `new-${crypto.randomUUID()}`,
                        name: 'newtag',
                        color: category.color,
                    });

                    this.openCategory = category.uid;
                    this.editingCategory = category.uid;
                },
                async removeTag(category, tag) {
                    if (! this.isAdmin) {
                        return;
                    }

                    const result = await window.sitesphereSwal.confirm({
                        title: 'Delete Tag?',
                        text: `Are you sure you want to delete "${tag.name}"? This action cannot be undone.`,
                        icon: 'warning',
                        confirmButtonText: 'Yes, delete it!'
                    });

                    if (result.isConfirmed) {
                        category.tags = category.tags.filter((item) => item.uid !== tag.uid);
                        this.$nextTick(() => {
                            const formElement = document.querySelector('[data-edit-tag-form]');
                            if (formElement) {
                                formElement.submit();
                            }
                        });
                    }
                },
                shareModalOpen: false,
                shareSourceCategory: null,
                shareSelectedTags: [],
                shareSelectedCategories: [],
                openShareModal(category) {
                    this.shareSourceCategory = category;
                    this.shareSelectedTags = (category.tags || []).map((t) => t.uid);
                    this.shareSelectedCategories = [];
                    this.shareModalOpen = true;
                },
                toggleShareTag(tagUid) {
                    const idx = this.shareSelectedTags.indexOf(tagUid);
                    if (idx > -1) {
                        this.shareSelectedTags.splice(idx, 1);
                    } else {
                        this.shareSelectedTags.push(tagUid);
                    }
                },
                toggleShareCategory(catUid) {
                    const idx = this.shareSelectedCategories.indexOf(catUid);
                    if (idx > -1) {
                        this.shareSelectedCategories.splice(idx, 1);
                    } else {
                        this.shareSelectedCategories.push(catUid);
                    }
                },
                confirmShare() {
                    if (! this.shareSourceCategory || ! this.shareSelectedTags.length || ! this.shareSelectedCategories.length) {
                        return;
                    }
                    const tagsToShare = this.shareSourceCategory.tags.filter((t) => this.shareSelectedTags.includes(t.uid));
                    this.shareSelectedCategories.forEach((catUid) => {
                        const targetCategory = this.categories.find((c) => c.uid === catUid);
                        if (targetCategory) {
                            tagsToShare.forEach((sourceTag) => {
                                const exists = targetCategory.tags.some((t) => t.name.toLowerCase().trim() === sourceTag.name.toLowerCase().trim());
                                if (! exists) {
                                    targetCategory.tags.push({
                                        id: null,
                                        uid: `new-${crypto.randomUUID()}`,
                                        name: sourceTag.name,
                                        color: sourceTag.color,
                                    });
                                }
                            });
                        }
                    });
                    this.closeShareModal();
                },
                closeShareModal() {
                    this.shareModalOpen = false;
                    this.shareSourceCategory = null;
                    this.shareSelectedTags = [];
                    this.shareSelectedCategories = [];
                },
                syncTagColors(changedTag, field) {
                    const nameLower = (changedTag.name || '').toLowerCase().trim();
                    if (! nameLower) {
                        return;
                    }

                    if (field === 'color') {
                        this.categories.forEach((category) => {
                            (category.tags || []).forEach((tag) => {
                                if ((tag.name || '').toLowerCase().trim() === nameLower) {
                                    tag.color = changedTag.color;
                                }
                            });
                        });
                    } else if (field === 'name') {
                        let matchingColor = null;
                        for (const category of this.categories) {
                            for (const tag of category.tags || []) {
                                if (tag.uid !== changedTag.uid && (tag.name || '').toLowerCase().trim() === nameLower) {
                                    matchingColor = tag.color;
                                    break;
                                }
                            }
                            if (matchingColor) {
                                break;
                            }
                        }
                        if (matchingColor) {
                            changedTag.color = matchingColor;
                            this.categories.forEach((category) => {
                                (category.tags || []).forEach((tag) => {
                                    if ((tag.name || '').toLowerCase().trim() === nameLower) {
                                        tag.color = matchingColor;
                                    }
                                });
                            });
                        }
                    }
                },
                tint(color) {
                    const hex = /^#[0-9A-Fa-f]{6}$/.test(color || '') ? color : '#6c5ce7';
                    const red = parseInt(hex.slice(1, 3), 16);
                    const green = parseInt(hex.slice(3, 5), 16);
                    const blue = parseInt(hex.slice(5, 7), 16);

                    return `rgba(${red}, ${green}, ${blue}, 0.14)`;
                },
                previewTags() {
                    if (this.openCategory) {
                        const openCat = this.categories.find((c) => c.uid === this.openCategory);
                        if (openCat) {
                            return openCat.tags || [];
                        }
                    }
                    return this.categories.flatMap((category) => category.tags || []).slice(0, 7);
                }
            };
        };
    </script>

    <x-layout.nav />

    <div class="dashboard-page dashboard-page--{{ $dashboardMenuLocation }} edit-tag-page">
        <x-layout.menu :menu-bar-location="$dashboardMenuLocation" />

        <main class="dashboard-content edit-tag-content" aria-labelledby="editTagTitle">
            <section class="edit-tag-shell" data-edit-tag-page
                x-data="editTagPage({ taxonomy: @js($taxonomy), isAdmin: @js($isAdminTagEditor) })">

                <header class="edit-tag-header">
                    <div>
                        <p class="dashboard-kicker">{{ $isAdminTagEditor ? 'Global taxonomy control' : 'Personal tag style settings' }}</p>
                        <h1 id="editTagTitle">
                            <x-fas-tags class="edit-tag-title-icon" aria-hidden="true" />
                            {{ $isAdminTagEditor ? 'Admin Tag Styles' : 'Tag Styles' }}
                        </h1>
                        <p>Manage content categories and sub-tags used in website review posts.</p>
                    </div>
                </header>

                <section class="edit-tag-summary" aria-label="Tag summary">
                    <article>
                        <span>Categories</span>
                        <strong>{{ $tagSummary['categories'] }}</strong>
                    </article>
                    <article>
                        <span>Global Tags</span>
                        <strong>{{ $tagSummary['tags'] }}</strong>
                    </article>
                    <article>
                        <span>{{ $isAdminTagEditor ? 'Mode' : 'Custom Styles' }}</span>
                        <strong>{{ $isAdminTagEditor ? 'Admin' : $tagSummary['custom'] }}</strong>
                    </article>
                </section>

                <form method="POST" action="{{ route('edit-tag.update') }}" class="edit-tag-grid" data-edit-tag-form>
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="taxonomy" :value="payload()" data-edit-tag-payload>

                    <section class="edit-tag-card edit-tag-taxonomy-card">
                        <div class="edit-tag-card-header">
                            <div>
                                <h2>Categories &amp; Tags</h2>
                                <p>Search works across categories and sub-tags.</p>
                            </div>
                        </div>

                        <div class="edit-tag-toolbar">
                            <label class="edit-tag-search">
                                <x-fas-search class="edit-tag-search-icon" aria-hidden="true" />
                                <span class="sr-only">Search categories or tags</span>
                                <input type="search" x-model.debounce.150ms="search" placeholder="Search categories or tags..."
                                    data-edit-tag-search>
                            </label>

                            <button type="button" class="edit-tag-filter" :aria-pressed="filter === 'categories'"
                                @click="toggleFilter" data-edit-tag-filter>
                                <span x-text="filter === 'categories' ? 'Categories' : 'All'"></span>
                            </button>
                        </div>

                        <div class="edit-tag-table-header">Categories and sub-tags</div>

                        <div class="edit-tag-list" data-edit-tag-list>
                            <template x-for="category in filteredCategories()" :key="category.uid">
                                <article class="thread-category" :class="{ 'is-open': openCategory === category.uid }"
                                    data-edit-tag-category>
                                    <div class="thread-category-toggle-wrapper">
                                        <template x-if="isAdmin && isEditing(category)">
                                            <div class="thread-category-toggle thread-category-toggle--editing">
                                                <span class="thread-category-head">
                                                    <span class="thread-category-color" :style="{ backgroundColor: category.color }"></span>
                                                    <input class="thread-category-name-input" type="text" x-model="category.name"
                                                        aria-label="Category name" @click.stop @keydown.enter.prevent>
                                                    <span class="thread-category-count" x-text="category.tags.length"></span>
                                                </span>
                                                <button type="button" class="thread-chevron-btn" @click="toggleCategory(category)"
                                                    :aria-expanded="(openCategory === category.uid).toString()" aria-label="Toggle category">
                                                    <span class="thread-chevron" aria-hidden="true">⌄</span>
                                                </button>
                                            </div>
                                        </template>
                                        <template x-if="! (isAdmin && isEditing(category))">
                                            <button type="button" class="thread-category-toggle" @click="toggleCategory(category)"
                                                :aria-expanded="(openCategory === category.uid).toString()">
                                                <span class="thread-category-head">
                                                    <span class="thread-category-color" :style="{ backgroundColor: category.color }"></span>
                                                    <span class="thread-category-name" x-text="category.name"></span>
                                                    <span class="thread-category-count" x-text="category.tags.length"></span>
                                                </span>
                                                <span class="thread-chevron" aria-hidden="true">⌄</span>
                                            </button>
                                        </template>
                                    </div>
                                    <div class="thread-dropdown" x-show="openCategory === category.uid" x-cloak>
                                        <div class="thread-actions">
                                            <button type="button" class="thread-btn" @click="startEditing(category)" :class="{ 'is-active': isEditing(category) }"><span x-text="isEditing(category) ? 'Done Editing' : 'Edit'"></span></button>

                                            <template x-if="isAdmin">
                                                <button type="button" class="thread-btn" @click="addTag(category)">Add Tag</button>
                                            </template>

                                            <template x-if="isAdmin">
                                                <button type="button" class="thread-btn danger" @click="removeCategory(category)">Delete</button>
                                            </template>

                                            <template x-if="isAdmin">
                                                <button type="button" class="thread-share-icon-btn" @click="openShareModal(category)" aria-label="Share tags across categories">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-share-fill" viewBox="0 0 16 16">
                                                        <path d="M11 2.5a2.5 2.5 0 1 1 .603 1.628l-6.718 3.12a2.5 2.5 0 0 1 0 1.504l6.718 3.12a2.5 2.5 0 1 1-.488.876l-6.718-3.12a2.5 2.5 0 1 1 0-3.256l6.718-3.12A2.5 2.5 0 0 1 11 2.5"/>
                                                    </svg>
                                                </button>
                                            </template>
                                        </div>

                                        <template x-if="isAdmin && isEditing(category)">
                                            <label class="thread-category-color-row">
                                                <span>Category color</span>
                                                <div class="color-picker-component" x-data="{
                                                    showPicker: false,
                                                    textValue: category.color,
                                                    syncFromText() {
                                                        let val = this.textValue.trim();
                                                        if (val && !val.startsWith('#')) val = '#' + val;
                                                        if (/^#[0-9A-Fa-f]{6}$/.test(val)) {
                                                            category.color = val;
                                                        }
                                                    },
                                                    syncFromPicker(e) {
                                                        category.color = e.target.value;
                                                        this.textValue = e.target.value;
                                                    }
                                                }">
                                                    <div class="color-picker-row">
                                                        <button type="button" class="color-picker-swatch"
                                                            :style="{ backgroundColor: category.color }"
                                                            @click="$refs.catPicker.click()"
                                                            aria-label="Pick color">
                                                        </button>
                                                        <input type="text" class="color-picker-text"
                                                            x-model="textValue"
                                                            @input="syncFromText()"
                                                            @blur="if (!/^#[0-9A-Fa-f]{6}$/.test(textValue)) textValue = category.color"
                                                            placeholder="#FF5733"
                                                            maxlength="7"
                                                            spellcheck="false"
                                                            autocomplete="off">
                                                        <input type="color" x-ref="catPicker"
                                                            :value="category.color"
                                                            @input="syncFromPicker($event)"
                                                            class="color-picker-native"
                                                            tabindex="-1">
                                                    </div>
                                                </div>
                                            </label>
                                        </template>

                                        <div class="thread-tags">
                                            <template x-for="tag in visibleTags(category)" :key="tag.uid">
                                                <span class="thread-tag-chip"
                                                    :style="{ backgroundColor: tint(tag.color), color: tag.color }"
                                                    data-edit-tag-chip>
                                                    <template x-if="isEditing(category)">
                                                        <input type="text" x-model="tag.name" @input="syncTagColors(tag, 'name')" aria-label="Tag name" class="outline-none" @keydown.space.stop @keyup.space.stop @keypress.space.stop @keydown.enter.prevent>
                                                    </template>
                                                    <template x-if="! isEditing(category)">
                                                        <span class="thread-tag-name" x-text="tag.name"></span>
                                                    </template>
                                                    <template x-if="isEditing(category)">
                                                        <div class="color-picker-component" x-data="{
                                                            textValue: tag.color,
                                                            syncFromText() {
                                                                let val = this.textValue.trim();
                                                                if (val && !val.startsWith('#')) val = '#' + val;
                                                                if (/^#[0-9A-Fa-f]{6}$/.test(val)) {
                                                                    tag.color = val;
                                                                    syncTagColors(tag, 'color');
                                                                }
                                                            },
                                                            syncFromPicker(e) {
                                                                tag.color = e.target.value;
                                                                this.textValue = e.target.value;
                                                                syncTagColors(tag, 'color');
                                                            }
                                                        }" style="display: inline-flex; align-items: center; gap: 4px;">
                                                            <button type="button" class="color-picker-swatch"
                                                                style="width: 24px; height: 24px; border-radius: 4px;"
                                                                :style="{ backgroundColor: tag.color }"
                                                                @click="$refs.tagPicker.click()"
                                                                aria-label="Pick color">
                                                            </button>
                                                            <input type="text" class="color-picker-text"
                                                                style="width: 80px; padding: 4px 6px; font-size: 11px;"
                                                                x-model="textValue"
                                                                @input="syncFromText()"
                                                                @blur="if (!/^#[0-9A-Fa-f]{6}$/.test(textValue)) textValue = tag.color"
                                                                placeholder="#FF5733"
                                                                maxlength="7"
                                                                spellcheck="false"
                                                                autocomplete="off">
                                                            <input type="color" x-ref="tagPicker"
                                                                :value="tag.color"
                                                                @input="syncFromPicker($event)"
                                                                class="color-picker-native"
                                                                tabindex="-1">
                                                        </div>
                                                    </template>
                                                    <template x-if="isAdmin && isEditing(category)">
                                                        <button type="button" class="thread-chip-remove"
                                                            @click="removeTag(category, tag)" aria-label="Remove tag">
                                                            &times;
                                                        </button>
                                                    </template>
                                                </span>
                                            </template>

                                            <template x-if="visibleTags(category).length === 0">
                                                <span class="thread-tag-chip muted">No matching tags</span>
                                            </template>
                                        </div>
                                    </div>
                                </article>
                            </template>

                            <div class="edit-tag-empty" x-show="filteredCategories().length === 0" x-cloak>
                                No tags found.
                            </div>
                        </div>
                    </section>

                    <aside class="edit-tag-side">
                        <section class="edit-tag-card edit-tag-preview-card">
                            <h2>Preview</h2>
                            <p>This is how tags will appear in website review posts.</p>

                            <div class="mock-post">
                                <div class="skeleton-line short"></div>
                                <div class="skeleton-line long"></div>
                                <div class="skeleton-line medium"></div>
                                <div class="post-tags">
                                    <template x-for="tag in previewTags()" :key="tag.uid">
                                        <span class="preview-badge"
                                            :style="{ backgroundColor: tint(tag.color), color: tag.color }"
                                            x-text="tag.name"></span>
                                    </template>
                                    <template x-if="previewTags().length === 0">
                                        <span class="preview-badge muted">No tags yet</span>
                                    </template>
                                </div>
                            </div>

                            <div class="tag-save-actions">
                                @if ($isAdminTagEditor)
                                    <button type="button" class="btn-secondary" @click="addCategory" data-edit-tag-add-category>
                                        <x-fas-plus class="btn-icon" aria-hidden="true" />
                                        <span>Add Category</span>
                                    </button>
                                    <button type="button" class="btn-primary" data-edit-tag-publish @click="confirmSubmit('Publish Tags?', 'Are you sure you want to publish these tags to all users?')" :class="{ 'is-loading': isSubmitting }" :disabled="isSubmitting">
                                        <span class="button-label">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="btn-icon" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                                                <path d="M8.5 1.5A1.5 1.5 0 0 1 10 3v1.5A1.5 1.5 0 0 1 8.5 6h-3A1.5 1.5 0 0 1 4 4.5v-3h4.5Z" />
                                                <path d="M2 1.5A1.5 1.5 0 0 1 3.5 0h7.086a1.5 1.5 0 0 1 1.061.44l3.914 3.913A1.5 1.5 0 0 1 16 5.414V14.5a1.5 1.5 0 0 1-1.5 1.5H14v-5a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v5h-.5A1.5 1.5 0 0 1 0 14.5v-13A1.5 1.5 0 0 1 1.5 0H2v1.5Zm3 9A1 1 0 0 0 4 11.5V16h8v-4.5a1 1 0 0 0-1-1H5Z" />
                                            </svg>
                                            <span>Publish to users</span>
                                        </span>
                                    </button>
                                @else
                                    <button type="button" class="btn-primary" data-edit-tag-save @click="confirmSubmit('Save Tags?', 'Are you sure you want to save these tag styles?')" :class="{ 'is-loading': isSubmitting }" :disabled="isSubmitting">
                                        <span class="button-label">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="btn-icon" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                                                <path d="M8.5 1.5A1.5 1.5 0 0 1 10 3v1.5A1.5 1.5 0 0 1 8.5 6h-3A1.5 1.5 0 0 1 4 4.5v-3h4.5Z" />
                                                <path d="M2 1.5A1.5 1.5 0 0 1 3.5 0h7.086a1.5 1.5 0 0 1 1.061.44l3.914 3.913A1.5 1.5 0 0 1 16 5.414V14.5a1.5 1.5 0 0 1-1.5 1.5H14v-5a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v5h-.5A1.5 1.5 0 0 1 0 14.5v-13A1.5 1.5 0 0 1 1.5 0H2v1.5Zm3 9A1 1 0 0 0 4 11.5V16h8v-4.5a1 1 0 0 0-1-1H5Z" />
                                            </svg>
                                            <span>Save Changes</span>
                                        </span>
                                    </button>
                                @endif
                            </div>
                        </section>
                    </aside>
                </form>

                @unless ($isAdminTagEditor)
                    <form method="POST" action="{{ route('edit-tag.reset') }}" class="edit-tag-reset-form"
                        data-edit-tag-reset-form @submit.prevent="confirmReset($el)">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-secondary" data-edit-tag-reset>
                            <x-fas-rotate-left class="btn-icon" aria-hidden="true" />
                            <span>Reset to Defaults</span>
                        </button>
                    </form>
                @endunless
                @if ($isAdminTagEditor)
                    <!-- Share tags modal -->
                    <div class="thread-share-overlay" :class="{ 'show': shareModalOpen }" x-show="shareModalOpen" x-cloak @click.self="closeShareModal">
                        <div class="thread-share-dialog" role="dialog" aria-modal="true" aria-labelledby="threadShareTitle">
                            <div class="thread-share-header">
                                <div>
                                    <h3 id="threadShareTitle">Share tags</h3>
                                    <p>Choose tags from <span style="font-weight: 800; color: var(--accent-color);" x-text="shareSourceCategory ? shareSourceCategory.name : ''"></span> and the categories that should receive them.</p>
                                </div>
                                <button type="button" class="thread-share-close" @click="closeShareModal" aria-label="Close">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-circle-fill" viewBox="0 0 16 16" aria-hidden="true">
                                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293z"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="thread-share-grid">
                                <section>
                                    <h4>Tags to share</h4>
                                    <div class="thread-share-options">
                                        <template x-if="shareSourceCategory">
                                            <template x-for="tag in shareSourceCategory.tags" :key="tag.uid">
                                                <label class="thread-share-option" :class="{ 'is-selected': shareSelectedTags.includes(tag.uid) }">
                                                    <input type="checkbox" :value="tag.uid" :checked="shareSelectedTags.includes(tag.uid)" @change="toggleShareTag(tag.uid)">
                                                    <span x-text="tag.name"></span>
                                                    <span class="thread-share-check" aria-hidden="true">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2" viewBox="0 0 16 16">
                                                            <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0"/>
                                                        </svg>
                                                    </span>
                                                </label>
                                            </template>
                                        </template>
                                        <template x-if="shareSourceCategory && shareSourceCategory.tags.length === 0">
                                            <p class="thread-share-empty">This category has no tags to share yet.</p>
                                        </template>
                                    </div>
                                </section>
                                <section>
                                    <h4>Share across categories</h4>
                                    <div class="thread-share-options">
                                        <template x-for="cat in categories.filter(c => c.uid !== (shareSourceCategory ? shareSourceCategory.uid : ''))" :key="cat.uid">
                                            <label class="thread-share-option" :class="{ 'is-selected': shareSelectedCategories.includes(cat.uid) }">
                                                <input type="checkbox" :value="cat.uid" :checked="shareSelectedCategories.includes(cat.uid)" @change="toggleShareCategory(cat.uid)">
                                                <span x-text="cat.name"></span>
                                                <span class="thread-share-check" aria-hidden="true">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2" viewBox="0 0 16 16">
                                                        <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0"/>
                                                    </svg>
                                                </span>
                                            </label>
                                        </template>
                                        <template x-if="categories.length <= 1">
                                            <p class="thread-share-empty">No other categories available.</p>
                                        </template>
                                    </div>
                                </section>
                            </div>
                            <div class="thread-share-actions">
                                <button type="button" class="btn-secondary" @click="closeShareModal">Cancel</button>
                                <button type="button" class="btn-primary" @click="confirmShare" :disabled="!shareSelectedTags.length || !shareSelectedCategories.length">Share selected</button>
                            </div>
                        </div>
                    </div>
                @endif
            </section>
        </main>
    </div>

    @if(session('success') || $errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                window.sitesphereSwal.toast({
                    icon: '{{ session('success') ? 'success' : 'error' }}',
                    title: @json(session('success') ?? $errors->first()),
                    position: '{{ $toastPosition ?? "top-end" }}'
                });
            });
        </script>
    @endif
@endsection
