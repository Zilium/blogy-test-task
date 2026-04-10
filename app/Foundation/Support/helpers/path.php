<?php
/**
 * -----------------------------------------------------------------------------
 * Хелперы для работы с путями и пространствами имён приложения.
 * -----------------------------------------------------------------------------
 *
 * Этот набор функций обеспечивает единый способ построения абсолютных путей
 * и определения пространств имён в рамках структуры проекта.
 *
 * Основные задачи:
 *  - централизованное построение путей (base_path, app_path, storage_path и др.);
 *  - кроссплатформенная нормализация (замена `\` на `/`, устранение дублей слэшей);
 *  - динамическое формирование пространств имён (например, App\Modules\Admin);
 *  - удобное обращение к директориям и представлениям без жёсткого связывания
 *    с файловой структурой.
 *
 * ----------------------------------------------------------------------------- 
 * Примеры использования:
 * -----------------------------------------------------------------------------
 * ```php
 * // Получить путь к конфигу:
 * $path = config_path('database.php');
 *
 * // Получить путь к представлению в публичной зоне:
 * $view = view_path('public', 'errors/404.php');
 *
 * // Получить пространство имён контроллера панели администратора:
 * $namespace = namespace_path('admin'); // App\Modules\Admin
 * ```
 *
 * ----------------------------------------------------------------------------- 
 * Особенности:
 * -----------------------------------------------------------------------------
 * - Все пути нормализуются через {@see normalize_path()} в UNIX-формат (`/`);
 * - Функции используют {@see ROOT_DIR} как корневую точку отсчёта;
 * - Пространства имён формируются динамически, с учётом `config('app.namespace')`;
 * - Предназначены для использования ядром, резолверами и сервисами,
 *   которым требуется вычислять пути и FQCN-классы на лету.
 *
 * @package App\Foundation\Support\helpers
 */
declare(strict_types=1);

/**
 * Возвращает абсолютный путь к корню приложения.
 *
 * @param string $path Относительный путь (опционально).
 *
 * @return string Абсолютный путь.
 */
function base_path(string $path = ''): string
{
    $path = ROOT_DIR . ($path ? DIRECTORY_SEPARATOR . $path : '');
    return normalize_path($path);
}

/**
 * Возвращает абсолютный путь к директории /app.
 *
 * @param string $path Относительный путь внутри директории /app (опционально).
 *
 * @return string Абсолютный путь.
 */
function app_path(string $path = ''): string
{
    $path = base_path('App' . ($path ? DIRECTORY_SEPARATOR . $path : ''));
    return normalize_path($path);
}

/**
 * Возвращает абсолютный путь к директории /storage.
 *
 * @param string $path Относительный путь внутри /storage (опционально).
 *
 * @return string Абсолютный путь.
 */
function storage_path(string $path = ''): string
{
    $path = base_path('storage' . ($path ? DIRECTORY_SEPARATOR . $path : ''));
    return normalize_path($path);
}

/**
 * Возвращает абсолютный путь к директории /config.
 *
 * @param string $path Относительный путь внутри /config (опционально).
 *
 * @return string Абсолютный путь.
 */
function config_path(string $path = ''): string
{
    $path = base_path('config' . ($path ? DIRECTORY_SEPARATOR . $path : ''));
    return normalize_path($path);
}

if (!function_exists('routes_path')) {
    /**
     * Возвращает абсолютный путь к директории /config.
     *
     * @param string $path Относительный путь внутри /config (опционально).
     *
     * @return string Абсолютный путь.
     */
    function routes_path(string $path = ''): string
    {
        $path = base_path('routes' . ($path ? DIRECTORY_SEPARATOR . $path : ''));
        return normalize_path($path);
    }
}


/**
 * Возвращает абсолютный путь к директории /resources.
 *
 * @param string $path Относительный путь внутри /resources (опционально).
 *
 * @return string Абсолютный путь.
 */
function resources_path(string $path = ''): string
{
    $path = base_path('resources' . ($path ? DIRECTORY_SEPARATOR . $path : ''));
    return normalize_path($path);
}

/**
 * Возвращает абсолютный путь к директории представлений (views)
 * с учётом префикса (public, admin).
 *
 * Пример:
 * ```php
 * view_path('public', 'errors/404.php');
 * // => /var/www/project/resources/views/public/errors/404.php
 * ```
 *
 * @param string $prefix Префикс (например, "public" или "admin").
 * @param string $path Относительный путь внутри каталога /views/prefix.
 *
 * @return string Абсолютный путь к представлению.
 */
function view_path(string $path = ''): string
{
    $base = resources_path('templates');
    
    if (empty($path)) return normalize_path($base);

    $fullPath = $base . '/' . ltrim($path, '/\\');

    return normalize_path($fullPath);
}

/**
 * Приводит путь к единообразному (UNIX-стилю) виду.
 *
 * Заменяет обратные слэши на прямые, убирает дублирующиеся разделители
 * и нормализует относительные сегменты вроде "./" и "../".
 *
 * @param string $path Путь для нормализации.
 *
 * @return string Нормализованный путь.
 */
function normalize_path(string $path): string
{
    $path = str_replace('\\', '/', $path);
    $path = preg_replace('#/+#', '/', $path);
    return rtrim($path, '/');
}