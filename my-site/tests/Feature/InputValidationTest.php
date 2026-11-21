<?php

namespace Tests\Feature;

use App\Models\Idea;
use App\Models\SuggestedIdea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Tests\TestCase;

/**
 * Пара 6: проверка входных данных на сервере (длины, диапазоны, белые списки).
 */
class InputValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    /** Справочники, без которых форма «Предложить идею» ничего не примет */
    private function refs(): void
    {
        foreach ([
            'seasons' => 'зима', 'companies' => 'один', 'activity_levels' => 'активно',
            'budgets' => 'бесплатно', 'durations' => 'час', 'location_types' => 'дома',
        ] as $table => $name) {
            DB::table($table)->insert(['name' => $name]);
        }
    }

    private function validIdea(array $override = []): array
    {
        return array_merge([
            'title' => 'Каток вечером',
            'description' => 'Взять коньки и горячий чай.',
            'seasons' => ['зима'],
            'companies' => ['один'],
            'activity_levels' => ['активно'],
            'budgets' => ['бесплатно'],
            'durations' => ['час'],
            'location_types' => ['дома'],
        ], $override);
    }

    // ---------- регистрация и вход ----------

    public function test_registration_rejects_too_long_and_too_short_values(): void
    {
        $this->post('/register', [
            'name' => str_repeat('а', 101),
            'email' => str_repeat('a', 250).'@x.com',
            'password' => str_repeat('a', 129),
            'password_confirmation' => str_repeat('a', 129),
        ])->assertSessionHasErrors(['name', 'email', 'password']);

        $this->post('/register', [
            'name' => 'А',
            'email' => 'short@example.com',
            'password' => 'password-123',
            'password_confirmation' => 'password-123',
        ])->assertSessionHasErrors(['name']);

        $this->assertSame(0, User::count());
    }

    public function test_password_of_9_characters_is_rejected_and_long_phrase_with_spaces_is_accepted(): void
    {
        $this->post('/register', [
            'name' => 'Анна',
            'email' => 'anna@example.com',
            'password' => '123456789',
            'password_confirmation' => '123456789',
        ])->assertSessionHasErrors('password');

        $phrase = 'Это очень длинная парольная фраза 2026';
        $this->post('/register', [
            'name' => 'Анна',
            'email' => 'anna@example.com',
            'password' => $phrase,
            'password_confirmation' => $phrase,
        ])->assertSessionHasNoErrors()->assertRedirect(route('home', absolute: false));
    }

    public function test_registration_ignores_is_admin_from_request(): void
    {
        $this->post('/register', [
            'name' => 'Хитрый',
            'email' => 'sly@example.com',
            'password' => 'password-123',
            'password_confirmation' => 'password-123',
            'is_admin' => 1,
            'role' => 'admin',
        ]);

        $user = User::where('email', 'sly@example.com')->firstOrFail();
        $this->assertFalse($user->isAdmin());
    }

    public function test_login_rejects_oversized_password(): void
    {
        $this->post('/login', [
            'email' => 'user@example.com',
            'password' => str_repeat('a', 129),
        ])->assertSessionHasErrors('password');
    }

    // ---------- фильтры и номер страницы ----------

    public function test_filter_values_are_limited(): void
    {
        $this->get('/?mode=filters&seasons[]='.str_repeat('а', 101))
            ->assertSessionHasErrors('seasons.0');

        $many = collect(range(1, 21))->map(fn ($i) => 'seasons[]=v'.$i)->implode('&');
        $this->get('/?mode=filters&'.$many)->assertSessionHasErrors('seasons');

        $this->get('/?mode=hack')->assertSessionHasErrors('mode');
    }

    public function test_page_number_must_be_an_integer_in_range(): void
    {
        foreach (['abc', '0', '-5', '100001', '1.5'] as $page) {
            $this->get('/catalog?page='.$page)->assertSessionHasErrors('page');
        }

        $this->get('/catalog?page=1')->assertSessionHasNoErrors();

        $user = User::factory()->create();
        $this->actingAs($user)->get('/favorites?page=abc')->assertSessionHasErrors('page');
        $this->actingAs($user)->get('/history?page=abc')->assertSessionHasErrors('page');
    }

    // ---------- «Предложить идею» ----------

    public function test_suggest_shows_human_messages_per_field(): void
    {
        $this->refs();
        $user = User::factory()->create();

        $this->actingAs($user)->post('/suggest', $this->validIdea([
            'title' => 'ab',
            'description' => str_repeat('а', 2001),
            'seasons' => [],
        ]))->assertSessionHasErrors([
            'title' => 'Название должно быть не короче 3 символов.',
            'description' => 'Описание не должно быть длиннее 2000 символов.',
            'seasons' => 'Выберите хотя бы одно значение в поле «Сезон».',
        ]);

        $this->assertSame(0, SuggestedIdea::count());
    }

    public function test_suggest_title_length_boundaries(): void
    {
        $this->refs();
        $user = User::factory()->create();

        $this->actingAs($user)->post('/suggest', $this->validIdea(['title' => str_repeat('а', 201)]))
            ->assertSessionHasErrors(['title' => 'Название не должно быть длиннее 200 символов.']);

        $this->actingAs($user)->post('/suggest', $this->validIdea(['title' => str_repeat('а', 200)]))
            ->assertSessionHasNoErrors();
    }

    public function test_suggest_rejects_values_not_from_reference_lists(): void
    {
        $this->refs();
        $user = User::factory()->create();

        $this->actingAs($user)->post('/suggest', $this->validIdea(['seasons' => ['не-существует']]))
            ->assertSessionHasErrors(['seasons.0' => 'Выбранного значения нет в списке поля «Сезон».']);

        $this->actingAs($user)->post('/suggest', $this->validIdea(['seasons' => ['зима', 'зима']]))
            ->assertSessionHasErrors('seasons.0');

        $eleven = collect(range(1, 11))->map(fn ($i) => 'v'.$i)->all();
        $this->actingAs($user)->post('/suggest', $this->validIdea(['seasons' => $eleven]))
            ->assertSessionHasErrors(['seasons' => 'В поле «Сезон» можно выбрать не больше 10 значений.']);

        $this->actingAs($user)->post('/suggest', $this->validIdea(['seasons' => 'зима']))
            ->assertSessionHasErrors('seasons');

        $this->assertSame(0, SuggestedIdea::count());
    }

    public function test_suggest_accepts_valid_idea_and_server_sets_service_fields(): void
    {
        $this->refs();
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($user)->post('/suggest', $this->validIdea([
            'status' => 'approved',
            'user_id' => $other->id,
        ]))->assertSessionHasNoErrors()->assertRedirect(route('home', absolute: false));

        $saved = SuggestedIdea::firstOrFail();
        $this->assertSame('pending', $saved->status);
        $this->assertSame($user->id, $saved->user_id);
    }

    public function test_suggest_form_page_shows_all_errors_in_one_list(): void
    {
        $this->refs();
        $user = User::factory()->create();

        $this->actingAs($user)->from('/suggest')
            ->post('/suggest', $this->validIdea(['title' => 'ab', 'description' => str_repeat('а', 2001)]))
            ->assertRedirect('/suggest');

        $this->actingAs($user)->get('/suggest')
            ->assertOk()
            ->assertSee('Название должно быть не короче 3 символов.')
            ->assertSee('Описание не должно быть длиннее 2000 символов.');
    }

    public function test_file_sent_to_form_is_ignored_and_not_stored(): void
    {
        $this->refs();
        Storage::fake('local');
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)->post('/suggest', $this->validIdea([
            'photo' => UploadedFile::fake()->create('shell.php', 10, 'application/x-php'),
        ]))->assertSessionHasNoErrors();

        $this->assertSame([], Storage::disk('local')->allFiles());
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    // ---------- номера в адресах и статусы ----------

    public function test_non_numeric_ids_in_urls_give_404(): void
    {
        $this->post('/favorites/abc')->assertNotFound();

        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        $this->actingAs($admin)->post('/admin/suggested-ideas/abc/approve')->assertNotFound();
        $this->actingAs($admin)->post('/admin/suggested-ideas/1;DROP/reject')->assertNotFound();
    }

    public function test_status_outside_the_whitelist_cannot_be_saved(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Idea::create(['title' => 'Идея', 'status' => 'hacked', 'created_at' => now()]);
    }

    public function test_suggested_status_outside_the_whitelist_cannot_be_saved(): void
    {
        $this->expectException(InvalidArgumentException::class);

        SuggestedIdea::forceCreate([
            'user_id' => User::factory()->create()->id,
            'title' => 'Идея',
            'status' => 'hacked',
            'created_at' => now(),
        ]);
    }
}
