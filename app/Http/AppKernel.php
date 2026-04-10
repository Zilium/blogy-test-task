<?php
declare(strict_types=1);

namespace App\Http;

use Core\Kernel;
use Core\Http\Request;
use Core\Http\Response;
use Core\Routing\Router;
use App\Foundation\Providers\RouteServiceProvider;

/**
 * -----------------------------------------------------------------------------
 * Класс AppKernel
 * -----------------------------------------------------------------------------
 *
 * Конкретная реализация ядра для текущего приложения.
 *
 * Расширяет базовый абстрактный класс {@see Core\Kernel}, добавляя
 * прикладную инициализацию окружения (service profile, helpers)
 * и собственную стратегию маршрутизации.
 *
 * @see Core\Kernel
 * @see App\Providers\RouteServiceProvider
 * 
 * @package App\Http
 */
final class AppKernel extends Kernel
{
    /**
     * Фаза загрузки окружения приложения.
     *
     * Выполняется перед инициализацией обработчиков ошибок и запуском сессии.
     * Здесь подключаются сервис-профиль и глобальные helper-функции.
     *
     * @return void
     */
    protected function boot(): void
    {
        $this->loadServiceProfile();
        $this->loadHelpers();
    }

    /**
     * Загружает сервис-профиль приложения.
     *
     * Профиль определяет, какие сервисы и зависимости будут зарегистрированы
     * в контейнере на этапе загрузки (например, full).
     *
     * Алгоритм:
     *  1. Определяет текущий URI и вычисляет профиль загрузки;
     *  2. Подключает соответствующий файл конфигурации из {@see PROFILES_DIR};
     *  3. Вызывает возвращаемое замыкание с передачей DI-контейнера.
     *
     * @return void
     */
    private function loadServiceProfile(): void
    {
        $profile = 'full';

        $file = PROFILES_DIR . "/{$profile}.php";
        $services = require $file;
        $services($this->container);
    }

    /**
     * Подключает набор глобальных helper-функций.
     *
     * @return void
     */
    private function loadHelpers(): void
    {
        foreach ([CORE_HELPERS_DIR, APP_HELPERS_DIR] as $dir) {
            foreach (glob($dir . '/*.php') as $file) {
                require_once $file;
            }
        }
    }

    /**
     * Обрабатывает маршрутизацию входящего HTTP-запроса.
     *
     * Вызывается из {@see Core\Kernel::run()} после завершения фазы boot(),
     * регистрации ошибок и запуска сессии.
     *
     * -----------------------------------------------------------------------------
     * Алгоритм:
     * -----------------------------------------------------------------------------
     *  1. Получает текущий URI из {@see Request};
     *  2. Регистрирует маршруты через {@see RouteServiceProvider};
     *  3. Возвращает представление от контроллера {@see Router::dispatch()}.
     *
     * @return Response
     */
    protected function handleRouting(): Response
    {
        /** @var Request $request */
        $request = $this->container->get(Request::class);
        $uri = $request->getRequestUri();

        // Регистрируем маршруты и провайдеры приложения.
        RouteServiceProvider::register($uri);

        /** @var Router $router */
        $router = $this->container->get(Router::class);
        
        return $router->dispatch();
    }
}