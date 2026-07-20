<?php

namespace App\Support;

class CommentDecorations
{
    /**
     * @return array<string, array{char: string, label: string}>
     */
    public static function emojis(): array
    {
        return [
            'smile' => ['char' => '😊', 'label' => 'Smile'],
            'laugh' => ['char' => '😂', 'label' => 'Laugh'],
            'love' => ['char' => '😍', 'label' => 'Love'],
            'shock' => ['char' => '😱', 'label' => 'Shock'],
            'cry' => ['char' => '😭', 'label' => 'Cry'],
            'fire' => ['char' => '🔥', 'label' => 'Fire'],
        ];
    }

    /**
     * @return array<string, array{emoji: string, label: string, tone: string}>
     */
    public static function stickers(): array
    {
        return [
            'neko_wave' => ['emoji' => '🐱', 'label' => 'Neko Wave', 'tone' => 'warning'],
            'hype_blast' => ['emoji' => '💥', 'label' => 'Hype Blast', 'tone' => 'error'],
            'happy_blob' => ['emoji' => '🫶', 'label' => 'Happy Blob', 'tone' => 'success'],
            'sleep_mode' => ['emoji' => '😴', 'label' => 'Sleep Mode', 'tone' => 'info'],
        ];
    }
}
