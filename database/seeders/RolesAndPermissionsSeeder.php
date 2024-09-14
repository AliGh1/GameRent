<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'create.platform',
            'view.platform',
            'edit.platform',
            'delete.platform',
            'create.genre',
            'view.genre',
            'edit.genre',
            'delete.genre',
            'create.game',
            'edit.game',
            'delete.game',
            'create.account',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        Role::create(['name' => 'Super Admin']);
    }
}
