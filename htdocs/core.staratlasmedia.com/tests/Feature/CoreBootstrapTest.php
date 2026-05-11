<?php

namespace Tests\Feature;

use Tests\TestCase;

class CoreBootstrapTest extends TestCase
{
    public function test_health_endpoint_is_available(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response
            ->assertOk()
            ->assertJson([
                'status' => 'ok',
                'service' => 'star-atlas-core',
            ]);
    }

    public function test_cors_allows_only_exact_configured_origins(): void
    {
        $allowed = $this->withHeader('Origin', 'https://www.clubalfa.it')
            ->getJson('/api/v1/health');

        $allowed
            ->assertHeader('Access-Control-Allow-Origin', 'https://www.clubalfa.it')
            ->assertHeader('Vary', 'Origin');

        $blocked = $this->withHeader('Origin', 'https://clubalfa.it')
            ->getJson('/api/v1/health');

        $blocked
            ->assertOk()
            ->assertHeaderMissing('Access-Control-Allow-Origin')
            ->assertHeader('Vary', 'Origin');
    }

    public function test_default_admin_path_is_not_registered(): void
    {
        $this->get('/admin')->assertNotFound();
    }
}
