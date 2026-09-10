<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesAndAdminSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        $admin = User::firstOrCreate(
            ['email' => 'admin@lelabel.local'],
            ['name' => 'Administrateur', 'password' => 'password', 'email_verified_at' => now()]
        );

        $admin->assignRole($adminRole);
    }
}
