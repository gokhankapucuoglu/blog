<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Yazılım', 'slug' => 'yazilim', 'status' => 1],
            ['name' => 'Eğitim', 'slug' => 'egitim', 'status' => 1],
            ['name' => 'Teknoloji', 'slug' => 'teknoloji', 'status' => 1],
        ];

        foreach ($categories as $cat) {
            $parent = Category::create($cat);

            if ($parent->slug === 'yazilim') {
                Category::create(['name' => 'Laravel', 'slug' => 'laravel', 'parent_id' => $parent->id, 'status' => 1]);
                Category::create(['name' => 'React', 'slug' => 'react', 'parent_id' => $parent->id, 'status' => 1]);
            }

            if ($parent->slug === 'teknoloji') {
                Category::create(['name' => 'Yapay Zeka', 'slug' => 'yapay-zeka', 'parent_id' => $parent->id, 'status' => 1]);
            }
        }
    }
}
