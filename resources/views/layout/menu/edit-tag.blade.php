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
                removeCategory(category) {
                    if (! this.isAdmin || ! confirm('Delete this category?')) {
                        return;
                    }

                    this.categories = this.categories.filter((item) => item.uid !== category.uid);
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
                removeTag(category, tag) {
                    if (! this.isAdmin || ! confirm('Delete this tag?')) {
                        return;
                    }

                    category.tags = category.tags.filter((item) => item.uid !== tag.uid);
                },
                tint(color) {
                    const hex = /^#[0-9A-Fa-f]{6}$/.test(color || '') ? color : '#6c5ce7';
                    const red = parseInt(hex.slice(1, 3), 16);
                    const green = parseInt(hex.slice(3, 5), 16);
                    const blue = parseInt(hex.slice(5, 7), 16);

                    return `rgba(${red}, ${green}, ${blue}, 0.14)`;
                },
                previewTags() {
                    return this.categories.flatMap((category) => category.tags || []).slice(0, 7);
                },
            };
        };
    </script>

    <x-layout.nav />

    <div class="dashboard-page dashboard-page--{{ $dashboardMenuLocation }} edit-tag-page">
        <x-layout.menu :menu-bar-location="$dashboardMenuLocation" />

        <main class="dashboard-content edit-tag-content" aria-labelledby="editTagTitle">
            <section class="edit-tag-shell" data-edit-tag-page
                x-data="editTagPage({ taxonomy: @js($taxonomy), isAdmin: @js($isAdminTagEditor) })">
                <nav class="edit-tag-breadcrumbs" aria-label="Breadcrumb">
                    <a href="{{ route('dashboard') }}">Settings</a>
                    <span aria-hidden="true">/</span>
                    <span>{{ $isAdminTagEditor ? 'Admin Tag Styles' : 'Tag Styles' }}</span>
                </nav>

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

                @if (session('success'))
                    <div class="edit-tag-message success" role="status" data-edit-tag-success>
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->has('taxonomy'))
                    <div class="edit-tag-message error" role="alert" data-edit-tag-error>
                        {{ $errors->first('taxonomy') }}
                    </div>
                @endif

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
                                    <button type="button" class="thread-category-toggle" @click="toggleCategory(category)"
                                        :aria-expanded="(openCategory === category.uid).toString()">
                                        <span class="thread-category-head">
                                            <span class="thread-category-color" :style="{ backgroundColor: category.color }"></span>
                                            <template x-if="isAdmin && isEditing(category)">
                                                <input class="thread-category-name-input" type="text" x-model="category.name"
                                                    aria-label="Category name">
                                            </template>
                                            <template x-if="! (isAdmin && isEditing(category))">
                                                <span class="thread-category-name" x-text="category.name"></span>
                                            </template>
                                            <span class="thread-category-count" x-text="category.tags.length"></span>
                                        </span>
                                        <span class="thread-chevron" aria-hidden="true">⌄</span>
                                    </button>

                                    <div class="thread-dropdown" x-show="openCategory === category.uid" x-cloak>
                                        <div class="thread-actions">
                                            <button type="button" class="thread-btn" @click="startEditing(category)"
                                                :class="{ 'is-active': isEditing(category) }">
                                                <span x-text="isEditing(category) ? 'Done Editing' : 'Edit'"></span>
                                            </button>

                                            <template x-if="isAdmin">
                                                <button type="button" class="thread-btn" @click="addTag(category)">Add Tag</button>
                                            </template>

                                            <template x-if="isAdmin">
                                                <button type="button" class="thread-btn danger"
                                                    @click="removeCategory(category)">Delete</button>
                                            </template>
                                        </div>

                                        <template x-if="isAdmin && isEditing(category)">
                                            <label class="thread-category-color-row">
                                                <span>Category color</span>
                                                <input type="color" x-model="category.color">
                                            </label>
                                        </template>

                                        <div class="thread-tags">
                                            <template x-for="tag in visibleTags(category)" :key="tag.uid">
                                                <span class="thread-tag-chip"
                                                    :style="{ backgroundColor: tint(tag.color), color: tag.color }"
                                                    data-edit-tag-chip>
                                                    <template x-if="isEditing(category)">
                                                        <input type="text" x-model="tag.name" aria-label="Tag name">
                                                    </template>
                                                    <template x-if="! isEditing(category)">
                                                        <span class="thread-tag-name" x-text="tag.name"></span>
                                                    </template>
                                                    <template x-if="isEditing(category)">
                                                        <input type="color" x-model="tag.color" aria-label="Tag color">
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
                                    <button type="submit" class="btn-primary" data-edit-tag-publish>
                                        <x-fas-floppy-disk class="btn-icon" aria-hidden="true" />
                                        <span>Publish to users</span>
                                    </button>
                                @else
                                    <button type="submit" class="btn-primary" data-edit-tag-save>
                                        <x-fas-floppy-disk class="btn-icon" aria-hidden="true" />
                                        <span>Save Changes</span>
                                    </button>
                                @endif
                            </div>
                        </section>
                    </aside>
                </form>

                @unless ($isAdminTagEditor)
                    <form method="POST" action="{{ route('edit-tag.reset') }}" class="edit-tag-reset-form"
                        data-edit-tag-reset-form>
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-secondary" data-edit-tag-reset>
                            <x-fas-rotate-left class="btn-icon" aria-hidden="true" />
                            <span>Reset to Defaults</span>
                        </button>
                    </form>
                @endunless
            </section>
        </main>
    </div>
@endsection
