<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\PostHistory;
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
        $admins = User::role(['super_admin', 'admin'])->get();
        $authors = User::role('author')->get();

        $adminIds = $admins->pluck('id')->toArray();
        $authorIds = $authors->pluck('id')->toArray();
        $allUserIds = array_merge($adminIds, $authorIds);

        $categoryIds = Category::whereNotNull('parent_id')->pluck('id')->toArray();
        if (empty($categoryIds)) {
            $categoryIds = Category::pluck('id')->toArray();
        }

        $tagsPool = ['yazılım', 'test', 'laravel', 'php', 'filament', 'teknoloji', 'yapay-zeka', 'web', 'tasarım'];

        for ($i = 1; $i <= 30; $i++) {
            $userId = fake()->randomElement($allUserIds);
            $isAdmin = in_array($userId, $adminIds);

            $status = $isAdmin ? fake()->randomElement([0, 2]) : fake()->randomElement([0, 1, 2, 3]);

            $publishedAt = null;
            if ($status === 2) {
                $publishedAt = fake()->boolean(80)
                    ? fake()->dateTimeBetween('-3 months', 'now')
                    : fake()->dateTimeBetween('now', '+2 weeks');
            }

            $title = fake()->sentence(rand(4, 8));
            $content = fake()->paragraphs(rand(3, 7), true);

            $post = Post::create([
                'user_id'      => $userId,
                'category_id'  => fake()->randomElement($categoryIds),
                'title'        => rtrim($title, '.'),
                'slug'         => Str::slug($title),
                'content'      => $content,
                'description'  => Str::limit($content, 150),
                'status'       => $status,
                'published_at' => $publishedAt,
                'image'        => 'default.jpg',
                'tags'         => fake()->randomElements($tagsPool, rand(2, 4)),
                'view_count'   => rand(50, 10000),
                'created_at'   => fake()->dateTimeBetween('-4 months', '-1 month'),
            ]);

            PostHistory::create([
                'post_id'     => $post->id,
                'user_id'     => $userId,
                'action'      => 'Oluşturuldu',
                'description' => 'Yazı taslak olarak oluşturuldu.',
                'created_at'  => $post->created_at,
            ]);

            if ($status === 1) {
                PostHistory::create([
                    'post_id'     => $post->id,
                    'user_id'     => $userId,
                    'action'      => 'Onaya Gönderildi',
                    'description' => 'Yazar tarafından admin onayına sunuldu.',
                    'created_at'  => $post->created_at->addHours(2),
                ]);
            }
            elseif ($status === 2) {
                if (! $isAdmin) {
                    PostHistory::create([
                        'post_id'     => $post->id,
                        'user_id'     => $userId,
                        'action'      => 'Onaya Gönderildi',
                        'description' => 'Yazar tarafından admin onayına sunuldu.',
                        'created_at'  => $post->created_at->addHours(2),
                    ]);
                }

                $approverId = $isAdmin ? $userId : fake()->randomElement($adminIds);

                PostHistory::create([
                    'post_id'     => $post->id,
                    'user_id'     => $approverId,
                    'action'      => $publishedAt > now() ? 'Planlandı' : 'Yayınlandı',
                    'description' => $isAdmin && $approverId === $userId
                        ? 'Admin tarafından kendi yazısı yayına alındı.'
                        : 'Admin tarafından onaylandı.',
                    'created_at'  => $post->created_at->addHours(5),
                ]);
            }
            elseif ($status === 3) {
                PostHistory::create([
                    'post_id'     => $post->id,
                    'user_id'     => $userId,
                    'action'      => 'Onaya Gönderildi',
                    'description' => 'Yazar tarafından admin onayına sunuldu.',
                    'created_at'  => $post->created_at->addHours(2),
                ]);

                PostHistory::create([
                    'post_id'     => $post->id,
                    'user_id'     => fake()->randomElement($adminIds),
                    'action'      => 'Reddedildi',
                    'description' => 'Sebebi: ' . fake()->sentence(),
                    'created_at'  => $post->created_at->addHours(5),
                ]);
            }
        }
    }
}
