<?php

declare(strict_types=1);

use Core\Container;
use Core\Kernel;
use RuntimeException;

if (!function_exists('app')) {
    /**
     * Глобальный доступ к DI-контейнеру приложения.
     *
     * Позволяет получать экземпляры сервисов из контейнера в любом месте кода.
     * 
     * Примеры:
     *   $config = app(Core\Config::class);
     *   $response = app(Core\Response::class);
     *   $container = app(); // вернуть сам контейнер
     * 
     * 
     * @param string|null $abstract Класс или интерфейс, который нужно получить.
     * @return mixed|Container Возвращает экземпляр сервиса или сам контейнер.
     * 
     * @throws RuntimeException Если Kernel ещё не инициализирован.
     */
    function app(?string $abstract = null): mixed {
        static $container = null;

        if ($container === null) {
            global $kernel;
            
            if (!isset($kernel) || !$kernel instanceof Kernel) {
                throw new RuntimeException(
                    'Ошибка: ядро приложения (Kernel) ещё не инициализировано. ' .
                    'Убедитесь, что $kernel создан до вызова функции app().'
                );
            }

            $container = $kernel->getContainer();
        }

        // Если указан класс - возвращаем зарегистрированный экземпляр.
        if ($abstract !== null) {
            try {
                return $container->get($abstract);
            } catch (\Throwable $e) {
                throw new RuntimeException(
                    "Ошибка: не удалось получить зависимость '{$abstract}' из контейнера. " .
                    "Подробности: " . $e->getMessage()
                );
            }
        }

        // Если аргумент не указан - вернуть сам контейнер.
        return $container;
    }
}