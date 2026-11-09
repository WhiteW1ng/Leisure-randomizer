<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Регистрация — Досуг-Рандом</title>

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

        .register-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 36px 40px 32px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
            max-width: 420px;
            width: 100%;
        }

        .register-title {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            font-weight: 600;
            color: #2d2d2d;
            margin-bottom: 8px;
        }

        .register-subtitle {
            font-size: 13px;
            color: #8a8a8a;
            margin-bottom: 28px;
            line-height: 1.4;
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

        .form-hint {
            font-size: 12px;
            color: #8a8a8a;
            margin-top: 6px;
        }

        .btn-register {
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
            margin-top: 8px;
            margin-bottom: 20px;
        }

        .btn-register:hover {
            background-color: #f0a95f;
        }

        .card-footer {
            text-align: center;
            font-size: 13px;
            color: #5a5a5a;
        }

        .card-footer a {
            color: #2d2d2d;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .card-footer a:hover {
            color: #f5b87a;
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

        /* Ошибки валидации */
        .error-message {
            color: #dc2626;
            font-size: 13px;
            margin-top: 6px;
        }

        @media (max-width: 480px) {
            .register-card {
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

    {{-- Карточка регистрации --}}
    <div class="register-card">
        <h1 class="register-title">Регистрация</h1>
        <p class="register-subtitle">Аккаунт нужен, чтобы избранное сохранялось на любом устройстве.</p>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            {{-- Имя --}}
            <div class="form-group">
                <label for="name" class="form-label">Имя</label>
                <input
                    id="name"
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    autofocus
                    autocomplete="name"
                    class="form-input"
                />
                @error('name')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            {{-- Электронная почта --}}
            <div class="form-group">
                <label for="email" class="form-label">Электронная почта</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
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
                    autocomplete="new-password"
                    class="form-input"
                />
                <div class="form-hint">Не короче 10 символов</div>
                @error('password')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            {{-- Повторите пароль --}}
            <div class="form-group">
                <label for="password_confirmation" class="form-label">Повторите пароль</label>
                <input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                    class="form-input"
                />
                @error('password_confirmation')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            {{-- Кнопка --}}
            <button type="submit" class="btn-register">Зарегистрироваться</button>

            {{-- Ссылка на вход --}}
            <div class="card-footer">
                Уже есть аккаунт? <a href="{{ route('login') }}">Войти</a>
            </div>
        </form>
    </div>

    {{-- Вернуться на главную --}}
    <a href="{{ url('/') }}" class="back-home">← Вернуться на главную</a>

</body>
</html>