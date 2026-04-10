<?php
/**
 * -----------------------------------------------------------------------------
 * Единая точка входа приложения.
 * -----------------------------------------------------------------------------
 * 
 * @package bootstrap
 * @author Роман Чеботарев
 */
declare(strict_types=1);

use App\Http\AppKernel;
use Core\Container;

// Проверка минимальной версии PHP.
if (version_compare(PHP_VERSION, '8.3.6', '<')) {
    exit('Для работы системы необходима версия PHP 8.3.6 или выше. Текущая версия: ' . PHP_VERSION);
}

// Подключение системных констант.
require_once __DIR__ . '/constants.php';
// Инициализация автозагрузки Composer.
require_once VENDOR_DIR . '/autoload.php';

// -----------------------------------------------------------------------------
// Окружение и URL
// -----------------------------------------------------------------------------
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
           (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) || 
           (isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] === 'https');

$protocol = $isHttps ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];

define('SITE', $protocol . $host);
define('HOST', $host);

// Создание DI-контейнер и регистрация его в самом себе.
$container = new Container();
$container->singleton(Container::class, $container);

// Запуск приложения через ядро.
$kernel = new AppKernel($container);
$kernel->run()->send();