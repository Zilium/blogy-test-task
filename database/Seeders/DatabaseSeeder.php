<?php

declare(strict_types=1);

namespace Database\Seeders;

use Core\Database\DB;
use Throwable;

class DatabaseSeeder
{
    public function run(DB $db): void
    {
        $db->beginTransaction();

        try {
            (new CategorySeeder())->run($db);
            (new ArticleSeeder())->run($db);

            $db->commit();
        } catch (Throwable $e) {
            $db->rollback();
            throw $e;
        }
    }
}