<?php

namespace Tests\Feature;

use App\Models\Prediction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StructuredDataTest extends TestCase
{
    use RefreshDatabase;

    private function jsonLdBlocks(string $html): array
    {
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $matches);

        $blocks = [];
        foreach ($matches[1] as $raw) {
            $decoded = json_decode(trim($raw), true);
            $this->assertNotNull($decoded, 'JSON-LD must be valid JSON: '.$raw);
            $blocks[] = $decoded;
        }

        return $blocks;
    }

    private function findByType(array $blocks, string $type): ?array
    {
        foreach ($blocks as $block) {
            if (($block['@type'] ?? null) === $type) {
                return $block;
            }
        }

        return null;
    }

    public function test_match_page_emits_valid_sports_event_and_breadcrumb(): void
    {
        $prediction = Prediction::factory()->create([
            'fixture_id' => 555001,
            'slug' => 'getafe-vs-malaga',
            'home_team' => 'Getafe',
            'away_team' => 'Malaga',
            'match_date' => '2026-09-20 15:00:00',
            'tip' => '1X',
            'category' => 'Double Chance',
        ]);

        $this->fakeApi([
            'fetchFixtureDetails' => $this->fixturePayload([
                'fixture' => ['id' => 555001, 'venue' => ['name' => 'Test Stadium']],
                'teams' => [
                    'home' => ['id' => 101, 'name' => 'Getafe', 'logo' => null],
                    'away' => ['id' => 202, 'name' => 'Malaga', 'logo' => null],
                ],
            ]),
        ]);

        $response = $this->get('http://www.livescorestips.test/match/getafe-vs-malaga/2026-09-20');
        $response->assertStatus(200);

        $blocks = $this->jsonLdBlocks($response->getContent());

        $event = $this->findByType($blocks, 'SportsEvent');
        $this->assertNotNull($event);
        $this->assertSame('Getafe vs Malaga', $event['name']);
        $this->assertSame('http://www.livescorestips.test/match/getafe-vs-malaga/2026-09-20', $event['url']);
        $this->assertSame('Football', $event['sport']);
        $this->assertSame('https://schema.org/EventScheduled', $event['eventStatus']);
        $this->assertSame('Test Stadium', $event['location']['name']);
        $this->assertCount(2, $event['competitor']);

        // No fabricated properties.
        $this->assertArrayNotHasKey('offers', $event);
        $this->assertArrayNotHasKey('attendance', $event);

        $breadcrumb = $this->findByType($blocks, 'BreadcrumbList');
        $this->assertNotNull($breadcrumb);
        $positions = array_column($breadcrumb['itemListElement'], 'position');
        $this->assertSame([1, 2, 3], $positions);
        $this->assertSame('Home', $breadcrumb['itemListElement'][0]['name']);
        $this->assertSame('Getafe vs Malaga', $breadcrumb['itemListElement'][2]['name']);
    }

    public function test_postponed_fixture_uses_event_postponed_status(): void
    {
        $prediction = Prediction::factory()->create([
            'fixture_id' => 555050,
            'slug' => 'postponed-vs-fixture',
            'home_team' => 'Postponed',
            'away_team' => 'Fixture',
            'match_date' => '2026-09-20 15:00:00',
            'tip' => '1X',
            'category' => 'Double Chance',
        ]);

        $this->fakeApi([
            'fetchFixtureDetails' => $this->fixturePayload([
                'fixture' => ['id' => 555050, 'status' => ['short' => 'PST', 'elapsed' => null]],
                'teams' => [
                    'home' => ['id' => 101, 'name' => 'Postponed', 'logo' => null],
                    'away' => ['id' => 202, 'name' => 'Fixture', 'logo' => null],
                ],
            ]),
        ]);

        $response = $this->get('http://www.livescorestips.test/match/postponed-vs-fixture/2026-09-20');
        $blocks = $this->jsonLdBlocks($response->getContent());

        $event = $this->findByType($blocks, 'SportsEvent');
        $this->assertSame('https://schema.org/EventPostponed', $event['eventStatus']);
    }
}
