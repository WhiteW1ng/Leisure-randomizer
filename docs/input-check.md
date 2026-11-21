# input-check.md — проверка входных данных (команда 4, «Рандомайзер досуга»)


Главное правило: **настоящая проверка — на сервере** (`$request->validate()`, FormRequest). Атрибуты `minlength`/`maxlength` в HTML — только удобство для человека, обойти их можно одним запросом без браузера, поэтому на них мы не полагаемся.

## 1. Таблица всех форм

| Страница (маршрут) | Поле | Тип и длина / диапазон | Какие проверки были | Что добавлено |
|---|---|---|---|---|
| Регистрация `POST /register` | name | строка, 2–100 | required, string, max:255 | min:2, max:100 |
| | email | строка до 255, email, нижний регистр | required, lowercase, email, max:255, unique | без изменений |
| | password | строка, 10–128 | required, confirmed, Password::defaults() (8) | max:128, минимум поднят до 10 |
| Вход `POST /login` | email | строка до 255 | required, string, email | max:255 |
| | password | строка до 128 | required, string | max:128 |
| Забыли пароль `POST /forgot-password` | email | строка до 255 | required, email | max:255 |
| Сброс пароля `POST /reset-password` | token | строка до 255 | required | string, max:255 |
| | email | строка до 255 | required, email | max:255 |
| | password | 10–128 | required, confirmed, Password::defaults() | max:128, минимум 10 |
| Подтверждение пароля `POST /confirm-password` | password | строка | проверка текущего пароля через Auth | без изменений (пароль сверяется с хэшем, в БД не пишется) |
| Профиль: имя и email `PATCH /profile` | name | строка, 2–100 | required, string, max:255 | min:2, max:100 |
| | email | строка до 255 | required, lowercase, email, max:255, unique (кроме себя) | без изменений |
| Профиль: смена пароля `PUT /password` | current_password | строка до 128 | required, current_password | string, max:128 |
| | password | 10–128 | required, confirmed, Password::defaults() | max:128, минимум 10 |
| Профиль: удаление `DELETE /profile` | password | строка до 128 | required, current_password | string, max:128 |
| Предложить идею `POST /suggest` | title | строка, 3–200 | required, max:255 | min:3, max:200 (как столбец в БД), русские сообщения |
| | description | строка до 2000, можно пусто | nullable, string | max:2000 |
| | seasons, companies, activity_levels, budgets, durations, location_types | массив из 1–10 значений; каждое — строка до 100, без повторов, **только из справочника в БД** | required, array | min:1, max:10, `.*` string/max:100/distinct/`Rule::exists(таблица, name)` |
| | status, user_id (в форме нет) | — | не принимались из запроса | по-прежнему игнорируются, ставит сервер |
| Случайная идея `GET /` (кнопка «Случайная») | mode | any / filters | in:any,filters | без изменений |
| | seasons[], companies[] и др. фильтры | массив до 20, значение строка до 100 | array, string | max:20, max:100 |
| Каталог (фильтры) `GET /catalog` | те же фильтры | то же | то же | то же |
| | page | целое 1–100000 | не проверялось | nullable, integer, min:1, max:100000 |
| Избранное `GET /favorites` | page | целое 1–100000 | не проверялось | добавлено |
| Избранное: «в избранное» `POST /favorites/{idea}` | idea (в адресе) | только цифры | findOrFail | `Route::pattern('idea','[0-9]+')` — не цифры = 404 |
| История `GET /history` | page | целое 1–100000 | не проверялось | добавлено |
| История: очистить `DELETE /history` | — (полей нет) | — | auth + CSRF | — |
| Админка: одобрить / отклонить `POST /admin/suggested-ideas/{id}/approve\|reject` | id (в адресе) | только цифры | findOrFail, проверка status=pending | `Route::pattern('id','[0-9]+')`; статус ставит сервер |
| Выход `POST /logout`, повторная отправка письма `POST /email/verification-notification` | — (полей нет) | — | CSRF, auth | — |

## 2. Белые списки (из кода)

Статусы лежат в `app/Support/Statuses.php`, а не берутся из запроса:

- заявка (`suggested_ideas.status`): `pending`, `approved`, `rejected`;
- идея (`ideas.status`): `active`, `rejected`.

Любая попытка сохранить другое значение падает с исключением: в `AppServiceProvider` на событие `saving` моделей `SuggestedIdea` и `Idea` повешена проверка `Statuses::check(...)`.
Значения фильтров (сезон, компания, бюджет и т. д.) проверяются через `Rule::exists(<таблица>, 'name')` — то есть только то, что есть в справочниках.

