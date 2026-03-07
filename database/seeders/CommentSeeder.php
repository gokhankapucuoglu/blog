<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\CommentLike;
use App\Models\CommentReport;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    public function run(): void
    {
        $posts = Post::where('status', Post::STATUS_PUBLISHED)
            ->where(function ($q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            })
            ->get();

        if ($posts->isEmpty()) {
            $this->command->warn('Yayınlanmış post bulunamadı, CommentSeeder atlandı.');
            return;
        }

        $users    = User::all();
        $adminIds = User::role(['super_admin', 'admin'])->pluck('id')->toArray();

        $commentBodies = [
            // Olumlu
            'Çok faydalı bir yazı olmuş, teşekkürler!',
            'Tam aradığım konuydu, detaylar için ayrıca teşekkür ederim.',
            'Anlatım gerçekten çok akıcı ve anlaşılır. Eline sağlık.',
            'Bu konuda daha önce hiç bu kadar net bir kaynak bulamamıştım.',
            'Harika bir içerik, sosyal medyada paylaşmak istiyorum.',
            'Özellikle son bölüm çok aydınlatıcıydı, devamını bekliyoruz.',

            // Soru / Tartışma
            'Peki bunu production ortamında kullanmak güvenli mi?',
            'Acaba bu yaklaşım büyük ölçekli projelerde nasıl davranıyor?',
            'Laravel 12 ile de aynı şekilde çalışıyor mu, deneyen var mı?',
            'Bunu mevcut projeme entegre etmek istiyorum, nereden başlamalıyım?',
            'Alternatif bir çözüm yolu var mı? Sizce hangisi daha performanslı?',
            'Kaynaklarda belirttiğiniz linke ulaşamıyorum, güncel linki paylaşabilir misiniz?',

            // Olumsuz / Eleştiri
            'Bazı kısımlar biraz muğlak kalmış, daha fazla örnek eklenebilirdi.',
            'Konu güzel ama kod örnekleri daha güncel olabilirdi.',
            'Bazı teknik detaylar eksik kalmış gibi hissettim.',

            // Cevap Tarzı Yorumlar
            'Ben de aynı sorunu yaşadım, şu şekilde çözdüm: config cache temizlemek işe yaradı.',
            'Yukarıdaki yorum için eklemek istiyorum: bu sadece MySQL için geçerli.',
            'Kesinlikle katılıyorum, ben de aynı deneyimi yaşadım.',
            'Benim durumumda biraz farklıydı ama genel mantık aynı.',
            'Bu bilgiyi bir ay önce bilseydim çok zaman kazanırdım!',
        ];

        $reportReasons = ['spam', 'hate_speech', 'harassment', 'misinformation', 'off_topic', 'other'];

        foreach ($posts as $post) {
            $rootCount = rand(3, 8);

            for ($i = 0; $i < $rootCount; $i++) {
                $commenter  = $users->random();
                $isAdmin    = in_array($commenter->id, $adminIds);
                $commentedAt = fake()->dateTimeBetween($post->published_at ?? '-3 months', 'now');

                $status = $isAdmin
                    ? Comment::STATUS_APPROVED
                    : fake()->randomElement([
                        Comment::STATUS_APPROVED,
                        Comment::STATUS_APPROVED,
                        Comment::STATUS_APPROVED,
                        Comment::STATUS_PENDING,
                        Comment::STATUS_REJECTED,
                        Comment::STATUS_SPAM,
                    ]);

                $moderatorId  = null;
                $moderatedAt  = null;
                $rejectionNote = null;

                if ($status !== Comment::STATUS_PENDING) {
                    $moderatorId = fake()->randomElement($adminIds);
                    $moderatedAt = fake()->dateTimeBetween($commentedAt, 'now');
                }

                if ($status === Comment::STATUS_REJECTED) {
                    $rejectionNote = fake()->randomElement([
                        'Uygunsuz içerik tespit edildi.',
                        'Konu ile alakasız yorum.',
                        'Reklam içeriği kabul edilmez.',
                        'Hakaret içerdiği için reddedildi.',
                    ]);
                }

                $rootComment = Comment::create([
                    'post_id'        => $post->id,
                    'user_id'        => $commenter->id,
                    'parent_id'      => null,
                    'ancestry'       => null,
                    'body'           => fake()->randomElement($commentBodies),
                    'status'         => $status,
                    'rejection_note' => $rejectionNote,
                    'moderated_by'   => $moderatorId,
                    'moderated_at'   => $moderatedAt,
                    'ip_address'     => fake()->ipv4(),
                    'created_at'     => $commentedAt,
                    'updated_at'     => $commentedAt,
                ]);

                if ($status === Comment::STATUS_APPROVED) {
                    $this->seedLikes($rootComment, $users);
                    $this->seedReports($rootComment, $users, $reportReasons);
                }

                if ($status === Comment::STATUS_APPROVED && fake()->boolean(60)) {
                    $replyCount = rand(1, 3);

                    for ($j = 0; $j < $replyCount; $j++) {
                        $replier   = $users->random();
                        $repliedAt = fake()->dateTimeBetween($commentedAt, 'now');

                        $replyStatus = fake()->randomElement([
                            Comment::STATUS_APPROVED,
                            Comment::STATUS_APPROVED,
                            Comment::STATUS_PENDING,
                        ]);

                        $reply = Comment::create([
                            'post_id'      => $post->id,
                            'user_id'      => $replier->id,
                            'parent_id'    => $rootComment->id,
                            'ancestry'     => (string) $rootComment->id,
                            'body'         => fake()->randomElement($commentBodies),
                            'status'       => $replyStatus,
                            'moderated_by' => $replyStatus !== Comment::STATUS_PENDING
                                                ? fake()->randomElement($adminIds)
                                                : null,
                            'moderated_at' => $replyStatus !== Comment::STATUS_PENDING
                                                ? fake()->dateTimeBetween($repliedAt, 'now')
                                                : null,
                            'ip_address'   => fake()->ipv4(),
                            'created_at'   => $repliedAt,
                            'updated_at'   => $repliedAt,
                        ]);

                        if ($replyStatus === Comment::STATUS_APPROVED) {
                            $this->seedLikes($reply, $users);
                        }

                        $rootComment->increment('reply_count');
                    }

                    $rootComment->update([
                        'like_count'    => $rootComment->likes()->where('type', 1)->count(),
                        'dislike_count' => $rootComment->likes()->where('type', -1)->count(),
                        'report_count'  => $rootComment->reports()->count(),
                    ]);
                }
            }
        }
    }

    private function seedLikes(Comment $comment, $users): void
    {
        $likers = $users->random(rand(0, min(8, $users->count())))->unique('id');

        foreach ($likers as $liker) {
            CommentLike::firstOrCreate(
                ['comment_id' => $comment->id, 'user_id' => $liker->id],
                ['type' => fake()->randomElement([1, 1, 1, -1])]
            );
        }
    }

    private function seedReports(Comment $comment, $users, array $reasons): void
    {
        if (! fake()->boolean(15)) return;

        $reporters = $users->random(rand(1, min(3, $users->count())))->unique('id');

        foreach ($reporters as $reporter) {
            CommentReport::firstOrCreate(
                ['comment_id' => $comment->id, 'user_id' => $reporter->id],
                [
                    'reason' => fake()->randomElement($reasons),
                    'note'   => fake()->boolean(40) ? fake()->sentence() : null,
                    'status' => fake()->randomElement([0, 0, 1]), // Çoğu bekliyor
                ]
            );
        }

        $comment->update(['report_count' => $comment->reports()->count()]);
    }
}
