<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('push_groups', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('language', 16)->nullable()->index();
            $table->string('status')->default('active')->index();
            $table->string('manifest_id')->nullable();
            $table->string('manifest_name')->nullable();
            $table->string('manifest_short_name')->nullable();
            $table->string('manifest_scope')->nullable();
            $table->string('manifest_start_url')->nullable();
            $table->string('service_worker_url')->nullable();
            $table->string('service_worker_scope')->nullable();
            $table->string('sw_version')->nullable();
            $table->json('pwa_config_json')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('sites', function (Blueprint $table): void {
            $table->foreignId('push_group_id')
                ->nullable()
                ->after('push_group')
                ->constrained('push_groups')
                ->nullOnDelete();
        });

        Schema::table('legacy_push_apps', function (Blueprint $table): void {
            $table->foreignId('push_group_id')
                ->nullable()
                ->after('merge_group')
                ->constrained('push_groups')
                ->nullOnDelete();
        });

        Schema::table('push_subscriptions', function (Blueprint $table): void {
            $table->foreignId('push_group_id')
                ->nullable()
                ->after('merge_group')
                ->constrained('push_groups')
                ->nullOnDelete();
        });

        $now = now();

        foreach ($this->pushGroups() as $code => $group) {
            DB::table('push_groups')->updateOrInsert(
                ['code' => $code],
                [
                    'name' => $group['name'],
                    'description' => $group['description'] ?? null,
                    'language' => $group['language'] ?? null,
                    'status' => $group['status'] ?? 'active',
                    'manifest_id' => $group['manifest_id'] ?? null,
                    'manifest_name' => $group['manifest_name'] ?? null,
                    'manifest_short_name' => $group['manifest_short_name'] ?? null,
                    'manifest_scope' => $group['manifest_scope'] ?? null,
                    'manifest_start_url' => $group['manifest_start_url'] ?? null,
                    'service_worker_url' => $group['service_worker_url'] ?? null,
                    'service_worker_scope' => $group['service_worker_scope'] ?? null,
                    'sw_version' => $group['sw_version'] ?? 'core-clean-v1',
                    'pwa_config_json' => isset($group['pwa_config_json']) ? json_encode($group['pwa_config_json'], JSON_THROW_ON_ERROR) : null,
                    'metadata_json' => isset($group['metadata_json']) ? json_encode($group['metadata_json'], JSON_THROW_ON_ERROR) : null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }

        $groups = DB::table('push_groups')->pluck('id', 'code');

        foreach ($groups as $code => $id) {
            DB::table('sites')
                ->where('push_group', $code)
                ->update(['push_group_id' => $id]);

            DB::table('legacy_push_apps')
                ->where('merge_group', $code)
                ->update(['push_group_id' => $id]);

            DB::table('push_subscriptions')
                ->where('merge_group', $code)
                ->update(['push_group_id' => $id]);
        }
    }

    public function down(): void
    {
        Schema::table('push_subscriptions', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('push_group_id');
        });

        Schema::table('legacy_push_apps', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('push_group_id');
        });

        Schema::table('sites', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('push_group_id');
        });

        Schema::dropIfExists('push_groups');
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function pushGroups(): array
    {
        return [
            'clubalfa_it' => [
                'name' => 'ClubAlfa.it',
                'language' => 'it',
                'manifest_id' => '/pwa/clubalfa-it',
                'manifest_name' => 'ClubAlfa.it',
                'manifest_short_name' => 'ClubAlfa',
                'manifest_scope' => '/',
                'manifest_start_url' => '/pwa-start/?app=clubalfa_it',
                'service_worker_url' => '/smart_sw.js',
                'service_worker_scope' => '/',
                'pwa_config_json' => [
                    'description' => 'Notizie auto, Alfa Romeo, Stellantis e motori.',
                    'categories' => ['news', 'automotive'],
                    'background_color' => '#eceff1',
                    'theme_color' => '#d12711',
                    'display' => 'standalone',
                    'lang' => 'it-IT',
                    'dir' => 'ltr',
                    'icons' => $this->clubAlfaIcons(),
                ],
            ],
            'clubalfa_en' => [
                'name' => 'ClubAlfa - Global',
                'language' => 'en',
                'manifest_id' => '/pwa/clubalfa-en',
                'manifest_name' => 'ClubAlfa - Global',
                'manifest_short_name' => 'ClubAlfa',
                'manifest_scope' => '/en/',
                'manifest_start_url' => '/en/pwa-start/?app=clubalfa_en',
                'service_worker_url' => '/en/smart_sw.js',
                'service_worker_scope' => '/en/',
                'pwa_config_json' => [
                    'description' => 'Automotive news in English.',
                    'categories' => ['news', 'automotive'],
                    'background_color' => '#eceff1',
                    'theme_color' => '#d12711',
                    'display' => 'standalone',
                    'orientation' => 'portrait',
                    'lang' => 'en',
                    'dir' => 'ltr',
                    'icons' => $this->clubAlfaIcons(),
                ],
            ],
            'motorisumotori_it' => [
                'name' => 'Motorisumotori.it',
                'language' => 'it',
                'service_worker_url' => '/smart_sw.js',
                'service_worker_scope' => '/',
            ],
            'mbenz_it' => [
                'name' => 'Mbenz.it',
                'language' => 'it',
                'service_worker_url' => '/smart_sw.js',
                'service_worker_scope' => '/',
            ],
            'notizieauto_it' => [
                'name' => 'NotizieAuto.it',
                'language' => 'it',
                'service_worker_url' => '/smart_sw.js',
                'service_worker_scope' => '/',
            ],
            'alfavirtualclub_it' => [
                'name' => 'AlfaVirtualClub.it',
                'language' => 'it',
                'service_worker_url' => '/smart_sw.js',
                'service_worker_scope' => '/',
            ],
            'robotica_news' => [
                'name' => 'Robotica.news',
                'language' => 'it',
                'service_worker_url' => '/smart_sw.js',
                'service_worker_scope' => '/',
            ],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function clubAlfaIcons(): array
    {
        return [
            [
                'src' => '/android-chrome-192x192.png',
                'sizes' => '192x192',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
            [
                'src' => '/clubalfa-pwa-logo-192-msk.png',
                'sizes' => '192x192',
                'type' => 'image/png',
                'purpose' => 'maskable',
            ],
            [
                'src' => '/android-chrome-512x512.png',
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
            [
                'src' => '/clubalfa-pwa-logo-512-msk.png',
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'maskable',
            ],
        ];
    }
};
