<?php

namespace Tests\Feature;

use App\Models\Categories;
use App\Models\Posts;
use App\Models\Tags;
use App\Models\User;
use Database\Seeders\FontsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditTagPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(FontsSeeder::class);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('edit-tag'))
            ->assertRedirect(route('login'));
    }

    public function test_regular_users_see_user_tag_page_without_admin_controls(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->createCategoryWithTag();

        $this->actingAs($user)
            ->get(route('edit-tag'))
            ->assertOk()
            ->assertSee('Tag Styles')
            ->assertSee('Save Changes')
            ->assertSee('Reset to Defaults')
            ->assertDontSee('Admin Tag Styles')
            ->assertDontSee('Publish to users')
            ->assertSee('resources/css/edit-tag.css', false);
    }

    public function test_admins_see_admin_tag_page_and_publish_controls(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createCategoryWithTag();

        $this->actingAs($admin)
            ->get(route('edit-tag'))
            ->assertOk()
            ->assertSee('Admin Tag Styles')
            ->assertSee('Publish to users')
            ->assertSee('Add Category')
            ->assertDontSee('Reset to Defaults');
    }

    public function test_user_saves_custom_tag_override_without_changing_global_tag(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        [$category, $tag] = $this->createCategoryWithTag();

        $payload = [
            [
                'id' => $category->id,
                'name' => $category->name,
                'color' => '#6C5CE7',
                'tags' => [
                    [
                        'id' => $tag->id,
                        'name' => 'Personal Name',
                        'color' => '#FF0000',
                    ],
                ],
            ],
        ];

        $this->actingAs($user)
            ->patch(route('edit-tag.update'), ['taxonomy' => json_encode($payload)])
            ->assertRedirect();

        $this->assertDatabaseHas('custom_tags', [
            'user_id' => $user->id,
            'tag_id' => $tag->id,
            'name' => 'Personal Name',
            'color' => '#FF0000',
        ]);

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => $tag->name,
            'tag_color' => '#374151',
        ]);
    }

    public function test_user_reset_removes_only_their_custom_tag_overrides(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create(['role' => 'user']);
        [, $tag] = $this->createCategoryWithTag();

        $tag->customTags()->create([
            'user_id' => $user->id,
            'name' => 'Mine',
            'color' => '#111111',
        ]);
        $tag->customTags()->create([
            'user_id' => $otherUser->id,
            'name' => 'Theirs',
            'color' => '#222222',
        ]);

        $this->actingAs($user)
            ->delete(route('edit-tag.reset'))
            ->assertRedirect();

        $this->assertDatabaseMissing('custom_tags', [
            'user_id' => $user->id,
            'tag_id' => $tag->id,
        ]);
        $this->assertDatabaseHas('custom_tags', [
            'user_id' => $otherUser->id,
            'tag_id' => $tag->id,
            'name' => 'Theirs',
        ]);
    }

    public function test_admin_can_publish_global_taxonomy_and_audit_log_is_created(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [$category, $tag] = $this->createCategoryWithTag();

        $payload = [
            [
                'id' => $category->id,
                'name' => 'Updated Category',
                'color' => '#00AAFF',
                'tags' => [
                    [
                        'id' => $tag->id,
                        'name' => 'Updated Tag',
                        'color' => '#AA00FF',
                    ],
                    [
                        'id' => null,
                        'name' => 'New Tag',
                        'color' => '#00CC88',
                    ],
                ],
            ],
        ];

        $this->actingAs($admin)
            ->patch(route('edit-tag.update'), ['taxonomy' => json_encode($payload)])
            ->assertRedirect()
            ->assertSessionHas('success', 'Tag defaults published for users.');

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Category',
            'category_color' => '#00AAFF',
        ]);
        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => 'Updated Tag',
            'tag_color' => '#AA00FF',
        ]);
        $this->assertDatabaseHas('tags', [
            'name' => 'New Tag',
            'tag_color' => '#00CC88',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'update_tag_taxonomy',
            'target_type' => Categories::class,
        ]);
    }

    public function test_admin_delete_is_blocked_when_related_tag_is_used_by_posts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [$category, $tag] = $this->createCategoryWithTag();
        $post = Posts::factory()->create();
        $post->tags()->attach($tag->id);

        $this->actingAs($admin)
            ->patch(route('edit-tag.update'), ['taxonomy' => json_encode([])])
            ->assertSessionHasErrors('taxonomy');

        $this->assertModelExists($category);
        $this->assertModelExists($tag);
    }

    public function test_regular_users_cannot_create_global_categories_or_tags(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $payload = [
            [
                'id' => null,
                'name' => 'User Created Global Category',
                'color' => '#123456',
                'tags' => [
                    [
                        'id' => null,
                        'name' => 'User Created Global Tag',
                        'color' => '#654321',
                    ],
                ],
            ],
        ];

        $this->actingAs($user)
            ->patch(route('edit-tag.update'), ['taxonomy' => json_encode($payload)])
            ->assertRedirect();

        $this->assertDatabaseMissing('categories', [
            'name' => 'User Created Global Category',
        ]);
        $this->assertDatabaseMissing('tags', [
            'name' => 'User Created Global Tag',
        ]);
    }

    public function test_edit_tag_menu_link_is_active_on_edit_tag_page(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->createCategoryWithTag();

        $this->actingAs($user)
            ->get(route('edit-tag'))
            ->assertOk()
            ->assertSee('href="'.route('edit-tag').'"', false)
            ->assertSee('class="layout-menu-link active"', false)
            ->assertSee('class="account-menu-link active"', false)
            ->assertSee('aria-current="page"', false);
    }

    /**
     * @return array{0: Categories, 1: Tags}
     */
    private function createCategoryWithTag(): array
    {
        $category = Categories::factory()->create([
            'name' => 'Programming',
            'slug' => 'programming',
            'category_color' => '#6C5CE7',
        ]);
        $tag = Tags::factory()->create([
            'name' => 'Laravel',
            'slug' => 'laravel',
            'tag_color' => '#374151',
        ]);

        $category->tags()->attach($tag->id);

        return [$category, $tag];
    }

    public function test_user_saves_custom_tag_override_via_ajax_returns_json(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        [$category, $tag] = $this->createCategoryWithTag();

        $payload = [
            [
                'id' => $category->id,
                'name' => $category->name,
                'color' => '#6C5CE7',
                'tags' => [
                    [
                        'id' => $tag->id,
                        'name' => 'Personal Name Ajax',
                        'color' => '#FF0000',
                    ],
                ],
            ],
        ];

        $this->actingAs($user)
            ->patchJson(route('edit-tag.update'), ['taxonomy' => json_encode($payload)])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Your tag styles were saved.',
            ]);

        $this->assertDatabaseHas('custom_tags', [
            'user_id' => $user->id,
            'tag_id' => $tag->id,
            'name' => 'Personal Name Ajax',
        ]);
    }

    public function test_admin_publishes_global_taxonomy_via_ajax_returns_json(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [$category, $tag] = $this->createCategoryWithTag();

        $payload = [
            [
                'id' => $category->id,
                'name' => 'Updated Category Ajax',
                'color' => '#00AAFF',
                'tags' => [
                    [
                        'id' => $tag->id,
                        'name' => 'Updated Tag Ajax',
                        'color' => '#AA00FF',
                    ],
                ],
            ],
        ];

        $this->actingAs($admin)
            ->patchJson(route('edit-tag.update'), ['taxonomy' => json_encode($payload)])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Tag defaults published for users.',
            ]);
    }
}
