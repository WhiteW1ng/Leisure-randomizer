<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>500 — Ошибка</title>
</head>
<body style="font-family: system-ui, sans-serif; max-width: 720px; margin: 4rem auto; padding: 1rem;">
    <h1>500 — @if(500 == 403) Доступ запрещён @elseif(500 == 404) Страница не найдена @else Ошибка сервера @endif</h1>
    <p>@if(500 == 403) У вас нет разрешения на просмотр этой страницы. @elseif(500 == 404) Запрашиваемая страница не найдена. @else Произошла внутренняя ошибка. Попробуйте позже. @endif</p>
    <p><a href="{{ route('home') }}">На главную</a></p>
    <p>Request ID: {{  ?? request()->attributes->get('request_id', 'unknown') }}</p>
</body>
</html>
