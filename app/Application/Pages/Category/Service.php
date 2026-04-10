<?php

declare(strict_types=1);

namespace App\Application\Pages\Category;

use App\Repositories\Category AS Repository;
use App\Foundation\Services\Pagination\Paginator;
use App\Foundation\Services\Pagination\Pagination;
use Exception;

/**
 * Сервис подготовки данных страницы категории.
 */
final class Service
{
    /**
     * @param Repository $repository Репозиторий категорий.
     */
    public function __construct(
        private Repository $repository,
    ) {
    }

    /**
     * Возвращает данные для страницы категории.
     *
     * @param int $categoryId ID категории.
     * @param int $page Текущая страница.
     * @param int $perPage Количество записей на страницу.
     * @param string $sortBy Поле сортировки.
     * @param string $baseUrl Базовый URL пагинации.
     * @param array $queryParams Query-параметры запроса.
     *
     * @return array{
     *     category: array,
     *     articles: array,
     *     total: int,
     *     pagination: array
     * }
     *
     * @throws Exception Если категория не найдена.
     */
    public function getData(
        int $categoryId,
        int $page,
        int $perPage,
        string $sortBy,
        string $baseUrl,
        array $queryParams
    ): array {
        $category = $this->repository->getCategoryById($categoryId);
        if (!$category) {
            throw new Exception('Страница не найдена', 404);
        }

        $sql = $this->repository->getCategoryArticlesQuery($categoryId, $sortBy);

        /** @var Paginator $paginator */
        $paginator = app()->make(Paginator::class, [
            'sql' => $sql,
            'page' => $page,
            'perPage' => $perPage,
        ]);

        $articles = $paginator->getData();
        $total = $paginator->getTotalRecords();

        foreach ($articles as $index => $article) {
            $articles[$index]['published_at'] = format_date($article['published_at']);
        }

        /** @var Pagination $pagination */
        $pagination = app()->make(Pagination::class, [
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'baseUrl' => $baseUrl,
            'queryParams' => $queryParams,
        ])->toArray();

        return [
            'category' => $category,
            'articles' => $articles,
            'total' => $total,
            'pagination' => $pagination,
        ];
    }
}