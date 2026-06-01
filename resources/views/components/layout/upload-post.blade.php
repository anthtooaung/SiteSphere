@php
    $categoryPayload = $categories
        ->mapWithKeys(fn ($category) => [
            $category->slug => [
                'name' => $category->name,
                'tags' => $category->tags
                    ->map(fn ($tag) => [
                        'id' => $tag->id,
                        'name' => $tag->name,
                        'color' => $tag->tag_color ?: '#6c5ce7',
                    ])
                    ->values(),
            ],
        ])
        ->all();

    $initialCategory = $categories->first();
    $profileName = auth()->user()?->name ?? 'Reviewer';
    $oldTagIds = collect(old('tags', []))->map(fn ($tag) => (string) $tag)->values();
@endphp

<main class="upload-post-page" id="uploadPostPage">
    <div class="upload-post-shell">
        <section class="upload-post-panel" aria-labelledby="uploadPostTitle">
            <div class="upload-post-scroll no-scrollbar">
                <form id="uploadPostForm" method="POST" action="{{ route('posts.store') }}" class="upload-post-form" novalidate>
                    @csrf

                    <div class="upload-post-fields">
                        <div class="upload-post-heading">
                            <h1 id="uploadPostTitle">Create Post</h1>
                            <span>* Required Fields</span>
                        </div>

                        <div class="upload-field">
                            <label for="input-title">Title <span>*</span></label>
                            <input type="text" id="input-title" name="title" value="{{ old('title') }}" placeholder="Enter title here..." @class(['is-invalid' => $errors->has('title')])>
                            <p id="error-title" @class(['upload-error', 'hidden' => ! $errors->has('title')])>{{ $errors->first('title') ?: 'Title is required.' }}</p>
                        </div>

                        <div class="upload-field">
                            <label for="input-link">Website Link <span>*</span></label>
                            <input type="url" id="input-link" name="url" value="{{ old('url') }}" placeholder="https://example.com" @class(['is-invalid' => $errors->has('url')])>
                            <p id="error-link" @class(['upload-error', 'hidden' => ! $errors->has('url')])>{{ $errors->first('url') ?: 'Please enter a valid link, for example https://example.com.' }}</p>
                        </div>

                        <div class="upload-field">
                            <label for="input-desc">Description <span>*</span></label>
                            <textarea id="input-desc" name="description" rows="3" placeholder="Write description here..." @class(['is-invalid' => $errors->has('description')])>{{ old('description') }}</textarea>
                            <p id="error-desc" @class(['upload-error', 'hidden' => ! $errors->has('description')])>{{ $errors->first('description') ?: 'Description is required.' }}</p>
                        </div>

                        <div class="upload-field">
                            <label for="tag-search-input">Tag <span>*</span></label>
                            <button type="button" id="tag-trigger-box" @class(['tag-trigger-box', 'is-invalid' => $errors->has('tags') || $errors->has('tags.*')])>
                                <span id="form-active-tags-preview" class="active-tags-preview"></span>
                                <span id="input-placeholder-text" class="tag-placeholder">Add tag here...</span>
                            </button>
                            <div id="selected-tags-inputs"></div>
                            <p id="error-tag" @class(['upload-error', 'hidden' => ! ($errors->has('tags') || $errors->has('tags.*'))])>{{ $errors->first('tags') ?: $errors->first('tags.*') ?: 'Please select at least one tag.' }}</p>
                        </div>
                    </div>

                    <div class="upload-post-actions">
                        <button type="button" id="discard-main-btn" class="upload-discard-button">
                            Discard
                        </button>
                        <button type="submit" id="submit-main-btn" class="upload-submit-button">
                            Submit Content
                        </button>
                    </div>
                </form>
            </div>

            <div id="tag-outside-tooltip" class="tag-tooltip is-hidden">
                <div class="tag-tooltip-card">
                    <div class="tag-tooltip-search">
                        <span aria-hidden="true">
                            <x-fas-search class="size-3" />
                        </span>
                        <input type="text" id="tag-search-input" placeholder="Search tags..." autocomplete="off">
                        <button type="button" id="closeTagTooltip" aria-label="Close tag picker">
                            <x-fas-times class="size-3" />
                        </button>
                    </div>

                    <div class="tag-picker-section">
                        <span>Select Category</span>
                        <div class="tag-category-list">
                            @forelse ($categories as $category)
                                <button
                                    type="button"
                                    class="tag-category-button"
                                    data-category-button="{{ $category->slug }}"
                                >
                                    {{ $category->name }}
                                </button>
                            @empty
                                <p class="tag-empty-state">No categories available.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="tag-picker-section">
                        <span>Suggested Tags</span>
                        <div id="suggested-tags-container" class="suggested-tags-container"></div>
                    </div>
                </div>
            </div>
        </section>

        <aside id="preview-wrapper-column" class="preview-wrapper-column" aria-label="Post preview">
            <x-layout.post-card
                title="Untitled Post"
                url="https://example.com"
                :category="$initialCategory?->name ?? 'Selected category'"
                :tags="['No tags selected']"
                :profiles="[[
                    'username' => '@'.\Illuminate\Support\Str::slug($profileName, '_'),
                    'initial' => \Illuminate\Support\Str::of($profileName)->substr(0, 1)->upper()->toString(),
                    'time' => 'Published just now',
                    'description' => 'No description written yet.',
                    'avatar' => auth()->user()?->getAvatarUrl() ?? '',
                ]]"
                class="upload-preview-card"
                data-upload-preview-card
            />
        </aside>
    </div>

    <script>
        window.uploadPostCategories = @js($categoryPayload);
        window.uploadPostInitialCategory = @js($initialCategory?->slug);
        window.uploadPostOldTags = @js($oldTagIds);
    </script>
</main>
