<?php

declare(strict_types=1);

if (!function_exists('file_put')) {
    /**
     * Пишет данные в лог-файл внутри директории LOGS_DIR.
     * 
     * Пример результата в лог-файле:
     * [2025-11-08 02:43:21]
     * Array
     * (
     *     [user] => admin
     *     [action] => login
     * )
     * 
     * @param mixed $data Данные для записи (массив, объект, строка и т.д.).
     * @param string $fileName Имя файла лога без расширения (по умолчанию `data`).
     * 
     * @return void
     */
    function file_put(mixed $data, string $fileName = 'data'): void {
        $timestamp = '[' . date('Y-m-d H:i:s') . ']';
        
        $formatted = is_scalar($data)
            ? (string) $data
            : print_r($data, true);

        $logData = "{$timestamp}\n{$formatted}\n\n";

        file_put_contents(
            LOGS_DIR . '/' . $fileName . '.log',
            $logData,
            FILE_APPEND
        );
    }
}

if (!function_exists('console_log')) {
    /**
     * Выводит данные в консоль браузера через JavaScript.
     * 
     * Преобразует PHP-данные в JSON и выводит их с помощью `console.log()`.
     * Удобно использовать при отладке AJAX-запросов или рендеринга HTML.
     * 
     * @param mixed $data Любые данные для вывода в консоль (массив, объект и т.п.).
     * 
     * @return void
     */
    function console_log($data): void {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        echo "<script>console.log({$json});</script>";
    }
}