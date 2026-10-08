<?php

namespace Tests\Feature;

use App\Models\Prediction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FixtureRoutingTest extends TestCase
{
    use RefreshDatabase;

    private const HOST = 'http://www.livescorestips.test';

    private function matchUrl(string $slug, string $date): string
    {
        return self::HOST.'/match/'.$slug.'/'.$date;
    }

    private function makePrediction(array $attributes = []): Prediction
    {
        return Prediction::factory()->create(array_merge([
            'fixture_id' => 555001,
            'slug' => 'getafe-vs-malaga',
            'home_team' => 'Getafe',
            'away_team' => 'Malaga',
            'match_date' => '2026-09-20 15:00:00',
            'tip' => '1X',
            'category' => 'Double Chance',
            'status' => 'pending',
        ], $attributes));
    }

    public function test_valid_canonical_match_url_returns_200_with_self_canonical(): void
    {
        $this->makePrediction();
        $this->fakeApi(['fetchFixtureDetails' => $this->fixturePayload([
            'fixture' => ['id' => 555001],
            'teams' => [
                'home' => ['id' => 101, 'name' => 'Getafe', 'logo' => null],
                'away' => ['id' => 202, 'name' => 'Malaga', 'logo' => null],
            ],
        ])]);

        $response = $this->get($this->matchUrl('getafe-vs-malaga', '2026-09-20'));

        $response->assertStatus(200);
        $response->assertSee(
            '<link rel="canonical" href="'.self::HOST.'/match/getafe-vs-malaga/2026-09-20">',
            false
        );
        $response->assertSee('name="robots" content="index, follow"', false);
        $response->assertSee('Getafe');
    }

    public function test_wrong_date_redirects_301_to_canonical_date(): void
    {
        $this->makePrediction();
        $this->fakeApi(['fetchFixtureDetails' => $this->fixturePayload(['fixture' => ['id' => 555001]])]);

        $response = $this->get($this->matchUrl('getafe-vs-malaga', '1999-01-01'));

        $response->assertStatus(301);
        $response->assertRedirect(self::HOST.'/match/getafe-vs-malaga/2026-09-20');
    }

    public function test_wrong_date_does_not_return_200_or_self_canonicalize(): void
    {
        $this->makePrediction();
        $this->fakeApi(['fetchFixtureDetails' => $this->fixturePayload(['fixture' => ['id' => 555001]])]);

        $response = $this->get($this->matchUrl('getafe-vs-malaga', '2027-12-31'));

        $response->assertStatus(301);
        $response->assertDontSee('1999-01-01');
    }

    public function test_canonical_target_returns_200(): void
    {
        $this->makePrediction();
        $this->fakeApi(['fetchFixtureDetails' => $this->fixturePayload(['fixture' => ['id' => 555001]])]);

        $this->get($this->matchUrl('getafe-vs-malaga', '2026-09-20'))->assertStatus(200);
    }

    public function test_nonexistent_match_returns_404_not_homepage_redirect(): void
    {
        $this->fakeApi();

        $response = $this->get($this->matchUrl('this-is-not-a-real-match-xyz', '2026-09-20'));

        $response->assertStatus(404);
    }

    public function test_numeric_unknown_fixture_returns_404_when_provider_confirms_nothing(): void
    {
        $this->fakeApi(['fetchFixtureDetails' => null]);

        $this->get($this->matchUrl('999999999', '2026-09-20'))->assertStatus(404);
    }

    public function test_api_failure_does_not_destroy_persisted_match_page(): void
    {
        $this->makePrediction();
        $this->fakeApi(['fetchFixtureDetails' => null]);

        $response = $this->get($this->matchUrl('getafe-vs-malaga', '2026-09-20'));

        $response->assertStatus(200);
        $response->assertSee('Getafe');
        $response->assertSee('Malaga');
        $response->assertSee('Expert Prediction');
    }

    public function test_api_exception_does_not_destroy_persisted_match_page(): void
    {
        $this->makePrediction();
        $this->fakeApi([
            'fetchFixtureDetails' => function () {
                throw new \RuntimeException('429 Too Many Requests');
            },
        ]);

        $response = $this->get($this->matchUrl('getafe-vs-malaga', '2026-09-20'));

        $response->assertStatus(200);
        $response->assertSee('Getafe');
    }

    public function test_legacy_fixture_id_url_redirects_301_to_canonical(): void
    {
        $this->makePrediction();
        $this->fakeApi();

        $response = $this->get(self::HOST.'/match/555001');

        $response->assertStatus(301);
        $response->assertRedirect(self::HOST.'/match/getafe-vs-malaga/2026-09-20');
    }

    public function test_invalid_slug_without_date_returns_404(): void
    {
        $this->fakeApi();

        $this->get(self::HOST.'/match/totally-fake-match')->assertStatus(404);
    }

    public function test_finished_match_page_shows_result_and_score(): void
    {
        Prediction::factory()->create([
            'fixture_id' => 555010,
            'slug' => 'aik-stockholm-vs-mjallby-aif',
            'home_team' => 'AIK Stockholm',
            'away_team' => 'Mjallby AIF',
            'match_date' => '2026-09-16 15:00:00',
            'tip' => 'Over 1.5',
            'category' => 'Over 1.5 Goals',
            'status' => 'pending',
            'home_score' => null,
            'away_score' => null,
        ]);

        $this->fakeApi([
            'fetchFixtureDetails' => $this->fixturePayload([
                'fixture' => ['id' => 555010, 'status' => ['short' => 'FT', 'elapsed' => 90]],
                'goals' => ['home' => 1, 'away' => 1],
            ]),
        ]);

        $response = $this->get($this->matchUrl('aik-stockholm-vs-mjallby-aif', '2026-09-16'));

        $response->assertStatus(200);
        $response->assertSee('Prediction Result');
        $response->assertSee('WON');
        $response->assertSee('1:1');
        $response->assertSee('Prediction Analysis');
        $response->assertSee('Key Match Factors');
    }

    public function test_voided_prediction_shows_void(): void
    {
        Prediction::factory()->create([
            'fixture_id' => 555011,
            'slug' => 'voided-vs-fixture',
            'home_team' => 'Voided FC',
            'away_team' => 'Fixture United',
            'match_date' => '2026-09-16 15:00:00',
            'tip' => '1X',
            'category' => 'Double Chance',
            'status' => 'void',
        ]);

        $this->fakeApi([
            'fetchFixtureDetails' => $this->fixturePayload([
                'fixture' => ['id' => 555011, 'status' => ['short' => 'CANC', 'elapsed' => null]],
                'goals' => ['home' => null, 'away' => null],
            ]),
        ]);

        $response = $this->get($this->matchUrl('voided-vs-fixture', '2026-09-16'));

        $response->assertStatus(200);
        $response->assertSee('VOID');
    }

    public function test_postponed_match_page_is_200_and_not_lost(): void
    {
        $this->makePrediction([
            'fixture_id' => 555012,
            'slug' => 'postponed-vs-fixture',
            'match_date' => '2026-09-16 15:00:00',
            'status' => 'pending',
        ]);

        $this->fakeApi([
            'fetchFixtureDetails' => $this->fixturePayload([
                'fixture' => ['id' => 555012, 'status' => ['short' => 'PST', 'elapsed' => null]],
                'goals' => ['home' => null, 'away' => null],
            ]),
        ]);

        $response = $this->get($this->matchUrl('postponed-vs-fixture', '2026-09-16'));

        $response->assertStatus(200);
        $response->assertDontSee('Prediction Result');
        $response->assertDontSee('LOST');
    }

    public function test_apex_host_redirects_to_www(): void
    {
        $response = $this->get('http://livescorestips.test/match/getafe-vs-malaga/2026-09-20');

        $response->assertStatus(301);
        $response->assertRedirect('https://www.livescorestips.test/match/getafe-vs-malaga/2026-09-20');
    }
}
