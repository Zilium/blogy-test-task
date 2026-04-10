<?php

declare(strict_types=1);

namespace App\Repositories;

/**
 * @package App\Repositories
 */
final class Article extends Repository
{

    /**
     * @param int $id
     * 
     * @return void
     */
   public function incrementViews(int $id): void
    {
        $sql = '
            UPDATE `articles`
            SET `views` = `views` + 1
            WHERE `id` = ' . $this->db->quoteInt($id);

        $this->db->query($sql);
    }

    /**
     * @param int $id
     * 
     * @return array|null
     */
    public function getArticleById(int $id): ?array
    {
        $sql = '
            SELECT
                `id`,
                `title`,
                `description`,
                `image`,
                `content`,
                `views`,
                `published_at`
            FROM `articles`
            WHERE `id` = ' . $this->db->quoteInt($id);

        return $this->db->getRow($sql);
    }

    /**
     * @param int $articleId
     * @param int $limit
     * 
     * @return array
     */
    public function getSimilarArticles(int $articleId, int $limit = 3): array
    {
        $sql = '
            SELECT
                a.`id`,
                a.`title`,
                a.`description`,
                a.`image`,
                a.`views`,
                a.`published_at`,
                COUNT(DISTINCT ac2.`category_id`) AS `common_categories_count`
            FROM `article_category` AS ac1
            INNER JOIN `article_category` AS ac2
                ON ac2.`category_id` = ac1.`category_id`
                    AND ac2.`article_id` != ' . $this->db->quoteInt($articleId) . '
            INNER JOIN `articles` AS a 
                ON a.`id` = ac2.`article_id`
            WHERE ac1.`article_id` = ' . $this->db->quoteInt($articleId) . '
            GROUP BY a.`id`, a.`title`, a.`description`, a.`views`, a.`published_at`
            ORDER BY `common_categories_count` DESC, a.`published_at` DESC, a.`id` DESC
            LIMIT ' . $this->db->quoteInt($limit);

        return $this->db->getRows($sql) ?? [];
    }
}