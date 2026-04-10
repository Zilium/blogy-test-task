<?php

declare(strict_types=1);

namespace App\Repositories;

final class Category extends Repository
{
    /**
     * @param int $id 
     * 
     * @return array|null
     */
    public function getCategoryById(int $id): ?array
    {
        $sql = '
            SELECT `id`, `title`, `description`
            FROM `categories`
            WHERE `id` = ' . $this->db->quoteInt($id) . '
        ';

        return $this->db->getRow($sql);
    }

    /**
     * @param @int $categoryId
     * @param string $sort
     * 
     * @return string 
     */
    public function getCategoryArticlesQuery(int $categoryId, string $sort): string
    {
        $orderBy = match ($sort) {
            'views' => 'a.`views` DESC, a.`published_at` DESC, a.`id` DESC',
            'date' => 'a.`published_at` DESC, a.`id` DESC',
            default => 'a.`published_at` DESC, a.`id` DESC',
        };

        return '
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
            ORDER BY ' . $orderBy;
    }
}