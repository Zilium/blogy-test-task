<?php

declare(strict_types=1);

use Core\Routing\Router;

if (!function_exists('route')) {
    /**
     * Возвращает текущие данные активного маршрута.
     * 
     * Позволяет получить информацию о текущем контроллере, действии (action),
     * пространстве имён и других параметрах маршрута.
     * 
     * Пример:
     * ```php
     * $router  = route();
     * echo $router ['controller']; // LoginController
     * echo $router ['action'];     // index
     * ```
     * 
     * @return array<string,mixed>|null Массив данных маршрута или null, если маршрут не найден.
     */
    function route(): ?array {
        /** @var Router $router */
        $router = app(Router::class);
        return $router::getRoute();
    }
}