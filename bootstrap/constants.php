<?php
/**
 * -----------------------------------------------------------------------------
 * Глобальные константы приложения.
 * -----------------------------------------------------------------------------
 *
 * @package Bootstrap
 */
declare(strict_types=1);

// -----------------------------------------------------------------------------
// Core
// -----------------------------------------------------------------------------
define('ROOT_DIR', dirname(__DIR__));
define('CORE_DIR', ROOT_DIR . '/core');
define('CONFIG_DIR', ROOT_DIR . '/config');
define('STORAGE_DIR', ROOT_DIR . '/storage');
define('VENDOR_DIR', ROOT_DIR . '/vendor');
define('PUBLIC_DIR', ROOT_DIR . '/public');
define('LOGS_DIR', STORAGE_DIR . '/logs');
define('SERVICES_DIR', CONFIG_DIR . '/services');
define('PROFILES_DIR', SERVICES_DIR . '/profiles');
define('RESOURCES_DIR', ROOT_DIR . '/resources');
define('CORE_HELPERS_DIR', CORE_DIR . '/Support/helpers');

// -----------------------------------------------------------------------------
// App
// -----------------------------------------------------------------------------
define('APP_DIR', ROOT_DIR . '/app');
define('APP_HELPERS_DIR', APP_DIR . '/Foundation/Support/helpers');