<?php

namespace Tests\Feature;

use App\Models\Idea;
use App\Models\IdeaHistory;
use App\Models\Season;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogFavoritesHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function idea(string $title, string $season = 'зима', string $status = 'active'): Idea
    {
        $idea = Idea::create([
            'title' => $title,
            'description' => 'Описание '.$title,
            'status' => $status,
            'location_type' => 'дома',
            'created_at' => now(),
        ]);
        $idea->seasons()->attach(Season::firstOrCreate(['name' => $season])->id);

        return $idea;
    }

    // ---------- каталог ----------

    public function test_catalog_is_public_and_shows_only_active_ideas(): void
    {
        $this->idea('Лыжи');
        $this->idea('Скрытая', 'зима', 'rejected');

        $this->get('/catalog')->assertOk()->assertSee('Лыжи')->assertDontSee('Скрытая');
    }

    public function test_catalog_filters(): void
    {
        $this->idea('Лыжи', 'зима');
        $this->idea('Купание', 'лето');

        $this->get('/catalog?seasons[]=лето')->assertOk()->assertSee('Купание')->assertDontSee('Лыжи');
    }

    public function test_catalog_is_paginated(): void
    {
        foreach (range(1, 15) as $i) {
            $this->idea(sprintf('Занятие %02d', $i));
        }

        $this->get('/catalog')->assertOk()->assertSee('Занятие 01')->assertDontSee('Занятие 15');
        $this->get('/catalog?page=2')->assertOk()->assertSee('Занятие 15');
    }

    // ---------- избранное ----------

    public function test_guest_is_redirected_from_favorites_and_toggle(): void
    {
        $idea = $this->idea('Лыжи');

        $this->get('/favorites')->assertRedirect('/login');
        $this->post('/favorites/'.$idea->id)->assertRedirect('/login');
    }

    public function test_user_can_add_and_remove_favorite(): void
    {
        $user = User::factory()->create();
        $idea = $this->idea('Лыжи');

        $this->actingAs($user)->post('/favorites/'.$idea->id)->assertRedirect();
        $this->assertDatabaseHas('favorites', ['user_id' => $user->id, 'idea_id' => $idea->id]);
        $this->actingAs($user)->get('/favorites')->assertOk()->assertSee('Лыжи');

        $this->actingAs($user)->post('/favorites/'.$idea->id)->assertRedirect();
        $this->assertDatabaseMissing('favorites', ['user_id' => $user->id, 'idea_id' => $idea->id]);
    }

    public function test_cannot_favorite_hidden_idea(): void
    {
        $idea = $this->idea('Скрытая', 'зима', 'rejected');

        $this->actingAs(User::factory()->create())
            ->post('/favorites/'.$idea->id)->assertNotFound();
    }

    public function test_favorites_are_private_to_user(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $this->actingAs($a)->post('/favorites/'.$this->idea('Лыжи')->id);

        $this->actingAs($b)->get('/favorites')->assertOk()->assertDontSee('Лыжи');
    }

    // ---------- история ----------

    public function test_random_idea_is_recorded_in_history_for_logged_in_user(): void
    {
        $user = User::factory()->create();
        $idea = $this->idea('Лыжи');

        $this->actingAs($user)->get('/dashboard?mode=any')->assertOk()->assertSee('Лыжи');

        $this->assertDatabaseHas('idea_histories', ['user_id' => $user->id, 'idea_id' => $idea->id]);
        $this->actingAs($user)->get('/history')->assertOk()->assertSee('Лыжи');
    }

    public function test_guest_roll_is_not_recorded(): void
    {
        $this->idea('Лыжи');

        $this->get('/?mode=any')->assertOk();

        $this->assertSame(0, IdeaHistory::count());
    }

    public function test_history_requires_login_and_is_private(): void
    {
        $this->get('/history')->assertRedirect('/login');

        $a = User::factory()->create();
        $b = User::factory()->create();
        $this->idea('Лыжи');
        $this->actingAs($a)->get('/dashboard?mode=any');

        $this->actingAs($b)->get('/history')->assertOk()->assertDontSee('Лыжи');
    }

    public function test_user_can_clear_only_own_history(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $idea = $this->idea('Лыжи');
        IdeaHistory::create(['user_id' => $a->id, 'idea_id' => $idea->id]);
        IdeaHistory::create(['user_id' => $b->id, 'idea_id' => $idea->id]);

        $this->actingAs($a)->delete('/history')->assertRedirect('/history');

        $this->assertDatabaseMissing('idea_histories', ['user_id' => $a->id]);
        $this->assertDatabaseHas('idea_histories', ['user_id' => $b->id]);
    }

    // ---------- администратор ----------

    public function test_admin_link_visible_only_for_admin(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/dashboard')->assertOk()->assertDontSee('Модерация');

        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();
        $this->actingAs($admin)->get('/dashboard')->assertOk()->assertSee('Модерация');
    }

    public function test_regular_user_cannot_open_moderation(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin/suggested-ideas')->assertRedirect('/');
    }
}
