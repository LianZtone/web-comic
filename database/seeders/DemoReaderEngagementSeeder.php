<?php

namespace Database\Seeders;

use App\Models\Chapter;
use App\Models\ChapterComment;
use App\Models\ChapterCommentVote;
use App\Models\ChapterReaction;
use App\Models\ComicComment;
use App\Models\ComicCommentVote;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DemoReaderEngagementSeeder extends Seeder
{
    public function run(): void
    {
        if (! $this->requiredTablesReady()) {
            return;
        }

        $chapter = Chapter::query()
            ->whereHas('comic', fn ($query) => $query->where('slug', 'aether-crown'))
            ->where('number', 1)
            ->first();

        if (! $chapter) {
            return;
        }

        ChapterReaction::query()->where('chapter_id', $chapter->id)->delete();
        ChapterComment::query()->where('chapter_id', $chapter->id)->delete();
        ComicComment::query()->where('comic_id', $chapter->comic_id)->delete();

        $this->seedReactions($chapter);
        $this->seedComments($chapter);
        $this->seedComicComments($chapter);
    }

    private function requiredTablesReady(): bool
    {
        return Schema::hasTable('chapters')
            && Schema::hasTable('chapter_reactions')
            && Schema::hasTable('chapter_comments')
            && Schema::hasTable('chapter_comment_votes')
            && Schema::hasTable('comic_comments')
            && Schema::hasTable('comic_comment_votes');
    }

    private function seedReactions(Chapter $chapter): void
    {
        $distribution = [
            'like' => 18,
            'hype' => 14,
            'sad' => 7,
            'twist' => 11,
        ];

        foreach ($distribution as $type => $count) {
            foreach (range(1, $count) as $index) {
                ChapterReaction::query()->create([
                    'chapter_id' => $chapter->id,
                    'type' => $type,
                    'reactor_key' => "seed-reader-{$type}-{$index}",
                    'created_at' => now()->subMinutes(($index * 3) + strlen($type)),
                    'updated_at' => now()->subMinutes(($index * 3) + strlen($type)),
                ]);
            }
        }
    }

    private function seedComments(Chapter $chapter): void
    {
        $names = [
            'Raka', 'Dina', 'Miko', 'Tari', 'Fikri', 'Alya', 'Bimo', 'Sasa', 'Nando', 'Lia',
            'Rere', 'Hana', 'Adit', 'Icha', 'Rizky', 'Nisa', 'Bagas', 'Karin', 'Yoga', 'Vina',
        ];

        $openings = [
            'Panel pembukanya kuat banget',
            'Chapter ini enak dibaca dari awal',
            'Saya suka ritme ceritanya',
            'Bagian konflik utamanya kena',
            'Visual chapter ini rapi banget',
            'Dialognya terasa natural',
            'Tension-nya dapet dari awal',
            'Dunianya makin menarik di chapter ini',
            'Karakter utamanya makin enak diikuti',
            'Cliffhanger penutupnya bekerja banget',
        ];

        $middles = [
            'terutama pas rahasia mahkota mulai dibahas.',
            'apalagi waktu pewarisnya mulai melawan dewan.',
            'dan chemistry antar karakternya juga enak.',
            'terasa kayak build up untuk arc besar berikutnya.',
            'latar kerajaannya juga kelihatan hidup.',
            'pacing-nya cepat tapi masih jelas diikuti.',
            'banyak panel yang cocok jadi highlight.',
            'saya jadi penasaran dengan motif tokoh pendukungnya.',
            'emosinya dapet tanpa terasa lebay.',
            'bagian akhirnya bikin pengen lanjut terus.',
        ];

        $closings = [
            'Semoga update berikutnya tetap sekonsisten ini.',
            'Tim Velmics pilih judul ini pas sih.',
            'Saya bakal pantau chapter selanjutnya.',
            'Ini salah satu pembuka seri yang paling meyakinkan.',
            'Potensinya besar buat jadi favorit mingguan.',
            'Baca sekali langsung pengen lanjut maraton.',
            'Semoga worldbuilding-nya makin dieksplor lagi.',
            'Saya pengen lihat dinamika istananya makin panas.',
            'Untuk chapter pertama, ini sudah ngunci perhatian.',
            'Overall, kuat dan rapi banget eksekusinya.',
        ];

        $comments = collect(range(1, 50))->map(function (int $index) use ($chapter, $names, $openings, $middles, $closings) {
            $name = $names[($index - 1) % count($names)];
            $opening = $openings[($index - 1) % count($openings)];
            $middle = $middles[($index + 2) % count($middles)];
            $closing = $closings[($index + 4) % count($closings)];
            $createdAt = now()->subHours(72 - $index)->subMinutes($index * 7);

            return ChapterComment::query()->create([
                'chapter_id' => $chapter->id,
                'display_name' => "{$name} {$index}",
                'body' => "{$opening}, {$middle} {$closing}",
                'likes_count' => 0,
                'is_visible' => true,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        });

        $comments->each(function (ChapterComment $comment, int $index) {
            $likeCount = 2 + ($index % 6);
            $dislikeCount = $index % 3 === 0 ? 1 : 0;

            foreach (range(1, $likeCount) as $voteIndex) {
                ChapterCommentVote::query()->create([
                    'chapter_comment_id' => $comment->id,
                    'voter_key' => "seed-like-{$comment->id}-{$voteIndex}",
                    'vote' => 'like',
                    'created_at' => $comment->created_at?->copy()->addMinutes($voteIndex),
                    'updated_at' => $comment->created_at?->copy()->addMinutes($voteIndex),
                ]);
            }

            foreach (range(1, $dislikeCount) as $voteIndex) {
                ChapterCommentVote::query()->create([
                    'chapter_comment_id' => $comment->id,
                    'voter_key' => "seed-dislike-{$comment->id}-{$voteIndex}",
                    'vote' => 'dislike',
                    'created_at' => $comment->created_at?->copy()->addMinutes($likeCount + $voteIndex),
                    'updated_at' => $comment->created_at?->copy()->addMinutes($likeCount + $voteIndex),
                ]);
            }

            $comment->update([
                'likes_count' => $likeCount,
            ]);
        });
    }

    private function seedComicComments(Chapter $chapter): void
    {
        $reviews = [
            ['name' => 'Raka Panel', 'score' => 5, 'body' => 'Untuk ukuran seri pembuka, hook dan worldbuilding Aether Crown terasa langsung punya identitas. Saya suka cara konfliknya dibuka pelan tapi tetap bikin penasaran.'],
            ['name' => 'Alya Baca', 'score' => 4, 'body' => 'Saya paling suka ritme bacanya karena tiap chapter terasa maju. Visual dan pacing-nya cukup stabil buat dibaca mingguan.'],
            ['name' => 'Nando Arc', 'score' => 5, 'body' => 'Politik istana dan sisi fantasinya nyatu, bukan tempelan. Potensinya besar kalau arc pewaris ini dijaga konsisten.'],
            ['name' => 'Sasa Loop', 'score' => 4, 'body' => 'Karakternya gampang diingat dan cover-nya menarik. Saya berharap interaksi antar fraksi makin diperluas di chapter berikutnya.'],
            ['name' => 'Yoga Draft', 'score' => 5, 'body' => 'Ini tipe judul yang kelihatan niat dari presentasi sampai setup konfliknya. Salah satu demo seri yang paling kuat di katalog saat ini.'],
            ['name' => 'Vina Note', 'score' => 4, 'body' => 'Masih awal tapi fondasinya sudah bagus. Kalau konsisten di kualitas panel dan progres cerita, seri ini gampang jadi favorit banyak pembaca.'],
        ];

        collect($reviews)->each(function (array $review, int $index) use ($chapter) {
            $createdAt = now()->subDays(8 - $index)->subHours($index + 2);

            $comment = ComicComment::query()->create([
                'comic_id' => $chapter->comic_id,
                'display_name' => $review['name'],
                'score' => $review['score'],
                'body' => $review['body'],
                'likes_count' => 0,
                'is_visible' => true,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            $likeCount = 3 + ($index % 4);
            $dislikeCount = $index % 3 === 0 ? 1 : 0;

            foreach (range(1, $likeCount) as $voteIndex) {
                ComicCommentVote::query()->create([
                    'comic_comment_id' => $comment->id,
                    'voter_key' => "seed-comic-like-{$comment->id}-{$voteIndex}",
                    'vote' => 'like',
                    'created_at' => $createdAt->copy()->addMinutes($voteIndex),
                    'updated_at' => $createdAt->copy()->addMinutes($voteIndex),
                ]);
            }

            foreach (range(1, $dislikeCount) as $voteIndex) {
                ComicCommentVote::query()->create([
                    'comic_comment_id' => $comment->id,
                    'voter_key' => "seed-comic-dislike-{$comment->id}-{$voteIndex}",
                    'vote' => 'dislike',
                    'created_at' => $createdAt->copy()->addMinutes($likeCount + $voteIndex),
                    'updated_at' => $createdAt->copy()->addMinutes($likeCount + $voteIndex),
                ]);
            }

            $comment->update([
                'likes_count' => $likeCount,
            ]);
        });
    }
}
