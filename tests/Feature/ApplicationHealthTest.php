<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationHealthTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_and_health_endpoint_are_available(): void
    {
        $this->get('/')->assertOk();
        $this->get('/login')->assertOk();
        $this->get('/cadastro')->assertOk();
        $this->get('/up')->assertOk();
    }

    public function test_dashboard_requires_authentication(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_service_worker_never_caches_dynamic_pages(): void
    {
        $serviceWorker = file_get_contents(public_path('service-worker.js'));

        $this->assertIsString($serviceWorker);
        $this->assertStringContainsString("request.mode === 'navigate'", $serviceWorker);
        $this->assertStringContainsString('caches.delete(cacheName)', $serviceWorker);
        $this->assertStringNotContainsString("const URLS = ['/", $serviceWorker);
    }
}
