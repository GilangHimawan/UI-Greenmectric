<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Core\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_login_admin()
    {
        // Pastikan role tersedia
        Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web'
        ]);

        // Membuat user
        $user = User::create([
            'name' => 'Administrator',
            'username' => 'admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role_aktif' => 'admin'
        ]);

        // Assign role
        $user->assignRole('admin');

        // Login
        $response = $this->post('/login', [
            'login' => 'admin',
            'password' => 'password',
        ]);

        $response->assertRedirect('/');

        $this->assertAuthenticated();
    }

    /** @test */
    public function login_berhasil_Koordinator() {
         Role::firstOrCreate([
            'name' => 'Koordinator',
            'guard_name' => 'web'
        ]);

        $user = User::create([
            'name' => 'Koordinator',
            'username' => 'koordinator',
            'email' => 'koordinator@test.com',
            'password' => Hash::make('password'),
            'role_aktif' => 'koordinator'
        ]);

        $user->assignRole('Koordinator');

        $response = $this->post('/login', [
            'login' => 'koordinator',
            'password' => 'password',
        ]);
        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function login_gagal_tidak_mengisi_form()
    {
        Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web'
        ]);

        $user = User::create([
            'name' => 'Administrator',
            'username' => 'admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role_aktif' => 'admin'
        ]);

        $user->assignRole('admin');

        $response = $this->post('/login', [
            'login' => '',
            'password' => '',
        ]);

        $response->assertSessionHasErrors();

        $this->assertGuest();
    }

    
}