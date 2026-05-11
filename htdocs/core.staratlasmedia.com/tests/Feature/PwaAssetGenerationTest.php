<?php

namespace Tests\Feature;

use App\Models\PushGroup;
use App\Services\Push\ManifestGenerator;
use App\Services\Push\ServiceWorkerGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PwaAssetGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_worker_generator_outputs_clean_push_only_worker(): void
    {
        $worker = app(ServiceWorkerGenerator::class)->generate(
            PushGroup::query()->where('code', 'clubalfa_it')->firstOrFail(),
        );

        $this->assertStringContainsString('self.skipWaiting()', $worker);
        $this->assertStringContainsString('self.clients.claim()', $worker);
        $this->assertStringContainsString("self.addEventListener('push'", $worker);
        $this->assertStringContainsString("self.addEventListener('notificationclick'", $worker);
        $this->assertStringContainsString('event.notification.data.url', $worker);
        $this->assertStringNotContainsString('eval', $worker);
        $this->assertStringNotContainsString("addEventListener('fetch'", $worker);
        $this->assertStringNotContainsString('data.target', $worker);
    }

    public function test_manifest_generator_outputs_clubalfa_it_and_en_stable_manifest_fields(): void
    {
        $generator = app(ManifestGenerator::class);

        $it = json_decode($generator->generate(PushGroup::query()->where('code', 'clubalfa_it')->firstOrFail()), true, flags: JSON_THROW_ON_ERROR);
        $en = json_decode($generator->generate(PushGroup::query()->where('code', 'clubalfa_en')->firstOrFail()), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame('/pwa/clubalfa-it', $it['id']);
        $this->assertSame('/', $it['scope']);
        $this->assertSame('/pwa-start/?app=clubalfa_it', $it['start_url']);

        $this->assertSame('/pwa/clubalfa-en', $en['id']);
        $this->assertSame('/en/', $en['scope']);
        $this->assertSame('/en/pwa-start/?app=clubalfa_en', $en['start_url']);
    }
}
