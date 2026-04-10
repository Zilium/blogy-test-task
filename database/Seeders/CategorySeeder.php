<?php

declare(strict_types=1);

namespace Database\Seeders;

use Core\Database\DB;

class CategorySeeder
{
    public function run(DB $db): void
    {
        $categories = [
            [
                'title' => 'Category 1',
                'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut quis elit pulvinar, efficitur ipsum eu,',
            ],
            [
                'title' => 'Category 2',
                'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut quis elit pulvinar, efficitur ipsum eu,',
            ],
            [
                'title' => 'Category 3',
                'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut quis elit pulvinar, efficitur ipsum eu,',
            ],
            [
                'title' => 'Category 4',
                'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut quis elit pulvinar, efficitur ipsum eu,',
            ],
            [
                'title' => 'Category 5',
                'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut quis elit pulvinar, efficitur ipsum eu,',
            ],
        ];

        foreach ($categories as $category) {
            $db->buildQuery('INSERT INTO `categories` SET', [
                'title' => $category['title'],
                'description' => $category['description'],
            ]);
        }
    }
}