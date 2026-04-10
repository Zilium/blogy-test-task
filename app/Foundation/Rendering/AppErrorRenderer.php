<?php

declare(strict_types=1);

namespace App\Foundation\Rendering;

use Core\Contracts\ErrorRendererInterface;

use Core\Routing\Router;
use Core\Http\Request;
use Core\Http\Response;

/**
 * -----------------------------------------------------------------------------
 * Класс AppErrorRenderer.
 * -----------------------------------------------------------------------------
 * Реализация {@see ErrorRendererInterface} для визуализации ошибок приложения.
 * 
 * Отвечает за обработку ошибок и исключений, поступающих из {@see Core\Services\ErrorHandler},
 * 
 * -----------------------------------------------------------------------------
 * Основные задачи:
 * -----------------------------------------------------------------------------
 * • Отображение подробных ошибок в режиме отладки (`debug = true`);
 * • Делегирование отображения системных ошибок контроллеру (`ErrorController`);
 * • Отображение шаблонов для пользователей;
 *
 * Благодаря этой реализации ядро (`Core`) не зависит от структуры каталогов приложения (`App`),
 * а механизм рендеринга ошибок может быть переопределён на уровне проекта.
 *
 * @package App\Foundation\Rendering
 */
class AppErrorRenderer implements ErrorRendererInterface
{
    /**
     * Конструктор AppErrorRenderer.
     *
     * @param Router $router Маршрутизатор приложения, используется для вызова контроллеров ошибок.
     * @param Request $request Объект HTTP-запроса.
     * @param Response $response Объект HTTP-ответа.
     */
    public function __construct(
        private readonly Router $router,
        private readonly Request $request,
        private readonly Response $response
    ) {}

    /**
     * Выполняет визуализацию ошибки в зависимости от статуса и режима отладки.
     *
     * @param array<string, mixed> $data Ассоциативный массив данных об ошибке:
     *     - `errno`   - код или тип ошибки;
     *     - `message` - текст сообщения;
     *     - `file`    - путь к файлу, где произошла ошибка;
     *     - `line`    - строка, на которой произошла ошибка;
     *     - `status`  - HTTP-код ответа.
     * @param int  $status HTTP-статус ошибки (например, 404, 500, 403).
     * @param bool $debug Флаг режима отладки (`true` - подробный вывод ошибок).
     *
     * @return void
     */
    public function render(array $data, int $status, bool $debug): Response
    {
        /**
         * ------------------------------------------------------------
         * РЕЖИМ ОТЛАДКИ (debug = true)
         * ------------------------------------------------------------
         * Если включён debug, отображаем шаблон development.php
         * с подробной информацией об ошибке.
         */
        if ($debug) {
            $path = view_path('errors/development.tpl');
           
            if (file_exists($path)) {
                $html = $this->renderTemplate($path, [
                    'data' => $data,
                    'status' => $status,
                ]);

                return $this->response
                        ->setStatusCode($status)
                        ->setHeader('Content-Type', $this->response::DEFAULT_CONTENT_TYPE)
                        ->setContent($html);
            }
        }

        /**
         * ------------------------------------------------------------
         * 404 - СТРАНИЦА НЕ НАЙДЕНА
         * ------------------------------------------------------------
         * Передаём управление контроллеру ошибки, чтобы отобразить
         * полноценную страницу с layout и общими данными.
         */
        if ($status === 404) {
            $this->router->setRoute([
                'controller' => 'Error',
                'action' => 'show404',
                'params' => $data,
            ]);

            return $this->router->dispatchController();
        }

        /**
         * ------------------------------------------------------------
         * ПРОИЗВОДСТВЕННЫЙ РЕЖИМ
         * ------------------------------------------------------------
         * Отображаем шаблон production.php - общую страницу ошибок.
         */
        $this->router->setRoute([
            'controller' => 'Error',
            'action' => 'showProductionError',
            'params' => $data,
        ]);

        return $this->router->dispatchController();

        /**
         * ------------------------------------------------------------
         * FALLBACK — если шаблон не найден
         * ------------------------------------------------------------
         * Выводим простую HTML-страницу с минимальной информацией.
         */
        $html = "<h1>Ошибка {$status}</h1>"
            . "<pre>" . htmlspecialchars(print_r($data, true)) . "</pre>";

        return $this->response
                ->setStatusCode($status)
                ->setHeader('Content-Type', $this->response::DEFAULT_CONTENT_TYPE)
                ->setContent($html);
    }


    /**
     * Рендерит PHP-шаблон в строку (без echo/exit).
     *
     * @param string $path
     * @param array<string, mixed> $vars
     *
     * @return string
     */
    private function renderTemplate(string $path, array $vars = []): string
    {
        ob_start();

        extract($vars, EXTR_SKIP);
        require $path;

        return (string) ob_get_clean();
    }
}