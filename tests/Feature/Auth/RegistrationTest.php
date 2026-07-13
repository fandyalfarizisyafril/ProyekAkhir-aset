<?php

use App\Models\User;
use Database\Seeders\DefaultSuperAdminSeeder;
use Illuminate\Support\Facades\Hash;

test('public registration routes are disabled', function () {
    $this->get('/register')->assertNotFound();
    $this->post('/register')->assertNotFound();
});

test('default super admin account is seeded and can login', function () {
    $this->seed(DefaultSuperAdminSeeder::class);

    $user = User::where('nip', '2255301053')->first();

    expect($user)->not->toBeNull();
    expect($user->role)->toBe('Super Admin');
    expect($user->status)->toBe('Aktif');
    expect(Hash::check('2255301053', $user->password))->toBeTrue();
    expect($user->email_verified_at)->not->toBeNull();

    $response = $this->post('/login', [
        'nip' => '2255301053',
        'password' => '2255301053',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect('/super-admin/dashboard');
});
