<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CSRFTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_csrf_protection_on_post(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->patch('/menu/edit-profile', ['name' => 'New Name']);

        // This might fail with 419 if CSRF is active and not handled by test
        // But usually actingAs() should handle session.
        $this->assertNotEquals(419, $response->getStatusCode(), 'POST request failed with 419 CSRF error');
    }
}