## 3. Что считает сервер, а не принимает из запроса

- **Роль / is_admin.** При регистрации поле `is_admin` из запроса не читается; в `User::$fillable` только name, email, password. Админ задаётся только в БД. Доступ к `/admin/*` проверяет middleware на сервере.
- **Статус заявки.** Пользователь не передаёт `status`: сервер ставит `pending` при создании, а `approved`/`rejected` — только админский контроллер, и только если заявка ещё `pending` (внутри транзакции с `lockForUpdate`, чтобы двойной клик не создал дубль).
- **Автор заявки.** `user_id` берётся из `auth()->id()`, а не из формы; `created_at` ставит сервер (`now()`).
- **Статус идеи.** При одобрении сервер сам пишет `status = 'active'`.
- **Цен в проекте нет** (бюджет — это значение из справочника `budgets`, а не число из формы), поэтому пересчёта сумм нет.
- **Избранное и история.** Пользователь берётся из сессии, а не из запроса.
- **Номера в адресах** (`{id}`, `{idea}`) допускают только цифры; всё остальное — 404 до входа в контроллер.

## 4. Загрузка файлов

**загрузки файлов нет**: ни одной формы с `enctype="multipart/form-data"` или `type="file"`, нет `$request->file()`, `hasFile`, `UploadedFile`, `store()`.

Проверка командой из корня проекта (результат — пусто):

```
grep -rn "UploadedFile\|->file(\|type=\"file\"\|hasFile\|enctype" app resources routes
```

Пятишаговая функция загрузки не нужна. На случай, если кто-то отправит файл в обычную форму, есть тест `test_file_sent_to_form_is_ignored_and_not_stored`: файл `shell.php` не сохраняется ни на одном диске.

## 5. Скриншоты отказов сервера

Сервер отклоняет неверные данные и показывает понятную ошибку. Ниже два примера (файлы лежат в папке `docs`).

**Пример 1. Форма «Предложить идею»:** название из 2 символов (минимум 3), в группе «Сезон» ничего не выбрано. Сервер не принимает идею и показывает список ошибок. (input-suggest-errors.png)

**Пример 2. Регистрация:** пароль из 9 символов (минимум 10). Сервер не создаёт пользователя и показывает ошибку у поля пароля.
(input-register-password.png)

Остальные случаи (слишком длинные значения, значения не из справочника, `status` и `user_id` в запросе, нецифровые номера в адресе, статус не из белого списка) проверяются автотестами из раздела 6.

## 6. Тесты

Файл `tests/Feature/InputValidationTest.php` (регистрация, вход, фильтры, page, «Предложить идею», цифровые id, статусы).
Вывод `php artisan test`:

