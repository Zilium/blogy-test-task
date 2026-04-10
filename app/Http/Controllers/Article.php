<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Core\Http\Response;
use App\Application\Pages\Article\Service;

/**
 * Контроллер страницы статьи.
 */
class Article extends Controller
{
    /**
     * Отображает страницу статьи.
     *
     * @return Response
     */
    public function index(): Response
    {
        $this->shareStyleBundles(['article']);

        $id = (int) ($this->route['id'] ?? 0);

        /** @var Service $service */
        $service = $this->container->get(Service::class);
        $data = $service->getData($id);

        $html = $this->view
            ->setMeta($data['article']['title'], 'description', 'keywords')
            ->setView('pages/article/index')
            ->setData($data)
            ->render();

        return $this->response->html($html);
    }
}