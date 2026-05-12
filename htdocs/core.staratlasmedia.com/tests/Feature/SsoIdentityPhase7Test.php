<?php

namespace Tests\Feature;

use App\Filament\Resources\AuthProviderResource;
use App\Filament\Resources\AuthProviderSiteSettingResource;
use App\Models\AuthProvider;
use App\Models\AuthAuthorizationCode;
use App\Models\BridgeInstallation;
use App\Models\PushGroup;
use App\Models\Site;
use App\Models\SiteOrigin;
use App\Models\User;
use App\Services\Auth\AuthorizationCodeService;
use App\Services\Auth\BridgeCallbackUrlResolver;
use App\Services\Auth\PpidService;
use App\Services\Bridge\BridgeTokenFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SsoIdentityPhase7Test extends TestCase
{
    use RefreshDatabase;

    public function test_phase_seven_tables_and_additive_columns_exist(): void
    {
        foreach ([
            'auth_providers',
            'auth_provider_site_settings',
            'webauthn_credentials',
            'webauthn_challenges',
            'magic_link_tokens',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Missing table [{$table}].");
        }

        $this->assertTrue(Schema::hasColumn('auth_authorization_codes', 'redirect_url'));
        $this->assertTrue(Schema::hasColumn('auth_authorization_codes', 'redirect_uri'));
        $this->assertTrue(Schema::hasColumn('auth_authorization_codes', 'bridge_installation_id'));
        $this->assertTrue(Schema::hasColumn('auth_authorization_codes', 'status'));
        $this->assertTrue(Schema::hasColumn('social_identities', 'provider_user_id'));
        $this->assertTrue(Schema::hasColumn('auth_sessions', 'session_uuid'));
        $this->assertTrue(Schema::hasColumn('login_events', 'bridge_installation_id'));
        $this->assertFalse(Schema::hasColumn('users', 'ppid'));
    }

    public function test_seeded_providers_are_disabled_and_not_public(): void
    {
        $providers = AuthProvider::query()->orderBy('sort_order')->get();

        $this->assertSame(['passkey', 'google', 'apple', 'magic_link', 'password', 'facebook'], $providers->pluck('code')->all());
        $this->assertTrue($providers->every(fn (AuthProvider $provider): bool => $provider->status === 'disabled'));
        $this->assertTrue($providers->every(fn (AuthProvider $provider): bool => $provider->is_public === false));
        $this->assertSame('passkey', $providers->first()->code);
        $this->assertSame('core.staratlasmedia.com', $providers->first()->config_json['rp_id']);
    }

    public function test_disabled_provider_routes_return_not_available(): void
    {
        $this->postJson('/auth/passkey/login/options')
            ->assertStatus(503)
            ->assertJsonPath('status', 'not_available')
            ->assertJsonPath('provider', 'passkey');

        $this->getJson('/auth/oauth/google/redirect')
            ->assertStatus(503)
            ->assertJsonPath('provider', 'google');
    }

    public function test_bridge_callback_url_is_path_aware_without_hardcoded_sections(): void
    {
        [$installation] = $this->claimedInstallation('/automobili/');

        $callback = app(BridgeCallbackUrlResolver::class)->callbackUrl($installation);

        $this->assertSame('https://www.clubalfa.it/automobili/core-auth/callback', $callback);
    }

    public function test_authorization_code_writes_redirect_uri_and_keeps_redirect_url_compatibility(): void
    {
        [$installation] = $this->claimedInstallation('/');
        $user = User::factory()->create();

        $result = app(AuthorizationCodeService::class)->create(
            user: $user,
            site: $installation->site,
            bridgeInstallation: $installation,
            origin: $installation->origin,
            redirectUri: 'https://www.clubalfa.it/core-auth/callback',
            state: 'state-1',
            nonce: 'nonce-1',
        );

        $record = $result['record']->fresh();

        $this->assertNotSame($result['code'], DB::table('auth_authorization_codes')->whereKey($record->id)->value('code_hash'));
        $this->assertSame('https://www.clubalfa.it/core-auth/callback', $record->redirect_url);
        $this->assertSame('https://www.clubalfa.it/core-auth/callback', $record->redirect_uri);
        $this->assertSame($record->redirect_uri, $record->effective_redirect_uri);

        $record->forceFill(['redirect_uri' => null])->save();
        $this->assertSame($record->redirect_url, $record->fresh()->effective_redirect_uri);
    }

    public function test_exchange_code_requires_hmac_and_consumes_code_once(): void
    {
        [$installation, $secret] = $this->claimedInstallation('/');
        $user = User::factory()->create();
        $result = app(AuthorizationCodeService::class)->create(
            user: $user,
            site: $installation->site,
            bridgeInstallation: $installation,
            origin: $installation->origin,
            redirectUri: 'https://www.clubalfa.it/core-auth/callback',
            state: 'state-1',
            nonce: 'nonce-1',
        );

        $this->postJson('/auth/exchange-code', ['code' => $result['code']])->assertUnauthorized();

        $body = json_encode(['code' => $result['code'], 'state' => 'state-1'], JSON_THROW_ON_ERROR);

        $this->withHeaders($this->hmacHeaders($installation, $secret, 'POST', '/auth/exchange-code', $body))
            ->json('POST', '/auth/exchange-code', ['code' => $result['code'], 'state' => 'state-1'])
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('payload.session.site_code', 'clubalfa_it');

        $this->assertSame('consumed', AuthAuthorizationCode::query()->firstOrFail()->status);

        $this->withHeaders($this->hmacHeaders($installation, $secret, 'POST', '/auth/exchange-code', $body))
            ->json('POST', '/auth/exchange-code', ['code' => $result['code'], 'state' => 'state-1'])
            ->assertUnprocessable();
    }

    public function test_ppids_are_site_scoped_and_network_scoped(): void
    {
        $user = User::factory()->create();
        [$clubAlfa] = $this->siteAndOrigin('/');
        $motor = Site::query()->create([
            'code' => 'motorisumotori_it',
            'name' => 'MotoriSuMotori',
            'canonical_origin' => 'https://www.motorisumotori.it',
            'language' => 'it',
            'push_group' => 'motorisumotori_it',
        ]);

        $service = app(PpidService::class);
        $clubPpid = $service->siteScoped($user, $clubAlfa);
        $motorPpid = $service->siteScoped($user, $motor);
        $networkPpid = $service->networkScoped($user);

        $this->assertNotSame($clubPpid->ppid, $motorPpid->ppid);
        $this->assertSame($networkPpid->ppid, $service->networkScoped($user)->ppid);
    }

    public function test_filament_provider_secret_fields_are_write_only(): void
    {
        $providerSource = file_get_contents(app_path('Filament/Resources/AuthProviderResource.php'));
        $overrideSource = file_get_contents(app_path('Filament/Resources/AuthProviderSiteSettingResource.php'));

        $this->assertIsString($providerSource);
        $this->assertIsString($overrideSource);
        $this->assertStringContainsString('Write-only encrypted provider secrets', $providerSource);
        $this->assertStringContainsString('Write-only encrypted override secrets', $overrideSource);
        $this->assertStringNotContainsString("TextColumn::make('encrypted_config_json')", $providerSource.$overrideSource);
        $this->assertTrue(AuthProviderResource::canCreate());
        $this->assertTrue(AuthProviderSiteSettingResource::canCreate());
    }

    /**
     * @return array{Site, SiteOrigin}
     */
    private function siteAndOrigin(string $pathPrefix): array
    {
        $pushGroup = PushGroup::query()->where('code', 'clubalfa_it')->firstOrFail();
        $site = Site::query()->create([
            'code' => 'clubalfa_it',
            'name' => 'ClubAlfa IT',
            'canonical_origin' => 'https://www.clubalfa.it',
            'language' => 'it',
            'push_group' => 'clubalfa_it',
            'push_group_id' => $pushGroup->id,
        ]);
        $origin = $site->origins()->create([
            'origin' => 'https://www.clubalfa.it',
            'path_prefix' => $pathPrefix,
            'is_primary' => $pathPrefix === '/',
        ]);

        return [$site, $origin];
    }

    /**
     * @return array{BridgeInstallation, string}
     */
    private function claimedInstallation(string $pathPrefix): array
    {
        [$site, $origin] = $this->siteAndOrigin($pathPrefix);
        $token = app(BridgeTokenFactory::class)->create($site, $site->pushGroup, $origin)['token'];
        $response = $this->postJson('/api/bridge/setup/claim', [
            'setup_token' => $token,
            'wp_home_url' => 'https://www.clubalfa.it',
            'wp_site_url' => 'https://www.clubalfa.it',
            'detected_origin' => 'https://www.clubalfa.it',
            'detected_base_path' => $pathPrefix,
            'plugin_version' => '0.1.0',
        ])->assertCreated();

        return [BridgeInstallation::query()->where('uuid', $response->json('bridge_installation_id'))->firstOrFail(), $response->json('bridge_secret')];
    }

    /**
     * @return array<string, string>
     */
    private function hmacHeaders(BridgeInstallation $installation, string $secret, string $method, string $path, string $body = ''): array
    {
        $timestamp = (string) now()->timestamp;
        $nonce = 'test-nonce';
        $canonical = implode("\n", [
            $method,
            $path,
            $timestamp,
            $nonce,
            hash('sha256', $body),
        ]);

        return [
            'X-Core-Bridge-Id' => $installation->uuid,
            'X-Core-Timestamp' => $timestamp,
            'X-Core-Nonce' => $nonce,
            'X-Core-Signature' => hash_hmac('sha256', $canonical, $secret),
        ];
    }
}
