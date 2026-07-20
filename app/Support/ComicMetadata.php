<?php

namespace App\Support;

class ComicMetadata
{
    public static function formats(): array
    {
        return ['Manga', 'Manhwa', 'Manhua', 'Comic'];
    }

    public static function sources(): array
    {
        return ['Project', 'Mirror'];
    }
}