```
   PASS  Tests\Unit\ExampleTest
  ✓ that true is true

   PASS  Tests\Feature\Auth\AuthenticationTest
  ✓ login screen can be rendered                                                                                                                                        5.06s  
  ✓ users can authenticate using the login screen                                                                                                                       1.78s  
  ✓ users can not authenticate with invalid password                                                                                                                    0.28s  
  ✓ users can logout                                                                                                                                                    0.02s  

   PASS  Tests\Feature\Auth\EmailVerificationTest
  ✓ email verification screen can be rendered                                                                                                                           0.24s  
  ✓ email can be verified                                                                                                                                               0.15s  
  ✓ email is not verified with invalid hash                                                                                                                             0.14s  

   PASS  Tests\Feature\Auth\PasswordConfirmationTest
  ✓ confirm password screen can be rendered                                                                                                                             0.26s  
  ✓ password can be confirmed                                                                                                                                           0.01s  
  ✓ password is not confirmed with invalid password                                                                                                                     0.21s  

   PASS  Tests\Feature\Auth\PasswordResetTest
  ✓ reset password link screen can be rendered                                                                                                                          0.29s  
  ✓ reset password link can be requested                                                                                                                                0.79s  
  ✓ reset password screen can be rendered                                                                                                                               0.65s  
  ✓ password can be reset with valid token                                                                                                                              0.23s  

   PASS  Tests\Feature\Auth\PasswordUpdateTest
  ✓ password can be updated                                                                                                                                             0.03s  
  ✓ correct password must be provided to update password                                                                                                                0.03s  

   PASS  Tests\Feature\Auth\RegistrationTest
  ✓ registration screen can be rendered                                                                                                                                 0.08s  
  ✓ new users can register                                                                                                                                              0.04s  

   PASS  Tests\Feature\CatalogFavoritesHistoryTest
  ✓ catalog is public and shows only active ideas                                                                                                                       1.26s  
  ✓ catalog filters                                                                                                                                                     0.02s  
  ✓ catalog is paginated                                                                                                                                                0.03s  
  ✓ guest is redirected from favorites and toggle                                                                                                                       0.03s  
  ✓ user can add and remove favorite                                                                                                                                    0.11s  
  ✓ cannot favorite hidden idea                                                                                                                                         0.04s  
  ✓ favorites are private to user                                                                                                                                       0.01s  
  ✓ random idea is recorded in history for logged in user                                                                                                               0.26s  
  ✓ guest roll is not recorded                                                                                                                                          0.01s  
  ✓ history requires login and is private                                                                                                                               0.02s  
  ✓ user can clear only own history                                                                                                                                     0.01s  
  ✓ admin link visible only for admin                                                                                                                                   0.01s  
  ✓ regular user cannot open moderation                                                                                                                                 0.04s  

   PASS  Tests\Feature\ExampleTest
  ✓ the application returns a successful response                                                                                                                       0.01s  

   PASS  Tests\Feature\InputValidationTest
  ✓ registration rejects too long and too short values                                                                                                                  0.04s  
  ✓ password of 9 characters is rejected and long phrase with spaces is accepted                                                                                        0.01s  
  ✓ registration ignores is admin from request                                                                                                                          0.01s  
  ✓ login rejects oversized password                                                                                                                                    0.01s  
  ✓ filter values are limited                                                                                                                                           0.01s  
  ✓ page number must be an integer in range                                                                                                                             0.04s  
  ✓ suggest shows human messages per field                                                                                                                              0.09s  
  ✓ suggest title length boundaries                                                                                                                                     0.03s  
  ✓ suggest rejects values not from reference lists                                                                                                                     0.02s  
  ✓ suggest accepts valid idea and server sets service fields                                                                                                           0.01s  
  ✓ suggest form page shows all errors in one list                                                                                                                      0.15s  
  ✓ file sent to form is ignored and not stored                                                                                                                         0.62s  
  ✓ non numeric ids in urls give 404                                                                                                                                    0.01s  
  ✓ status outside the whitelist cannot be saved                                                                                                                        0.01s  
  ✓ suggested status outside the whitelist cannot be saved                                                                                                              0.01s  

   PASS  Tests\Feature\ProfileTest
  ✓ profile page is displayed                                                                                                                                           1.04s  
  ✓ profile information can be updated                                                                                                                                  0.05s  
  ✓ email verification status is unchanged when the email address is unchanged                                                                                          0.01s  
  ✓ user can delete their account                                                                                                                                       0.01s  
  ✓ correct password must be provided to delete account                                                                                                                 0.01s  

   PASS  Tests\Feature\RandomIdeaTest
  ✓ home page is public and has buttons                                                                                                                                 0.01s  
  ✓ random button returns active idea only                                                                                                                              0.04s  
  ✓ filters limit the choice                                                                                                                                            0.05s  
  ✓ any mode ignores selected filters                                                                                                                                   0.01s  
  ✓ message when nothing found                                                                                                                                          0.01s  
  ✓ bad mode is rejected                                                                                                                                                0.01s  
  ✓ html in idea is escaped                                                                                                                                             0.01s  
  ✓ guest cannot open suggest form and is sent to login                                                                                                                 0.01s  
  ✓ logged in user can open suggest form                                                                                                                                0.02s  
  ✓ home is the only main page and dashboard is gone                                                                                                                    0.01s  

   PASS  Tests\Feature\SecurityAuditTest
  ✓ sql payload in filter is treated as data                                                                                                                            0.02s  
  ✓ sql payload in catalog filter is treated as data                                                                                                                    0.01s  
  ✓ script in idea title is escaped in catalog                                                                                                                          0.01s  
  ✓ script in suggested idea is escaped for admin                                                                                                                       0.11s  
  ✓ suggest rejects unknown filter value and long text                                                                                                                  0.01s  
  ✓ suggest ignores status and user id from request                                                                                                                     0.01s  
  ✓ regular user cannot approve                                                                                                                                         0.01s  
  ✓ repeated approve does not create duplicate idea                                                                                                                     0.03s  
  ✓ reviewed idea cannot be rejected afterwards                                                                                                                         0.01s  

  Tests:    72 passed (286 assertions)
  Duration: 26.41s

```
