<?php

declare(strict_types = 1);

namespace Database\Seeders;

use App\Enums\Can;
use App\Enums\UserStatusEnum;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $user = User::factory()->create([
            'name'   => 'Wender',
            'email'  => 'wender_dev@hotmail.com',
            'role'   => 'admin',
            'status' => UserStatusEnum::ACTIVE,
        ]);

        $this->call([
            GroupSeeder::class,
            RolesPermissionSeeder::class,
            AcquirerSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            //CheckoutSeeder::class,
        ]);

        $user->roles()->attach(Role::where('name', 'admin')->first());
        $user->permissions()->attach(Permission::where('name', Can::ViewUser->value)->first());
    }
}
