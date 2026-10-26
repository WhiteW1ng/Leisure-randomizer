# Проверка входа и паролей

Команда: 4, проект: Рандомайзер досуга, стек: PHP (Laravel Breeze)

В таблице: «Где в коде» — файл и метод; «Как проверили, кто» — способ проверки и имя участника, который его выполнил.

| № | Требование | Да / нет | Где в коде (файл:строка) | Как проверили, кто | Результат |
|---|---|---|---|---|---|
| 1 | Пароль — только медленный хеш (bcrypt, scrypt или Argon2) | да | app/Http/Controllers/Auth/RegisteredUserController.php (`Hash::make`); app/Models/User.php (`'password' => 'hashed'`); .env (`BCRYPT_ROUNDS=12`) | открыла базу через `php artisan tinker`: `App\Models\User::pluck('password','email')` — Илюшина | в колонке пароля `$2y$12$…`, самого пароля нет |
| 2 | У каждого хеша своя соль | да | bcrypt в `Hash::make` (соль создаётся автоматически и хранится внутри строки хеша) | зарегистрировала двух пользователей с одинаковым паролем, сравнили хеши в базе — Илюшина | хеши разные |
| 3 | Пароль не короче 10 символов; длинные пароли и любые символы разрешены | да | app/Providers/AppServiceProvider.php, метод `boot()`: `Password::defaults(fn () => Password::min(10))` | пароль из 9 символов; фраза из 30 символов с пробелами — Илюшина | 9 символов — отказ; 30 символов с пробелами — принят |
| 4 | При ошибке входа — одно сообщение | да | app/Http/Requests/Auth/LoginRequest.php, метод `authenticate()` (`auth.failed`) | вход с несуществующим email, затем с верным email и неверным паролем — Илюшина | текст ошибки одинаковый в обоих случаях |
| 5 | Не больше 5 неудач за 5 минут | да | app/Http/Requests/Auth/LoginRequest.php: `ensureIsNotRateLimited()`, `RateLimiter::hit($this->throttleKey(), 300)` | 6 неверных паролей подряд для одного email — Рудикова | после 5-й неудачи вход блокируется, сообщение о слишком большом числе попыток |
| 6 | Пароль не попадает в журнал, в сообщения об ошибках и обратно в форму | да | resources/views/auth/login.blade.php (у поля пароля нет `value=`); `Log::info($request->all())` в проекте не используется | после неудачного входа поле пароля пустое; открыла storage/logs/laravel.log и нашла пароль поиском — Рудикова | поле пустое; в журнале пароля нет |
| 7 | Новая сессия при входе | да | app/Http/Controllers/Auth/AuthenticatedSessionController.php, метод `store()`: `$request->session()->regenerate()` | F12 → Application → Cookies: значение `laravel_session` до входа и после — Рудикова | значения разные |
| 8 | Cookie сессии с HttpOnly и SameSite=Lax | да | config/session.php: `'http_only' => env('SESSION_HTTP_ONLY', true)`, `'same_site' => env('SESSION_SAME_SITE', 'lax')` | F12 → Application → Cookies, колонки HttpOnly и SameSite — Фирсова | HttpOnly — стоит галочка; SameSite — Lax. Secure не включён (локально нет HTTPS) |
| 9 | Выход — кнопка-форма POST с CSRF; сессия уничтожается | да | routes/auth.php (маршрут `logout` — POST); AuthenticatedSessionController, метод `destroy()`: `invalidate()`, `regenerateToken()`; кнопка выхода в resources/views/layouts/navigation.blade.php (форма с `@csrf`) | вышла кнопкой, нажала «Назад» и обновила страницу — Фирсова | в личный кабинет не возвращает, перенаправляет на вход |
| 10 | В формах регистрации и входа есть CSRF-токен | да | resources/views/auth/register.blade.php и login.blade.php: `@csrf` | Ctrl+U на страницах входа и регистрации, поиск по `_token` — Фирсова | скрытое поле `_token` есть в обеих формах |
| 11 | Роль и кто вошёл — только из сессии и базы | да | app/Models/User.php: в `fillable` нет `role`; RegisteredUserController создаёт пользователя только из `name`, `email`, `password` | в форму регистрации через F12 добавили поле `role=admin` и зарегистрировались — Сашина | пользователь создан как обычный, роли admin нет |
| 12 | После входа перенаправление только на свои страницы | да | AuthenticatedSessionController, метод `store()`: `redirect()->intended(route('dashboard'))`; адрес из параметров запроса не используется | открыла `/login?next=https://example.com`, вошла — Сашина | после входа открылся наш `/dashboard` |
| 13 | APP_KEY и пароль базы — в .env; .env — в .gitignore | да | .gitignore (строка `.env`); .env.example без секретов | `git ls-files` (в PowerShell: `git ls-files \| findstr .env`) — Сашина | виден только `.env.example` |
| 14 | Страницы для вошедших недоступны гостю | да | routes/web.php: `/dashboard` и маршруты профиля под `middleware(['auth'...])` | в окне инкогнито открыли `/dashboard` — Сашина | перенаправляет на страницу входа |

## Что не сделано и почему

- При регистрации Breeze показывает «The email has already been taken». Это позволяет узнать, что такой email уже есть в системе (перечисление пользователей). Для учебного проекта допустимо; на требование 4 (вход) это не влияет, там сообщение одно и то же.
- Флаг `Secure` для cookie сессии не включён: проверяем локально по HTTP. На сервере с HTTPS нужно поставить `SESSION_SECURE_COOKIE=true` в `.env`.

