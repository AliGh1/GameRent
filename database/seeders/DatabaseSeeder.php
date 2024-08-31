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
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        Genre::factory()->count(5)->create();
        Platform::factory()->count(3)->create();

        $games = Game::factory()->count(10)->create();

        Account::factory()->count(20)->recycle($games)->create();
    }
}
