<?php

namespace Tests\Feature;

use App\Models\Idea;
use App\Models\Season;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RandomIdeaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function idea(string $title, string $season, string $status = 'active'): Idea
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

    public function test_home_page_is_public_and_has_buttons(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Случайное занятие')
            ->assertSee('Фильтры')
            ->assertSee('Предложить идею');
    }

    public function test_random_button_returns_active_idea_only(): void
    {
        $this->idea('Лыжи', 'зима');
        $this->idea('Скрытая идея', 'зима', 'rejected');

        foreach (range(1, 10) as $i) {
            $this->get('/?mode=any')->assertOk()->assertSee('Лыжи')->assertDontSee('Скрытая идея');
        }
    }

    public function test_filters_limit_the_choice(): void
    {
        $this->idea('Лыжи', 'зима');
        $this->idea('Купание', 'лето');

        foreach (range(1, 10) as $i) {
            $this->get('/?mode=filters&seasons[]=лето')
                ->assertOk()->assertSee('Купание')->assertDontSee('Лыжи');
        }
    }

    public function test_any_mode_ignores_selected_filters(): void
    {
        $this->idea('Лыжи', 'зима');

        $this->get('/?mode=any&seasons[]=лето')->assertOk()->assertSee('Лыжи');
    }

    public function test_message_when_nothing_found(): void
    {
        $this->idea('Лыжи', 'зима');

        $this->get('/?mode=filters&seasons[]=лето')
            ->assertOk()->assertSee('Подходящих занятий не нашлось');
    }

    public function test_bad_mode_is_rejected(): void
    {
        $this->get('/?mode=evil')->assertSessionHasErrors('mode');
    }

    public function test_html_in_idea_is_escaped(): void
    {
        $this->idea('<script>alert(1)</script>', 'зима');

        $this->get('/?mode=any')->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
    }

    public function test_guest_cannot_open_suggest_form_and_is_sent_to_login(): void
    {
        $this->get('/suggest')->assertRedirect('/login');
        $this->post('/suggest', ['title' => 'Идея'])->assertRedirect('/login');
    }

    public function test_logged_in_user_can_open_suggest_form(): void
    {
        $this->actingAs(User::factory()->create())->get('/suggest')->assertOk();
    }

    public function test_dashboard_requires_login_and_shows_random_block(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');

        $this->actingAs(User::factory()->create())
            ->get('/dashboard')->assertOk()->assertSee('Случайное занятие');
    }
}
