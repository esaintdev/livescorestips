<?php

namespace Tests\Unit;

use Tests\TestCase;

class AssetHelperTest extends TestCase
{
    public function test_asset_v_is_stable_and_versioned(): void
    {
        $first = asset_v('images/icon.png');
        $second = asset_v('images/icon.png');

        $this->assertSame($first, $second, 'Asset version must be stable between calls');
        $this->assertStringContainsString('/images/icon.png?v=', $first);
    }

    public function test_asset_v_handles_missing_file_without_error(): void
    {
        $url = asset_v('css/does-not-exist-xyz.css');

        $this->assertStringContainsString('/css/does-not-exist-xyz.css', $url);
    }

    public function test_meta_description_clamps_long_text_on_word_boundary(): void
    {
        $long = trim(str_repeat('world class football predictions ', 12)); // ~348 chars
        $out = meta_description($long);

        $this->assertLessThanOrEqual(160, mb_strlen($out));
        $this->assertStringNotContainsString('worldclass', $out);
        $this->assertStringEndsNotWith(' ', $out);
    }

    public function test_meta_description_keeps_text_that_already_fits(): void
    {
        $text = 'A concise meta description that fits comfortably within the limit.';

        $this->assertSame($text, meta_description($text));
    }

    public function test_meta_description_strips_html_and_collapses_whitespace(): void
    {
        $this->assertSame('Hello world', meta_description("  <b>Hello</b>\n\n   world  "));
    }
}
