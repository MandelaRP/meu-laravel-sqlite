<?php

declare(strict_types = 1);

namespace Database\Seeders;

use App\Enums\Can;
use App\Enums\Roles;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (Roles::cases() as $role) {
            Role::create(['name' => $role->value]);
        }

        $permissions = Can::cases();

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission->value]);
        }

        //$userAdmin->permissions()->attach(Permission::where('name', Can::ViewUser->value)->first());
    }
}
