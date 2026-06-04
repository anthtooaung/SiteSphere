<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\FontsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EditProfilePageTest extends TestCase
{
    use RefreshDatabase;

    private const TINY_PNG_DATA_URL = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(FontsSeeder::class);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('edit-profile'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_view_the_dashboard_shell_edit_profile_page(): void
    {
        $user = User::factory()->create([
            'name' => 'Lin Thant Aung',
            'email' => 'lin@example.com',
            'user_dob' => '2005-10-12',
            'user_phone' => '9963269801',
            'user_bio' => 'Attending Metro IT & Japanese Language Centre',
        ]);

        $this->actingAs($user)
            ->get(route('edit-profile'))
            ->assertOk()
            ->assertSee('data-edit-profile-page', false)
            ->assertSee('dashboard-page--left', false)
            ->assertSee('resources/css/edit-profile.css', false)
            ->assertSee('Profile Settings')
            ->assertSee('id="profile-form"', false)
            ->assertSee('name="cropped_avatar"', false)
            ->assertSee('id="crop-modal"', false)
            ->assertSee('data-bio-counter', false)
            ->assertSee('Save Changes')
            ->assertSee('value="Lin Thant Aung"', false)
            ->assertSee('value="lin@example.com"', false)
            ->assertSee('value="2005-10-12"', false)
            ->assertSee('9963269801')
            ->assertSee('Attending Metro IT &amp; Japanese Language Centre', false);
    }

    public function test_profile_fields_update_successfully(): void
    {
        $user = User::factory()->create([
            'email' => 'old@example.com',
            'is_verified' => true,
        ]);

        $this->actingAs($user)
            ->patch(route('edit-profile.update'), [
                'name' => 'Updated User',
                'email' => 'updated@example.com',
                'user_dob' => '1999-01-15',
                'user_phone' => '912345678',
                'user_bio' => 'Updated profile bio.',
            ])
            ->assertRedirect(route('edit-profile'))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Profile changes saved successfully.');

        $user->refresh();

        $this->assertSame('Updated User', $user->name);
        $this->assertSame('updated@example.com', $user->email);
        $this->assertSame('1999-01-15', (string) $user->user_dob);
        $this->assertSame('912345678', $user->user_phone);
        $this->assertSame('Updated profile bio.', $user->user_bio);
        $this->assertTrue((bool) $user->is_verified);
    }

    public function test_email_must_be_unique_except_for_the_current_user(): void
    {
        $user = User::factory()->create(['email' => 'profile-owner@example.com']);
        $otherUser = User::factory()->create(['email' => 'already-used@example.com']);

        $this->actingAs($user)
            ->patch(route('edit-profile.update'), [
                'name' => 'Profile Owner',
                'email' => $otherUser->email,
                'user_dob' => null,
                'user_phone' => null,
                'user_bio' => null,
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_invalid_profile_data_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('edit-profile.update'), [
                'name' => '',
                'email' => 'not-an-email',
                'user_dob' => now()->addDay()->format('Y-m-d'),
                'user_phone' => str_repeat('1', 21),
                'user_bio' => str_repeat('A', 261),
                'cropped_avatar' => 'not-a-data-url',
            ])
            ->assertSessionHasErrors([
                'name',
                'email',
                'user_dob',
                'user_phone',
                'user_bio',
                'cropped_avatar',
            ]);
    }

    public function test_cropped_avatar_data_url_is_stored_on_the_public_disk(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('edit-profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'user_dob' => null,
                'user_phone' => null,
                'user_bio' => null,
                'cropped_avatar' => self::TINY_PNG_DATA_URL,
            ])
            ->assertRedirect(route('edit-profile'))
            ->assertSessionHasNoErrors();

        $user->refresh();

        $this->assertStringStartsWith('profile_images/', $user->user_image);
        Storage::disk('public')->assertExists($user->user_image);
    }

    public function test_previous_local_avatar_file_is_deleted_after_replacement(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('profile_images/old.png', 'old-image');

        $user = User::factory()->create([
            'user_image' => 'profile_images/old.png',
        ]);

        $this->actingAs($user)
            ->patch(route('edit-profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'user_dob' => null,
                'user_phone' => null,
                'user_bio' => null,
                'cropped_avatar' => self::TINY_PNG_DATA_URL,
            ])
            ->assertRedirect(route('edit-profile'))
            ->assertSessionHasNoErrors();

        $user->refresh();

        Storage::disk('public')->assertMissing('profile_images/old.png');
        Storage::disk('public')->assertExists($user->user_image);
    }
}
