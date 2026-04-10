<?php

declare(strict_types=1);

namespace App\Foundation\Resolvers;

use Core\Contracts\ControllerResolverInterface;

/**
 * Отвечает за формирование полного пространства имён (FQCN) контроллера
 * на основе данных маршрута.
 * 
 * Поддерживает вложенные контроллеры:
 *   controller: "user/profile"
 *   → App\Http\Controllers\User\Profile
 * 
 * Используется ядром (Router) для динамического определения нужного класса
 * при обработке входящего HTTP-запроса.
 *
 * ---------------------------------------------------------------------------
 * Примеры преобразования маршрутов:
 * ---------------------------------------------------------------------------
 * controller: "home"
 * → App\Http\Controllers\home
 *
 * controller: "user/profile",
 * → App\Http\Controllers\User\Profile
 *
 * controller: "user/listing",
 * → App\Http\Controllers\User\Listing
 *
 * $resolver = new AppControllerResolver();
 * $class = $resolver->resolve($route);
 * // Результат: "App\Http\Controllers\Home"
 * ```
 * 
 * ---------------------------------------------------------------------------
 * Требования:
 * ---------------------------------------------------------------------------
 * - В значении 'controller' допускаются один или несколько сегментов,
 *   разделённых '/'. Последний сегмент - имя контроллера.
 *
 * @package App\Foundation\Resolvers
 */
class AppControllerResolver implements ControllerResolverInterface
{
    /**
     * Формирует FQCN контроллера на основе данных маршрута.
     *
     * Если маршрут не передан, используется текущий, полученный через {@see route()}.
     * 
     * @param array|null $route Ассоциативный массив данных маршрута:
     *                          - controller: "home"
     *                          - action: "index"
     *
     * @return string Полное имя класса контроллера
     *                (например: "App\Http\Controllers\Home").
     */
    public function resolve(?array $route = null): string
    {
        $route ??= route();

        $zoneNamespace = 'App\\Http\\Controllers\\';

        $segments = explode('/', trim($route['controller'], '/'));
        $segments = array_map(static fn($p) => upperCamelCase($p), $segments);
        $controllerName = array_pop($segments);
        $subdirs = $segments ? implode('\\', $segments) . '\\' : '';

        return $zoneNamespace . $subdirs . $controllerName;
    }
}