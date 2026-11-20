<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * Допустимые статусы — белый список из кода. Статус никогда не приходит из запроса:
 * его ставит сервер, а модель дополнительно не даст сохранить значение не из списка.
 */
final class Statuses
{
    /** Заявки на модерации (таблица suggested_ideas) */
    public const SUGGESTED = ['pending', 'approved', 'rejected'];

    /** Идеи в каталоге (таблица ideas): active — показывается, rejected — снята с показа */
    public const IDEA = ['active', 'rejected'];

    /** null допустим: тогда значение берётся из умолчания в базе */
    public static function check(?string $status, array $allowed): void
    {
        if ($status !== null && ! in_array($status, $allowed, true)) {
            throw new InvalidArgumentException('Недопустимый статус: '.$status);
        }
    }
}
