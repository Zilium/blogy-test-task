<?php

declare(strict_types=1);

namespace App\Application\Pages\Home;

use App\Repositories\Home AS Repository;

/**
 * Сервис подготовки данных главной страницы.
 */
final class Service
{
    /**
     * @param Repository $repository Репозиторий главной страницы.
     */
    public function __construct(
        private Repository $repository,
    ) {
    }

    /**
     * Возвращает данные для главной страницы.
     *
     * @return array
     */
    public function getData(): array
    {
        $categories = $this->repository->getCategoriesWithArticles();

        foreach ($categories as $index => $category) {
            $articles = $this->repository->getLatestArticlesByCategory((int) $category['id'], 3);

            foreach ($articles as $articleIndex => $article) {
                $articles[$articleIndex]['published_at'] = format_date($article['published_at']);
            }

            $categories[$index]['articles'] = $articles;
        }

        return $categories;
    }
}