<?php

declare(strict_types=1);

use Core\Database\DB;

return new class {
    
    public function up(DB $db): void
    {
        $db->query('
            CREATE TABLE IF NOT EXISTS `article_category` (
                `article_id` INT UNSIGNED NOT NULL,
                `category_id` INT UNSIGNED NOT NULL,
                PRIMARY KEY (`article_id`, `category_id`),
                INDEX `idx_category_id` (`category_id`),
                CONSTRAINT `fk_article_category_article_id`
                    FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`)
                    ON DELETE CASCADE,
                CONSTRAINT `fk_article_category_category_id`
                    FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
                    ON DELETE CASCADE
            ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
        ');
    }

    public function down(DB $db): void
    {
        $db->query('DROP TABLE IF EXISTS `article_category`');
    }
};