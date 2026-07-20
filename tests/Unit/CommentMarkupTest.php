<?php

namespace Tests\Unit;

use App\Support\CommentMarkup;
use PHPUnit\Framework\TestCase;

class CommentMarkupTest extends TestCase
{
    public function test_it_renders_supported_markup_tags(): void
    {
        $html = CommentMarkup::toHtml("Halo [b]tebal[/b] [i]miring[/i] [spoiler]rahasia[/spoiler]")->toHtml();

        $this->assertStringContainsString('<strong', $html);
        $this->assertStringContainsString('tebal', $html);
        $this->assertStringContainsString('<em', $html);
        $this->assertStringContainsString('miring', $html);
        $this->assertStringContainsString('<details', $html);
        $this->assertStringContainsString('rahasia', $html);
    }

    public function test_it_escapes_html_outside_supported_markup(): void
    {
        $html = CommentMarkup::toHtml('<script>alert(1)</script> [b]aman[/b]')->toHtml();

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
        $this->assertStringContainsString('<strong', $html);
    }

    public function test_it_renders_supported_emoji_and_sticker_tokens(): void
    {
        $html = CommentMarkup::toHtml('[emoji]smile[/emoji] [sticker]neko_wave[/sticker]')->toHtml();

        $this->assertStringContainsString('😊', $html);
        $this->assertStringContainsString('Neko Wave', $html);
        $this->assertStringContainsString('🐱', $html);
    }
}
