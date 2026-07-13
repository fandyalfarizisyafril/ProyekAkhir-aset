<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultSuperAdminSeeder extends Seeder
{
    /**
     * Seed akun Super Admin default untuk akses awal sistem.
     */
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['nip' => '2255301053'],
            [
                'nama' => 'Super Admin SIMA',
                'email' => 'superadmin@sima-diskominfotik.local',
                'password' => Hash::make('2255301053'),
                'no_hp' => null,
                'role' => 'Super Admin',
                'bidang_id' => null,
                'status' => 'Aktif',
            ]
        );

        $user->forceFill([
            'email_verified_at' => now(),
        ])->save();
    }
}
