<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\FirebaseStorageService;
use Database\Seeders\FontsSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class EditProfilePageTest extends TestCase
{
    use LazilyRefreshDatabase;

    private const TINY_PNG_DATA_URL = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=';

    private const TINY_GIF_DATA_URL = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(FontsSeeder::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
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

        $response = $this->actingAs($user)
            ->get(route('edit-profile'))
            ->assertOk();

        $html = $response->getContent();
        $this->assertTrue(
            str_contains($html, 'build/assets/edit-profile-') || str_contains($html, 'resources/css/edit-profile.css'),
            'Failed asserting that edit-profile.css is loaded'
        );

        $response->assertSee('data-edit-profile-page', false)
            ->assertSee('dashboard-page--left', false)
            ->assertSee('Profile Settings')
            ->assertSee('id="profile-form"', false)
            ->assertSee('name="cropped_avatar"', false)
            ->assertSee('id="crop-modal"', false)
            ->assertSee('data-bio-counter', false)
            ->assertSee('Animated GIF up to 1MB')
            ->assertSee('Save Changes')
            ->assertSee(":class=\"{ 'is-loading': isSubmitting }\"", false)
            ->assertSee(':disabled="isSubmitting"', false)
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

    public function test_cropped_avatar_data_url_is_uploaded_to_firebase(): void
    {
        $firebaseUrl = 'https://firebasestorage.googleapis.com/v0/b/test.appspot.com/o/profile_images%2Ftest-avatar.png?alt=media';

        $mockStorage = Mockery::mock(FirebaseStorageService::class);
        $mockStorage->shouldReceive('uploadBase64Image')
            ->once()
            ->with(self::TINY_PNG_DATA_URL)
            ->andReturn($firebaseUrl);

        $this->app->instance(FirebaseStorageService::class, $mockStorage);

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

        $this->assertSame($firebaseUrl, $user->user_image);
    }

    public function test_small_gif_avatar_data_url_is_uploaded_to_firebase(): void
    {
        $firebaseUrl = 'https://firebasestorage.googleapis.com/v0/b/test.appspot.com/o/profile_images%2Ftest-avatar.gif?alt=media';

        $mockStorage = Mockery::mock(FirebaseStorageService::class);
        $mockStorage->shouldReceive('uploadBase64Image')
            ->once()
            ->with(self::TINY_GIF_DATA_URL)
            ->andReturn($firebaseUrl);

        $this->app->instance(FirebaseStorageService::class, $mockStorage);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('edit-profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'user_dob' => null,
                'user_phone' => null,
                'user_bio' => null,
                'cropped_avatar' => self::TINY_GIF_DATA_URL,
            ])
            ->assertRedirect(route('edit-profile'))
            ->assertSessionHasNoErrors();

        $user->refresh();

        $this->assertSame($firebaseUrl, $user->user_image);
    }

    public function test_oversized_gif_avatar_data_url_is_rejected(): void
    {
        $user = User::factory()->create();
        $oversizedGif = 'data:image/gif;base64,'.base64_encode(str_repeat('A', 1024 * 1024 + 1));

        $this->actingAs($user)
            ->patch(route('edit-profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'user_dob' => null,
                'user_phone' => null,
                'user_bio' => null,
                'cropped_avatar' => $oversizedGif,
            ])
            ->assertSessionHasErrors('cropped_avatar');

        $this->assertNull($user->refresh()->user_image);
    }

    public function test_previous_firebase_avatar_is_deleted_after_replacement(): void
    {
        $oldFirebaseUrl = 'https://firebasestorage.googleapis.com/v0/b/test.appspot.com/o/profile_images%2Fold-avatar.png?alt=media';
        $newFirebaseUrl = 'https://firebasestorage.googleapis.com/v0/b/test.appspot.com/o/profile_images%2Fnew-avatar.png?alt=media';

        $mockStorage = Mockery::mock(FirebaseStorageService::class);
        $mockStorage->shouldReceive('uploadBase64Image')
            ->once()
            ->with(self::TINY_PNG_DATA_URL)
            ->andReturn($newFirebaseUrl);
        $mockStorage->shouldReceive('delete')
            ->once()
            ->with($oldFirebaseUrl);

        $this->app->instance(FirebaseStorageService::class, $mockStorage);

        $user = User::factory()->create([
            'user_image' => $oldFirebaseUrl,
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

        $this->assertSame($newFirebaseUrl, $user->user_image);
    }

    public function test_local_profile_image_is_not_deleted_via_firebase(): void
    {
        // Local images (profile_images/xxx.png) should NOT be deleted via Firebase
        $mockStorage = Mockery::mock(FirebaseStorageService::class);
        $mockStorage->shouldReceive('uploadBase64Image')
            ->once()
            ->andReturn('https://firebasestorage.googleapis.com/v0/b/test.appspot.com/o/profile_images%2Fnew-avatar.png?alt=media');
        $mockStorage->shouldReceive('delete')
            ->never(); // Should NOT delete local images

        $this->app->instance(FirebaseStorageService::class, $mockStorage);

        $user = User::factory()->create([
            'user_image' => 'profile_images/old-local.png',
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
    }

    public function test_ajax_profile_fields_update_returns_json_successfully(): void
    {
        $user = User::factory()->create([
            'email' => 'old@example.com',
            'is_verified' => true,
        ]);

        $this->actingAs($user)
            ->patchJson(route('edit-profile.update'), [
                'name' => 'Updated User via Ajax',
                'email' => 'updated-ajax@example.com',
                'user_dob' => '1999-01-15',
                'user_phone' => '912345678',
                'user_bio' => 'Updated profile bio via Ajax.',
            ])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Profile changes saved successfully.',
            ]);

        $user->refresh();

        $this->assertSame('Updated User via Ajax', $user->name);
        $this->assertSame('updated-ajax@example.com', $user->email);
    }
}
