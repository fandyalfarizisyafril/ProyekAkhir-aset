<?php

use App\Models\Bidang;
use App\Models\User;

test('super admin user form automatically provides default bidang options', function () {
    $superAdmin = User::factory()->create(['role' => 'Super Admin']);

    Bidang::query()->delete();

    $response = $this->actingAs($superAdmin)
        ->get(route('super-admin.pengguna.create'));

    $response->assertOk();
    $response->assertSee('Aptika');
    $response->assertSee('Persandian');
    $response->assertSee('Statistik');
    expect(Bidang::count())->toBeGreaterThan(0);
});
