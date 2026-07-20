<?php

namespace Database\Seeders;

use App\Models\Chapter;
use App\Models\Comic;
use App\Support\ComicGenres;
use App\Support\ComicMetadata;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class DemoComicSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = $this->catalog();

        foreach ($catalog as $index => $entry) {
            $title = $entry['title'];
            $slug = Str::slug($title);
            $chapterTotal = (int) ($entry['chapter_total'] ?? rand(4, 8));
            $genres = array_values(array_intersect($entry['genres'], ComicGenres::all()));

            $comic = Comic::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $title,
                    'subtitle' => $entry['subtitle'],
                    'tagline' => $entry['tagline'],
                    'summary' => $entry['summary'],
                    'author' => $entry['author'],
                    'artist' => $entry['artist'],
                    'status' => $entry['status'],
                    'comic_type' => in_array($entry['comic_type'], ComicMetadata::formats(), true) ? $entry['comic_type'] : 'Manhwa',
                    'source_type' => in_array($entry['source_type'], ComicMetadata::sources(), true) ? $entry['source_type'] : 'Project',
                    'schedule' => $entry['schedule'],
                    'year' => $entry['year'],
                    'rating' => $entry['rating'],
                    'readers' => $entry['readers'],
                    'genres' => $genres,
                    'features' => $entry['features'],
                    'cover_url' => $this->coverUrl($slug, $index + 1),
                    'banner_url' => $this->bannerUrl($slug, $index + 1),
                    'is_featured' => (bool) ($entry['is_featured'] ?? false),
                    'sort_order' => $index + 1,
                    'is_recommended' => (bool) ($entry['is_recommended'] ?? false),
                    'recommended_order' => (int) ($entry['recommended_order'] ?? 0),
                    'is_admin_pick' => (bool) ($entry['is_admin_pick'] ?? false),
                    'admin_pick_order' => (int) ($entry['admin_pick_order'] ?? 0),
                ]
            );

            for ($chapterNumber = 1; $chapterNumber <= $chapterTotal; $chapterNumber++) {
                Chapter::query()->updateOrCreate(
                    [
                        'comic_id' => $comic->id,
                        'number' => $chapterNumber,
                    ],
                    [
                        'title' => 'Chapter '.$chapterNumber.': '.$this->chapterTitle($genres, $chapterNumber),
                        'release_label' => now()->subDays(max(0, ($chapterTotal - $chapterNumber) * 3))->translatedFormat('d M Y'),
                        'summary' => $this->chapterSummary($title, $chapterNumber),
                        'pages' => $this->chapterPages($slug, $chapterNumber),
                        'is_published' => true,
                    ]
                );
            }
        }
    }

    private function coverUrl(string $slug, int $seed): string
    {
        return "https://picsum.photos/seed/velmics-cover-{$seed}-{$slug}/420/600";
    }

    private function bannerUrl(string $slug, int $seed): string
    {
        return "https://picsum.photos/seed/velmics-banner-{$seed}-{$slug}/1200/420";
    }

    private function chapterTitle(array $genres, int $chapterNumber): string
    {
        $prefix = Arr::first($genres) ?: 'Opening';

        return match ($chapterNumber % 5) {
            1 => $prefix.' Awakens',
            2 => 'First Contact',
            3 => 'Split Decision',
            4 => 'Echoes of Night',
            default => 'Turning Point',
        };
    }

    private function chapterSummary(string $title, int $chapterNumber): string
    {
        return "{$title} masuk ke babak baru saat chapter {$chapterNumber} membuka konflik yang lebih besar dan taruhannya makin tinggi.";
    }

    private function chapterPages(string $slug, int $chapterNumber): array
    {
        return collect(range(1, 6))->map(function (int $pageNumber) use ($slug, $chapterNumber) {
            return [
                'number' => $pageNumber,
                'caption' => "Panel {$pageNumber} dari chapter {$chapterNumber}.",
                'image' => "https://picsum.photos/seed/velmics-page-{$slug}-{$chapterNumber}-{$pageNumber}/1100/1600",
            ];
        })->all();
    }

    private function catalog(): array
    {
        return [
            [
                'title' => 'Aether Crown',
                'subtitle' => 'A fantasy campaign about heirs and ruins.',
                'tagline' => 'Pewaris kerajaan langit harus memilih takhta atau kebebasan.',
                'summary' => 'Di atas gugusan pulau terapung, seorang pewaris kerajaan diburu dewan istana setelah mahkota kuno memilih dirinya sebagai penguasa baru.',
                'author' => 'Mira Solenne',
                'artist' => 'Juno Vale',
                'status' => 'Ongoing',
                'comic_type' => 'Manhwa',
                'source_type' => 'Project',
                'schedule' => 'Senin malam',
                'year' => '2026',
                'rating' => 4.8,
                'readers' => '12.4k',
                'genres' => ['Fantasy', 'Adventure', 'Drama', 'Webtoon'],
                'features' => ['Political fantasy', 'Found family', 'Slow-burn conflict'],
                'is_featured' => true,
                'is_recommended' => true,
                'recommended_order' => 1,
                'chapter_total' => 8,
            ],
            [
                'title' => 'Neon Exorcist',
                'subtitle' => 'Urban hauntings in a city of static lights.',
                'tagline' => 'Pembasmi roh freelance menghadapi iblis yang hidup di jaringan kota.',
                'summary' => 'Seorang eksorsis muda yang bekerja untuk bayar sewa apartemen terseret ke perang antara kultus data dan roh listrik.',
                'author' => 'Rafi Kisar',
                'artist' => 'Len Arc',
                'status' => 'Ongoing',
                'comic_type' => 'Manga',
                'source_type' => 'Mirror',
                'schedule' => 'Rabu sore',
                'year' => '2025',
                'rating' => 4.6,
                'readers' => '8.9k',
                'genres' => ['Action', 'Supernatural', 'Urban Fantasy', 'Thriller'],
                'features' => ['City noir vibe', 'Fast combat', 'Occult tech'],
                'is_recommended' => true,
                'recommended_order' => 2,
                'chapter_total' => 7,
            ],
            [
                'title' => 'Lotus After War',
                'subtitle' => 'Rebuilding a nation after a broken dynasty.',
                'tagline' => 'Jenderal yang pensiun dipaksa kembali untuk menyelamatkan negeri.',
                'summary' => 'Setelah perang saudara usai, seorang jenderal legendaris harus menghadapi korupsi baru yang lebih berbahaya daripada musuh lama.',
                'author' => 'Han Li',
                'artist' => 'Peony Studio',
                'status' => 'Completed',
                'comic_type' => 'Manhua',
                'source_type' => 'Project',
                'schedule' => 'Tamat',
                'year' => '2024',
                'rating' => 4.7,
                'readers' => '16.8k',
                'genres' => ['Historical', 'Drama', 'Martial Arts', 'Military'],
                'features' => ['Court politics', 'War strategy', 'Mature cast'],
                'is_admin_pick' => true,
                'admin_pick_order' => 1,
                'chapter_total' => 6,
            ],
            [
                'title' => 'Second Bell Homeroom',
                'subtitle' => 'One classroom hides two timelines.',
                'tagline' => 'Seorang murid baru bisa mengulang jam kedua setiap hari.',
                'summary' => 'Siswa pindahan dengan kemampuan mengulang satu jam yang sama berusaha menyelamatkan teman-teman sekelasnya dari tragedi berantai.',
                'author' => 'Ayu Satrine',
                'artist' => 'Noon Paper',
                'status' => 'Ongoing',
                'comic_type' => 'Comic',
                'source_type' => 'Project',
                'schedule' => 'Kamis pagi',
                'year' => '2026',
                'rating' => 4.5,
                'readers' => '5.3k',
                'genres' => ['School Life', 'Mystery', 'Time Travel', 'Psychological'],
                'features' => ['Loop mystery', 'Classroom tension', 'Character secrets'],
                'chapter_total' => 5,
            ],
            [
                'title' => 'Wildheart Pitch',
                'subtitle' => 'Baseball underdogs chasing a televised dream.',
                'tagline' => 'Tim bisbol sekolah pinggiran mengincar turnamen nasional.',
                'summary' => 'Klub bisbol yang hampir dibubarkan mendapat kesempatan terakhir untuk lolos ke kejuaraan nasional dengan pelatih eksentrik.',
                'author' => 'Dion Meraki',
                'artist' => 'Kite Frame',
                'status' => 'Seasonal',
                'comic_type' => 'Manga',
                'source_type' => 'Mirror',
                'schedule' => 'Jumat malam',
                'year' => '2025',
                'rating' => 4.4,
                'readers' => '6.7k',
                'genres' => ['Sports', 'Comedy', 'School Life', 'Shounen'],
                'features' => ['Underdog team', 'Tournament arc', 'Energetic pacing'],
                'chapter_total' => 4,
            ],
            [
                'title' => 'Midnight Clinic',
                'subtitle' => 'A hospital for patients ordinary medicine cannot save.',
                'tagline' => 'Dokter jaga malam merawat pasien dengan penyakit supranatural.',
                'summary' => 'Di rumah sakit rahasia, dokter muda menangani pasien dari dunia gaib sambil menutupi keberadaan klinik itu dari publik.',
                'author' => 'Nara Flint',
                'artist' => 'Sable House',
                'status' => 'Ongoing',
                'comic_type' => 'Manhwa',
                'source_type' => 'Project',
                'schedule' => 'Selasa malam',
                'year' => '2026',
                'rating' => 4.9,
                'readers' => '11.1k',
                'genres' => ['Medical', 'Supernatural', 'Drama', 'Mystery'],
                'features' => ['Monster patients', 'Healing drama', 'Hidden ward'],
                'is_recommended' => true,
                'recommended_order' => 3,
                'chapter_total' => 8,
            ],
            [
                'title' => 'Silver Fang Contract',
                'subtitle' => 'Monsters, debt, and an inconvenient alliance.',
                'tagline' => 'Pemburu monster berutang nyawa pada serigala iblis.',
                'summary' => 'Setelah perburuan gagal, seorang pemburu amatir harus bekerja sama dengan serigala iblis yang dia coba bunuh.',
                'author' => 'Kail Ren',
                'artist' => 'Arca Moon',
                'status' => 'Ongoing',
                'comic_type' => 'Manhwa',
                'source_type' => 'Mirror',
                'schedule' => 'Sabtu malam',
                'year' => '2025',
                'rating' => 4.6,
                'readers' => '9.2k',
                'genres' => ['Action', 'Fantasy', 'Supernatural', 'Thriller'],
                'features' => ['Monster pact', 'Banter duo', 'Dark fantasy'],
                'is_admin_pick' => true,
                'admin_pick_order' => 2,
                'chapter_total' => 7,
            ],
            [
                'title' => 'Glass City Idols',
                'subtitle' => 'A music label built on lies and second chances.',
                'tagline' => 'Dua trainee gagal membentuk duo idol yang tak sengaja viral.',
                'summary' => 'Di kota industri yang hampir bangkrut, dua trainee gagal dipaksa debut bersama dan justru menjadi harapan baru agensi kecil mereka.',
                'author' => 'Celeste N',
                'artist' => 'Mint Loop',
                'status' => 'Ongoing',
                'comic_type' => 'Comic',
                'source_type' => 'Project',
                'schedule' => 'Minggu sore',
                'year' => '2026',
                'rating' => 4.3,
                'readers' => '4.8k',
                'genres' => ['Music', 'Drama', 'Comedy', 'Slice of Life'],
                'features' => ['Music industry', 'Slow growth', 'Warm ensemble'],
                'chapter_total' => 5,
            ],
            [
                'title' => 'Ashes of the Gate',
                'subtitle' => 'Survivors guarding a portal that should have stayed closed.',
                'tagline' => 'Gerbang dimensi yang retak membuat kota hidup dalam status darurat.',
                'summary' => 'Sekelompok penyintas membangun komunitas di sekitar gerbang dimensi sambil melawan makhluk yang bocor ke dunia mereka.',
                'author' => 'Tegar Voss',
                'artist' => 'Rho Studio',
                'status' => 'Hiatus',
                'comic_type' => 'Manhua',
                'source_type' => 'Mirror',
                'schedule' => 'Tidak tentu',
                'year' => '2023',
                'rating' => 4.2,
                'readers' => '7.1k',
                'genres' => ['Sci-Fi', 'Survival', 'Action', 'Thriller'],
                'features' => ['Portal disaster', 'Base defense', 'Suspense'],
                'chapter_total' => 4,
            ],
            [
                'title' => 'Villainess in Quarter Four',
                'subtitle' => 'Corporate warfare meets reincarnation chaos.',
                'tagline' => 'Eksekutif magang sadar dirinya reinkarnasi villainess novel bisnis.',
                'summary' => 'Seorang analis junior terbangun dalam tubuh villainess novel kantor dan mencoba mencegah kebangkrutan perusahaan keluarga.',
                'author' => 'Rina Chao',
                'artist' => 'Velvet Pen',
                'status' => 'Ongoing',
                'comic_type' => 'Manhwa',
                'source_type' => 'Project',
                'schedule' => 'Rabu malam',
                'year' => '2026',
                'rating' => 4.7,
                'readers' => '13.5k',
                'genres' => ['Villainess', 'Romance', 'Comedy', 'Drama'],
                'features' => ['Office romance', 'Reincarnation', 'Sharp dialogue'],
                'is_recommended' => true,
                'recommended_order' => 4,
                'chapter_total' => 6,
            ],
            [
                'title' => 'Crimson Harbor',
                'subtitle' => 'A crime syndicate hidden behind a fishing town.',
                'tagline' => 'Detektif baru menemukan kota pelabuhan dikuasai keluarga kriminal.',
                'summary' => 'Seorang detektif transfer dipaksa bekerja sama dengan informan lokal untuk membongkar sindikat yang menguasai pelabuhan merah.',
                'author' => 'Bram Kelan',
                'artist' => 'Iona Crest',
                'status' => 'Completed',
                'comic_type' => 'Comic',
                'source_type' => 'Project',
                'schedule' => 'Tamat',
                'year' => '2022',
                'rating' => 4.8,
                'readers' => '10.1k',
                'genres' => ['Crime', 'Mystery', 'Thriller', 'Seinen'],
                'features' => ['Detective noir', 'Coastal setting', 'Tight mystery'],
                'is_admin_pick' => true,
                'admin_pick_order' => 3,
                'chapter_total' => 5,
            ],
            [
                'title' => 'Starfall Nursery',
                'subtitle' => 'Children born under falling stars change the world.',
                'tagline' => 'Pengasuh panti luar biasa melindungi anak-anak dengan kekuatan langit.',
                'summary' => 'Di sebuah panti terpencil, para anak yatim dengan kekuatan kosmik diburu organisasi yang ingin mempersenjatai mereka.',
                'author' => 'Mila Arden',
                'artist' => 'Sora Ink',
                'status' => 'Ongoing',
                'comic_type' => 'Manhwa',
                'source_type' => 'Project',
                'schedule' => 'Sabtu pagi',
                'year' => '2026',
                'rating' => 4.9,
                'readers' => '15.7k',
                'genres' => ['Fantasy', 'Drama', 'Slice of Life', 'Shoujo'],
                'features' => ['Caretaker lead', 'Warm cast', 'Magic children'],
                'is_featured' => true,
                'is_admin_pick' => true,
                'admin_pick_order' => 4,
                'chapter_total' => 7,
            ],
        ];
    }
}
