<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Electronics & Devices' => ['Smartphones', 'Laptops & Chargers', 'Tablets', 'Headphones & Earbuds', 'Flash Drives / Hard Drives'],
            'Documents & Cards' => ['Student ID Cards', 'National IDs & Passports', 'Certificates & Transcripts', 'Bank Cards & Wallets', 'Notebooks & Textbooks'],
            'Personal Accessories' => ['Keys & Keychains', 'Glasses & Sunglasses', 'Watches & Jewelry', 'Bags & Backpacks'],
            'Clothing & Apparel' => ['Jackets & Sweaters', 'Hats & Caps', 'Shoes'],
        ];

        foreach ($categories as $parentName => $subCategories) {
            $parent = Category::firstOrCreate(['slug' => Str::slug($parentName)], [
                'name' => $parentName,
                'description' => $parentName . ' category',
                'is_active' => true,
            ]);

            foreach ($subCategories as $subName) {
                Category::firstOrCreate(['slug' => Str::slug($subName)], [
                    'parent_id' => $parent->id,
                    'name' => $subName,
                    'description' => $subName . ' sub-category',
                    'is_active' => true,
                ]);
            }
        }
    }
}
