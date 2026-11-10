# Аудит SQL-инъекций и XSS

Команда: 4, проект: Рандомайзер досуга, стек: Laravel 13 (Breeze, Blade, Eloquent), база: SQLite

## 1. Итог

- **SQL-инъекции:** прямых уязвимостей нет. В коде нет ни одного «сырого» запроса: везде Eloquent и Query Builder, значения передаются отдельно от текста запроса (привязка параметров).
- **XSS:** прямых уязвимостей нет. Весь пользовательский текст выводится через `{{ }}` (экранируется). Ни одного `{!! !!}` в проекте нет.
- **Рядом найдено и исправлено четыре слабых места** (раздел 5): нестрогая проверка формы «Предложить идею», повторное одобрение одной заявки, лишний `HtmlString` в шаблоне, нет ограничения частоты отправки идей.

## 2. Поиск «чисто»: команды и результат

Команды выполняются в папке `my-site` (PowerShell: `Select-String -Path ... -Pattern ... -Recurse` или `git grep`; ниже `git grep`, он работает из любой папки репозитория):

| Что ищем | Команда | Результат |
|---|---|---|
| Сырые SQL-запросы | `git grep -n -E "DB::(select\|statement\|raw\|unprepared)\|whereRaw\|selectRaw\|orderByRaw\|havingRaw\|groupByRaw\|->raw\(" -- app routes resources database/seeders` | 0 совпадений |
| Вывод без экранирования | `git grep -n "{!!" -- resources` | 0 совпадений |
| Склейка строки в SQL | `git grep -n -E "\"(SELECT\|INSERT\|UPDATE\|DELETE)" -- app` | 0 совпадений |
| HTML, собранный в коде | `git grep -n "HtmlString\|new HtmlString" -- app resources` | 0 совпадений (был 1, см. раздел 5, пункт 3) |
| JS с данными пользователя | `git grep -n -E "innerHTML\|document\.write\|eval\(\|@json" -- resources` | 0 совпадений |

## 3. Таблица 1. Все места работы с базой

«Параметризован?»: да — значения передаются как параметры `?`, а не вставляются в текст запроса. «н/п» — запрос не использует ввод пользователя.

