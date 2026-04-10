<?php

declare(strict_types=1);

use Core\Database\DB;

return new class {
    
    public function up(DB $db): void
    {
        $db->query('
            CREATE TABLE IF NOT EXISTS `articles` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `title` VARCHAR(255) NOT NULL,
                `description` TEXT NULL DEFAULT NULL,
                `image` VARCHAR(255) NULL DEFAULT NULL,
                `content` LONGTEXT NOT NULL,
                `views` INT UNSIGNED NOT NULL DEFAULT 0,
                `published_at` TIMESTAMP NULL DEFAULT NULL,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                INDEX `idx_published_at` (`published_at`),
                INDEX `idx_views` (`views`)
            ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
        ');
    }

    public function down(DB $db): void
    {
        $db->query('DROP TABLE IF EXISTS `articles`');
    }
};