<?php

declare(strict_types=1);

namespace Database\Seeders;

use Core\Database\DB;

class ArticleSeeder
{
    public function run(DB $db): void
    {
        $articles = [
            [
                'title' => 'Article 1. Lorem ipsum dolor sit amet',
                'description' => '',
                'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit...',
                'image' => '/uploads/articles/1.jpg',
                'views' => random_int(10, 500),
                'published_at' => '2026-04-01 10:00:00',
                'categories' => [1, 2, 3, 4],
            ],
            [
                'title' => 'Article 2. Lorem ipsum dolor sit amet',
                'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut quis elit pulvinar, efficitur ipsum eu, faucibus enim.',
                'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit...',
                'image' => '/uploads/articles/2.jpg',
                'views' => random_int(10, 500),
                'published_at' => '2026-04-02 11:00:00',
                'categories' => [1, 2, 3, 4],
            ],
            [
                'title' => 'Article 3. Lorem ipsum dolor sit amet',
                'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut quis elit pulvinar, efficitur ipsum eu, faucibus enim.',
                'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit...',
                'image' => '/uploads/articles/3.jpg',
                'views' => random_int(10, 500),
                'published_at' => '2026-04-03 12:00:00',
                 'categories' => [1, 2, 3, 4],
            ],
            [
                'title' => 'Article 4. Lorem ipsum dolor sit amet',
                'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut quis elit pulvinar, efficitur ipsum eu, faucibus enim.',
                'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit...',
                'image' => '/uploads/articles/4.jpg',
                'views' => random_int(10, 500),
                'published_at' => '2026-04-04 13:00:00',
                 'categories' => [1],
            ],
            [
                'title' => 'Article 5. Lorem ipsum dolor sit amet',
                'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut quis elit pulvinar, efficitur ipsum eu, faucibus enim.',
                'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit...',
                'image' => '/uploads/articles/5.jpg',
                'views' => random_int(10, 500),
                'published_at' => '2026-04-05 14:00:00',
                 'categories' => [1],
            ],
            [
                'title' => 'Article 6. Lorem ipsum dolor sit amet',
                'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut quis elit pulvinar, efficitur ipsum eu, faucibus enim.',
                'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit...',
                'image' => '/uploads/articles/6.jpg',
                'views' => random_int(10, 500),
                'published_at' => '2026-04-06 15:00:00',
                'categories' => [1, 3],
            ],
            [
                'title' => 'Article 7. Lorem ipsum dolor sit amet',
                'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut quis elit pulvinar, efficitur ipsum eu, faucibus enim.',
                'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit...',
                'image' => '/uploads/articles/7.jpg',
                'views' => random_int(10, 500),
                'published_at' => '2026-04-07 15:00:00',
                'categories' => [1, 4],
            ],
            [
                'title' => 'Article 8. Lorem ipsum dolor sit amet',
                'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut quis elit pulvinar, efficitur ipsum eu, faucibus enim.',
                'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit...',
                'image' => '/uploads/articles/8.jpg',
                'views' => random_int(10, 500),
                'published_at' => '2026-04-08 15:00:00',
                'categories' => [1],
            ],
        ];

        foreach ($articles as $article) {
            $db->buildQuery('INSERT INTO `articles` SET', [  
                'title' => $article['title'],
                'description' => $article['description'],
                'image' => $article['image'],
                'content' => $article['content'],
                'views' => $article['views'],
                'published_at' => $article['published_at'],
            ]);

            $articleId = (int) $db->insertId();

            foreach ($article['categories'] as $categoryId) {
                 $db->buildQuery('INSERT INTO `article_category` SET', [
                    'article_id' => $articleId,
                    'category_id' => $categoryId,
                ]);
            }
        }
    }
}