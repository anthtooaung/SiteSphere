<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserSlugTest extends TestCase
{
    use RefreshDatabase;

    public function test_slug_is_generated_from_name_on_create(): void
    {
        $user = User::factory()->create(['name' => 'John Doe']);

        $this->assertSame('john-doe', $user->slug);
    }

    public function test_slug_updates_when_name_changes(): void
    {
        $user = User::factory()->create(['name' => 'John Doe']);

        $user->update(['name' => 'Jane Smith']);

        $this->assertSame('jane-smith', $user->fresh()->slug);
    }

    public function test_slug_does_not_change_when_other_fields_change(): void
    {
        $user = User::factory()->create(['name' => 'John Doe']);
        $originalSlug = $user->slug;

        $user->update(['user_bio' => 'Some bio text']);

        $this->assertSame($originalSlug, $user->fresh()->slug);
    }

    public function test_slug_is_unique_on_create_when_name_conflicts(): void
    {
        $first = User::factory()->create(['name' => 'John Doe']);
        $second = User::factory()->create(['name' => 'John Doe']);

        $this->assertSame('john-doe', $first->slug);
        $this->assertSame('john-doe-1', $second->slug);
    }

    public function test_slug_is_unique_on_update_when_name_conflicts(): void
    {
        $existing = User::factory()->create(['name' => 'Jane Smith']);
        $user = User::factory()->create(['name' => 'John Doe']);

        $user->update(['name' => 'Jane Smith']);

        $this->assertSame('jane-smith-1', $user->fresh()->slug);
    }

    public function test_slug_keeps_own_slug_when_name_unchanged_after_update(): void
    {
        $user = User::factory()->create(['name' => 'John Doe']);

        $user->update(['name' => 'John Doe']);

        $this->assertSame('john-doe', $user->fresh()->slug);
    }
}
