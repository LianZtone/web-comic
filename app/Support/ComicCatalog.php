<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class ComicCatalog
{
    public static function all(): Collection
    {
        return collect(self::records())
            ->map(fn (array $comic) => self::hydrateComic($comic))
            ->values();
    }

    public static function find(?string $slug): ?array
    {
        return self::all()->firstWhere('slug', $slug);
    }

    public static function findOrFail(string $slug): array
    {
        return self::find($slug) ?? abort(404);
    }

    public static function findChapterOrFail(string $slug, int $chapterNumber): array
    {
        $comic = self::findOrFail($slug);

        $chapter = collect($comic['chapters'])->firstWhere('number', $chapterNumber);

        if (! $chapter) {
            abort(404);
        }

        return [
            'comic' => $comic,
            'chapter' => $chapter,
        ];
    }

    private static function hydrateComic(array $comic): array
    {
        $comic['cover'] = self::posterSvg($comic);
        $comic['banner'] = self::bannerSvg($comic);
        $comic['genre_line'] = implode(' / ', $comic['genres']);
        $comic['comic_type'] = (string) ($comic['comic_type'] ?? 'Manhwa');
        $comic['source_type'] = (string) ($comic['source_type'] ?? 'Project');
        $comic['is_featured'] = (bool) ($comic['is_featured'] ?? false);
        $comic['sort_order'] = (int) ($comic['sort_order'] ?? 0);
        $comic['is_recommended'] = (bool) ($comic['is_recommended'] ?? false);
        $comic['recommended_order'] = (int) ($comic['recommended_order'] ?? 0);
        $comic['is_admin_pick'] = (bool) ($comic['is_admin_pick'] ?? false);
        $comic['admin_pick_order'] = (int) ($comic['admin_pick_order'] ?? 0);
        $comic['views_count'] = self::parseCompactNumber((string) ($comic['readers'] ?? '0'));
        $comic['views_label'] = self::compactNumber($comic['views_count']);
        $comic['bookmarks_count'] = max(0, (int) round($comic['views_count'] * 0.08));
        $comic['bookmarks_label'] = self::compactNumber($comic['bookmarks_count']);
        $comic['rating_average'] = max(0, min(5, round((float) ($comic['rating'] ?? 4.8), 1)));
        $comic['rating_count'] = max(0, (int) round($comic['views_count'] * 0.06));
        $comic['rating_count_label'] = self::compactNumber($comic['rating_count']);
        $comic['rating'] = number_format($comic['rating_average'], 1);
        $comic['next_release_time'] = self::nextReleaseTime($comic['schedule'] ?? null);

        $comic['chapters'] = collect($comic['chapters'])
            ->values()
            ->map(function (array $chapter, int $index) use ($comic) {
                $chapter['number'] = (int) $chapter['number'];
                $chapter['label'] = 'Chapter '.str_pad((string) $chapter['number'], 2, '0', STR_PAD_LEFT);
                $chapter['page_count'] = count($chapter['pages']);
                $chapter['reading_time'] = max(4, (int) ceil($chapter['page_count'] * 0.8)).' min read';
                $chapter['release_label'] = self::humanizeReleaseLabel($chapter['release'] ?? null);
                $chapter['preview'] = self::chapterPreviewSvg($comic, $chapter);
                $chapter['summary'] = $chapter['summary'] ?? $chapter['pages'][0];
                $chapter['pages'] = collect($chapter['pages'])
                    ->values()
                    ->map(function (string $caption, int $pageIndex) use ($comic, $chapter) {
                        return [
                            'number' => $pageIndex + 1,
                            'caption' => $caption,
                            'image' => self::readerPageSvg($comic, $chapter, $pageIndex + 1, $caption),
                        ];
                    })
                    ->all();
                $chapter['is_latest'] = $index === count($comic['chapters']) - 1;

                return $chapter;
            })
            ->all();

        $comic['chapter_total'] = count($comic['chapters']);
        $comic['page_total'] = collect($comic['chapters'])->sum('page_count');
        $comic['latest_chapter'] = collect($comic['chapters'])->last();
        $comic['first_chapter'] = collect($comic['chapters'])->first();

        return $comic;
    }

    private static function humanizeReleaseLabel(?string $release): string
    {
        if (! is_string($release) || trim($release) === '') {
            return 'Baru saja';
        }

        try {
            return Carbon::parse($release)->locale(app()->getLocale())->diffForHumans();
        } catch (\Throwable) {
            return $release;
        }
    }

    private static function compactNumber(int $value): string
    {
        if ($value >= 1000000) {
            return rtrim(rtrim(number_format($value / 1000000, 1), '0'), '.').'M';
        }

        if ($value >= 1000) {
            return rtrim(rtrim(number_format($value / 1000, 1), '0'), '.').'k';
        }

        return (string) $value;
    }

    private static function parseCompactNumber(string $value): int
    {
        $normalized = strtolower(trim($value));

        if ($normalized === '') {
            return 0;
        }

        if (preg_match('/^([\d.]+)\s*([km]?)$/', $normalized, $matches) !== 1) {
            return (int) preg_replace('/\D+/', '', $normalized);
        }

        $number = (float) $matches[1];
        $suffix = $matches[2] ?? '';

        return match ($suffix) {
            'm' => (int) round($number * 1000000),
            'k' => (int) round($number * 1000),
            default => (int) round($number),
        };
    }

    private static function nextReleaseTime(?string $schedule): ?string
    {
        $normalized = strtolower(trim((string) $schedule));

        if ($normalized === '' || in_array($normalized, ['tba', 'tamat', 'arsip lengkap', 'tidak tentu'], true)) {
            return null;
        }

        $dayMap = [
            'senin' => Carbon::MONDAY,
            'selasa' => Carbon::TUESDAY,
            'rabu' => Carbon::WEDNESDAY,
            'kamis' => Carbon::THURSDAY,
            'jumat' => Carbon::FRIDAY,
            'sabtu' => Carbon::SATURDAY,
            'minggu' => Carbon::SUNDAY,
        ];

        $hour = match (true) {
            str_contains($normalized, 'pagi') => 9,
            str_contains($normalized, 'siang') => 13,
            str_contains($normalized, 'sore') => 16,
            str_contains($normalized, 'malam') => 20,
            default => 19,
        };

        $targetDay = collect($dayMap)->first(fn (int $value, string $label) => str_contains($normalized, $label));

        if ($targetDay === null) {
            return null;
        }

        $next = now()->copy()->startOfMinute();

        while ((int) $next->dayOfWeek !== $targetDay) {
            $next->addDay();
        }

        $next->setTime($hour, 0, 0);

        if ($next->isPast()) {
            $next->addWeek();
        }

        return $next->toIso8601String();
    }

    private static function records(): array
    {
        return [
            [
                'slug' => 'afterglow-protocol',
                'title' => 'Afterglow Protocol',
                'subtitle' => 'Mystery / Sci-Fi',
                'tagline' => 'Sebuah kota mati menyimpan siaran radio yang menjawab masa depan sebelum ia terjadi.',
                'summary' => 'Nara, arsiparis malam, menemukan gelombang radio yang menyiarkan laporan kejadian beberapa jam sebelum insiden itu benar-benar terjadi. Saat siaran mulai menyebut namanya sendiri, ia harus memilih antara mengungkap jaringan observatorium tua atau menjaga kota dari kepanikan massal.',
                'author' => 'R. Mahendra',
                'artist' => 'Luna P.',
                'status' => 'Ongoing',
                'schedule' => 'Jumat malam',
                'year' => '2026',
                'rating' => '4.9',
                'readers' => '12.4k',
                'genres' => ['Sci-Fi', 'Mystery', 'Thriller'],
                'features' => [
                    'Vertical reader yang fokus ke ritme panel sunyi dan close-up.',
                    'Metadata chapter siap dipakai untuk progress, bookmark, dan continue reading.',
                    'Arah visual neon-rust dengan suasana arsip kota industrial.',
                ],
                'palette' => [
                    'start' => '#ffb36b',
                    'end' => '#7b2d26',
                    'accent' => '#ffe3b8',
                    'ink' => '#1f1010',
                ],
                'chapters' => [
                    [
                        'number' => 1,
                        'title' => 'Dead Air at Platform Nine',
                        'release' => '14 Mar 2026',
                        'summary' => 'Siaran pertama muncul saat stasiun tua dibuka kembali untuk audit malam.',
                        'pages' => [
                            'Nara tiba di platform sembilan saat hujan menempel seperti jelaga tipis.',
                            'Pengeras suara retak dan menyebut kecelakaan yang belum terjadi.',
                            'Ia menulis tiap detail siaran di kartu indeks yang sudah pudar.',
                            'Lampu sinyal berubah merah satu per satu tanpa ada kereta melintas.',
                            'Di ujung peron, sebuah radio tabung menyala sendiri di balik pagar besi.',
                        ],
                    ],
                    [
                        'number' => 2,
                        'title' => 'Index of Missing Voices',
                        'release' => '17 Mar 2026',
                        'summary' => 'Daftar suara hilang membawa Nara ke ruang arsip bawah tanah.',
                        'pages' => [
                            'Rak-rak baja di ruang arsip menyimpan pita tanpa label dan jam rusak.',
                            'Setiap pita memutar potongan kabar yang cocok dengan catatan siaran kemarin.',
                            'Nama ayah Nara muncul pada map observatorium yang telah disegel dua puluh tahun.',
                            'Seorang teknisi listrik memperingatkan bahwa frekuensi itu hanya aktif setelah tengah malam.',
                            'Pada pita terakhir, Nara mendengar dirinya sendiri meminta untuk tidak membuka pintu timur.',
                        ],
                    ],
                    [
                        'number' => 3,
                        'title' => 'The Door in Static',
                        'release' => '20 Mar 2026',
                        'summary' => 'Pintu timur terbuka dan seluruh kota seperti menahan napas.',
                        'pages' => [
                            'Siaran malam itu mengulang satu koordinat dengan nada darurat.',
                            'Nara menelusuri lorong servis sambil membawa radio tabung yang semakin panas.',
                            'Di pintu timur, cat dinding mengelupas membentuk peta jalur kereta lama.',
                            'Begitu gerendel dibuka, statis radio berubah menjadi suara kerumunan yang panik.',
                            'Panel terakhir menampilkan observatorium menyala kembali untuk pertama kali sejak kebakaran.',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'salt-and-steel',
                'title' => 'Salt & Steel',
                'subtitle' => 'Action / Fantasy',
                'tagline' => 'Pelabuhan gurun berputar oleh mesin garam raksasa, dan tiap perjanjian dibayar dengan besi.',
                'summary' => 'Mika, kurir pelabuhan yang tumbuh di antara derek tua dan mesin desalinasi, terjebak dalam perang dagang antar guild ketika kompas warisan ibunya mulai menunjukkan jalur menuju laut yang telah lama mengering.',
                'author' => 'Ariesta K.',
                'artist' => 'Fikri D.',
                'status' => 'Seasonal',
                'schedule' => 'Selasa sore',
                'year' => '2026',
                'rating' => '4.8',
                'readers' => '9.1k',
                'genres' => ['Action', 'Fantasy', 'Adventure'],
                'features' => [
                    'Hero section yang menonjolkan worldbuilding dan CTA start reading.',
                    'Kartu chapter siap dipakai untuk monetisasi early access.',
                    'Palet pasir, karat, dan kaca laut untuk mood petualangan steampunk.',
                ],
                'palette' => [
                    'start' => '#f0cc7c',
                    'end' => '#8a4d2c',
                    'accent' => '#fff0c3',
                    'ink' => '#24130d',
                ],
                'chapters' => [
                    [
                        'number' => 1,
                        'title' => 'Port of Dry Waves',
                        'release' => '11 Mar 2026',
                        'summary' => 'Mika menerima paket ilegal tepat saat pasar lelang guild dibuka.',
                        'pages' => [
                            'Pelabuhan gurun berderak ketika roda garam raksasa mulai berputar saat fajar.',
                            'Mika melompati peti kargo sambil membawa kompas tua yang tidak berhenti berdetak.',
                            'Lelang guild berubah ricuh saat simbol keluarga ibunya muncul di salah satu kontainer.',
                            'Seorang kapten pemburu utang menawarkan perlindungan dengan harga yang terlalu murah.',
                            'Kompas menembakkan garis cahaya ke arah dasar laut yang telah menjadi padang garam.',
                        ],
                    ],
                    [
                        'number' => 2,
                        'title' => 'Guild Market Duel',
                        'release' => '15 Mar 2026',
                        'summary' => 'Persaingan antar guild memaksa Mika bertarung di pasar gantung.',
                        'pages' => [
                            'Bendera guild menutup langit ketika pasar gantung dipenuhi penonton taruhan.',
                            'Mika menggunakan rantai kait pelabuhan sebagai senjata improvisasi.',
                            'Pedagang topeng memperlihatkan peta jalur air bawah tanah sebagai imbalan kemenangan.',
                            'Benturan baja dan kristal garam menyulut ledakan cahaya di tengah arena.',
                            'Di akhir duel, lawan Mika berbisik bahwa ibunya masih hidup di luar tepi peta.',
                        ],
                    ],
                    [
                        'number' => 3,
                        'title' => 'Compass to the Hollow Sea',
                        'release' => '19 Mar 2026',
                        'summary' => 'Ekspedisi kecil berangkat ke bekas lautan yang menyimpan mesin kuno.',
                        'pages' => [
                            'Karavan berangkat malam hari untuk menghindari badai kaca.',
                            'Jejak kompas membelah padang garam seperti urat cahaya biru.',
                            'Mika menemukan menara suar yang setengah tenggelam di pasir asin.',
                            'Mesin inti di bawah menara ternyata masih memompa air ke kota secara diam-diam.',
                            'Panel terakhir memperlihatkan siluet kapal raksasa terkubur di cakrawala.',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'ember-letter',
                'title' => 'Ember Letter',
                'subtitle' => 'Drama / Romance',
                'tagline' => 'Surat yang terbakar setengah jalan selalu tiba pada orang yang salah.',
                'summary' => 'Di kota bukit yang hidup dari kantor pos tua, Sava bertugas menyortir surat-surat terbakar dari kebakaran besar sepuluh tahun lalu. Saat sebuah amplop hangus masih menyimpan detak jantung kecil, ia dan pelukis mural bernama Elan mulai memburu kisah cinta yang tidak pernah selesai.',
                'author' => 'Mira Sapta',
                'artist' => 'Nadia T.',
                'status' => 'Ongoing',
                'schedule' => 'Minggu pagi',
                'year' => '2026',
                'rating' => '4.7',
                'readers' => '7.8k',
                'genres' => ['Drama', 'Romance', 'Slice of Life'],
                'features' => [
                    'Tone lembut untuk halaman detail, cocok menonjolkan sinopsis dan author note.',
                    'Layout chapter list memudahkan pembaca lompat ke arc tertentu.',
                    'Kartu related titles siap membantu discovery komik sejenis.',
                ],
                'palette' => [
                    'start' => '#ffc1ad',
                    'end' => '#8f3b57',
                    'accent' => '#ffe9df',
                    'ink' => '#2c151d',
                ],
                'chapters' => [
                    [
                        'number' => 1,
                        'title' => 'Ashen Envelope',
                        'release' => '09 Mar 2026',
                        'summary' => 'Amplop hangus pertama kali memanggil nama Sava dengan suara bergetar.',
                        'pages' => [
                            'Sava menyusun surat terbakar di ruangan berbau teh hitam dan abu.',
                            'Satu amplop memantulkan cahaya merah kecil dari balik sobekan segelnya.',
                            'Elan datang untuk meminta izin melukis dinding kantor pos yang retak.',
                            'Saat amplop dibuka, potongan kalimat cinta itu menyebut mural berwarna biru.',
                            'Sava dan Elan memutuskan mencari alamat tujuan sebelum huruf-hurufnya menghilang.',
                        ],
                    ],
                    [
                        'number' => 2,
                        'title' => 'Mural at Daybreak',
                        'release' => '13 Mar 2026',
                        'summary' => 'Petunjuk di tembok kota membawa mereka pada nama yang sudah lama dihapus.',
                        'pages' => [
                            'Mural biru di tanjakan timur menyembunyikan lapisan cat yang lebih tua.',
                            'Setiap goresan kuas Elan membuka pecahan nama pasangan dari masa lalu.',
                            'Seorang tukang roti mengingat malam kebakaran dan surat yang tak pernah terkirim.',
                            'Sava menemukan cap pos tahun terakhir sebelum kantor itu ditutup.',
                            'Pada senja hari, amplop mulai hangat dan memaksa mereka turun ke distrik lama.',
                        ],
                    ],
                    [
                        'number' => 3,
                        'title' => 'Postmark in the Rain',
                        'release' => '18 Mar 2026',
                        'summary' => 'Hujan pertama musim ini menghidupkan tinta yang sempat lenyap.',
                        'pages' => [
                            'Hujan membuat jalan batu memantulkan warna mural seperti kaca.',
                            'Tinta pada amplop hidup kembali dan menuliskan jam pertemuan yang terlewat.',
                            'Sava menyadari kisah surat itu paralel dengan ketakutannya sendiri akan kehilangan.',
                            'Elan mengaku ia sengaja menunda pekerjaannya agar bisa terus menemani Sava.',
                            'Mereka berdiri di depan rumah kosong saat lonceng pos berbunyi sendiri dari dalam.',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'hollow-choir',
                'title' => 'Hollow Choir',
                'subtitle' => 'Horror / Supernatural',
                'tagline' => 'Suara paduan suara itu terdengar indah sampai salah satu nada memanggil namamu.',
                'summary' => 'Sekolah biara St. Agatha membuka kembali kelas malam untuk paduan suara elit. Niken, murid pindahan yang tidak bisa membaca not, justru menjadi satu-satunya orang yang mendengar nada ke-13 yang tersembunyi di setiap latihan.',
                'author' => 'Dion Prasetya',
                'artist' => 'S. Kejora',
                'status' => 'Ongoing',
                'schedule' => 'Kamis malam',
                'year' => '2026',
                'rating' => '4.9',
                'readers' => '11.2k',
                'genres' => ['Horror', 'Supernatural', 'School'],
                'features' => [
                    'Reader page cocok untuk long-panel horor dengan ritme lambat.',
                    'Layout gelap membantu fokus ke cover, warning, dan daftar chapter.',
                    'Komponen badge bisa dipakai untuk rating usia dan content notice.',
                ],
                'palette' => [
                    'start' => '#93a0c9',
                    'end' => '#21192f',
                    'accent' => '#e1e5ff',
                    'ink' => '#100c17',
                ],
                'chapters' => [
                    [
                        'number' => 1,
                        'title' => 'The Thirteenth Note',
                        'release' => '08 Mar 2026',
                        'summary' => 'Nada tambahan pertama kali terdengar saat latihan berlangsung tanpa listrik.',
                        'pages' => [
                            'Kapel sekolah tenggelam dalam gelap ketika generator mati tepat sebelum latihan.',
                            'Niken mendengar satu nada lebih tinggi dari semua suara lain di ruangan.',
                            'Tidak ada orang lain yang mengaku mendengarnya, bahkan konduktor yang paling dekat.',
                            'Buku himne terbuka sendiri pada halaman yang sobek dan bernoda air.',
                            'Di kaca patri, refleksi paduan suara tampak berjumlah satu orang lebih banyak.',
                        ],
                    ],
                    [
                        'number' => 2,
                        'title' => 'Dormitory Silence',
                        'release' => '12 Mar 2026',
                        'summary' => 'Asrama perempuan menjadi terlalu sunyi setelah lampu padam kedua kali.',
                        'pages' => [
                            'Lorong asrama panjang itu memantulkan langkah kaki seperti ruang kosong.',
                            'Teman sekamar Niken tidur sambil bersenandung nada yang sama dari kapel.',
                            'Sebuah pintu kamar terkunci dari dalam padahal penghuninya sedang latihan.',
                            'Niken menemukan lembar absensi lama dengan namanya tertulis pada tahun yang mustahil.',
                            'Ketika lonceng tengah malam berbunyi, seluruh jendela asrama terbuka bersamaan.',
                        ],
                    ],
                    [
                        'number' => 3,
                        'title' => 'Choir of No Faces',
                        'release' => '16 Mar 2026',
                        'summary' => 'Latihan rahasia di ruang bawah kapel membuka asal nada ke-13.',
                        'pages' => [
                            'Konduktor membawa Niken ke ruang bawah kapel melalui tangga spiral sempit.',
                            'Dinding ruang latihan dipenuhi potret murid tanpa mata dan tanpa nama.',
                            'Nada ke-13 ternyata berasal dari organ pipa yang terkubur di balik altar.',
                            'Saat organ dimainkan, seluruh potret membuka mulut seperti ikut bernyanyi.',
                            'Panel penutup menunjukkan not balok membentuk wajah Niken di partitur lama.',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'northwind-atlas',
                'title' => 'Northwind Atlas',
                'subtitle' => 'Adventure / Coming of Age',
                'tagline' => 'Peta ini tidak menunjukkan tempat, ia menunjukkan orang yang belum siap pulang.',
                'summary' => 'Raka, kurir udara remaja, mendapatkan atlas yang menambahkan pulau baru setiap kali seseorang memilih pergi. Bersama navigator amatir bernama Tia, ia menyeberangi kota terapung untuk memetakan rute pulang yang mungkin tidak pernah ada.',
                'author' => 'Nino Hadika',
                'artist' => 'Alya M.',
                'status' => 'Completed',
                'schedule' => 'Arsip lengkap',
                'year' => '2025',
                'rating' => '4.6',
                'readers' => '6.3k',
                'genres' => ['Adventure', 'Fantasy', 'Coming of Age'],
                'features' => [
                    'Cocok sebagai kategori completed dan binge-read di homepage.',
                    'Cover dan hero memakai komposisi angin, kabut, dan kartografi.',
                    'CTA reader memudahkan pengguna langsung lanjut ke chapter berikutnya.',
                ],
                'palette' => [
                    'start' => '#9fd6d3',
                    'end' => '#31587a',
                    'accent' => '#e1fbff',
                    'ink' => '#102032',
                ],
                'chapters' => [
                    [
                        'number' => 1,
                        'title' => 'Map of Departures',
                        'release' => '01 Nov 2025',
                        'summary' => 'Atlas itu muncul saat pesawat pos Raka jatuh di pulau kabut.',
                        'pages' => [
                            'Pesawat pos kecil Raka jatuh mendarat di padang bunga kabut tanpa penghuni.',
                            'Di balik bangkai peti surat, ia menemukan atlas biru dengan halaman yang terus bergerak.',
                            'Tia mengenali simbol-simbol angin yang dipakai pelaut langit generasi lama.',
                            'Saat seorang penumpang memutuskan pergi dari kampung halamannya, satu pulau baru muncul di atlas.',
                            'Mereka bersepakat memakai atlas itu untuk mengantar surat ke tempat-tempat yang tidak ada di peta resmi.',
                        ],
                    ],
                    [
                        'number' => 2,
                        'title' => 'Cloudbridge Run',
                        'release' => '08 Nov 2025',
                        'summary' => 'Rute jembatan awan menguji apakah mereka benar-benar siap pergi jauh.',
                        'pages' => [
                            'Jembatan awan hanya terbentuk saat matahari berada tepat di balik menara jam.',
                            'Raka harus menerbangkan glider surat menembus arus angin vertikal.',
                            'Tia membaca atlas yang terus menambah catatan perjalanan mereka sendiri.',
                            'Di tengah badai, mereka menerima surat tanpa alamat yang ditujukan untuk rumah.',
                            'Sesaat sebelum mendarat, atlas menghapus satu pulau lama dari tepi halaman.',
                        ],
                    ],
                    [
                        'number' => 3,
                        'title' => 'Home Written in Fog',
                        'release' => '15 Nov 2025',
                        'summary' => 'Pulau terakhir bukan tujuan, melainkan keberanian untuk kembali.',
                        'pages' => [
                            'Pulau terakhir di atlas hanya muncul setelah Raka menuliskan ketakutannya sendiri.',
                            'Mereka menemukan mercusuar langit yang dijaga oleh pengantar surat generasi pertama.',
                            'Sang penjaga mengungkap atlas itu tidak menuntun orang pulang, melainkan membuat orang mengerti arti pulang.',
                            'Raka memutuskan mengirim surat untuk dirinya di masa lalu sebagai penutup rute.',
                            'Bab penutup menunjukkan atlas kosong kembali, siap menunggu penjelajah baru.',
                        ],
                    ],
                ],
            ],
        ];
    }

    private static function posterSvg(array $comic): string
    {
        $title = self::escape($comic['title']);
        $subtitle = self::escape($comic['subtitle']);
        $tagline = self::escape($comic['tagline']);
        $start = $comic['palette']['start'];
        $end = $comic['palette']['end'];
        $accent = $comic['palette']['accent'];
        $ink = $comic['palette']['ink'];

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 820 1160" role="img" aria-label="{$title}">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="{$start}" />
      <stop offset="100%" stop-color="{$end}" />
    </linearGradient>
    <radialGradient id="glow" cx="30%" cy="20%" r="80%">
      <stop offset="0%" stop-color="{$accent}" stop-opacity="0.9" />
      <stop offset="100%" stop-color="{$accent}" stop-opacity="0" />
    </radialGradient>
  </defs>
  <rect width="820" height="1160" rx="48" fill="url(#bg)" />
  <rect x="36" y="36" width="748" height="1088" rx="32" fill="none" stroke="{$accent}" stroke-opacity="0.45" />
  <circle cx="210" cy="190" r="220" fill="url(#glow)" />
  <path d="M100 870 C250 730, 500 970, 720 760" stroke="{$accent}" stroke-opacity="0.5" stroke-width="10" fill="none" />
  <path d="M90 910 C240 770, 470 1010, 730 810" stroke="{$accent}" stroke-opacity="0.25" stroke-width="4" fill="none" />
  <text x="78" y="124" fill="{$ink}" fill-opacity="0.7" font-family="Sora, Arial, sans-serif" font-size="30" letter-spacing="7">SCRIPTORIA MVP</text>
  <text x="78" y="700" fill="{$ink}" font-family="Newsreader, Georgia, serif" font-size="108" font-weight="700">{$title}</text>
  <text x="82" y="760" fill="{$ink}" fill-opacity="0.82" font-family="Sora, Arial, sans-serif" font-size="28" letter-spacing="4">{$subtitle}</text>
  <foreignObject x="78" y="816" width="650" height="200">
    <div xmlns="http://www.w3.org/1999/xhtml" style="font-family:Sora, Arial, sans-serif;font-size:30px;line-height:1.45;color:{$ink};opacity:0.86;">
      {$tagline}
    </div>
  </foreignObject>
</svg>
SVG;

        return self::svgDataUri($svg);
    }

    private static function bannerSvg(array $comic): string
    {
        $title = self::escape($comic['title']);
        $start = $comic['palette']['start'];
        $end = $comic['palette']['end'];
        $accent = $comic['palette']['accent'];
        $ink = $comic['palette']['ink'];

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1600 900" role="img" aria-label="{$title}">
  <defs>
    <linearGradient id="hero" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="{$start}" />
      <stop offset="100%" stop-color="{$end}" />
    </linearGradient>
    <radialGradient id="mist" cx="30%" cy="15%" r="70%">
      <stop offset="0%" stop-color="{$accent}" stop-opacity="0.9" />
      <stop offset="100%" stop-color="{$accent}" stop-opacity="0" />
    </radialGradient>
  </defs>
  <rect width="1600" height="900" fill="url(#hero)" rx="44" />
  <circle cx="380" cy="180" r="300" fill="url(#mist)" />
  <path d="M0 620 C260 520, 480 720, 760 630 S1260 500, 1600 650 V900 H0 Z" fill="{$ink}" fill-opacity="0.16" />
  <path d="M0 700 C260 600, 550 810, 860 700 S1300 590, 1600 760" stroke="{$accent}" stroke-opacity="0.55" stroke-width="6" fill="none" />
  <text x="110" y="150" fill="{$ink}" fill-opacity="0.72" font-family="Sora, Arial, sans-serif" font-size="38" letter-spacing="10">FEATURED STORY</text>
  <text x="110" y="510" fill="{$ink}" font-family="Newsreader, Georgia, serif" font-size="170" font-weight="700">{$title}</text>
</svg>
SVG;

        return self::svgDataUri($svg);
    }

    private static function chapterPreviewSvg(array $comic, array $chapter): string
    {
        $title = self::escape($comic['title']);
        $chapterTitle = self::escape($chapter['title']);
        $start = $comic['palette']['start'];
        $accent = $comic['palette']['accent'];
        $ink = $comic['palette']['ink'];

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 960 540" role="img" aria-label="{$chapterTitle}">
  <rect width="960" height="540" fill="{$ink}" rx="28" />
  <rect x="28" y="28" width="904" height="484" rx="22" fill="{$start}" fill-opacity="0.3" stroke="{$accent}" stroke-opacity="0.4" />
  <circle cx="760" cy="140" r="124" fill="{$accent}" fill-opacity="0.36" />
  <path d="M80 410 C190 330, 290 440, 410 370 S650 260, 860 360" stroke="{$accent}" stroke-width="8" stroke-opacity="0.44" fill="none" />
  <text x="72" y="120" fill="{$accent}" font-family="Sora, Arial, sans-serif" font-size="26" letter-spacing="6">{$title}</text>
  <text x="72" y="274" fill="white" font-family="Newsreader, Georgia, serif" font-size="72" font-weight="700">{$chapterTitle}</text>
  <text x="72" y="338" fill="white" fill-opacity="0.78" font-family="Sora, Arial, sans-serif" font-size="24">{$chapter['label']} • {$chapter['release']}</text>
</svg>
SVG;

        return self::svgDataUri($svg);
    }

    private static function readerPageSvg(array $comic, array $chapter, int $pageNumber, string $caption): string
    {
        $title = self::escape($comic['title']);
        $chapterTitle = self::escape($chapter['title']);
        $caption = self::escape($caption);
        $start = $comic['palette']['start'];
        $end = $comic['palette']['end'];
        $accent = $comic['palette']['accent'];
        $ink = $comic['palette']['ink'];

        $offsetA = 120 + ($pageNumber * 17);
        $offsetB = 620 - ($pageNumber * 11);
        $offsetC = 210 + ($pageNumber * 13);

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 1680" role="img" aria-label="{$chapterTitle} page {$pageNumber}">
  <defs>
    <linearGradient id="page" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="{$start}" />
      <stop offset="100%" stop-color="{$end}" />
    </linearGradient>
  </defs>
  <rect width="1200" height="1680" rx="28" fill="url(#page)" />
  <rect x="42" y="42" width="1116" height="1596" rx="24" fill="{$ink}" fill-opacity="0.16" stroke="{$accent}" stroke-opacity="0.38" />
  <circle cx="{$offsetA}" cy="280" r="180" fill="{$accent}" fill-opacity="0.32" />
  <circle cx="950" cy="{$offsetC}" r="220" fill="{$ink}" fill-opacity="0.18" />
  <path d="M110 980 C270 830, 420 1040, 590 930 S870 760, 1080 940" stroke="{$accent}" stroke-width="12" stroke-opacity="0.54" fill="none" />
  <path d="M120 1130 C250 1040, 470 1210, 660 1100 S960 960, 1080 1130" stroke="white" stroke-width="4" stroke-opacity="0.34" fill="none" />
  <rect x="88" y="122" width="280" height="42" rx="21" fill="{$ink}" fill-opacity="0.24" />
  <text x="104" y="151" fill="{$ink}" fill-opacity="0.72" font-family="Sora, Arial, sans-serif" font-size="24" letter-spacing="4">PAGE {$pageNumber}</text>
  <text x="88" y="250" fill="{$ink}" font-family="Newsreader, Georgia, serif" font-size="84" font-weight="700">{$title}</text>
  <text x="88" y="316" fill="{$ink}" fill-opacity="0.82" font-family="Sora, Arial, sans-serif" font-size="28" letter-spacing="4">{$chapterTitle}</text>
  <foreignObject x="88" y="1190" width="960" height="250">
    <div xmlns="http://www.w3.org/1999/xhtml" style="font-family:Sora, Arial, sans-serif;font-size:36px;line-height:1.45;color:{$ink};">
      {$caption}
    </div>
  </foreignObject>
  <rect x="88" y="1470" width="220" height="2" fill="{$ink}" fill-opacity="0.42" />
  <text x="88" y="1520" fill="{$ink}" fill-opacity="0.76" font-family="Sora, Arial, sans-serif" font-size="22">Velmics MVP Reader</text>
  <text x="980" y="1520" fill="{$ink}" fill-opacity="0.76" font-family="Sora, Arial, sans-serif" font-size="22">{$pageNumber}</text>
</svg>
SVG;

        return self::svgDataUri($svg);
    }

    private static function svgDataUri(string $svg): string
    {
        return 'data:image/svg+xml;charset=UTF-8,'.rawurlencode($svg);
    }

    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1);
    }
}
