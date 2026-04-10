<?php

declare(strict_types=1);

if (!function_exists('format_date')) {
    /**
     * Форматирует дату.
     * 
     * @param string|int|DateTime $date Дата для форматирования (timestamp, строка или объект DateTime)
     * @return string например: April 10, 2026
     */
    function format_date(string|int|\DateTimeInterface|null $date = null): string
    {
        $formatter = new IntlDateFormatter(
            'en_US',
            IntlDateFormatter::NONE,
            IntlDateFormatter::NONE,
            null,
            null,
            'MMMM d, yyyy'
        );
        
        if ($date === null) {
            $date = new DateTime();
        }
        
        if (is_string($date)) {
            $date = new DateTime($date);
        }
        
        if (is_int($date)) {
            $date = (new DateTime())->setTimestamp($date);
        }
        
        return $formatter->format($date);
    }
}