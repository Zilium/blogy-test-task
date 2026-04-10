<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Core\Http\Response;

use App\Application\Pages\Home\Service;

/**
 * Контроллер главной страницы.
 */
class Home extends Controller
{
    /**
     * Отображает главную страницу.
     * 
     * @return Response
     */
    public function index(): Response
    {
        $this->shareStyleBundles(['home']);

        /** @var Service $service */
        $service = $this->container->get(Service::class);
        $categories = $service->getData();

        $html = $this->view
            ->setMeta('Главная страница', 'description', 'keywords')
            ->setView('pages/home/index')
            ->setData([
                'categories' => $categories,
            ])
            ->render();

        return $this->response->html($html);
    }
}