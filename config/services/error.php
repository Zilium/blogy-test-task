<?php

use Core\Container;
use Core\Contracts\ErrorRendererInterface;
use App\Foundation\Rendering\AppErrorRenderer;
use Core\Routing\Router;
use Core\Services\ErrorHandler;
use Core\Services\Config;
use Core\Http\Request;
use Core\Http\Response;

/**
 * -----------------------------------------------------------------------------
 * Регистрация сервисов обработки ошибок и исключений.
 * -----------------------------------------------------------------------------
 * Определяет зависимости, отвечающие за перехват, обработку и отображение ошибок:
 *  - {@see Core\Services\ErrorHandler} - глобальный обработчик ошибок и исключений;
 *  - {@see Core\Contracts\ErrorRendererInterface} - контракт визуализации ошибок;
 *  - {@see App\Foundation\Rendering\AppErrorRenderer} - реализация визуального рендеринга,
 *   включая обработку кодов 404, 500 и других системных ошибок.
 *
 * -----------------------------------------------------------------------------
 * Архитектурные особенности:
 * -----------------------------------------------------------------------------
 * - {@see ErrorHandler} регистрирует глобальные обработчики PHP-ошибок, исключений и фатальных сбоев;
 * - {@see ErrorRendererInterface} определяет интерфейс для визуализации ошибок;
 * - {@see AppErrorRenderer} реализует отображение ошибок и может использовать {@see Core\Routing\Router}
 *   для поиска шаблонов или маршрутов кастомных страниц ошибок (например, "error.404");
 * - Конфигурация (debug) берётся из {@see Core\Services\Config}.
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
     * ErrorRenderer: визуализация ошибок.
     * ------------------------------------------------------------
     * Сервис регистрирует реализацию интерфейса {@see Core\Contracts\ErrorRendererInterface}.
     * Отвечает за формирование страниц ошибок (404, 500 и т.п.).
     * 
     * Интерфейс: {@see Core\Contracts\ErrorRendererInterface}.
     * Реализация: {@see App\Foundation\Rendering\AppErrorRenderer}.
     * Использует {@see Core\Routing\Router} для генерации или поиска шаблонов ошибок.
     */
    $container->singleton(ErrorRendererInterface::class, fn(Container $container) => new AppErrorRenderer(
        $container->get(Router::class),
        $container->get(Request::class),
        $container->get(Response::class)
    ));

    /**
     * ------------------------------------------------------------
     * ErrorHandler: глобальная обработка ошибок и исключений.
     * ------------------------------------------------------------
     * Регистрирует обработчики ошибок, исключений и фатальных сбоев.
     * Использует {@see Core\Services\Config} для определения debug-режима.
     * Передаёт визуализацию ошибок {@see Core\Contracts\ErrorRendererInterface}.
     */
    $container->singleton(ErrorHandler::class, fn(Container $container) => new ErrorHandler(
        $container->get(Config::class),
        $container->get(ErrorRendererInterface::class)
    ));
};