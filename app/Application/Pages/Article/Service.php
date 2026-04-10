<?php

declare(strict_types=1);

namespace App\Application\Pages\Article;

use App\Repositories\Article AS Repository;
use Exception;

/**
 * Сервис подготовки данных страницы статьи.
 */
final class Service
{
    /**
     * @param Repository $repository Репозиторий статей.
     */
    public function __construct(
        private Repository $repository,
    ) {
    }

    /**
     * Возвращает данные для страницы статьи.
     *
     * @param int $id ID статьи.
     *
     * @return array{
     *     article: array,
     *     related: array{
     *         articles: array
     *     }
     * }
     *
     * @throws Exception Если статья не найдена.
     */
    public function getData(int $id): array
    {
        $article = $this->repository->getArticleById($id);
        if (!$article) {
            throw new Exception('Страница не найдена', 404);
        }

        $articleId = (int) $article['id'];
        if ($this->shouldIncrementViews($articleId)) {
            $this->repository->incrementViews($articleId);

            if (isset($article['views'])) {
                $article['views'] = (int) $article['views'] + 1;
            }
        }
       
        $article['published_at'] = format_date($article['published_at']);

        $relatedArticles = $this->repository->getSimilarArticles($articleId);
        foreach ($relatedArticles as $index => $relatedArticle) {
            $relatedArticles[$index]['published_at'] = format_date($relatedArticle['published_at']);
        }

        return [
            'article' => $article,
            'related' => [
                'articles' => $relatedArticles,
            ],
        ];
    }

    /**
     * Определяет, нужно ли увеличивать счетчик просмотров статьи.
     *
     * @param int $id ID статьи.
     *
     * @return bool
     */
    private function shouldIncrementViews(int $id): bool
    {
        if (!(bool) config('app.articles.unique_views_in_session', true)) {
            return true;
        }

        $sessionKey = 'article.views.' . $id;

        if (session()->get($sessionKey)) {
            return false;
        }

        session()->set($sessionKey, true);

        return true;
    }
}