<?php

namespace Tests\Feature;

use App\Models\Categories;
use App\Models\Comments;
use App\Models\Notificatioins;
use App\Models\Posts;
use App\Models\Ratings;
use App\Models\Tags;
use App\Models\User;
use App\Models\UserPosts;
use Database\Seeders\FontsSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use LazilyRefreshDatabase;

    private const MOBILE_USER_AGENT = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(FontsSeeder::class);
    }

    private function getAsAuthenticatedMobileUser(User $user, string $uri): TestResponse
    {
        $previousUserAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        $_SERVER['HTTP_USER_AGENT'] = self::MOBILE_USER_AGENT;

        try {
            return $this->actingAs($user)->get($uri);
        } finally {
            if ($previousUserAgent === null) {
                unset($_SERVER['HTTP_USER_AGENT']);
            } else {
                $_SERVER['HTTP_USER_AGENT'] = $previousUserAgent;
            }
        }
    }

    public function test_guests_can_view_home_posts_with_auth_gated_actions(): void
    {
        $reviewer = User::factory()->create();
        $reviewer->settings()->update(['user_post_visible' => true]);
        $post = Posts::factory()->create([
            'title' => 'Public Guest Visible Post',
            'url' => 'https://guest-visible-post.test',
        ]);

        UserPosts::factory()->create([
            'post_id' => $post->id,
            'user_id' => $reviewer->id,
            'description' => 'Guests can read this post before logging in.',
        ]);

        $response = $this->get('/home');

        $response
            ->assertOk()
            ->assertSee('Public Guest Visible Post')
            ->assertSee('https://guest-visible-post.test')
            ->assertSee('Guests can read this post before logging in.')
            ->assertSee('href="'.route('login').'"', false)
            ->assertSee('data-auth-required="bookmark"', false)
            ->assertSee('data-auth-required="review"', false);
    }

    public function test_home_limits_initial_server_render_to_nine_posts(): void
    {
        $reviewer = User::factory()->create();
        $reviewer->settings()->update(['user_post_visible' => true]);
        $viewer = User::factory()->create();

        for ($index = 1; $index <= 10; $index++) {
            $post = Posts::factory()->create([
                'title' => 'Server Limited Post '.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                'created_at' => now()->addSeconds($index),
                'updated_at' => now()->addSeconds($index),
            ]);

            UserPosts::factory()->create([
                'post_id' => $post->id,
                'user_id' => $reviewer->id,
                'description' => 'Server limited post description '.$index,
            ]);
        }

        $response = $this->actingAs($viewer)->get('/home');

        $response
            ->assertOk()
            ->assertSee('<strong id="resultsCount" x-text="totalResults">10</strong>', false)
            ->assertSee('Server Limited Post 10')
            ->assertSee('Server Limited Post 02')
            ->assertDontSee('Server Limited Post 01');
    }

    public function test_authenticated_users_can_view_empty_home_without_review_cards(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/home');

        $response
            ->assertOk()
            ->assertSee('Discover Useful Websites')
            ->assertSee('<strong id="resultsCount" x-text="totalResults">0</strong>', false)
            ->assertDontSee('Aurora Pay checkout keeps timing out')
            ->assertDontSee('data-post-card-title', false)
            ->assertDontSee('cdnjs.cloudflare.com/ajax/libs/font-awesome', false)
            ->assertDontSee('class="fas', false)
            ->assertSee('href="'.route('home').'"', false)
            ->assertSee('class="desktop-link active"', false)
            ->assertSee('aria-current="page"', false)
            ->assertSee('href="'.route('posts.create').'"', false);
    }

    public function test_home_hides_show_more_categories_when_there_are_five_or_fewer_categories(): void
    {
        $user = User::factory()->create();

        Categories::factory()->count(5)->create();

        $response = $this->actingAs($user)->get('/home');

        $response
            ->assertOk()
            ->assertDontSee('id="showCategoryBtn"', false)
            ->assertDontSee('Show More Categories');
    }

    public function test_home_shows_show_more_categories_when_there_are_more_than_five_categories(): void
    {
        $user = User::factory()->create();

        Categories::factory()->count(6)->create();

        $response = $this->actingAs($user)->get('/home');

        $response
            ->assertOk()
            ->assertSee('id="showCategoryBtn"', false)
            ->assertSee('Show More Categories');
    }

    public function test_home_aside_renders_category_component_items_and_tag_template(): void
    {
        $user = User::factory()->create();
        $category = Categories::factory()->create([
            'name' => 'Developer Tools',
            'slug' => 'developer-tools',
        ]);

        $tag = Tags::factory()->create([
            'name' => 'frontend',
            'slug' => 'frontend',
            'tag_color' => '#374151',
        ]);

        DB::table('category_tags')->insert([
            'category_id' => $category->id,
            'tag_id' => $tag->id,
        ]);

        $response = $this->actingAs($user)->get('/home');

        $response
            ->assertOk()
            ->assertSee('data-filter-component="category"', false)
            ->assertSee('value="developer-tools"', false)
            ->assertSee('Developer Tools')
            ->assertSee('data-filter-component="tag"', false)
            ->assertSee('class="tag-check"', false);
    }

    public function test_home_nav_category_menu_uses_database_categories(): void
    {
        $user = User::factory()->create();

        Categories::factory()->create([
            'name' => 'Developer Tools',
            'slug' => 'developer-tools',
        ]);

        $response = $this->actingAs($user)->get('/home');

        $response
            ->assertOk()
            ->assertSee('Developer Tools')
            ->assertSee('href="'.route('home', ['category' => 'developer-tools']).'"', false);

        $mobileResponse = $this->getAsAuthenticatedMobileUser($user, '/home');

        $mobileResponse
            ->assertOk()
            ->assertSeeInOrder([
                'data-mobile-menu-open',
                '<div class="mobile-menu-overlay category-mobile-overlay" id="mobileCategoryOverlay">',
            ], false);
    }

    public function test_home_exposes_initial_category_from_query_string(): void
    {
        $user = User::factory()->create();

        Categories::factory()->create([
            'name' => 'Developer Tools',
            'slug' => 'developer-tools',
        ]);

        $response = $this->actingAs($user)->get('/home?category=developer-tools');

        $response
            ->assertOk()
            ->assertSee('window.homeInitialCategory = "developer-tools";', false);
    }

    public function test_home_nav_notification_dropdown_uses_current_users_unread_notifications(): void
    {
        $user = User::factory()->create();
        $sender = User::factory()->create();
        $otherUser = User::factory()->create();

        Notificatioins::factory()->create([
            'to_user_id' => $user->id,
            'from_user_id' => $sender->id,
            'message' => 'Fresh unread notification',
            'is_read' => false,
        ]);
        Notificatioins::factory()->read()->create([
            'to_user_id' => $user->id,
            'from_user_id' => $sender->id,
            'message' => 'Already read notification',
        ]);
        Notificatioins::factory()->create([
            'to_user_id' => $otherUser->id,
            'from_user_id' => $sender->id,
            'message' => 'Someone else notification',
            'is_read' => false,
        ]);

        $response = $this->actingAs($user)->get('/home');

        $response
            ->assertOk()
            ->assertSee('aria-label="1 unread notifications"', false)
            ->assertSee('<span class="noti-badge">1</span>', false)
            ->assertSee('Fresh unread notification')
            ->assertDontSee('Already read notification')
            ->assertDontSee('Someone else notification');

        $mobileResponse = $this->getAsAuthenticatedMobileUser($user, '/home');

        $mobileResponse
            ->assertOk()
            ->assertSee('<span class="mobile-badge">1</span>', false);
    }

    public function test_home_nav_notification_dropdown_shows_empty_state_without_unread_notifications(): void
    {
        $user = User::factory()->create();

        Notificatioins::factory()->read()->create([
            'to_user_id' => $user->id,
            'message' => 'Read notification only',
        ]);

        $response = $this->actingAs($user)->get('/home');

        $response
            ->assertOk()
            ->assertSee('No unread notifications')
            ->assertSee('aria-label="Notifications"', false)
            ->assertDontSee('noti-badge', false)
            ->assertDontSee('Read notification only');
    }

    public function test_home_nav_notification_dropdown_uses_cached_unread_notifications(): void
    {
        Cache::flush();

        $user = User::factory()->create();
        $sender = User::factory()->create();

        Notificatioins::factory()->create([
            'to_user_id' => $user->id,
            'from_user_id' => $sender->id,
            'message' => 'Cached unread notification',
            'is_read' => false,
        ]);

        $this->actingAs($user)
            ->get('/home')
            ->assertOk()
            ->assertSee('Cached unread notification');

        $notificationQueries = 0;
        DB::listen(function ($query) use (&$notificationQueries): void {
            if (str_contains($query->sql, 'from "notificatioins"') || str_contains($query->sql, 'from `notificatioins`')) {
                $notificationQueries++;
            }
        });

        $this->actingAs($user)
            ->get('/home')
            ->assertOk()
            ->assertSee('aria-label="1 unread notifications"', false)
            ->assertSee('Cached unread notification');

        $this->assertSame(0, $notificationQueries);
    }

    public function test_home_uses_light_mode_theme_and_font_variables(): void
    {
        $user = User::factory()->create();
        $fontId = DB::table('fonts')
            ->where('font_family', '"Open Sans", sans-serif')
            ->value('id');
        $customThemeId = DB::table('custom_themes')->insertGetId([
            'user_id' => $user->id,
            'background_color' => '#102030',
            'text_color' => '#f8fafc',
            'accent_color' => '#14b8a6',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('settings')
            ->where('user_id', $user->id)
            ->update([
                'custom_theme_id' => $customThemeId,
                'use_custom_theme' => true,
                'updated_at' => now(),
            ]);

        DB::table('user_current_fonts')->insert([
            'user_id' => $user->id,
            'font_id' => $fontId,
            'created_at' => now()->addSecond(),
            'updated_at' => now()->addSecond(),
        ]);

        $response = $this->actingAs($user)->get('/home');

        $response
            ->assertOk()
            ->assertSee('--accent-color: #14b8a6;', false)
            ->assertSee('--background-color: #102030;', false)
            ->assertSee('--text-color: #f8fafc;', false)
            ->assertSee('--font-family: "Open Sans", sans-serif;', false)
            ->assertSee('class="site-brand flex items-center space-x-0 rtl:space-x-reverse"', false)
            ->assertSee('fill="var(--accent-color, #6c5ce7)"', false);

        $this->assertStringContainsString(
            '.site-brand span',
            file_get_contents(resource_path('css/nav.css')),
        );
    }

    public function test_home_uses_custom_theme_variables_when_dark_mode_is_enabled(): void
    {
        $user = User::factory()->create();
        $customThemeId = DB::table('custom_themes')->insertGetId([
            'user_id' => $user->id,
            'background_color' => '#102030',
            'text_color' => '#f8fafc',
            'accent_color' => '#14b8a6',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('settings')
            ->where('user_id', $user->id)
            ->update([
                'custom_theme_id' => $customThemeId,
                'dark_mode' => true,
                'use_custom_theme' => true,
                'updated_at' => now(),
            ]);

        $response = $this->actingAs($user)->get('/home');

        $response
            ->assertOk()
            ->assertSee('--accent-color: #14b8a6;', false)
            ->assertSee('--background-color: #102030;', false)
            ->assertSee('--text-color: #f8fafc;', false);
    }

    public function test_home_post_card_uses_theme_variables_for_surfaces_text_and_effects(): void
    {
        $viewer = User::factory()->create();
        $reviewer = User::factory()->create();
        $post = Posts::factory()->create([
            'title' => 'Theme Controlled Card',
            'url' => 'https://theme-controlled-card.test',
        ]);

        UserPosts::factory()->create([
            'post_id' => $post->id,
            'user_id' => $reviewer->id,
            'description' => 'Card color should follow theme variables.',
        ]);

        $response = $this->actingAs($viewer)->get('/home');

        $response
            ->assertOk()
            ->assertSee('Theme Controlled Card')
            ->assertSee('[background:var(--background-color,#ffffff)]', false)
            ->assertSee('[color:var(--text-color,#0d1b2a)]', false)
            ->assertSee('[border-color:color-mix(in_srgb,var(--accent-color,#6c5ce7)', false)
            ->assertSee('hover:[box-shadow:0_8px_18px_color-mix(in_srgb,var(--accent-color,#6c5ce7)', false);
    }

    public function test_home_renders_aside_component_with_default_placement(): void
    {
        $user = User::factory()->create();

        DB::table('settings')
            ->where('user_id', $user->id)
            ->update([
                'menuBar_location' => 'left',
                'updated_at' => now(),
            ]);

        $response = $this->actingAs($user)->get('/home');

        $response
            ->assertOk()
            ->assertSee('id="sidebar"', false)
            ->assertSee('home-aside--left', false)
            ->assertSee('data-menu-bar-location="left"', false);
    }

    public function test_home_aside_uses_each_valid_menu_bar_location(): void
    {
        foreach (['left', 'right', 'top', 'bottom'] as $location) {
            $user = User::factory()->create();

            DB::table('settings')
                ->where('user_id', $user->id)
                ->update([
                    'menuBar_location' => $location,
                    'updated_at' => now(),
                ]);

            $response = $this->actingAs($user)->get('/home');

            $response
                ->assertOk()
                ->assertSee('home-aside--'.$location, false)
                ->assertSee('data-menu-bar-location="'.$location.'"', false);

            if (in_array($location, ['top', 'bottom'], true)) {
                $response
                    ->assertSee('home-aside--dropdown', false)
                    ->assertSee('data-dropdown-aside="true"', false)
                    ->assertSee('Filters', false);
            } else {
                $response
                    ->assertDontSee('home-aside--dropdown', false)
                    ->assertSee('data-dropdown-aside="false"', false)
                    ->assertSee('Refine websites by rating, category, and tags.');

                if ($location === 'right') {
                    $response->assertSee('right-0', false);
                    $response->assertDontSee('left-0', false);
                } else {
                    $response->assertSee('left-0', false);
                    $response->assertDontSee('right-0', false);
                }
            }
        }
    }

    public function test_home_aside_falls_back_to_left_for_invalid_or_missing_settings(): void
    {
        $this->blade('<x-layout.home-aside :categories="collect()" menu-bar-location="sideways" />')
            ->assertSee('home-aside--left', false)
            ->assertSee('data-menu-bar-location="left"', false);

        $missingSettingsUser = User::factory()->create();

        DB::table('settings')
            ->where('user_id', $missingSettingsUser->id)
            ->delete();

        $this->actingAs($missingSettingsUser)
            ->get('/home')
            ->assertOk()
            ->assertSee('home-aside--left', false)
            ->assertSee('data-menu-bar-location="left"', false);
    }

    public function test_home_review_grid_uses_auto_fill_and_three_desktop_columns_fallback(): void
    {
        $homepageCss = file_get_contents(resource_path('css/homepage.css'));

        $this->assertStringContainsString('grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));', $homepageCss);
        $this->assertStringContainsString('gap: 20px;', $homepageCss);
        $this->assertStringContainsString('align-items: stretch;', $homepageCss);
        $this->assertStringContainsString('justify-content: stretch;', $homepageCss);
    }

    public function test_home_renders_one_card_per_post_with_many_descriptions(): void
    {
        $viewer = User::factory()->create();
        $firstReviewer = User::factory()->create([
            'name' => 'First Reviewer',
            'user_image' => 'profile_images/first-reviewer-avatar.jpg',
        ]);
        $secondReviewer = User::factory()->create(['name' => 'Second Reviewer']);
        $firstReviewer->settings()->update(['user_post_visible' => true]);
        $secondReviewer->settings()->update(['user_post_visible' => true]);
        $post = Posts::factory()->create([
            'title' => 'Shared URL Review',
            'url' => 'https://shared-example.test',
        ]);

        UserPosts::factory()->create([
            'post_id' => $post->id,
            'user_id' => $firstReviewer->id,
            'description' => 'First description for the same website.',
        ]);
        UserPosts::factory()->create([
            'post_id' => $post->id,
            'user_id' => $secondReviewer->id,
            'description' => 'Second description for the same website.',
        ]);

        $response = $this->actingAs($viewer)->get('/home');
        $content = $response->getContent();
        $cardStart = strpos($content, 'Shared URL Review');
        $cardEnd = strpos($content, '</article>', $cardStart);
        $cardHtml = substr($content, $cardStart, $cardEnd - $cardStart);

        $response
            ->assertOk()
            ->assertSee('Shared URL Review')
            ->assertSee('https://shared-example.test')
            ->assertSee('First description for the same website.')
            ->assertSee('Second description for the same website.')
            ->assertSee('data-profile-scroll="left"', false)
            ->assertSee('data-profile-scroll="right"', false)
            ->assertSee('data-profile-tabs', false)
            ->assertSee('data-profile-tab', false)
            ->assertSee('x-bind:data-active', false)
            ->assertSee('[-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden', false)
            ->assertSee('[border-color:var(--accent-color,#6c5ce7)] [color:var(--accent-color,#6c5ce7)] font-bold', false);

        $homepageCss = file_get_contents(resource_path('css/homepage.css'));

        $this->assertSame(1, substr_count($content, '<article'));
        $this->assertStringContainsString('first-reviewer-avatar.jpg', $content);
        $this->assertStringNotContainsString('hover:[color:color-mix(in_srgb,var(--text-color,#0d1b2a)_72%,transparent)]', $cardHtml);
        $this->assertStringContainsString('.home-page [data-profile-tabs]', $homepageCss);
        $this->assertStringContainsString('scrollbar-width: none;', $homepageCss);
        $this->assertStringContainsString('.home-page [data-profile-tab]:hover', $homepageCss);
        $this->assertStringContainsString('border-bottom-color: var(--accent-color, #6c5ce7);', $homepageCss);
    }

    public function test_home_post_card_visible_badges_use_real_tags_not_categories(): void
    {
        $viewer = User::factory()->create();
        $reviewer = User::factory()->create();
        $reviewer->settings()->update(['user_post_visible' => true]);
        $category = Categories::factory()->create([
            'name' => 'Category Should Not Be Badge',
            'slug' => 'category-should-not-be-badge',
        ]);
        $tag = Tags::factory()->create([
            'name' => 'True Visible Tag',
            'slug' => 'true-visible-tag',
        ]);
        $post = Posts::factory()->create([
            'title' => 'Real Tag Card',
            'url' => 'https://real-tag-card.test',
        ]);

        DB::table('category_tags')->insert([
            'category_id' => $category->id,
            'tag_id' => $tag->id,
        ]);
        $post->tags()->attach($tag->id);
        UserPosts::factory()->create([
            'post_id' => $post->id,
            'user_id' => $reviewer->id,
            'description' => 'This card should show real tag badges.',
        ]);

        $response = $this->actingAs($viewer)->get('/home');
        $content = $response->getContent();
        $cardStart = strpos($content, 'Real Tag Card');
        $cardEnd = strpos($content, '</article>', $cardStart);
        $cardHtml = substr($content, $cardStart, $cardEnd - $cardStart);

        $response
            ->assertOk()
            ->assertSee('data-category="category-should-not-be-badge"', false)
            ->assertSee('data-tags="True Visible Tag"', false)
            ->assertSee('data-tag-scroll="left"', false)
            ->assertSee('data-tag-scroll="right"', false)
            ->assertSee('x-on:click="scrollTags(-1)"', false)
            ->assertSee('x-on:click="scrollTags(1)"', false)
            ->assertSee('x-ref="tagScroller"', false)
            ->assertSee('data-post-card-tags', false)
            ->assertSee('overflow-x-auto scroll-smooth', false)
            ->assertSee('[-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden', false)
            ->assertSee('shrink-0 items-center gap-1.5 whitespace-nowrap', false)
            ->assertSee('True Visible Tag');

        $homepageCss = file_get_contents(resource_path('css/homepage.css'));

        $this->assertStringContainsString('True Visible Tag', $cardHtml);
        $this->assertStringNotContainsString('Category Should Not Be Badge', $cardHtml);
        $this->assertStringContainsString('.home-page [data-post-card-tags]', $homepageCss);
    }

    public function test_home_anonymizes_profile_tabs_when_user_post_visibility_is_disabled(): void
    {
        $viewer = User::factory()->create();
        $visibleReviewer = User::factory()->create(['name' => 'Visible Reviewer']);
        $hiddenReviewer = User::factory()->create(['name' => 'Hidden Reviewer']);
        $visibleReviewer->settings()->update(['user_post_visible' => true]);
        $hiddenReviewer->settings()->update(['user_post_visible' => false]);

        $mixedPost = Posts::factory()->create([
            'title' => 'Mixed Visibility Post',
            'url' => 'https://mixed-visibility.test',
        ]);

        UserPosts::factory()->create([
            'post_id' => $mixedPost->id,
            'user_id' => $visibleReviewer->id,
            'description' => 'Visible reviewer description.',
            'user_hidden' => false,
        ]);
        UserPosts::factory()->create([
            'post_id' => $mixedPost->id,
            'user_id' => $hiddenReviewer->id,
            'description' => 'Hidden reviewer description.',
            'user_hidden' => true,
        ]);

        $response = $this->actingAs($viewer)->get('/home');

        $response
            ->assertOk()
            ->assertSee('Mixed Visibility Post')
            ->assertSee('Visible reviewer description.')
            ->assertSee('Hidden reviewer description.')
            ->assertSee('@visible_reviewer')
            ->assertSee('Anonymous')
            ->assertDontSee('@hidden_reviewer');
    }

    public function test_home_renders_user_hidden_contributions_as_anonymous(): void
    {
        $viewer = User::factory()->create();
        $reviewer = User::factory()->create(['name' => 'Hidden Contribution']);
        $reviewer->settings()->update(['user_post_visible' => true]);
        $post = Posts::factory()->create([
            'title' => 'User Hidden Post',
            'url' => 'https://user-hidden.test',
        ]);

        UserPosts::factory()->create([
            'post_id' => $post->id,
            'user_id' => $reviewer->id,
            'description' => 'This user hidden contribution should render anonymously.',
            'user_hidden' => true,
        ]);

        $response = $this->actingAs($viewer)->get('/home');

        $response
            ->assertOk()
            ->assertSee('User Hidden Post')
            ->assertSee('This user hidden contribution should render anonymously.')
            ->assertSee('Anonymous')
            ->assertDontSee('@hidden_contribution');
    }

    public function test_comments_and_ratings_are_post_level_totals(): void
    {
        $viewer = User::factory()->create();
        $reviewer = User::factory()->create();
        $reviewer->settings()->update(['user_post_visible' => true]);
        $post = Posts::factory()->create([
            'title' => 'Post Level Totals',
            'url' => 'https://totals-example.test',
        ]);

        UserPosts::factory()->create([
            'post_id' => $post->id,
            'user_id' => $reviewer->id,
            'description' => 'Description does not own comments or ratings.',
        ]);

        Comments::factory()->count(2)->create([
            'post_id' => $post->id,
        ]);
        Ratings::factory()->create([
            'post_id' => $post->id,
            'rating' => 5,
        ]);
        Ratings::factory()->create([
            'post_id' => $post->id,
            'rating' => 3,
        ]);

        $response = $this->actingAs($viewer)->get('/home');

        $response
            ->assertOk()
            ->assertSee('Post Level Totals')
            ->assertSee('x-text="commentsTotal()">2</span>', false)
            ->assertSee('<span x-text="ratingsTotal()">2</span> ratings', false)
            ->assertSee('>4.0</span>', false);
    }

    public function test_home_displays_custom_user_tag_overrides(): void
    {
        $user = User::factory()->create();
        $category = Categories::factory()->create(['name' => 'Tech', 'slug' => 'tech']);
        $tag = Tags::factory()->create(['name' => 'Original Tag', 'slug' => 'original-tag', 'tag_color' => '#111111']);

        $category->tags()->attach($tag->id);

        $post = Posts::factory()->create(['title' => 'Post with Custom Tag']);
        $post->tags()->attach($tag->id);

        UserPosts::factory()->create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'description' => 'Test description',
        ]);

        $tag->customTags()->create([
            'user_id' => $user->id,
            'name' => 'Customized Tag Name',
            'color' => '#FF0055',
        ]);

        $response = $this->actingAs($user)->get('/home');

        $response->assertOk();
        $response->assertSee('Customized Tag Name');
        $response->assertSee('#FF0055');
        $response->assertDontSee('Original Tag');
    }

    public function test_authenticated_user_sees_review_link_pointing_to_post_detail_review_textarea(): void
    {
        $viewer = User::factory()->create();
        $reviewer = User::factory()->create();
        $reviewer->settings()->update(['user_post_visible' => true]);
        $post = Posts::factory()->create([
            'title' => 'Review Target Post',
            'slug' => 'review-target-post',
        ]);

        UserPosts::factory()->create([
            'post_id' => $post->id,
            'user_id' => $reviewer->id,
            'description' => 'A post card test.',
        ]);

        $response = $this->actingAs($viewer)->get('/home');

        $response->assertOk();
        $response->assertSee('Review Target Post');
        $expectedUrl = route('posts.show', 'review-target-post').'#reviewForm';
        $response->assertSee('href="'.$expectedUrl.'"', false);
    }
}
