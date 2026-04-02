<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_uses_compact_sections(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Priorites');
        $response->assertSee('Pilotage admin');
        $response->assertSee('Activite recente');
        $response->assertDontSee('Encaissements 30/90 jours');
        $response->assertDontSee('Depenses 30/90 jours');
        $response->assertDontSee('Sorties stock importantes');
    }
}
