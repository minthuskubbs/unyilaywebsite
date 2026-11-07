<?php

namespace Tests\Feature;

use App\Services\BrassShowroomManager;
use App\Services\BrassShowroomFrontendReleaseManager;
use App\Services\BrassShowroomNotionClient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class BrassShowroomManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_bundled_assets_are_versioned_so_deploys_bypass_the_immutable_cache(): void
    {
        $response = $this->get('/silver-art-gallery')->assertOk();

        $response->assertSee('assets/bundled/javascript?v='.filemtime(public_path('vendor/brass-showroom/brass-showroom.js')), false);
        $response->assertSee('assets/bundled/stylesheet?v='.filemtime(public_path('vendor/brass-showroom/brass-showroom.css')), false);
        $this->get('/brass/assets/bundled/javascript?v=1')->assertOk();
    }

    public function test_both_gallery_addresses_open_the_showroom(): void
    {
        $this->get('/silver-art-gallery')->assertOk();
        $this->get('/brass-art-gallery')->assertOk();
    }

    public function test_each_gallery_address_has_its_own_link_preview(): void
    {
        $this->get('/brass-art-gallery')->assertOk()
            ->assertSee('<title>ကြေးဝါအနုပညာပြခန်း — Brass Art Gallery</title>', false)
            ->assertSee('<meta property="og:title" content="ကြေးဝါအနုပညာပြခန်း — Brass Art Gallery">', false)
            ->assertSee('<meta property="og:url" content="'.url('/brass-art-gallery').'">', false)
            ->assertSee('<meta property="og:image" content="'.asset('images/brand/android-chrome-512x512.png').'">', false);

        $this->get('/silver-art-gallery')->assertOk()
            ->assertSee('<title>ငွေထည်အနုပညာပြခန်း — Silver Art Gallery</title>', false)
            ->assertSee('<meta property="og:title" content="ငွေထည်အနုပညာပြခန်း — Silver Art Gallery">', false)
            ->assertSee('<meta property="og:url" content="'.url('/silver-art-gallery').'">', false);
    }

    public function test_older_addresses_lead_to_the_matching_gallery(): void
    {
        foreach ([
            '/brass' => '/brass-art-gallery',
            '/brass?entrance=brass' => '/brass-art-gallery',
            '/brass?entrance=silver' => '/silver-art-gallery',
            '/art-gallery' => '/silver-art-gallery',
            '/art-gallery?entrance=silver' => '/silver-art-gallery',
            '/art-gallery?entrance=brass' => '/brass-art-gallery',
            '/silver-showroom' => '/silver-art-gallery',
            '/brass-showroom' => '/brass-art-gallery',
        ] as $from => $to) {
            $this->get($from)->assertStatus(301)->assertRedirect($to);
        }
    }

    public function test_mcp_endpoint_rejects_anonymous_requests(): void
    {
        $this->postJson('/mcp/brass-showroom', [
            'jsonrpc' => '2.0', 'id' => 1, 'method' => 'tools/list',
        ])->assertUnauthorized();
    }

    public function test_laravel_10_mcp_endpoint_initializes_and_lists_tools_for_owner(): void
    {
        $user = User::factory()->create();
        config()->set('brass-showroom.manager_user_ids', [$user->id]);
        Sanctum::actingAs($user, ['showroom:manage']);

        $this->postJson('/mcp/brass-showroom', [
            'jsonrpc' => '2.0', 'id' => 1, 'method' => 'initialize',
            'params' => ['protocolVersion' => '2025-06-18', 'capabilities' => [], 'clientInfo' => ['name' => 'test', 'version' => '1']],
        ])->assertOk()->assertJsonPath('result.protocolVersion', '2025-06-18');

        $this->postJson('/mcp/brass-showroom', [
            'jsonrpc' => '2.0', 'id' => 2, 'method' => 'tools/list', 'params' => [],
        ])->assertOk()->assertJsonFragment(['name' => 'get_showroom_state']);
    }

    public function test_draft_update_does_not_change_public_config_until_publish(): void
    {
        $manager = app(BrassShowroomManager::class);
        $publishedBefore = $manager->view('published')['config'];
        $draft = $manager->updateArtwork('boat', ['price_mmk' => 275000], 1);

        $this->assertSame(250000, $publishedBefore['artworks'][0]['priceMmk']);
        $this->assertSame(250000, $manager->view('published')['config']['artworks'][0]['priceMmk']);
        $this->assertSame(275000, $draft['config']['artworks'][0]['priceMmk']);

        $manager->publish($draft['draft_revision'], 'Price test', 1);
        $this->assertSame(275000, $manager->view('published')['config']['artworks'][0]['priceMmk']);
    }

    public function test_stale_draft_revision_cannot_be_published(): void
    {
        $manager = app(BrassShowroomManager::class);
        $old = $manager->view('draft')['draft_revision'];
        $manager->updateArtwork('boat', ['price_mmk' => 275000], 1);

        $this->expectException(ValidationException::class);
        $manager->publish($old, 'Stale publish', 1);
    }

    public function test_frontend_release_is_staged_and_activated_with_hashes_and_concurrency_guard(): void
    {
        Storage::fake('local');
        config()->set('brass-showroom.frontend_deploy_enabled', true);
        $manager = app(BrassShowroomFrontendReleaseManager::class);
        $javascript = '(()=>{const asset=new URL("/",window.location.origin);return asset.href;})();';
        $stylesheet = '.bs-root{color:#292c24}';
        $staged = $manager->stage([
            'version' => '2.1.0-test',
            'javascript_base64' => base64_encode($javascript),
            'stylesheet_base64' => base64_encode($stylesheet),
            'javascript_sha256' => hash('sha256', $javascript),
            'stylesheet_sha256' => hash('sha256', $stylesheet),
            'note' => 'Automated test',
        ], 1);

        $this->assertSame('bundled', $staged['active_release_id']);
        $active = $manager->activate($staged['staged_release']['id'], 'bundled', 'Test activation', 1);
        $this->assertSame($staged['staged_release']['id'], $active['active_release_id']);
    }

    public function test_frontend_release_rejects_dangerous_browser_primitive(): void
    {
        Storage::fake('local');
        $manager = app(BrassShowroomFrontendReleaseManager::class);
        $javascript = '(()=>{const asset=new URL("/",window.location.origin);document.write(asset.href);})();';
        $stylesheet = '.bs-root{color:#292c24}';

        $this->expectException(ValidationException::class);
        $manager->stage([
            'version' => '2.1.0-test',
            'javascript_base64' => base64_encode($javascript),
            'stylesheet_base64' => base64_encode($stylesheet),
            'javascript_sha256' => hash('sha256', $javascript),
            'stylesheet_sha256' => hash('sha256', $stylesheet),
        ], 1);
    }

    public function test_silver_artwork_can_be_added_from_a_bundled_image_and_published(): void
    {
        $manager = app(BrassShowroomManager::class);
        $draft = $manager->addArtwork([
            'id' => 's-test-cart-10x8', 'room' => 'silver', 'title_my' => 'နွားလှည်း', 'title_en' => 'Cart',
            'price_mmk' => 450000, 'width_inches' => 10, 'height_inches' => 8,
            'image_path' => '/vendor/brass-showroom/images/boat.webp',
        ], 1);

        $added = collect($draft['config']['artworks'])->firstWhere('id', 's-test-cart-10x8');
        $this->assertSame('silver', $added['room']);
        $this->assertSame('auto', $added['wall']);
        $this->assertSame('landscape', $added['orientation']);

        $manager->publish($draft['draft_revision'], 'Silver test', 1);
        $this->assertNotNull(collect($manager->view('published')['config']['artworks'])->firstWhere('id', 's-test-cart-10x8'));
    }

    public function test_more_than_twelve_artworks_are_accepted(): void
    {
        $manager = app(BrassShowroomManager::class);
        foreach (range(1, 10) as $n) {
            $manager->addArtwork([
                'id' => "s-bulk-{$n}", 'title_my' => 'ငွေ', 'title_en' => 'Silver', 'price_mmk' => 1,
                'width_inches' => 9, 'height_inches' => 7, 'image_path' => '/vendor/brass-showroom/images/boat.webp',
            ], 1);
        }
        $this->assertGreaterThan(12, count($manager->view('draft')['config']['artworks']));
    }

    public function test_brass_artwork_cannot_use_automatic_wall_and_paths_outside_vendor_are_rejected(): void
    {
        $manager = app(BrassShowroomManager::class);
        $brass = collect($manager->view('draft')['config']['artworks'])->first(fn ($a) => ($a['room'] ?? 'brass') === 'brass');

        try {
            $manager->updateArtwork($brass['id'], ['wall' => 'auto'], 1);
            $this->fail('Brass artwork accepted wall auto.');
        } catch (ValidationException) {
        }

        $this->expectException(ValidationException::class);
        $manager->addArtwork([
            'id' => 's-bad-path', 'title_my' => 'x', 'title_en' => 'x', 'price_mmk' => 1,
            'width_inches' => 9, 'height_inches' => 7, 'image_path' => '/vendor/../../.env',
        ], 1);
    }

    public function test_artwork_can_be_removed_from_draft(): void
    {
        $manager = app(BrassShowroomManager::class);
        $manager->addArtwork([
            'id' => 's-remove-me', 'title_my' => 'x', 'title_en' => 'x', 'price_mmk' => 1,
            'width_inches' => 9, 'height_inches' => 7, 'image_path' => '/vendor/brass-showroom/images/boat.webp',
        ], 1);
        $draft = $manager->removeArtwork('s-remove-me', 1);
        $this->assertNull(collect($draft['config']['artworks'])->firstWhere('id', 's-remove-me'));
    }

    /** A live showroom that lacks one catalogue piece, with no unpublished edits. */
    private function publishShowroomMissingBoat(BrassShowroomManager $manager): int
    {
        $draft = $manager->removeArtwork('boat', 1);
        $manager->publish($draft['draft_revision'], 'Older live showroom', 1);

        return count($manager->view('published')['config']['artworks']);
    }

    public function test_catalogue_import_fills_the_draft_and_only_goes_live_on_publish(): void
    {
        $manager = app(BrassShowroomManager::class);
        $liveCount = $this->publishShowroomMissingBoat($manager);

        $this->artisan('brass-showroom:import-catalogue')
            ->expectsOutputToContain('105 silver and 20 brass')
            ->assertSuccessful();

        $draft = $manager->view('draft');
        $this->assertCount(125, $draft['config']['artworks']);
        $this->assertCount($liveCount, $manager->view('published')['config']['artworks']);

        $this->artisan('brass-showroom:import-catalogue --publish='.$draft['draft_revision'])->assertSuccessful();
        $this->assertCount(125, $manager->view('published')['config']['artworks']);
    }

    public function test_catalogue_import_keeps_the_draft_revision_when_rerun_for_a_new_preview_link(): void
    {
        $manager = app(BrassShowroomManager::class);
        $this->publishShowroomMissingBoat($manager);
        $this->artisan('brass-showroom:import-catalogue')->assertSuccessful();
        $revision = $manager->view('draft')['draft_revision'];

        $this->artisan('brass-showroom:import-catalogue')->assertSuccessful();

        $this->assertSame($revision, $manager->view('draft')['draft_revision']);
    }

    public function test_catalogue_import_will_not_overwrite_unpublished_edits_without_force(): void
    {
        $manager = app(BrassShowroomManager::class);
        $this->publishShowroomMissingBoat($manager);
        $manager->updateArtwork('lawka', ['price_mmk' => 999000], 1);

        $this->artisan('brass-showroom:import-catalogue')->assertFailed();
        $this->assertSame(999000, collect($manager->view('draft')['config']['artworks'])->firstWhere('id', 'lawka')['priceMmk']);

        $this->artisan('brass-showroom:import-catalogue --force')->assertSuccessful();
        $this->assertCount(125, $manager->view('draft')['config']['artworks']);
    }

    public function test_catalogue_publish_rejects_a_stale_draft_revision(): void
    {
        $manager = app(BrassShowroomManager::class);
        $liveCount = $this->publishShowroomMissingBoat($manager);
        $this->artisan('brass-showroom:import-catalogue')->assertSuccessful();
        $stale = $manager->view('draft')['draft_revision'] - 1;

        $this->artisan('brass-showroom:import-catalogue --publish='.$stale)->assertFailed();

        $this->assertCount($liveCount, $manager->view('published')['config']['artworks']);
    }

    private function fakeNotion(): void
    {
        config()->set('brass-showroom.notion', ['sync_enabled' => true, 'token' => 'secret_test', 'databases' => ['db-silver', 'db-brass'], 'price_property' => 'Sale Price', 'version' => '2022-06-28']);
        Http::fake([
            'api.notion.com/v1/databases/db-silver/query' => Http::response(['results' => [], 'has_more' => false]),
            'api.notion.com/v1/databases/db-brass/query' => Http::response(['has_more' => false, 'results' => [
                ['id' => '27a7ca93f410809b9febc9c27a7dff7a', 'properties' => ['Sale Price' => ['type' => 'formula', 'formula' => ['type' => 'string', 'string' => '300,000']]]],
                ['id' => '2997ca93-f410-80a2-8ee4-f7da52230fdd', 'properties' => ['Sale Price' => ['type' => 'rich_text', 'rich_text' => [['plain_text' => '250000']]]]],
            ]]),
        ]);
    }

    public function test_notion_prices_update_the_live_showroom_and_keep_unpublished_draft_edits(): void
    {
        $this->fakeNotion();
        $manager = app(BrassShowroomManager::class);
        $manager->updateArtwork('lawka', ['price_mmk' => 999000], 1);
        $before = $manager->view('published')['published_revision'];

        $this->artisan('brass-showroom:sync-notion')->assertSuccessful();

        $published = collect($manager->view('published')['config']['artworks'])->keyBy('id');
        $draft = $manager->view('draft');
        $this->assertSame(300000, $published['boat']['priceMmk']);
        $this->assertSame(250000, $published['karaweik']['priceMmk']);
        $this->assertSame(250000, $published['lawka']['priceMmk']);
        $this->assertSame(300000, collect($draft['config']['artworks'])->firstWhere('id', 'boat')['priceMmk']);
        $this->assertSame(999000, collect($draft['config']['artworks'])->firstWhere('id', 'lawka')['priceMmk']);
        $this->assertSame($before + 1, $draft['published_revision']);
        $this->assertStringContainsString('Notion price sync', $manager->revisions()[0]['note']);

        // Nothing changed in Notion: no new revision.
        $this->artisan('brass-showroom:sync-notion')->assertSuccessful();
        $this->assertSame($before + 1, $manager->view('published')['published_revision']);
    }

    public function test_notion_dry_run_and_disabled_sync_change_nothing(): void
    {
        $this->fakeNotion();
        $manager = app(BrassShowroomManager::class);
        $this->artisan('brass-showroom:sync-notion --dry-run')->expectsOutputToContain('1 price(s) would change')->assertSuccessful();
        config()->set('brass-showroom.notion.sync_enabled', false);
        $this->artisan('brass-showroom:sync-notion')->assertSuccessful();
        $this->assertSame(250000, collect($manager->view('published')['config']['artworks'])->firstWhere('id', 'boat')['priceMmk']);
    }

    public function test_notion_price_parsing(): void
    {
        $this->assertSame(1430000, BrassShowroomNotionClient::price(['type' => 'formula', 'formula' => ['type' => 'string', 'string' => '1430000']]));
        $this->assertSame(250000, BrassShowroomNotionClient::price(['type' => 'number', 'number' => 250000]));
        $this->assertSame(250000, BrassShowroomNotionClient::price(['type' => 'rich_text', 'rich_text' => [['plain_text' => '၂၅၀,၀၀၀ ကျပ်']]]));
        $this->assertNull(BrassShowroomNotionClient::price(['type' => 'rich_text', 'rich_text' => []]));
        $this->assertNull(BrassShowroomNotionClient::price(['type' => 'formula', 'formula' => ['type' => 'string', 'string' => '']]));
        $this->assertNull(BrassShowroomNotionClient::price(['type' => 'number', 'number' => 0]));
        $this->assertNull(BrassShowroomNotionClient::price(null));
    }
}
