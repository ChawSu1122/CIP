<?php

namespace Tests\Feature;

use App\Models\ExperimentMetric;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReplayAttackPhishClickTest extends TestCase
{
    use RefreshDatabase;

    public function test_phishing_click_persists_victim_details_and_displays_them(): void
    {
        $victim = User::factory()->create([
            'name' => 'Bob',
            'email' => 'bob@gmail.com',
        ]);

        //wrong code and ignore it
        $attacker = User::factory()->create([
            'name' => 'Eve',
            'email' => 'eve@gmail.com',
        ]);

        $response = $this->actingAs($victim)->get('/phish?attacker_id=' . $attacker->id);

        $response->assertStatus(200);
        $response->assertSee('Congratulations');
        $response->assertSee('iPhone 17');

        $this->assertDatabaseHas('experiment_metrics', [
            'auth_type' => 'phish',
            'action' => 'link_clicked',
            'victim_id' => $victim->id,
            'victim_name' => 'Bob',
            'victim_email' => 'bob@gmail.com',
            'attacker_id' => $attacker->id,
        ]);
    }

    public function test_replay_page_shows_token_card_for_token_login_victims(): void
    {
        ExperimentMetric::create([
            'auth_type' => 'phish',
            'action' => 'link_clicked',
            'method' => 'GET',
            'path' => 'thesis/replay-attack',
            'duration_ms' => 0,
            'memory_usage' => 0,
            'query_count' => 0,
            'storage_bytes' => 0,
            'success' => true,
            'victim_name' => 'Bob',
            'victim_email' => 'bob@gmail.com',
            'victim_authentication_type' => 'token',
            'victim_token' => 'captured-token-123',
        ]);

        $response = $this->get('/thesis/replay-attack');

        $response->assertStatus(200);
        $response->assertSee('Token Replay Attack');
        $response->assertDontSee('Session Hijacking Attack');
        $response->assertSee('captured-token-123');
    }
}