| № | Файл:строка | Что делает | Как собирается запрос | Параметризован? |
|---|---|---|---|---|
| 1 | app/Http/Controllers/RandomIdeaController.php:37,43 | выбор случайного занятия (главная и дашборд) | Eloquent: `where('status','active')` + фильтры + `inRandomOrder()->first()` | да |
| 2 | app/Support/IdeaFilter.php:68 | отбор по фильтрам из адресной строки | `whereHas(..., whereIn('<таблица>.name', $names))`; `$names` приходят от пользователя и уходят как параметры; имя таблицы берётся из константы `MAP` в коде, а не из запроса | да |
| 3 | app/Support/IdeaFilter.php:78–83 | списки значений для галочек | `Model::pluck('name')`, ввода нет | н/п |
| 4 | app/Http/Controllers/RandomIdeaController.php:47 | запись выпавшей идеи в историю | `IdeaHistory::create(['user_id'=>из сессии, 'idea_id'=>из базы])` | да |
| 5 | app/Http/Controllers/CatalogController.php:17,20 | каталог | то же, что №1–2 + `orderBy('title')` (константа) + `paginate(12)` (номер страницы приводится к числу) | да |
| 6 | app/Http/Controllers/FavoriteController.php:14–18 | список избранного | `favoriteIdeas()->where('ideas.status','active')->orderByPivot(...)->paginate(12)`, пользователь — из сессии | да |
| 7 | app/Http/Controllers/FavoriteController.php:32 | добавить/убрать из избранного | `toggle([...])`; `{idea}` из адреса ищется по ключу с параметром (route-model binding) | да |
| 8 | app/Http/Controllers/HistoryController.php:13–17, 25 | показать и очистить историю | `where('user_id', <из сессии>)`, `orderByDesc` (константы), `delete()` | да |
| 9 | app/Http/Controllers/SuggestedIdeaController.php:20–25 | справочники для формы | `Model::all()`, ввода нет | н/п |
| 10 | app/Http/Controllers/SuggestedIdeaController.php:60 | сохранить предложенную идею | `SuggestedIdea::create($validated)`: только проверенные поля, `user_id` и `status` ставит сервер; массивы фильтров пишутся в JSON-колонки | да |
| 11 | app/Http/Controllers/Admin/SuggestedIdeasController.php:15–17 | список заявок для админа | `with('user')->orderBy('created_at','desc')->get()`, ввода нет | н/п |
| 12 | app/Http/Controllers/Admin/SuggestedIdeasController.php:26–27 | открыть заявку для одобрения | `DB::transaction` + `whereKey($id)->lockForUpdate()->firstOrFail()`, `$id` из адреса | да |
| 13 | app/Http/Controllers/Admin/SuggestedIdeasController.php:40–85 | создать идею, привязать фильтры, сменить статус | `Idea::create`, `Model::whereIn('name', <массив из базы>)->pluck('id')`, `attach`, `update` | да |
| 14 | app/Http/Controllers/Admin/SuggestedIdeasController.php:94–100 | отклонить заявку | `findOrFail($id)`, `update(['status'=>'rejected'])` | да |
| 15 | app/Http/Controllers/Auth/RegisteredUserController.php:39; app/Http/Requests/Auth/LoginRequest.php:45 | регистрация и вход (Breeze) | `User::create([...хеш пароля...])`, `Auth::attempt(['email','password'])` | да |
| 16 | app/Http/Controllers/ProfileController.php:29–35, 53 | изменить и удалить свой профиль (Breeze) | `fill($request->validated())` (в `fillable` только name, email, password), `save()`, `delete()` | да |
| 17 | app/Models/User.php:51 | номера избранных идей | `favoriteIdeas()->pluck('ideas.id')` | н/п |
| 18 | database/migrations/*, database/seeders/* | схема и начальные данные | пишутся разработчиками в коде, ввода пользователей нет | н/п |

Вывод по таблице 1: вручную собранных запросов нет, текст SQL не содержит данных пользователя.

## 4. Таблица 2. Все выводы пользовательских данных в шаблоны

| № | Файл:строка | Что выводится | Откуда данные | Экранируется? |
|---|---|---|---|---|
| 1 | resources/views/partials/idea-card.blade.php:3, 5 | название и описание идеи | введено пользователем, прошло модерацию | да, `{{ }}` |
| 2 | resources/views/partials/idea-card.blade.php:11 | теги (сезон, бюджет и т. д.) | справочники из базы | да |
| 3 | resources/views/partials/idea-card.blade.php:35 | `$reroll` в атрибуте `value` | контроллер, значения только `any` или `filters` | да |
| 4 | resources/views/partials/filter-panel.blade.php:5, 9, 12 | подписи и значения галочек | справочники и константы | да (в том числе в атрибутах: экранируются кавычки) |
| 5 | resources/views/partials/random-idea.blade.php:3 | адрес формы | `route()` | да |
| 6 | resources/views/admin/suggested-ideas/index.blade.php:40, 61 | название и описание заявки | введено пользователем | да |
| 7 | resources/views/admin/suggested-ideas/index.blade.php:42–43 | имя и email автора заявки | введено при регистрации | да |
| 8 | resources/views/admin/suggested-ideas/index.blade.php:68–108 | массивы фильтров из JSON | введено через форму, проверено по справочникам | да |
| 9 | resources/views/admin/suggested-ideas/index.blade.php:14, 21 | `session('success')`, `session('error')` (в тексте есть название идеи) | контроллер | да |
| 10 | resources/views/history/index.blade.php:22, 24 | название и сокращённое описание идеи | как №1 | да |
| 11 | resources/views/suggest/create.blade.php:17, 28 | `old('title')`, `old('description')` (вернуть введённое в форму) | ввод пользователя | да |
| 12 | resources/views/suggest/create.blade.php:20–135 | сообщения об ошибках валидации; значения и подписи галочек | Laravel; справочники | да |
| 13 | public-layout.blade.php:28, 31; dashboard.blade.php:12; history/index.blade.php:9; suggest/create.blade.php:7 | `session('success')`, `session('error')` | контроллеры | да |
| 14 | resources/views/catalog/index.blade.php:20, 26; favorites/index.blade.php:20; history/index.blade.php:33 | число найденного; `links()` (пагинация) | Laravel | да (число); `links()` — готовый HTML Laravel, параметры в нём экранируются |
| 15 | resources/views/components/input-error.blade.php:6; modal.blade.php:42–43; auth-session-status.blade.php:5 | сообщения об ошибках, имена окон (Breeze) | Laravel, константы | да |
| 16 | `<script>` в dashboard.blade.php и partials/random-idea.blade.php | данные пользователя в JavaScript | не передаются | н/п |

Вывод по таблице 2: все выводы идут через `{{ }}`. Конструкции `{!! !!}` нет. Данные пользователя в JavaScript и в атрибуты `href`, `src`, `style` не попадают.

## 5. Разбор демонстрации атаки (только свой проект и своя локальная база)

### 5.1. SQL-инъекция через фильтр

Что делаем: подставляем в фильтр «Сезон» строку-атаку вместо названия сезона.
Адрес для проверки: `http://127.0.0.1:8000/?mode=filters&seasons[]=%27%20OR%20%271%27%3D%271`
(в запросе: `' OR '1'='1`).

Ожидание для уязвимого кода: условие `OR '1'='1'` всегда истинно, вернулись бы все идеи.
Результат на нашем проекте: «Подходящих занятий не нашлось». Строка атаки обработана как название сезона, которого нет.

Запрос, который выполняется (его можно посмотреть через `php artisan tinker`):

```php
DB::enableQueryLog();
$q = App\Models\Idea::query()->where('status','active');
App\Support\IdeaFilter::apply($q, ['seasons' => ["' OR '1'='1"]]);
$q->pluck('title');
DB::getQueryLog();
```

Вывод:

```
SQL:      select "title" from "ideas" where "status" = ? and exists (select * from "seasons"
          inner join "idea_seasons" on "seasons"."id" = "idea_seasons"."season_id"
          where "ideas"."id" = "idea_seasons"."idea_id" and "seasons"."name" in (?))
bindings: ["active", "' OR '1'='1"]
найдено:  []
```

Разбор: в тексте запроса стоят только знаки `?`. Строка атаки находится во втором списке (`bindings`) и передаётся базе отдельно, как значение. База не разбирает её как SQL-код. То же самое проверено для `зима') OR 1=1 --` и `'; DROP TABLE ideas; --`: таблица `ideas` на месте, число строк не изменилось.

Скриншот «после»: `docs/screenshots/sqli-after.png` (страница с сообщением «Подходящих занятий не нашлось» и адрес с атакой в строке браузера).


### 5.2. SQL-инъекция через форму «Предложить идею»

Название идеи `x'); DROP TABLE users;--`. Запрос, который выполняется:

```
SQL:      insert into "suggested_ideas" ("user_id", "title", "status", "created_at", "seasons") values (?, ?, ?, ?, ?)
bindings: [1, "x'); DROP TABLE users;--", "pending", "...", "[\"зима\"]"]
в базе:   title = x'); DROP TABLE users;--
```

Разбор: строка сохранена как обычный текст, таблица `users` не тронута.

### 5.3. XSS через название идеи

Что делаем: вошедшим пользователем отправляем идею с названием `<script>alert('XSS')</script>` и описанием `<img src=x onerror=alert(1)>`, админ одобряет её. Открываем главную, каталог и страницу модерации.

Результат: окно `alert` не появляется, на странице текст показан буквами: `<script>alert('XSS')</script>`.
Выдача шаблона: `{{ $x }}` превращает символы в безопасные сущности:

```
<script>alert('XSS')</script>   →  &lt;script&gt;alert(&#039;XSS&#039;)&lt;/script&gt;
"><img src=x onerror=alert(1)>  →  &quot;&gt;&lt;img src=x onerror=alert(1)&gt;
' onmouseover='alert(1)         →  &#039; onmouseover=&#039;alert(1)
```

Разбор: браузер получает `&lt;` вместо `<`, поэтому тег не создаётся и скрипт не запускается. Это верно и внутри атрибутов (`value="{{ old('title') }}"`): кавычки тоже заменяются, выйти из атрибута нельзя.

Скриншоты «после»: `docs/screenshots/xss-catalog.png`, `docs/screenshots/xss-admin.png`.
Скриншот «до» не делали: уязвимого места нет, а намеренно создавать дыру в проекте мы не стали.

## 6. Найденные слабые места и исправления

Не SQL/XSS, но связаны с обработкой ввода. Все исправлены в коммите.

| № | Проблема | Где | Исправление |
|---|---|---|---|
| 1 | Форма «Предложить идею» принимала любую длину описания и любые строки в фильтрах, массивы без ограничений. Эти строки позже попадали в `whereIn` и JSON. | app/Http/Controllers/SuggestedIdeaController.php:34–52 | `description` до 2000 символов, `title` от 3 до 200; значения фильтров должны существовать в справочнике (`Rule::exists`); не больше 10 значений и без повторов |
| 2 | Админ мог одобрить одну заявку дважды (двойной клик, повторный запрос): создавались две одинаковые идеи; уже одобренную можно было «отклонить». | app/Http/Controllers/Admin/SuggestedIdeasController.php:24–100 | обработка только заявок со статусом `pending`, одобрение в транзакции с блокировкой строки |
| 3 | В шаблоне HTML кнопки собирался в PHP-коде через `HtmlString` (не уязвимо, но нарушает правило «никакого необработанного HTML»). | resources/views/partials/idea-card.blade.php, random-idea.blade.php | кнопка перенесена в шаблон, в него передаётся только слово `any` или `filters` |
| 4 | Отправку идей можно было спамить. | routes/web.php (маршрут `suggest.store`) | `throttle:10,1`: не более 10 отправок в минуту |

Отдельно отмечено: в `SuggestedIdea::$fillable` есть `user_id` и `status`. Сейчас это безопасно, потому что контроллер берёт их с сервера, а не из запроса, и ставит после `validate()`. Новый код не должен передавать в `create()` целиком `$request->all()`.

Тесты: `tests/Feature/SecurityAuditTest.php` (проверки SQL-пейлоадов, экранирования, формы и модерации). Результат запуска `php artisan test`:

```
php artisan test

   PASS  Tests\Unit\ExampleTest
  ✓ that true is true

   PASS  Tests\Feature\Auth\AuthenticationTest
  ✓ login screen can be rendered                                                                                                                                           1.74s  
  ✓ users can authenticate using the login screen                                                                                                                          0.64s  
  ✓ users can not authenticate with invalid password                                                                                                                       0.28s  
  ✓ users can logout                                                                                                                                                       0.01s  

   PASS  Tests\Feature\Auth\EmailVerificationTest
  ✓ email verification screen can be rendered                                                                                                                              0.11s  
  ✓ email can be verified                                                                                                                                                  0.12s  
  ✓ email is not verified with invalid hash                                                                                                                                0.16s  

   PASS  Tests\Feature\Auth\PasswordConfirmationTest
  ✓ confirm password screen can be rendered                                                                                                                                0.21s  
  ✓ password can be confirmed                                                                                                                                              0.01s  
  ✓ password is not confirmed with invalid password                                                                                                                        0.21s  

   FAIL  Tests\Feature\Auth\PasswordResetTest
  ✓ reset password link screen can be rendered                                                                                                                             0.24s  
  ✓ reset password link can be requested                                                                                                                                   0.79s  
  ✓ reset password screen can be rendered                                                                                                                                  0.64s  
  ⨯ password can be reset with valid token                                                                                                                                 0.39s  

   PASS  Tests\Feature\Auth\PasswordUpdateTest
  ✓ password can be updated                                                                                                                                                0.03s  
  ✓ correct password must be provided to update password                                                                                                                   0.01s  

   FAIL  Tests\Feature\Auth\RegistrationTest
  ✓ registration screen can be rendered                                                                                                                                    0.01s  
  ⨯ new users can register                                                                                                                                                 0.01s  

   PASS  Tests\Feature\CatalogFavoritesHistoryTest
  ✓ catalog is public and shows only active ideas                                                                                                                          0.04s  
  ✓ catalog filters                                                                                                                                                        0.01s  
  ✓ catalog is paginated                                                                                                                                                   0.03s  
  ✓ guest is redirected from favorites and toggle                                                                                                                          0.01s  
  ✓ user can add and remove favorite                                                                                                                                       0.04s  
  ✓ cannot favorite hidden idea                                                                                                                                            0.04s  
  ✓ favorites are private to user                                                                                                                                          0.01s  
  ✓ random idea is recorded in history for logged in user                                                                                                                  0.02s  
  ✓ guest roll is not recorded                                                                                                                                             0.01s  
  ✓ history requires login and is private                                                                                                                                  0.02s  
  ✓ user can clear only own history                                                                                                                                        0.01s  
  ✓ admin link visible only for admin                                                                                                                                      0.01s  
  ✓ regular user cannot open moderation                                                                                                                                    0.01s  

   PASS  Tests\Feature\ExampleTest
  ✓ the application returns a successful response                                                                                                                          0.01s  

   PASS  Tests\Feature\ProfileTest
  ✓ profile page is displayed                                                                                                                                              0.02s  
  ✓ profile information can be updated                                                                                                                                     0.05s  
  ✓ email verification status is unchanged when the email address is unchanged                                                                                             0.01s  
  ✓ user can delete their account                                                                                                                                          0.01s  
  ✓ correct password must be provided to delete account                                                                                                                    0.01s  

   PASS  Tests\Feature\RandomIdeaTest
  ✓ home page is public and has buttons                                                                                                                                    0.01s  
  ✓ random button returns active idea only                                                                                                                                 0.04s  
  ✓ filters limit the choice                                                                                                                                               0.04s  
  ✓ any mode ignores selected filters                                                                                                                                      0.01s  
  ✓ message when nothing found                                                                                                                                             0.01s  
  ✓ bad mode is rejected                                                                                                                                                   0.01s  
  ✓ html in idea is escaped                                                                                                                                                0.01s  
  ✓ guest cannot open suggest form and is sent to login                                                                                                                    0.01s  
  ✓ logged in user can open suggest form                                                                                                                                   0.01s  
  ✓ home is the only main page and dashboard is gone                                                                                                                       0.01s  

   PASS  Tests\Feature\SecurityAuditTest
  ✓ sql payload in filter is treated as data                                                                                                                               0.02s  
  ✓ sql payload in catalog filter is treated as data                                                                                                                       0.01s  
  ✓ script in idea title is escaped in catalog                                                                                                                             0.01s  
  ✓ script in suggested idea is escaped for admin                                                                                                                          0.01s  
  ✓ suggest rejects unknown filter value and long text                                                                                                                     0.03s  
  ✓ suggest ignores status and user id from request                                                                                                                        0.01s  
  ✓ regular user cannot approve                                                                                                                                            0.01s  
  ✓ repeated approve does not create duplicate idea                                                                                                                        0.01s  
  ✓ reviewed idea cannot be rejected afterwards                                                                                                                            0.01s  
  ──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────  
   FAILED  Tests\Feature\Auth\PasswordResetTest > password can be reset with valid token                                                                                          
  Session has unexpected errors: 

{
    "default": [
        "The password field must be at least 10 characters."
    ]
}
Failed asserting that true is false.

  at tests\Feature\Auth\PasswordResetTest.php:67
     63▕                 'password_confirmation' => 'password',
     64▕             ]);
     65▕ 
     66▕             $response
  ➜  67▕                 ->assertSessionHasNoErrors()
     68▕                 ->assertRedirect(route('login'));
     69▕ 
     70▕             return true;
     71▕         });

  1   vendor\laravel\framework\src\Illuminate\Support\Testing\Fakes\NotificationFake.php:284
  2   vendor\laravel\framework\src\Illuminate\Collections\Arr.php:1269

  ──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────  
   FAILED  Tests\Feature\Auth\RegistrationTest > new users can register                                                                                                           
  The user is not authenticated
Failed asserting that false is true.

  at tests\Feature\Auth\RegistrationTest.php:28
     24▕             'password' => 'password',
     25▕             'password_confirmation' => 'password',
     26▕         ]);
     27▕ 
  ➜  28▕         $this->assertAuthenticated();
     29▕         $response->assertRedirect(route('home', absolute: false));
     30▕     }
     31▕ }
     32▕


  Tests:    2 failed, 55 passed (212 assertions)
  Duration: 18.49s
```

## 7. Правила для нового кода

1. **Никакого сырого SQL.** Только Eloquent и Query Builder. Если без `whereRaw` и `DB::raw` не обойтись, значения передаются вторым аргументом (`whereRaw('x = ?', [$v])`), а строка запроса не содержит переменных.
2. **Выводим только через `{{ }}`.** Конструкция `{!! !!}` и `HtmlString` запрещены для всего, что пришло от пользователя. Если нужен HTML, он собирается в шаблоне, а в него передаются только безопасные значения.
3. **Ввод берём только через `validate()`.** В `create()` и `update()` передаём `$validated`, а не `$request->all()`. `user_id`, `status`, роль и подобные поля ставит сервер.
4. **Значения «из списка» проверяем по списку:** `Rule::in([...])` или `Rule::exists(...)`. Для текста всегда задаём `max`.

