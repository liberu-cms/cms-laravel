<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $permissions = Permission::where('guard_name', 'web')->pluck('id')->toArray();
        $adminRole->syncPermissions($permissions);

        $freeRole = Role::firstOrCreate(['name' => 'free', 'guard_name' => 'web']);
        $freePermissions = Permission::where('guard_name', 'web')->pluck('id')->toArray();
        $freeRole->syncPermissions($freePermissions);
    }
}
