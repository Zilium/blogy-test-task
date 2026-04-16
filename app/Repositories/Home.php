<?php

declare(strict_types=1);

namespace App\Repositories;

/**
 * @package App\Repositories
 */
final class Home extends Repository
{
    /**
     * @return array
     */
    public function getCategoriesWithArticles(): array
    {
        $sql = '
            SELECT
                c.`id`,
                c.`title`,
                COUNT(ac.`article_id`) AS `articles_count`
            FROM `categories` AS c
                INNER JOIN `article_category` AS ac 
                    ON ac.`category_id` = c.`id`
                INNER JOIN `articles` AS a 
                    ON a.`id` = ac.`article_id`
            WHERE a.`published_at` IS NOT NULL
                AND a.`published_at` <= NOW()
            GROUP BY c.`id`
            ORDER BY c.`title` ASC
        ';

        return $this->db->getRows($sql) ?? [];
    }

    /**
     * @param int $categoryId
     * @param int $limit
     * 
     * @return array
     */
    public function getLatestArticlesByCategory(int $categoryId, int $limit = 3): array
    {
        $sql = '
            SELECT
                a.`id`,
                a.`title`,
                a.`description`,
                a.`image`,
                a.`views`,
                a.`published_at`
            FROM `articles` AS a
            INNER JOIN `article_category` AS ac 
                ON ac.`article_id` = a.`id`
            WHERE ac.`category_id` = ' . $this->db->quoteInt($categoryId) . ' 
                AND a.`published_at` IS NOT NULL 
                AND a.`published_at` <= NOW()
            ORDER BY a.`published_at` DESC, a.`id` DESC
            LIMIT ' . $this->db->quoteInt($limit);

        return $this->db->getRows($sql) ?? [];
    }
}