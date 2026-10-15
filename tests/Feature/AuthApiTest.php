<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $status = 'active'): User
    {
        $role = new Role;
        $role->role = 'Admin';
        $role->save();

        $user = new User;
        $user->username = 'Admin';
        $user->password = Hash::make('Hola123');
        $user->name = 'Karen R. Coto';
        $user->gender = 'Mujer';
        $user->birth = '2008-07-15';
        $user->email = 'admin@example.test';
        $user->status = $status;
        $user->role_id = $role->id;
        $user->save();

        return $user;
    }

    public function test_active_user_can_login_read_profile_and_logout(): void
    {
        $this->user();

        $login = $this->postJson('/api/login', [
            'username' => 'Admin',
            'password' => 'Hola123',
            'device_name' => 'phpunit',
        ]);

        $login->assertOk()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.username', 'Admin')
            ->assertJsonPath('user.role', 'Admin');
        $this->assertStringContainsString('no-store', (string) $login->headers->get('Cache-Control'));

        $token = $login->json('access_token');

        $this->withToken($token)
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('user.username', 'Admin');

        $this->withToken($token)
            ->postJson('/api/logout')
            ->assertOk()
            ->assertJsonPath('message', 'Sesión cerrada.');

        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/user')->assertUnauthorized();
    }

    public function test_login_validates_required_fields_and_rejects_bad_credentials(): void
    {
        $this->postJson('/api/login', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['username', 'password', 'device_name']);

        $this->user();

        $this->postJson('/api/login', [
            'username' => 'Admin',
            'password' => 'incorrecta',
            'device_name' => 'phpunit',
        ])->assertUnauthorized()
            ->assertJsonPath('message', 'Credenciales incorrectas.');
    }

    public function test_inactive_user_cannot_login_and_an_active_session_is_revoked(): void
    {
        $inactiveUser = $this->user('inactive');

        $this->postJson('/api/login', [
            'username' => $inactiveUser->username,
            'password' => 'Hola123',
            'device_name' => 'phpunit',
        ])->assertForbidden()
            ->assertJsonPath('message', 'El usuario no está activo.');

        $inactiveUser->update(['status' => 'active']);
        $token = $inactiveUser->createToken('phpunit', ['profile:read'])->plainTextToken;
        $inactiveUser->update(['status' => 'inactive']);

        $this->withToken($token)->getJson('/api/user')
            ->assertForbidden()
            ->assertJsonPath('message', 'El usuario no está activo.');

        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $inactiveUser->id]);
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/user')->assertUnauthorized();
    }
}
