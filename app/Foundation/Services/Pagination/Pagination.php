<?php

declare(strict_types=1);

namespace App\Foundation\Services\Pagination;

/**
 * Класс Pagination
 * ---------------------------------------------------------------
 * Представляет объект чистой пагинации (DTO), содержащий
 * вычисленные данные для отображения постраничной навигации.
 *
 * Класс НЕ выполняет рендеринг HTML и НЕ генерирует ссылки.
 * Все данные, необходимые для UI-компонента (например baseUrl,
 * queryParams, prev/next/pages), предоставляются через toArray().
 *
 * Применение:
 *   - принимает параметры текущей страницы, общего количества,
 *     limit perPage, а также данные запроса (URL + GET-параметры);
 *   - вычисляет totalPages, prev/next, окно страниц;
 *   - используется во view-компоненте для построения пагинации.
 *
 * @package App\Foundation\Services\Pagination
 */
class Pagination
{
    /**
     * Текущая страница.
     *
     * @var int
     */
    private int $currentPage;

    /**
     * Количество элементов на одной странице.
     *
     * @var int
     */
    private int $perPage;

    /**
     * Общее количество элементов.
     *
     * @var int
     */
    private int $total;

    /**
     * Общее количество страниц.
     *
     * @var int
     */
    private int $totalPages;

    /**
     * Базовый путь запроса (URI без query string).
     *
     * @var string
     */
    private string $baseUrl;

    /**
     * GET-параметры текущего запроса (без page).
     *
     * @var array<string, mixed>
     */
    private array $queryParams;

    /**
     * Конструктор пагинации.
     *
     * @param int         $page        Номер текущей страницы.
     * @param int         $perPage     Количество элементов на странице.
     * @param int         $total       Общее количество элементов.
     * @param string      $baseUrl     Базовый URL без параметров (?page=...).
     * @param array       $queryParams GET-параметры текущего запроса.
     */
    public function __construct(int $page, int $perPage, int $total, string $baseUrl, array $queryParams)
    {
        $this->perPage = max(1, $perPage);
        $this->total = max(0, $total);

        $this->totalPages = max(1, (int)ceil($this->total / $this->perPage));
        $this->currentPage = $this->resolvePage($page);

        $this->baseUrl = $baseUrl;
        $this->queryParams = $queryParams;
    }

    /**
     * Возвращает текущую страницу.
     *
     * @return int
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * Возвращает общее количество страниц.
     *
     * @return int
     */
    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    /**
     * Возвращает количество элементов на странице.
     *
     * @return int
     */
    public function getPerPage(): int
    {
        return $this->perPage;
    }

    /**
     * Возвращает общее количество элементов.
     *
     * @return int
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * Определяет, существует ли предыдущая страница.
     *
     * @return bool
     */
    public function hasPrev(): bool
    {
        return $this->currentPage > 1;
    }

    /**
     * Определяет, существует ли следующая страница.
     *
     * @return bool
     */
    public function hasNext(): bool
    {
        return $this->currentPage < $this->totalPages;
    }

    /**
     * Возвращает номер предыдущей страницы.
     *
     * @return int
     */
    public function getPrevPage(): int
    {
        return max(1, $this->currentPage - 1);
    }

    /**
     * Возвращает номер следующей страницы.
     *
     * @return int
     */
    public function getNextPage(): int
    {
        return min($this->totalPages, $this->currentPage + 1);
    }

    /**
     * Возвращает окно страниц вокруг текущей.
     * По умолчанию показывает две страницы слева и справа.
     *
     * @param int $radius Количество страниц слева и справа от текущей.
     *
     * @return array<int>
     */
    public function getPageWindow(int $radius = 2): array
    {
        $pages = [];

        $start = max(1, $this->currentPage - $radius);
        $end = min($this->totalPages, $this->currentPage + $radius);

        for ($i = $start; $i <= $end; $i++) {
            $pages[] = $i;
        }

        return $pages;
    }

    /**
     * Корректирует номер страницы, предотвращая выход за границы.
     *
     * @param int $page
     * @return int
     */
    private function resolvePage(int $page): int
    {
        if ($page < 1) return 1;
        if ($page > $this->totalPages) return $this->totalPages;
        return $page;
    }

    /**
     * Возвращает массив данных для компонента пагинации.
     * Компонент рендеринга (view) использует эти данные
     * для построения HTML-разметки.
     *
     * @return array{
     *     total: int,
     *     per_page: int,
     *     current_page: int,
     *     total_pages: int,
     *     has_prev: bool,
     *     has_next: bool,
     *     prev_page: int,
     *     next_page: int,
     *     window: array<int>,
     *     base_url: string,
     *     params: array<string,mixed>
     * }
     */
    public function toArray(): array
    {
        return [
            'total' => $this->total,
            'per_page' => $this->perPage,
            'current_page' => $this->currentPage,
            'total_pages' => $this->totalPages,
            'has_prev' => $this->hasPrev(),
            'has_next' => $this->hasNext(),
            'prev_page' => $this->getPrevPage(),
            'next_page' => $this->getNextPage(),
            'window' => $this->getPageWindow(),
            'base_url' => $this->baseUrl,
            'params' => $this->queryParams,
        ];
    }

}
