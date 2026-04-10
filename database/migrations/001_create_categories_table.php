<?php

declare(strict_types=1);

use Core\Database\DB;

return new class {
    
    public function up(DB $db): void
    {
        $db->query('
            CREATE TABLE IF NOT EXISTS `categories` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `title` VARCHAR(255) NOT NULL,
                `description` TEXT NULL DEFAULT NULL,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
        ');
    }

    public function down(DB $db): void
    {
        $db->query('DROP TABLE IF EXISTS `categories`');
    }
};