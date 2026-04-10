<?php

declare(strict_types=1);

namespace App\Foundation\Support;

use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\Exception\SassException;
use ScssPhp\ScssPhp\OutputStyle;

use RuntimeException;

/**
 * Сборщик SCSS-бандлов.
 *
 * Отвечает за:
 * - компиляцию entry-файлов SCSS в CSS;
 * - отслеживание изменений исходных файлов через meta-файл;
 * - генерацию ссылок на стили с версией по времени изменения файла.
 *
 * В режиме разработки CSS пересобирается при изменении SCSS-файлов.
 * В продакшен-режиме сборка выполняется только один раз, если CSS-файл
 * еще не существует.
 */
final class ScssBuilder
{
    /**
     * @param bool $devMode Режим разработки.
     *                      true - пересобирать CSS при изменениях SCSS;
     *                      false - собирать только если итоговый CSS еще не создан.
     */
    public function __construct(
        private bool $devMode = false,
    ) {
    }

    /**
     * Собирает несколько SCSS-бандлов.
     *
     * @param array<int, string> $bundles Список имен бандлов без расширения.
     *
     * @return void
     */
    public function buildBundles(array $bundles): void
    {
        foreach ($bundles as $bundle) {
            $this->buildBundle($bundle);
        }
    }

    /**
     * Собирает один SCSS-бандл в CSS-файл.
     *
     * Entry-файл ищется по пути:
     * resources/assets/scss/entries/{bundle}.scss
     *
     * Итоговый CSS сохраняется по пути:
     * public/assets/css/{bundle}.css
     *
     * Meta-файл с временем изменения исходников сохраняется по пути:
     * storage/cache/scss/{bundle}.meta.json
     *
     * @param string $bundle Имя бандла без расширения.
     *
     * @throws RuntimeException Если entry-файл не найден, произошла ошибка
     *                          компиляции или не удалось записать файлы.
     *
     * @return void
     */
    public function buildBundle(string $bundle): void
    {
        $sourceFile = RESOURCES_DIR . '/assets/scss/entries/' . $bundle . '.scss';
        $outputFile = PUBLIC_DIR . '/assets/css/' . $bundle . '.css';
        $metaFile = STORAGE_DIR . '/cache/scss/' . $bundle . '.meta.json';

        if (!is_file($sourceFile)) {
            throw new RuntimeException("SCSS entry not found: {$sourceFile}");
        }

        $this->ensureDir(dirname($outputFile));
        $this->ensureDir(dirname($metaFile));

        if ($this->shouldSkipBuild($outputFile)) {
            return;
        }

        if (!$this->needsBuild($outputFile, $metaFile)) {
            return;
        }

        $compiler = new Compiler();
        $compiler->setImportPaths(RESOURCES_DIR . '/assets/scss');
        $compiler->setOutputStyle(
            $this->devMode ? OutputStyle::EXPANDED : OutputStyle::COMPRESSED
        );

        try {
            $result = $compiler->compileFile($sourceFile);
        } catch (SassException $e) {
            throw new RuntimeException('SCSS compile error: ' . $e->getMessage(), 0, $e);
        }

        if (file_put_contents($outputFile, $result->getCss()) === false) {
            throw new RuntimeException("Failed to write compiled CSS: {$outputFile}");
        }

        $files = array_unique([$sourceFile, ...$result->getIncludedFiles()]);
        $meta = ['files' => []];

        foreach ($files as $file) {
            $meta['files'][$file] = is_file($file) ? filemtime($file) : 0;
        }

        if (file_put_contents(
            $metaFile,
            json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)
        ) === false) {
            throw new RuntimeException("Failed to write SCSS meta file: {$metaFile}");
        }
    }

    /**
     * Формирует ссылки на CSS-файлы с query-параметром версии.
     *
     * Версия формируется на основе времени изменения CSS-файла.
     * Это позволяет сбрасывать кеш браузера после пересборки стилей.
     *
     * @param array<int, string> $bundles Список имен бандлов без расширения.
     *
     * @return array<int, string> Список URL-адресов CSS-файлов.
     */
    public function makeStyleLinks(array $bundles): array
    {
        $links = [];

        foreach ($bundles as $bundle) {
            $file = PUBLIC_DIR . '/assets/css/' . $bundle . '.css';
            $version = is_file($file) ? (string) filemtime($file) : (string) time();

            $links[] = '/assets/css/' . $bundle . '.css?v=' . $version;
        }

        return $links;
    }

    /**
     * Определяет, нужно ли пропустить сборку бандла.
     *
     * В продакшен-режиме сборка пропускается, если итоговый CSS-файл
     * уже существует.
     *
     * @param string $outputFile Абсолютный путь к итоговому CSS-файлу.
     *
     * @return bool true, если сборку нужно пропустить, иначе false.
     */
    private function shouldSkipBuild(string $outputFile): bool
    {
        return !$this->devMode && is_file($outputFile);
    }

    /**
     * Проверяет, требуется ли пересборка CSS.
     *
     * Сборка требуется, если:
     * - отсутствует итоговый CSS-файл;
     * - отсутствует meta-файл;
     * - meta-файл поврежден или некорректен;
     * - удален хотя бы один из исходных файлов;
     * - изменилось время модификации хотя бы одного исходного файла.
     *
     * @param string $outputFile Абсолютный путь к итоговому CSS-файлу.
     * @param string $metaFile Абсолютный путь к meta-файлу.
     *
     * @return bool true, если требуется пересборка, иначе false.
     */
    private function needsBuild(string $outputFile, string $metaFile): bool
    {
        if (!is_file($outputFile) || !is_file($metaFile)) {
            return true;
        }

        $metaContent = file_get_contents($metaFile);
        if ($metaContent === false) {
            return true;
        }

        $meta = json_decode($metaContent, true);

        if (!is_array($meta) || empty($meta['files']) || !is_array($meta['files'])) {
            return true;
        }

        foreach ($meta['files'] as $file => $mtime) {
            if (!is_file($file)) {
                return true;
            }

            $currentMtime = filemtime($file);
            if ($currentMtime === false || (int) $currentMtime !== (int) $mtime) {
                return true;
            }
        }

        return false;
    }

    /**
     * Гарантирует существование директории.
     *
     * Если директория отсутствует, будет выполнена попытка создать ее
     * рекурсивно.
     *
     * @param string $dir Абсолютный путь к директории.
     *
     * @throws RuntimeException Если директорию не удалось создать.
     *
     * @return void
     */
    private function ensureDir(string $dir): void
    {
        if (is_dir($dir)) {
            return;
        }

        if (!mkdir($dir, 0777, true) && !is_dir($dir)) {
            throw new RuntimeException("Failed to create directory: {$dir}");
        }
    }
}