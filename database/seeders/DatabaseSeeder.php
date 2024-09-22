<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Game;
use App\Models\Genre;
use App\Models\Platform;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $superAdmin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
        ]);

        $superAdmin->assignRole('Super Admin');

        Genre::factory()->count(5)->create();
        Platform::factory()->count(3)->create();

        $games = Game::factory()->count(10)->create();

        Account::factory()->count(20)->recycle($games)->create();
    }
}
