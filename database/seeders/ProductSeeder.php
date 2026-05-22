<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\User;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $owner = User::where('email', 'owner@example.com')->first();
        $user = User::where('email', 'user@example.com')->first();

        if ($owner) {
            Product::factory()->count(10)->create(['user_id' => $owner->id]);
        }

        if ($user) {
            Product::factory()->count(5)->create(['user_id' => $user->id]);
        }
    }
}
