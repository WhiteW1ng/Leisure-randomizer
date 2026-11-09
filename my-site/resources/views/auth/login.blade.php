<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Вход — Досуг-Рандом</title>

    {{-- Шрифты --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600&family=Inter:wght@400;500&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3ebe3;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .brand-logo {
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            font-weight: 500;
            color: #2d2d2d;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 28px;
        }

        .brand-logo .star {
            color: #f5b87a;
            font-size: 18px;
        }

        .login-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 36px 40px 32px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
            max-width: 420px;
            width: 100%;
        }

        .login-title {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            font-weight: 600;
            color: #2d2d2d;
            margin-bottom: 8px;
        }

        .login-subtitle {
            font-size: 13px;
            color: #8a8a8a;
            margin-bottom: 28px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            color: #2d2d2d;
            margin-bottom: 8px;
            font-weight: 400;
        }

        .form-input {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #e0d8d0;
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            background: #fafafa;
            outline: none;
            transition: border-color 0.2s;
        }

        .form-input:focus {
            border-color: #f5b87a;
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
            font-size: 13px;
            color: #5a5a5a;
            cursor: pointer;
        }

        .remember input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #f5b87a;
            cursor: pointer;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background-color: #f5b87a;
            color: #2d2d2d;
            border: none;
            border-radius: 50px;
            font-size: 15px;
            font-weight: 500;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: background-color 0.2s;
            margin-bottom: 20px;
        }

        .btn-login:hover {
            background-color: #f0a95f;
        }

        .card-footer {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
        }

        .card-footer a {
            color: #5a5a5a;
            text-decoration: none;
            transition: color 0.2s;
        }

        .card-footer a:hover {
            color: #2d2d2d;
        }

        .back-home {
            margin-top: 24px;
            font-size: 13px;
            color: #8a8a8a;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: color 0.2s;
        }

        .back-home:hover {
            color: #2d2d2d;
        }

        /* Стили для сообщений об ошибках от Breeze */
        .error-message {
            color: #dc2626;
            font-size: 13px;
            margin-top: 6px;
        }

        .session-status {
            background: #f0fdf4;
            color: #166534;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 28px 24px 24px;
            }
        }
    </style>
</head>
<body>

    {{-- Логотип --}}
    <div class="brand-logo">
        <span class="star">✦</span> Досуг-Рандом
    </div>

    {{-- Карточка входа --}}
    <div class="login-card">
        <h1 class="login-title">Вход</h1>
        <p class="login-subtitle">Войдите, чтобы сохранять идеи в избранное.</p>

        {{-- Статус сессии (например, "Письмо отправлено") --}}
        @if (session('status'))
            <div class="session-status">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            {{-- Email --}}
            <div class="form-group">
                <label for="email" class="form-label">Электронная почта</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="username"
                    class="form-input"
                />
                @error('email')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            {{-- Пароль --}}
            <div class="form-group">
                <label for="password" class="form-label">Пароль</label>
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    class="form-input"
                />
                @error('password')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            {{-- Запомнить меня --}}
            <label class="remember">
                <input id="remember_me" type="checkbox" name="remember">
                <span>Запомнить меня</span>
            </label>

            {{-- Кнопка --}}
            <button type="submit" class="btn-login">Войти</button>

            {{-- Ссылки --}}
            <div class="card-footer">
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}">Забыли пароль?</a>
                @endif
                <a href="{{ route('register') }}">Регистрация</a>
            </div>
        </form>
    </div>

    {{-- Вернуться на главную --}}
    <a href="{{ url('/') }}" class="back-home">← Вернуться на главную</a>

</body>
</html>