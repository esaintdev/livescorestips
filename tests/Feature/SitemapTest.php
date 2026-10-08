<?php

namespace Tests\Feature;

use App\Models\Prediction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_index_lists_child_sitemaps(): void
    {
        $response = $this->get('http://www.livescorestips.test/sitemap.xml');

        $response->assertStatus(200);
        $response->assertSee('sitemaps/main.xml', false);
        $response->assertSee('sitemaps/blog.xml', false);
        $response->assertSee('sitemaps/seo.xml', false);
        $response->assertSee('sitemaps/predictions.xml', false);
    }

    public function test_predictions_sitemap_emits_canonical_slug_urls_only(): void
    {
        Prediction::factory()->create([
            'fixture_id' => 700001,
            'slug' => 'alpha-vs-beta',
            'home_team' => 'Alpha',
            'away_team' => 'Beta',
            'match_date' => now()->addDay(),
            'tip' => '1X',
            'category' => 'Double Chance',
        ]);

        // Same provider fixture, alternate record — must be deduplicated.
        Prediction::factory()->create([
            'fixture_id' => 700001,
            'slug' => 'alpha-vs-beta-2',
            'home_team' => 'Alpha',
            'away_team' => 'Beta',
            'match_date' => now()->addDay(),
            'tip' => 'Awaiting odds',
            'category' => 'Home Win',
        ]);

        // No slug — cannot resolve directly, must be excluded.
        Prediction::factory()->create([
            'fixture_id' => 700002,
            'slug' => null,
            'home_team' => 'Gamma',
            'away_team' => 'Delta',
            'match_date' => now()->addDay(),
            'tip' => '1X',
        ]);

        $response = $this->get('http://www.livescorestips.test/sitemaps/predictions.xml');

        $response->assertStatus(200);
        $response->assertSee('match/alpha-vs-beta/', false);
        $response->assertDontSee('match/alpha-vs-beta-2', false);
        $response->assertDontSee('match/700002/', false);
        $response->assertDontSee('match/700001/', false);
    }

    public function test_predictions_sitemap_uses_content_updated_at_for_lastmod(): void
    {
        $prediction = Prediction::factory()->create([
            'fixture_id' => 700010,
            'slug' => 'lastmod-vs-test',
            'home_team' => 'Lastmod',
            'away_team' => 'Test',
            'match_date' => now()->addDay(),
            'tip' => '1X',
            'content_updated_at' => '2026-05-01 10:00:00',
            'updated_at' => '2026-05-01 10:00:00',
        ]);

        $response = $this->get('http://www.livescorestips.test/sitemaps/predictions.xml');

        $response->assertStatus(200);
        $response->assertSee('match/lastmod-vs-test/', false);
        $response->assertSee('2026-05-01T10:00:00', false);
    }

    public function test_predictions_sitemap_excludes_fabricated_dates(): void
    {
        Prediction::factory()->create([
            'fixture_id' => 700020,
            'slug' => 'dated-vs-match',
            'home_team' => 'Dated',
            'away_team' => 'Match',
            'match_date' => '2026-09-20 15:00:00',
            'tip' => '1X',
        ]);

        $response = $this->get('http://www.livescorestips.test/sitemaps/predictions.xml');

        $response->assertSee('match/dated-vs-match/2026-09-20', false);
        $response->assertDontSee('match/dated-vs-match/1999', false);
        $response->assertDontSee('match/dated-vs-match/2027', false);
    }

    public function test_sitemap_match_url_resolves_directly_to_200(): void
    {
        $prediction = Prediction::factory()->create([
            'fixture_id' => 700030,
            'slug' => 'direct-vs-resolve',
            'home_team' => 'Direct',
            'away_team' => 'Resolve',
            'match_date' => now()->addDay(),
            'tip' => '1X',
            'category' => 'Double Chance',
        ]);

        $this->fakeApi([
            'fetchFixtureDetails' => $this->fixturePayload([
                'fixture' => ['id' => 700030],
                'teams' => [
                    'home' => ['id' => 101, 'name' => 'Direct', 'logo' => null],
                    'away' => ['id' => 202, 'name' => 'Resolve', 'logo' => null],
                ],
            ]),
        ]);

        $url = 'http://www.livescorestips.test/match/direct-vs-resolve/'.$prediction->match_date->format('Y-m-d');
        $this->get($url)->assertStatus(200);
    }
}
