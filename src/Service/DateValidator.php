<?php
declare(strict_types=1);
namespace App\Service;

/**
 *  Класс - Сервис для валидации даты.
 */
class DateValidator
{
    /**
     * Проверяет, является ли строка корректной датой в формате 'Y-m-d'.
     *
     * @param string $date Строка даты для проверки.
     *
     * @return bool Возвращает true, если дата корректна, иначе false.
     */
    public function isValidDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
}
