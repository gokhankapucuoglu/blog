<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::first();
        $author = User::where('id', '!=', $admin->id)->first();
        $category = Category::whereNotNull('parent_id')->first();

        $posts = [
            [
                'user_id' => $admin->id,
                'title' => 'Laravel 11 ile Gelen Yenilikler',
                'content' => 'Bu yazıda Laravel 11 sürümüyle gelen minimalist yapıyı inceliyoruz...',
                'status' => 2,
                'published_at' => now()->subDays(2),
            ],
            [
                'user_id' => $author->id,
                'title' => 'Geleceğin Teknolojisi: Yapay Zeka',
                'content' => 'Yapay zeka dünyasında bizi neler bekliyor? İşte detaylar...',
                'status' => 1,
                'published_at' => null,
            ],
            [
                'user_id' => $author->id,
                'title' => 'Zamanlanmış Bir Post Testi',
                'content' => 'Bu post onaylı olsa bile tarihi gelecekte olduğu için görünmemeli.',
                'status' => 2,
                'published_at' => now()->addDays(5),
            ],
            [
                'user_id' => $admin->id,
                'title' => 'Taslak Olarak Kalan Bir Yazı',
                'content' => 'Bu yazı henüz bitmediği için yazar tarafından taslakta bırakıldı.',
                'status' => 0,
                'published_at' => null,
            ],
        ];

        foreach ($posts as $post) {
            Post::create(array_merge($post, [
                'category_id' => $category->id,
                'slug' => Str::slug($post['title']),
                'description' => Str::limit($post['content'], 150),
                'image' => 'default.jpg',
                'tags' => ['yazilim', 'test', 'laravel'],
                'view_count' => rand(100, 5000),
            ]));
        }
    }
}
