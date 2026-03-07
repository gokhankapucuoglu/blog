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

        $tagsPool = ['yazılım', 'test', 'laravel', 'php', 'filament', 'teknoloji', 'yapay-zeka', 'web', 'tasarım', 'react', 'vue'];

        $realisticTitles = [
            'Laravel 11 ile Gelen Harika Yenilikler ve Performans İyileştirmeleri',
            'Yapay Zeka Destekli Kodlama: GitHub Copilot Geleceği Nasıl Şekillendiriyor?',
            'PHP 8.3 Sürümünde Gözden Kaçırmamanız Gereken 5 Önemli Özellik',
            'Filament PHP ile Hızlı ve Şık Admin Panelleri Geliştirmek Çok Kolay',
            'React vs Vue.js: Gelecekte Hangi Framework Tercih Edilmeli?',
            'Yeni Başlayanlar İçin Temiz Kod (Clean Code) Yazma Rehberi',
            'Web Geliştirmede Tailwind CSS Kullanmanın Sağladığı İnanılmaz Avantajlar',
            'Yazılım Test Süreçlerinde Otomasyonun Önemi ve Modern Araçlar',
            'Mikroservis Mimarisine Geçiş: Doğru Bilinen Yanlışlar Nelerdir?',
            'Docker ve Container Teknolojileri Neden Bu Kadar Popüler Oldu?',
            'Mobil Uygulama Geliştirmede Flutter Fırtınası Devam Ediyor',
            'Veritabanı Optimizasyonu: Yavaş Sorguları Nasıl Hızlandırırsınız?',
            'Frontend Dünyasında State Management: Redux ve Alternatifleri',
            'Siber Güvenlik 101: Geliştiriciler İçin Temel Güvenlik Önlemleri',
            'Uzaktan Çalışma (Remote Work) Kültüründe Verimliliği Artırma Yolları'
        ];

        $realisticParagraphs = [
            "Teknoloji dünyası her geçen gün büyük bir hızla evrilmeye devam ediyor. Geliştiriciler olarak bu hıza ayak uydurmak ve modern araçları projelerimize entegre etmek en büyük önceliğimiz haline geldi. Sektördeki en son gelişmeler, kullandığımız framework'lerin yeni sürümleriyle birlikte iş akışlarımızı tamamen değiştiriyor.",
            "Özellikle son dönemde çıkan güncellemelerle birlikte, performans tarafında ciddi iyileştirmeler yapıldı. Eskiden saatler süren yapılandırma işlemleri artık dakikalar içinde çözülebiliyor. Kodun okunabilirliği ve sürdürülebilirliği açısından sunulan yeni standartlar, takım çalışmasını çok daha verimli bir hale getiriyor.",
            "Eğer siz de mevcut projelerinizde eski yapıları kullanmaya devam ediyorsanız, bu yeni teknolojilere geçiş yapmanın tam zamanı olabilir. Başlangıçta öğrenme eğrisi biraz dik görünse de, uzun vadede kazanacağınız zaman ve efor kesinlikle bu yatırıma değecektir. Modern mimariler hayat kurtarır.",
            "Topluluk desteği, bu araçların arkasındaki en büyük güç. Geliştiriciler karşılaştıkları sorunları hızlıca çözebiliyor ve açık kaynak (open source) dünyası sayesinde her geçen gün yeni eklentiler sisteme dahil ediliyor. Özetle, geleceğin projelerini inşa ederken doğru araç setini seçmek başarının anahtarıdır."
        ];

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

            $title = fake()->randomElement($realisticTitles) . ' - ' . rand(1, 99);

            $content = "<p>" . $realisticParagraphs[0] . "</p><p>" . $realisticParagraphs[1] . "</p><p>" . $realisticParagraphs[2] . "</p>";

            if (fake()->boolean(50)) {
                $content .= "<p>" . $realisticParagraphs[3] . "</p>";
            }

            $post = Post::create([
                'user_id'      => $userId,
                'category_id'  => fake()->randomElement($categoryIds),
                'title'        => $title,
                'slug'         => Str::slug($title),
                'content'      => $content,
                'description'  => Str::limit(strip_tags($content), 150),
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
                'description' => 'Güncelleme işlemi yapıldı.',
                'created_at'  => $post->created_at,
            ]);

            if ($status === 1) {
                PostHistory::create([
                    'post_id'     => $post->id,
                    'user_id'     => $userId,
                    'action'      => 'Onaya Gönderildi',
                    'description' => 'Admin onayına sunuldu.',
                    'created_at'  => $post->created_at->addHours(2),
                ]);
            }
            elseif ($status === 2) {
                if (! $isAdmin) {
                    PostHistory::create([
                        'post_id'     => $post->id,
                        'user_id'     => $userId,
                        'action'      => 'Onaya Gönderildi',
                        'description' => 'Admin onayına sunuldu.',
                        'created_at'  => $post->created_at->addHours(2),
                    ]);
                }

                $approverId = $isAdmin ? $userId : fake()->randomElement($adminIds);
                $isFuture = $publishedAt > now();

                PostHistory::create([
                    'post_id'     => $post->id,
                    'user_id'     => $approverId,
                    'action'      => $isFuture ? 'Planlandı' : 'Yayınlandı',
                    'description' => $isFuture
                                        ? "Onaylandı ve {$publishedAt->format('d/m/Y H:i')} tarihinde yayınlanacak."
                                        : 'Onaylandı ve yayınlandı.',
                    'created_at'  => $post->created_at->addHours(5),
                ]);
            }
            elseif ($status === 3) {
                PostHistory::create([
                    'post_id'     => $post->id,
                    'user_id'     => $userId,
                    'action'      => 'Onaya Gönderildi',
                    'description' => 'Admin onayına sunuldu.',
                    'created_at'  => $post->created_at->addHours(2),
                ]);

                $reasons = [
                    'İçerik kurallarımıza uymuyor, lütfen tekrar düzenleyin.',
                    'Daha fazla görsel ve detay eklemeniz gerekiyor.',
                    'Yazım hataları çok fazla, düzeltip tekrar gönderin.',
                    'Konu daha önce başka bir yazarımız tarafından işlenmiş.'
                ];

                PostHistory::create([
                    'post_id'     => $post->id,
                    'user_id'     => fake()->randomElement($adminIds),
                    'action'      => 'Reddedildi',
                    'description' => 'Sebebi: ' . fake()->randomElement($reasons),
                    'created_at'  => $post->created_at->addHours(5),
                ]);
            }
        }
    }
}
