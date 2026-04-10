<?php

declare(strict_types=1);

use Core\Services\Config;

if (!function_exists('config')) {
    /**
     * Возвращает значение конфигурационного параметра.
     * 
     * Поддерживает доступ к вложенным параметрам через точку.
     * Пример: `config('mail.smtp.host')`
     * 
     * Обращается к сервису {@see Core\Services\Config} из контейнера.
     * Если ключ отсутствует, возвращается значение по умолчанию.
     * 
     * @param string $key Ключ конфигурационного параметра (вложенные - через точку).
     * @param mixed $default Значение по умолчанию, если параметр не найден.
     * 
     * @return mixed Значение параметра или значение по умолчанию.
     */
    function config(string $key, $default = null): mixed {
        /** @var Config $config */
        return app(Config::class)->get($key, $default);
    }
}