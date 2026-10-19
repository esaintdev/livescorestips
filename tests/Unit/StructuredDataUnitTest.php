<?php

namespace Tests\Unit;

use App\Http\Controllers\FixtureController;
use App\Models\Prediction;
use App\Services\ApiFootballService;
use App\Services\MatchContentService;
use App\Services\PredictionResultService;
use Carbon\Carbon;
use Mockery;
use Tests\TestCase;

class StructuredDataUnitTest extends TestCase
{
    private FixtureController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $apiService = Mockery::mock(ApiFootballService::class);
        $contentService = Mockery::mock(MatchContentService::class);
        $resultService = Mockery::mock(PredictionResultService::class);

        $this->controller = new FixtureController($apiService, $contentService, $resultService);
    }

    public function test_structured_data_includes_venue_when_provided(): void
    {
        $fixture = [
            'fixture' => [
                'id' => 1234,
                'date' => '2026-10-20T19:00:00+00:00',
                'status' => ['short' => 'NS'],
                'venue' => ['name' => 'Old Trafford', 'city' => 'Manchester'],
            ],
            'league' => [
                'name' => 'Premier League',
                'country' => 'England',
                'logo' => 'https://example.com/pl.png',
            ],
            'teams' => [
                'home' => ['name' => 'Manchester United', 'logo' => 'https://example.com/mu.png'],
                'away' => ['name' => 'Liverpool', 'logo' => 'https://example.com/liv.png'],
            ],
        ];

        [$event, $breadcrumb] = $this->controller->structuredData($fixture, null, null);

        $this->assertSame('SportsEvent', $event['@type']);
        $this->assertSame('Manchester United vs Liverpool', $event['name']);
        $this->assertSame('https://schema.org/OfflineEventAttendanceMode', $event['eventAttendanceMode']);
        $this->assertSame('https://schema.org/EventScheduled', $event['eventStatus']);

        // Location verification
        $this->assertArrayHasKey('location', $event);
        $this->assertSame('Place', $event['location']['@type']);
        $this->assertSame('Old Trafford', $event['location']['name']);
        $this->assertSame('Manchester', $event['location']['address']['addressLocality']);
        $this->assertSame('England', $event['location']['address']['addressCountry']);

        // Dates
        $this->assertSame('2026-10-20T19:00:00+00:00', $event['startDate']);
        $this->assertSame('2026-10-20T20:50:00+00:00', $event['endDate']);

        // Organizer & images
        $this->assertSame('Premier League', $event['organizer']['name']);
        $this->assertNotEmpty($event['image']);
        $this->assertStringContainsString('Manchester United vs Liverpool', $event['description']);
    }

    public function test_structured_data_always_provides_location_when_venue_is_missing(): void
    {
        $fixture = [
            'fixture' => [
                'id' => 9999,
                'date' => '2026-10-25T15:00:00+00:00',
                'status' => ['short' => 'NS'],
                'venue' => ['name' => null, 'city' => null],
            ],
            'league' => [
                'name' => 'La Liga',
                'country' => 'Spain',
            ],
            'teams' => [
                'home' => ['name' => 'Real Betis'],
                'away' => ['name' => 'Sevilla'],
            ],
        ];

        [$event, $breadcrumb] = $this->controller->structuredData($fixture, null, null);

        $this->assertArrayHasKey('location', $event);
        $this->assertSame('Place', $event['location']['@type']);
        $this->assertSame('Real Betis Stadium', $event['location']['name']);
        $this->assertSame('Spain', $event['location']['address']['addressCountry']);
    }

    public function test_structured_data_falls_back_to_prediction_match_date(): void
    {
        $fixture = [
            'fixture' => [
                'id' => 8888,
                'date' => null,
                'status' => ['short' => null],
                'venue' => [],
            ],
            'league' => [
                'name' => null,
                'country' => null,
            ],
            'teams' => [
                'home' => ['name' => 'Arsenal'],
                'away' => ['name' => 'Chelsea'],
            ],
        ];

        $prediction = new Prediction();
        $prediction->match_date = Carbon::parse('2026-11-01 14:00:00');

        [$event, $breadcrumb] = $this->controller->structuredData($fixture, $prediction, null);

        $this->assertArrayHasKey('location', $event);
        $this->assertSame('Arsenal Stadium', $event['location']['name']);
        $this->assertStringStartsWith('2026-11-01T14:00:00', $event['startDate']);
        $this->assertStringStartsWith('2026-11-01T15:50:00', $event['endDate']);
    }
}
