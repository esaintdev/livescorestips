<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatedCanonicalTest extends TestCase
{
    use RefreshDatabase;

    private const HOST = 'http://www.livescorestips.test';

    protected function setUp(): void
    {
        parent::setUp();
        $this->fakeApi();
    }

    private function today(): string
    {
        return now()->format('Y-m-d');
    }

    public function test_root_homepage_canonicalises_to_root(): void
    {
        $this->get(self::HOST.'/')
            ->assertStatus(200)
            ->assertSee('<link rel="canonical" href="'.self::HOST.'">', false);
    }

    public function test_home_today_canonicalises_to_root(): void
    {
        $this->get(self::HOST.'/home/'.$this->today())
            ->assertStatus(200)
            ->assertSee('<link rel="canonical" href="'.self::HOST.'">', false);
    }

    public function test_home_past_date_canonicalises_to_root(): void
    {
        $past = now()->subDays(3)->format('Y-m-d');

        $this->get(self::HOST.'/home/'.$past)
            ->assertStatus(200)
            ->assertSee('<link rel="canonical" href="'.self::HOST.'">', false);
    }

    public function test_category_date_canonicalises_to_base_category(): void
    {
        $this->get(self::HOST.'/double-chance/'.$this->today())
            ->assertStatus(200)
            ->assertSee('<link rel="canonical" href="'.self::HOST.'/double-chance">', false);
    }

    public function test_predictions_all_date_canonicalises_to_base_listing(): void
    {
        $this->get(self::HOST.'/predictions/all/'.$this->today())
            ->assertStatus(200)
            ->assertSee('<link rel="canonical" href="'.self::HOST.'/predictions/all">', false);
    }

    public function test_banker_date_canonicalises_to_base(): void
    {
        $this->get(self::HOST.'/banker-tips/'.$this->today())
            ->assertStatus(200)
            ->assertSee('<link rel="canonical" href="'.self::HOST.'/banker-tips">', false);
    }

    public function test_dated_navigation_links_are_nofollow(): void
    {
        $response = $this->get(self::HOST.'/');

        // The prev/next date arrows point to noindex archive URLs and must not
        // be followed, so Googlebot does not spend crawl budget on them.
        $response->assertStatus(200);

        $prev = route('home.date', ['date' => now()->subDay()->format('Y-m-d')]);
        $next = route('home.date', ['date' => now()->addDay()->format('Y-m-d')]);

        $response->assertSee('href="'.$prev.'" rel="nofollow"', false);
        $response->assertSee('href="'.$next.'" rel="nofollow"', false);
    }
}
