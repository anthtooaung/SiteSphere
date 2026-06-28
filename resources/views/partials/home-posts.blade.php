@foreach ($posts as $post)
    <x-layout.post-card
        :post-id="$post['id']"
        :url="$post['url']"
        :category="$post['category']"
        :tags="$post['tags']"
        :profiles="$post['profiles']"
        :average-rating="$post['average_rating']"
        :ratings-count="$post['ratings_count']"
        :comments-count="$post['comments_count']"
        :saved="$post['is_bookmarked']"
        :slug="$post['slug']"
        :is-unsecure="$post['is_unsecure']"
        :has-reported="$post['has_reported']"
        data-category="{{ $post['category_slug'] }}"
        data-rating="{{ (int) floor($post['average_rating']) }}"
        data-tags="{{ implode(',', collect($post['tags'])->pluck('name')->all()) }}"
    />
@endforeach
