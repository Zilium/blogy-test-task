<?php

use Core\Container;
use Core\Http\Request;
use Core\Services\Session;
use Core\Http\Response;
use Core\Contracts\ControllerResolverInterface;
use App\Foundation\Resolvers\AppControllerResolver;
use Core\Routing\Router;

/**
 * -----------------------------------------------------------------------------
 * Регистрация сервисов HTTP-запроса, ответа и маршрутизации.
 * -----------------------------------------------------------------------------
 * Определяет основные компоненты HTTP-уровня приложения:
 * - {@see Core\Http\Request} - инкапсулирует данные текущего запроса;
 * - {@see Core\Http\Response} - отвечает за формирование HTTP-ответа;
 * - {@see Core\Routing\Router} - управляет таблицей маршрутов и обработкой запросов.
 *
 * Все HTTP-компоненты регистрируются как singleton-сервисы
 * и доступны через DI-контейнер.
 *
 * -----------------------------------------------------------------------------
 * Архитектурные особенности:
 * -----------------------------------------------------------------------------
 * • {@see Request} создаётся через статический метод capture(), который собирает
 *   данные из суперглобальных массивов ($_GET, $_POST, $_FILES, $_COOKIE и т.д.);
 * • {@see Response} формирует HTTP-ответ и управляет заголовками, телом и статусом;
 * • {@see Router} использует {@see ControllerResolverInterface} для определения
 *   контроллера и метода, обрабатывающих текущий маршрут.
 *
 * @package config\services
 *
 * @param Container $container Контейнер внедрения зависимостей.
 *
 * @return void
 */
return function (Container $container): void {
    /**
     * ------------------------------------------------------------
     * Request: HTTP-запрос.
     * ------------------------------------------------------------
     * Собирает данные текущего запроса и связывает его с активной сессией.
     * Если {@see Session} не зарегистрирован в контейнере (например, при
     * облегчённой загрузке сервисов), используется fallback к
     * {@see Session::getInstance()}.
     */
    $container->singleton(Request::class, function (Container $container) {
        $request = Request::capture();

        $session = $container->has(Session::class)
            ? $container->get(Session::class)
            : Session::getInstance();

        $request->setSession($session);
        return $request;
    });

    /**
     * ------------------------------------------------------------
     * Response: HTTP-ответ.
     * ------------------------------------------------------------
     * Формирует HTTP-ответ клиенту, управляет заголовками и телом ответа.
     */
    $container->singleton(Response::class, new Response());

    /**
     * ------------------------------------------------------------
     * ControllerResolver: разрешение контроллеров.
     * ------------------------------------------------------------
     * Определяет, какой контроллер и метод должны обработать текущий маршрут.
     * Интерфейс: {@see Core\Contracts\ControllerResolverInterface}.
     * Реализация: {@see App\Foundation\Resolvers\AppControllerResolver}.
     */
    $container->singleton(ControllerResolverInterface::class, new AppControllerResolver());

    /**
     * ------------------------------------------------------------
     * Router: маршрутизация HTTP-запросов.
     * ------------------------------------------------------------
     * Отвечает за поиск подходящего маршрута и вызов соответствующего контроллера.
     * Работает в связке с {@see Request}, {@see Response} и {@see ControllerResolverInterface}.
     */
    $container->singleton(Router::class, new Router(
        $container->get(Request::class),
        $container->get(Response::class),
        $container->get(ControllerResolverInterface::class),
        $container
    ));
};