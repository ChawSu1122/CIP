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
        $user = User::factory()->create([
            'name' => 'Bob',
            'email' => 'bob@example.com',
        ]);

        $response = $this->actingAs($user)->get('/phish');

        $response->assertStatus(200);
        $response->assertSee('Congratulations!!');
        $response->assertSee('Bob');
        $response->assertSee('bob@example.com');

        $this->assertDatabaseHas('experiment_metrics', [
            'auth_type' => 'phish',
            'action' => 'link_clicked',
            'victim_id' => $user->id,
            'victim_name' => 'Bob',
            'victim_email' => 'bob@example.com',
            'attacker_id' => 53,
        ]);
    }
}
