<?php

declare(strict_types=1);

namespace App\Foundation\Services;

use RuntimeException;
use Throwable;

/**
 * Рендерит PHP-шаблоны для заданной области (admin/public).
 * 
 * Поддерживает структуру вида "папка/шаблон", возможность переопределения имени файла,
 * безопасно извлекает переменные и возвращает HTML-контент.
 */
class ViewRenderer
{
    /**
     * Рендерит шаблон с заданными данными.
     *
     * @param string $template Относительный путь к шаблону без расширения (.php).
     * @param array $data Переменные, доступные внутри шаблона.
     * @param string $overrideFileName Имя файла, если отличается от последнего сегмента пути.
     * @param string $area Область шаблона: 'admin' или 'public'.
     * 
     * @return string HTML-контент шаблона.
     * 
     * @throws RuntimeException Если шаблон не найден.
     */
    public static function render(
        string $template, 
        array $data = [], 
        string $overrideFileName = '',
    ): string {

        $templatePath = self::buildTemplatePath($template, $overrideFileName);

        return self::renderTemplate($templatePath, $data);
    }

    /**
     * Собирает путь к файлу шаблона.
     * 
     * @param string $template Относительный путь к шаблону.
     * @param string $overrideFileName Имя файла (если отличается).
     * 
     * @return string Полный путь к шаблону.
     * 
     * @throws RuntimeException Если файл не найден.
     */
    private static function buildTemplatePath(string $template, string $overrideFileName): string
    {
        $arrTemplate = explode('/', $template);
        
        $dirs = $arrTemplate;
        $rawFileName = array_pop($dirs);

        $fileName = $overrideFileName !== '' 
            ? $overrideFileName 
            : kebabToSnake($rawFileName);

        $path = implode('/', $arrTemplate) . '/' . $fileName . '.php';

        $fullPath = view_path($path);

        if (!file_exists($fullPath)) {
            throw new RuntimeException("Шаблон {$template} не найден по пути: {$fullPath}", 422);
        }

        return $fullPath;
    }

    /**
     * Выполняет шаблон и возвращает его содержимое.
     * 
     * @param string $fullPath Полный путь к файлу шаблона.
     * @param array $data Данные, доступные в шаблоне.
     * 
     * @return string HTML-результат выполнения шаблона.
     * 
     * @throws RuntimeException При ошибке выполнения шаблона.
     */
    private static function renderTemplate(string $fullPath, array $data): string
    {
        try {
            extract($data);
            ob_start();
            
            include $fullPath;
            
            return (string) ob_get_clean();
        } catch (Throwable $e) {
            ob_end_clean();
            throw new RuntimeException("Ошибка рендеринга шаблона: {$e->getMessage()}", $e->getCode(), $e);
        }
    }
}