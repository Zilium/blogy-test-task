<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Core\Http\Response;

/**
 * Контроллер обработки ошибок.
 * 
 * Отвечает за отображение пользовательских страниц ошибок (например, 404).
 * 
 * Используется при возникновении исключений или попытках доступа
 * к несуществующим маршрутам на сайте. Позволяет рендерить кастомные
 * страницы ошибок с использованием стандартного шаблона публичной части.
 */
class Error extends Controller
{
    /**
     * Основное действие контроллера для отображения страницы 404.
     * 
     * Метод вызывается при обращении к несуществующему маршруту и подготавливает данные,
     * передаваемые во View. Обычно используется шаблон `templates/error/404.tpl`.
     * 
     * @return Response
     */
    public function show404(): Response
    {
        $this->shareStyleBundles();

        $html = $this->view
            ->setMeta('404 - Страница не найдена')
            ->setView('errors/404')
            ->setData($this->route['params'] ?? [])
            ->render();

        return $this->response->html($html, $this->response::HTTP_NOT_FOUND);
    }

    /**
     * Основное действие контроллера для отображения страницы 500.
     * 
     * @return Response
     */
    public function showProductionError(): Response
    {
        $this->shareStyleBundles();

        $html = $this->view
            ->setMeta('Ошибка')
            ->setView('errors/production')
            ->setData($this->route['params'] ?? [])
            ->render();

        return $this->response->html($html, $this->response::HTTP_INTERNAL_SERVER_ERROR);
    }
}