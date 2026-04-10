<?php

declare(strict_types=1);

namespace Core\Routing;

use Core\Container;
use Core\Http\Request;
use Core\Http\Response;
use Core\Contracts\ControllerResolverInterface;

use Exception;
use RuntimeException;

/**
 * -----------------------------------------------------------------------------
 * Класс Router
 * -----------------------------------------------------------------------------
 * 
 * Отвечает за маршрутизацию HTTP-запросов:
 * - регистрацию маршрутов;
 * - поиск подходящего маршрута по URI;
 * - определение контроллера и действия;
 * - запуск контроллера через DI-контейнер.
 * 
 * -----------------------------------------------------------------------------
 * Возможности:
 * -----------------------------------------------------------------------------
 * • Регистрация маршрутов с использованием регулярных выражений;
 * • Автоматическая нормализация URI (нижний регистр, убирание двойных слэшей);
 * • Прямая диспетчеризация контроллера (например, из ErrorHandler);
 * • Статическое хранение текущего маршрута для глобального доступа.
 * 
 * @package Core\Routing
 */
class Router
{
    /**
     * Текущий активный маршрут.
     *
     * Хранится статически, чтобы быть доступным из любой точки приложения.
     *
     * @var array<string, mixed>
     */
    private static array $route = [];

    /**
     * Зарегистрированные маршруты.
     *
     * Ключи - регулярные выражения; значения - конфигурации маршрутов.
     *
     * @var array<string, array<string, mixed>>
     */
    private static array $routes = [];

    /**
     * Регистрирует новый маршрут.
     *
     * @param string $pattern Регулярное выражение маршрута.
     * @param array<string, mixed> $route Конфигурация (controller, action, prefix и т.д.).
     *
     * @return void
     */
    public static function add(string $pattern, array $route = []): void
    {
        self::$routes[$pattern] = $route;
    }

    /**
     * Инициализирует маршрутизатор с необходимыми зависимостями.
     * 
     * @param Request $request Объект текущего HTTP-запроса.
     * @param Response $response Объект HTTP-ответа.
     * @param ControllerResolverInterface $resolver Резолвер, определяющий FQCN контроллера.
     * @param Container $container DI-контейнер приложения.
     */
    public function __construct(
        private Request $request,
        private Response $response,
        private ControllerResolverInterface $resolver,
        private Container $container,
    ) {}

    /**
     * Устанавливает текущий маршрут вручную.
     *
     * Используется, например, при обработке системных ошибок.
     *
     * @param array<string, mixed> $route Конфигурация маршрута.
     *
     * @return void
     */
    public function setRoute(array $route): void
    {
        self::$route = $route;
    }

    /**
     * Возвращает текущий активный маршрут.
     *
     * @return array<string, mixed>
     */
    public static function getRoute(): array
    {
        return self::$route;
    }

    /**
     * Устанавливает таблицу маршрутов приложения.
     *
     * Используется при восстановлении маршрутов из кэша
     * (например, {@see \Core\Routing\RouteLoader::load()}),
     * либо при динамической подмене набора маршрутов в процессе работы ядра.
     *
     * Позволяет задать полный массив всех маршрутов приложения,
     * зарегистрированных в системе.
     *
     * @param array<string, mixed> $routes
     *
     * @return void
     */
    public static function setRoutes(array $routes): void
    {
        self::$routes = $routes;
    }

    /**
     * Возвращает все зарегистрированные маршруты.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function getRoutes(): array
    {
        return self::$routes;
    }
    
    /**
     * Выполняет поиск маршрута и запуск соответствующего контроллера.
     *
     * @return Response
     *
     * @throws Exception Если маршрут не найден.
     * @throws RuntimeException Если контроллер не существует.
     */
    public function dispatch(): Response
    {
        $rawUri = $this->request->getRequestUri();

        [$uri, $redirectUri] = $this->normalize($rawUri);

        if ($redirectUri !== null) {
            return $this->response->redirect(
                SITE . $redirectUri,
                $this->response::HTTP_MOVED_PERMANENTLY
            );
        }
        
        if (!$this->matchRoute($uri)) {
            throw new Exception(
                'Страница не найдена: ' . $uri, 
                $this->response::HTTP_NOT_FOUND
            );
        }

        return $this->dispatchController();
    }

    /**
     * Выполняет запуск контроллера на основе текущего маршрута.
     *
     * Используется как при обычной маршрутизации, так и вручную -
     * например, при вызове из ErrorHandler.
     *
     * @return Response
     *
     * @throws RuntimeException Если контроллер не найден.
     */
    public function dispatchController(): Response
    {
        $controllerClass = $this->resolver->resolve();
        if (!class_exists($controllerClass)) {
            throw new RuntimeException(
                "Контроллер {$controllerClass} не найден",
                $this->response::HTTP_NOT_FOUND
            );
        }

        /** @var Controller $controller */
        $controller = $this->container->make($controllerClass, [
            'route' => self::$route
        ]);

        return $controller->runAction();
    }

    /**
     * Нормализует URI и определяет необходимость редиректа.
     *
     * Удаляет лишние слэши, приводит путь к нижнему регистру.
     * 
     * @param string $uri Исходный URI запроса.
     * 
     * @return array{0: string, 1: string|null}
     *  0 — нормализованный URI для matchRoute() (без query string)
     *  1 — URI для редиректа (с query string) или null, если редирект не нужен
     */
    private function normalize($uri): array
    {
        // Разделяем URI и query string
        $parts = explode('?', $uri, 2);
        $path = $parts[0];
        $query = $parts[1] ?? null;

        // Нормализуем только путь (часть до '?')
        $normalizedPath  = preg_replace('#/{2,}#', '/', $path);
        $normalizedPath = strtolower($normalizedPath);

        // Собираем обратно с сохранением query string
        $redirectUri = $normalizedPath . ($query ? "?{$query}" : '');

        // Если путь изменился — редирект нужен
        if ($path !== $normalizedPath) {
            return [$normalizedPath, $redirectUri];
        }

        return [$normalizedPath, null];
    }

    /**
     * Сопоставляет URI с зарегистрированными маршрутами.
     *
     * При успешном совпадении сохраняет параметры маршрута в self::$route.
     *
     * @param string $uri Текущий URI.
     *
     * @return bool true, если найден маршрут, иначе false.
     */
    private function matchRoute(string $uri): bool
    {
        foreach (self::$routes as $pattern => $route) {
            if (preg_match("#{$pattern}#", $uri, $matches)) {
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $route[$key] = $value;
                    }
                }
                
                $this->setRoute($route);
                
                return true;
            }
        }
        return false;
    }
}