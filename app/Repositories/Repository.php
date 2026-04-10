<?php

declare(strict_types=1);

namespace App\Repositories;

use Core\Database\DB;

/**
 * @package App\Repositories
 */
abstract class Repository
{
    /**
     * Название таблицы в БД.
     * 
     * @var string 
     */
    protected string $table;

    public function __construct(
        protected DB $db
    ) {}
}