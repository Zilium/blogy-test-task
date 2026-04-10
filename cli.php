<?php

declare(strict_types=1);

use Core\Database\DB;
use Database\Seeders\DatabaseSeeder;

require_once __DIR__ . '/bootstrap/constants.php';
require_once VENDOR_DIR . '/autoload.php';

$command = $argv[1] ?? null;
$db = DB::getInstance();

/**
 * Получить список файлов миграций.
 * 
 * @return array
 */
function getMigrationFiles(): array
{
    $files = glob(__DIR__ . '/database/migrations/*.php');
    sort($files);

    return $files ?: [];
}

/**
 * Загрузить миграцию из файла.
 * 
 * @param string $file
 * 
 * @return object
 */
function loadMigration(string $file): object
{
    $migration = require $file;

    if (!is_object($migration) || !method_exists($migration, 'up') || !method_exists($migration, 'down')) {
        throw new RuntimeException('Invalid migration: ' . basename($file));
    }

    return $migration;
}

/**
 * Применить все миграции.
 * 
 * @param DB $db
 * 
 * @return void
 */
function runMigrate(DB $db): void
{
    $files = getMigrationFiles();

    if (empty($files)) {
        echo "No migration files found.\n";
        return;
    }

    foreach ($files as $file) {
        $migration = loadMigration($file);
        $migration->up($db);

        echo '[OK] migrate ' . basename($file) . PHP_EOL;
    }
}

/**
 * Откатить все миграции в обратном порядке.
 * 
 * @param DB $db
 * 
 * @return void
 */
function runDrop(DB $db): void
{
    $files = getMigrationFiles();

    if (empty($files)) {
        echo "No migration files found.\n";
        return;
    }

    $files = array_reverse($files);

    foreach ($files as $file) {
        $migration = loadMigration($file);
        $migration->down($db);

        echo '[OK] drop ' . basename($file) . PHP_EOL;
    }
}

/**
 * Заполнить БД тестовыми данными.
 * 
 * @param DB $db
 * 
 * @return void
 */
function runSeed(DB $db): void
{
    (new DatabaseSeeder())->run($db);

    echo "Seeding completed.\n";
}

try {
    switch ($command) {
        case 'migrate':
            runMigrate($db);
            break;

        case 'seed':
            runSeed($db);
            break;

        case 'drop':
            runDrop($db);
            break;

        case 'fresh':
            runDrop($db);
            runMigrate($db);
            runSeed($db);
            break;

        default:
            echo "Available commands:\n";
            echo "  php cli.php migrate\n";
            echo "  php cli.php seed\n";
            echo "  php cli.php drop\n";
            echo "  php cli.php fresh\n";
            exit(0);
    }
} catch (Throwable $e) {
    echo '[ERROR] ' . $e->getMessage() . PHP_EOL;
    exit(1);
}