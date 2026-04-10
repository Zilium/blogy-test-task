<?php

declare(strict_types=1);

namespace App\Foundation\Services\Pagination;

use Core\Database\DB;

/**
 * Class Paginator
 *
 * Выполняет постраничную навигацию SQL-запросов.
 * Позволяет получить общее количество записей, данные текущей страницы,
 * а также корректно вычисляет смещение и предел выборки (LIMIT).
 *
 * SQL-запрос должен быть валидным SELECT без LIMIT.
 *
 * @package App\Foundation\Services\Pagination
 * @version 1.0.0
 */
class Paginator
{
    /** 
     * Исходный SQL-запрос без LIMIT.
     * 
     * @var string 
     */
    private string $sql;

    /**
     * Номер текущей страницы (начиная с 1).
     * 
     * @var int 
     */
    private int $page;

    /** 
     * Количество записей на странице.
     * 
     * @var int 
     */
    private int $perPage;

    /** 
     * Общее количество строк, найденных по исходному запросу.
     * 
     * @var int 
     */
    private int $totalRecords;
    
    /**
     * Данные, полученные после выполнения запроса с LIMIT.
     * 
     * @var array 
     */
    private array $data = [];

    /** 
     * Экземпляр класса DB для выполнения SQL-запросов.
     * 
     * @var DB
     */
    private DB $db;
    
    /**
     * Конструктор выполняет SQL-запрос и инициализирует параметры пагинации.
     * 
     * @param DB $db Экземпляр базы данных.
     * @param string $sql SQL-запрос без LIMIT.
     * @param int $page Номер страницы (начиная с 1).
     * @param int $perPage Количество записей на странице.
     */
    public function __construct(DB $db, string $sql, int $page, int $perPage)
    {
        $this->db = $db;
        $this->sql = $sql;
        $this->page = max(1, $page);
        $this->perPage = max(1, $perPage);
        
        $this->totalRecords = $this->countTotalRecords();
        $this->applyPagination();
        $this->fetchData();
    }

    /**
     * Подсчитывает количество строк, удовлетворяющих исходному SQL-запросу.
     * Удаляет ORDER BY, если он присутствует.
     *
     * @return int Общее количество записей.
     */
    private function countTotalRecords(): int
    {
        $baseSql = preg_replace('/ORDER BY .*/i', '', $this->sql);
        
        $sql = "SELECT COUNT(*) AS count FROM ({$baseSql}) AS subquery";
        $query = $this->db->query($sql);
        $result = $this->db->fetchAssoc($query);

        return (int) ($result['count'] ?? 0);
    }

    /**
     * Применяет смещение и лимит к SQL-запросу.
     * Гарантирует, что номер страницы не выйдет за границы.
     *
     * @return void
     */
    private function applyPagination(): void
    {
       $maxPage = (int) ceil($this->totalRecords / $this->perPage);
       $this->page = min($this->page, max(1, $maxPage));

       $offset = ($this->page - 1) * $this->perPage;
 
        $this->sql .= ' LIMIT ' . $this->db->quoteInt($offset, true) . ', ' . $this->db->quoteInt($this->perPage, true);
    }

    /**
     * Выполняет SQL-запрос с LIMIT и сохраняет данные текущей страницы.
     *
     * @return void
     */
    private function fetchData(): void
    {
        $result = $this->db->query($this->sql);

        while ($row = $this->db->fetchAssoc($result)) {
            $this->data[] = $row;
        }
    }

    /**
     * Возвращает общее количество записей во всей выборке.
     *
     * @return int
     */
    public function getTotalRecords(): int
    {
        return $this->totalRecords;
    }

    /**
     * Возвращает данные текущей страницы.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getData(): array
    { 
        return $this->data;  
    }
}