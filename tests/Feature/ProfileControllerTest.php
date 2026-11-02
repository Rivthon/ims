<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_authenticated_user_can_update_own_profile(): void
    {
        $user = User::factory()->create(['name' => 'Nama Lama', 'email' => 'lama@example.com', 'unit' => 'Unit Lama']);

        $response = $this->actingAs($user)->put(route('profile.update'), ['name' => 'Nama Baru', 'email' => 'baru@example.com', 'unit' => 'ICT']);

        $response->assertRedirect()->assertSessionHas('success');
        $user->refresh();
        $this->assertSame('Nama Baru', $user->name);
        $this->assertSame('baru@example.com', $user->email);
        $this->assertSame('ICT', $user->unit);
    }

    public function test_user_can_change_password_with_correct_current_password(): void
    {
        $user = User::factory()->create(['password' => 'Password123']);

        $response = $this->actingAs($user)->put(route('profile.password.update'), [
            'current_password' => 'Password123', 'password' => 'PasswordBaru123', 'password_confirmation' => 'PasswordBaru123',
        ]);

        $response->assertRedirect()->assertSessionHas('success');
        $this->assertTrue(Hash::check('PasswordBaru123', $user->fresh()->password));
    }

    public function test_password_change_rejects_incorrect_current_password(): void
    {
        $user = User::factory()->create(['password' => 'Password123']);

        $response = $this->actingAs($user)->from(route('profile.edit'))->put(route('profile.password.update'), [
            'current_password' => 'Salah123', 'password' => 'PasswordBaru123', 'password_confirmation' => 'PasswordBaru123',
        ]);

        $response->assertRedirect(route('profile.edit'))->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('Password123', $user->fresh()->password));
    }
}
