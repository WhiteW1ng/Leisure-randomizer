# input-check.md — проверка входных данных (команда 4, «Рандомайзер досуга»)

Коммит с серверной проверкой: **<ВСТАВИТЬ ССЫЛКУ НА КОММИТ>**

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

В проекте команды 4 **загрузки файлов нет**: ни одной формы с `enctype="multipart/form-data"` или `type="file"`, нет `$request->file()`, `hasFile`, `UploadedFile`, `store()`.

Проверка командой из корня проекта (результат — пусто):

```
grep -rn "UploadedFile\|->file(\|type=\"file\"\|hasFile\|enctype" app resources routes
```

Пятишаговая функция загрузки не нужна. На случай, если кто-то отправит файл в обычную форму, есть тест `test_file_sent_to_form_is_ignored_and_not_stored`: файл `shell.php` не сохраняется ни на одном диске.

## 5. Тесты

Файл `tests/Feature/InputValidationTest.php` (регистрация, вход, фильтры, page, «Предложить идею», цифровые id, статусы).
Вывод `php artisan test`:

```
<ВСТАВИТЬ ВЫВОД php artisan test>
```

Скриншоты отказов (по 1–2): <ВСТАВИТЬ — например, форма «Предложить идею» с ошибками «Название должно быть не короче 3 символов»>.
