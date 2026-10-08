<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class GuestPageCacheTest extends TestCase
{
    use RefreshDatabase;

    private const HOST = 'http://www.livescorestips.test';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'cache.page_cache.enabled' => true,
            'cache.page_cache.store' => 'array',
            'cache.page_cache.ttl' => 120,
        ]);
        Cache::store('array')->flush();

        $this->fakeApi();
    }

    public function test_guest_page_is_cached(): void
    {
        $this->get(self::HOST.'/')->assertStatus(200)->assertHeader('X-Page-Cache', 'MISS');
        $this->get(self::HOST.'/')->assertStatus(200)->assertHeader('X-Page-Cache', 'HIT');
    }

    public function test_cached_page_is_rehydrated_with_csrf_token_not_placeholder(): void
    {
        $this->get(self::HOST.'/');

        $hit = $this->get(self::HOST.'/');
        $hit->assertHeader('X-Page-Cache', 'HIT');
        $hit->assertDontSee('___LST_CSRF_TOKEN___', false);
        $hit->assertSee('name="csrf-token"', false);
    }

    public function test_live_query_bypasses_cache(): void
    {
        $this->get(self::HOST.'/?live')->assertHeaderMissing('X-Page-Cache');
    }

    public function test_authenticated_user_bypasses_cache(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(self::HOST.'/')->assertHeaderMissing('X-Page-Cache');
    }

    public function test_non_whitelisted_route_is_not_cached(): void
    {
        $this->get(self::HOST.'/login')->assertStatus(200)->assertHeaderMissing('X-Page-Cache');
    }

    public function test_disabled_flag_bypasses_cache(): void
    {
        config(['cache.page_cache.enabled' => false]);

        $this->get(self::HOST.'/')->assertHeaderMissing('X-Page-Cache');
    }
}
