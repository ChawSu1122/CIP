<?php

namespace Tests\Feature;

use App\Models\AuthSecurityEvent;
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

    public function test_revocation_latency_page_shows_comparison_result_banner(): void
    {
        $response = $this->get('/dashboard/revocation-latency');

        $response->assertStatus(200);
        $response->assertSee('Comparison Result');
        $response->assertSee('Session is winner because Revocation Latency of Session is less than Revocation Latency of Token.');
    }

    public function test_session_logout_redirects_to_session_login_page(): void
    {
        $victim = User::factory()->create([
            'name' => 'Bob',
            'email' => 'bob@gmail.com',
        ]);

        $this->actingAs($victim)
            ->withSession(['victim_authentication_type' => 'session']);

        $this->post(route('logout'))
            ->assertRedirect(route('session.login'));
    }

    public function test_token_logout_redirects_to_token_login_page(): void
    {
        $victim = User::factory()->create([
            'name' => 'Bob',
            'email' => 'bob@gmail.com',
        ]);

        $this->actingAs($victim)
            ->withSession(['victim_authentication_type' => 'token']);

        $this->post(route('logout'))
            ->assertRedirect(route('token.login'));
    }

    public function test_reset_clears_active_phished_credentials(): void
    {
        ExperimentMetric::create([
            'auth_type' => 'phish',
            'action' => 'link_clicked',
            'method' => 'GET',
            'path' => '/phish',
            'duration_ms' => 0,
            'memory_usage' => 0,
            'query_count' => 0,
            'storage_bytes' => 0,
            'success' => true,
            'victim_name' => 'Bob',
            'victim_email' => 'bob@gmail.com',
            'victim_authentication_type' => 'session',
            'victim_session_id' => 'session-abc',
            'victim_token' => null,
        ]);

        ExperimentMetric::create([
            'auth_type' => 'phish',
            'action' => 'link_clicked',
            'method' => 'GET',
            'path' => '/phish',
            'duration_ms' => 0,
            'memory_usage' => 0,
            'query_count' => 0,
            'storage_bytes' => 0,
            'success' => true,
            'victim_name' => 'Alice',
            'victim_email' => 'alice@gmail.com',
            'victim_authentication_type' => 'token',
            'victim_session_id' => null,
            'victim_token' => 'captured-token-xyz',
        ]);

        $this->postJson(route('dashboard.revocation-latency.reset-captured-credentials'))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSame(
            0,
            ExperimentMetric::where('auth_type', 'phish')
                ->where('action', 'link_clicked')
                ->count()
        );
    }

    public function test_reset_clears_pending_security_alerts(): void
    {
        $user = User::factory()->create([
            'name' => 'Bob',
            'email' => 'bob@gmail.com',
        ]);

        AuthSecurityEvent::create([
            'user_id' => $user->id,
            'type' => 'token',
            'status' => 'pending',
            'message' => 'Someone is trying to use your account.',
            'payload' => ['token' => 'captured-token-123'],
        ]);

        $this->postJson(route('dashboard.revocation-latency.reset-captured-credentials'))
            ->assertOk();

        $this->assertSame(
            0,
            AuthSecurityEvent::where('user_id', $user->id)
                ->where('status', 'pending')
                ->count()
        );
    }
}
