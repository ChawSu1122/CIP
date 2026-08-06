<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Desktop Buying',
            'Laptop Deals',
            'Buy & Sell',
            'Hardware Advice',
            'IT Support'
        ];

        foreach ($categories as $category) {
            Category::create(['name' => $category]);
        }
    }
}
