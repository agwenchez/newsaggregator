<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use DB;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        DB::table('sources')->insert([
            ['name' => 'bbc', 'base_url' => 'https://www.bbc.com','created_at' => now(), 'updated_at' => now()],
            ['name' => 'theguardian', 'base_url' => 'https://www.theguardian.com','created_at' => now(), 'updated_at' => now()],
            ['name' => 'nytimes', 'base_url' => 'https://www.nytimes.com','created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
