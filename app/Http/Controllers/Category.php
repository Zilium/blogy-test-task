<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Core\Http\Response;
use App\Application\Pages\Category\Service;

/**
 * Контроллер страницы категории.
 */
final class Category extends Controller
{
    /**
     * Отображает страницу категории.
     * 
     * @return Response
     */
    public function index(): Response
    {
        $this->shareStyleBundles(['category']);
       
        $id = (int) ($this->route['id'] ?? 0);
        $page = max(1, (int) $this->request->get('page', 1));
        $sort = (string) $this->request->get('sort', 'date');
        $perPage = (int) config('app.pagination.per_page', 12);

        /** @var Service $service */
        $service = $this->container->get(Service::class);
        $data = $service->getData(
            $id,
            $page,
            $perPage,
            $sort,
            $this->request->getPath(),
            $this->request->getQueryParams()
        );

        $html = $this->view
            ->setMeta($data['category']['title'], 'description', 'keywords')
            ->setView('pages/category/index')
            ->setData($data)
            ->render();

        return $this->response->html($html);
    }
}