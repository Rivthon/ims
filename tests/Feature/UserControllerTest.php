<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_super_admin_can_create_user_with_role(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Staff Baru', 'email' => 'staff.baru@example.com', 'unit' => 'ICT', 'role' => 'ict_staff',
            'is_active' => 1, 'password' => 'Password123', 'password_confirmation' => 'Password123',
        ]);

        $response->assertRedirect(route('users.index'));
        $user = User::query()->where('email', 'staff.baru@example.com')->firstOrFail();
        $this->assertSame('ict_staff', $user->role);
        $this->assertTrue($user->is_active);
        $this->assertTrue(Hash::check('Password123', $user->password));
    }

    public function test_non_admin_cannot_manage_users(): void
    {
        $staff = User::factory()->create(['role' => 'ict_staff']);

        $this->actingAs($staff)->get(route('users.index'))->assertForbidden();
        $this->actingAs($staff)->post(route('users.store'), [])->assertForbidden();
    }

    public function test_super_admin_cannot_deactivate_own_account(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin', 'is_active' => true]);

        $response = $this->actingAs($admin)->from(route('users.edit', $admin))->put(route('users.update', $admin), [
            'name' => $admin->name, 'email' => $admin->email, 'unit' => 'ICT', 'role' => 'super_admin', 'is_active' => 0,
        ]);

        $response->assertRedirect(route('users.edit', $admin))->assertSessionHasErrors('role');
        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create(['email' => 'inactive@example.com', 'password' => 'Password123', 'is_active' => false]);

        $response = $this->post('/login', ['email' => $user->email, 'password' => 'Password123']);

        $response->assertRedirect()->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_inactive_authenticated_session_is_logged_out(): void
    {
        $user = User::factory()->create(['is_active' => false]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('login'))->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
