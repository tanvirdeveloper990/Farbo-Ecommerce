<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::firstOrCreate(['name' => 'Administrator']);
        foreach (['users.manage' => 'Manage customer accounts', 'roles.manage' => 'Manage team roles (owner only)', 'permissions.manage' => 'Manage permissions (owner only)'] as $name => $description) {
            $permission = Permission::firstOrCreate(['name' => $name], ['description' => $description]);
            $role->permissions()->syncWithoutDetaching([$permission->id]);
        }
    }
}
