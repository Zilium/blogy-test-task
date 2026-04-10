<?php

declare(strict_types=1);

namespace Core\Services;

use RuntimeException;

/**
 * Класс Config управляет загрузкой и доступом к конфигурации приложения.
 * 
 * Основные возможности:
 * - Автоматическая загрузка всех файлов из директории `config/`, кроме `database.php`;
 * - Доступ к значениям через "dot"-нотацию (`mail.smtp.host`);
 * - Возврат значения по умолчанию, если ключ не найден;
 * - Возможность изменять параметры во время выполнения.
 * 
 * ```php
 * $config = new Config();
 * echo $config->get('app.debug'); // true
 * echo $config->get('mail.smtp.host');
 * ```
 *
 * @package Core\Services
 * @version 1.0.0
 */
class Config
{
    /**
     * Массив конфигурационных данных приложения.
     * 
     * Формируется при загрузке всех файлов `config/*.php`.
     * 
     * @var array<string, mixed>
     */
    private array $items = [];
    
    /**
     * Конструктор.
     *
     * Загружает все конфигурационные файлы из директории `config/`,
     * исключая `database.php`. Имя файла используется как верхний ключ.
     *
     * @throws RuntimeException Если директория конфигурации не найдена
     *                          или один из файлов имеет некорректный формат.
     */
    public function __construct()
    {
        $configDir = CONFIG_DIR;

        if (!is_dir($configDir)) {
            throw new RuntimeException("Директория конфигурации не найдена: {$configDir}", 500);
        }

        // Получаем список всех .php-файлов, кроме database.php
        $files = glob($configDir . '/*.php') ?: [];
        foreach ($files as $file) {
            $filename = basename($file, '.php');

            if ($filename === 'database') {
                continue;
            }

            $data = require $file;

            if (!is_array($data)) {
                throw new RuntimeException("Некорректный формат конфигурационного файла: {$file}");
            }

            $this->items[$filename] = $data;
        }
    }

    /**
     * Возвращает значение конфигурации по ключу.
     * 
     * Поддерживает "dot"-нотацию (например: `app.debug`).
     * 
     * @param string $key Ключ конфигурации.
     * @param mixed $default Значение по умолчанию, если ключ не найден.
     * 
     * @return mixed Значение из конфигурации или $default.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        // Поддержка верхнего ключа: config('app') -> массив.
        if (array_key_exists($key, $this->items)) {
            return $this->items[$key];
        }

        // config('app.debug') → значение из вложенного массива.
        $segments = explode('.', $key);
        $value = $this->items;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    /**
     * Проверяет существование ключа в конфигурации.
     * 
     * @param string $key Ключ конфигурации (поддерживает dot-нотацию).
     * 
     * @return bool true, если ключ существует; иначе false.
     */
    public function has(string $key): bool
    {
        return $this->get($key, '__config_missing__') !== '__config_missing__';
    }

    /**
     * Устанавливает значение в конфигурации во время выполнения.
     * 
     * Это не изменяет файл, а только текущее состояние в памяти.
     * 
     * @param string $key Ключ конфигурации (поддерживает dot-нотацию).
     * @param mixed $value Новое значение.
     * 
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        $segments = explode('.', $key);
        $array =& $this->items;

        foreach ($segments as $segment) {
            if (!isset($array[$segment]) || !is_array($array[$segment])) {
                $array[$segment] = [];
            }
            $array =& $array[$segment];
        }

        $array = $value;
    }
}