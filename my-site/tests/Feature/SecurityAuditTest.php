<?php

namespace Tests\Feature;

use App\Models\Idea;
use App\Models\Season;
use App\Models\SuggestedIdea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Проверки пары 5: SQL-инъекции и XSS, плюс защита модерации.
 */
class SecurityAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        return $admin;
    }

    private function pending(string $title = 'Идея на проверку'): SuggestedIdea
    {
        Season::firstOrCreate(['name' => 'зима']);

        return SuggestedIdea::create([
            'user_id' => User::factory()->create()->id,
            'title' => $title,
            'description' => 'Описание',
            'status' => 'pending',
            'created_at' => now(),
            'seasons' => ['зима'],
            'location_types' => ['дома'],
        ]);
    }

    // ---------- SQL-инъекции ----------

    public function test_sql_payload_in_filter_is_treated_as_data(): void
    {
        Idea::create(['title' => 'Лыжи', 'status' => 'active', 'created_at' => now()]);

        foreach (["' OR '1'='1", "зима') OR 1=1 --", "'; DROP TABLE ideas; --"] as $payload) {
            $this->get('/?mode=filters&seasons[]='.urlencode($payload))
                ->assertOk()
                ->assertSee('Подходящих занятий не нашлось');
        }

        $this->assertTrue(Schema::hasTable('ideas'));
        $this->assertSame(1, Idea::count());
    }

    public function test_sql_payload_in_catalog_filter_is_treated_as_data(): void
    {
        Idea::create(['title' => 'Лыжи', 'status' => 'active', 'created_at' => now()]);

        $this->get('/catalog?budgets[]='.urlencode("' OR 1=1 --"))
            ->assertOk()
            ->assertDontSee('Лыжи');
    }

    // ---------- XSS ----------

    public function test_script_in_idea_title_is_escaped_in_catalog(): void
    {
        Idea::create([
            'title' => "<script>alert('XSS')</script>",
            'status' => 'active',
            'created_at' => now(),
        ]);

        $this->get('/catalog')
            ->assertOk()
            ->assertDontSee("<script>alert('XSS')</script>", false)
            ->assertSee('&lt;script&gt;', false);
    }

    public function test_script_in_suggested_idea_is_escaped_for_admin(): void
    {
        $this->pending('<img src=x onerror=alert(1)>');

        $this->actingAs($this->admin())
            ->get('/admin/suggested-ideas')
            ->assertOk()
            ->assertDontSee('<img src=x onerror=alert(1)>', false)
            ->assertSee('&lt;img src=x onerror=alert(1)&gt;', false);
    }

    // ---------- проверка входных данных формы «Предложить идею» ----------

    public function test_suggest_rejects_unknown_filter_value_and_long_text(): void
    {
        Season::firstOrCreate(['name' => 'зима']);
        $user = User::factory()->create();

        $this->actingAs($user)->post('/suggest', [
            'title' => 'Нормальное название',
            'description' => str_repeat('а', 2001),
            'seasons' => ['не-существует'],
        ])->assertSessionHasErrors(['description', 'seasons.0']);

        $this->assertSame(0, SuggestedIdea::count());
    }

    public function test_suggest_ignores_status_and_user_id_from_request(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        foreach ([
            'seasons' => 'зима', 'companies' => 'один', 'activity_levels' => 'активно',
            'budgets' => 'бесплатно', 'durations' => 'час', 'location_types' => 'дома',
        ] as $table => $name) {
            \Illuminate\Support\Facades\DB::table($table)->insert(['name' => $name]);
        }

        $this->actingAs($user)->post('/suggest', [
            'title' => 'Новая идея',
            'seasons' => ['зима'], 'companies' => ['один'], 'activity_levels' => ['активно'],
            'budgets' => ['бесплатно'], 'durations' => ['час'], 'location_types' => ['дома'],
            'status' => 'approved',
            'user_id' => $other->id,
        ])->assertRedirect();

        $saved = SuggestedIdea::firstOrFail();
        $this->assertSame('pending', $saved->status);
        $this->assertSame($user->id, $saved->user_id);
    }

    // ---------- модерация ----------

    public function test_regular_user_cannot_approve(): void
    {
        $suggested = $this->pending();

        $this->actingAs(User::factory()->create())
            ->post('/admin/suggested-ideas/'.$suggested->id.'/approve')
            ->assertRedirect('/');

        $this->assertSame('pending', $suggested->fresh()->status);
        $this->assertSame(0, Idea::count());
    }

    public function test_repeated_approve_does_not_create_duplicate_idea(): void
    {
        $suggested = $this->pending();
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/suggested-ideas/'.$suggested->id.'/approve');
        $this->actingAs($admin)->post('/admin/suggested-ideas/'.$suggested->id.'/approve');

        $this->assertSame(1, Idea::count());
        $this->assertSame('approved', $suggested->fresh()->status);
    }

    public function test_reviewed_idea_cannot_be_rejected_afterwards(): void
    {
        $suggested = $this->pending();
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/suggested-ideas/'.$suggested->id.'/approve');
        $this->actingAs($admin)->post('/admin/suggested-ideas/'.$suggested->id.'/reject');

        $this->assertSame('approved', $suggested->fresh()->status);
    }
}
