<?php

declare(strict_types=1);

namespace Core\Services;

use Core\Traits\TSingleton;
use Core\Support\Arr;

/**
 * -------------------------------------------------------------------------
 * Сервис для управления сессиями
 * -------------------------------------------------------------------------
 * 
 * Обеспечивает безопасную работу с данными сессии через объектные методы.
 * Поддерживает:
 *  - вложенные ключи через точечную нотацию ("user.profile.name");
 *  - flash-сообщения;
 *  - избирательное сохранение и обновление групп данных.
 * 
 * @package Core\Services
 */
class Session
{
    use TSingleton;
    
    /**
     * Устанавливает значение в сессии по заданному ключу.
     * Поддерживает вложенные ключи через точечную нотацию (например, 'user.profile.name').
     *
     * @param string $key Ключ для сохранения значения.
     * @param mixed $value Значение для сохранения.
     * 
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        $keys = explode('.', $key);
        $array = &$_SESSION;

        foreach ($keys as $part) {
            if (!isset($array[$part]) || !is_array($array[$part])) {
                $array[$part] = [];
            }
            $array = &$array[$part];
        }
        
        $array = $value;
    }

    /**
     * Сохраняет данные в сессию, рекурсивно исключая указанные поля.
     *
     * @param string $key Ключ, например "user.role", "user".
     * @param array $data Данные для сохранения.
     * @param array $excludeFields Поля для исключения (например, ['password']).
     * 
     * @return void
     */
    public function setGroup(string $key, array $data, array $excludeFields = []): void
    {
        $filteredData = Arr::excludeKeys($data, $excludeFields);
        self::set($key, $filteredData);
    }

    /**
     * Обновляет данные по заданному ключу (массивом), объединив с текущими.
     * Если текущие данные отсутствуют, будет использован только новый массив.
     *
     * @param string $key Ключ с точечной нотацией.
     * @param array $data Данные для обновления.
     * @param array $excludeFields Ключи, которые нужно исключить из $data перед мержем.
     * 
     * @return void
     */
    public function update(string $key, array $data, array $excludeFields = []): void
    {
        $filteredData = Arr::excludeKeys($data, $excludeFields);
        $currentData = self::get($key) ?? [];

        if (!is_array($currentData)) {
            $currentData = []; // гарантируем, что это массив
        }

        $merged = array_merge($currentData, $filteredData);
        self::set($key, $merged);
    }

    /**
     * Получает значение из сессии по ключу.
     * Поддерживает вложенные ключи через точечную нотацию.
     *
     * @param string $key Ключ для получения значения.
     * @param mixed $default
     * 
     * @return mixed Значение или null, если ключ не существует.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);
        $array = $_SESSION;

        foreach ($keys as $part) {
            if (!isset($array[$part])) {
                return $default;
            }
            $array = $array[$part];
        }
        
        return $array;
    }

    /**
     * Получает только указанные ключи из сессии и возвращает их как ассоциативный массив.
     * Поддерживает вложенные ключи через точечную нотацию.
     * 
     * @param array $keys Массив ключей (например, ['user.id', 'user.email', 'user']).
     * @param bool $useShortKeys Использовать только последнюю часть ключа после точки (по умолчанию true).
     *                           Если false или если ключ не содержит точки, будет использован исходный ключ.
     * 
     * @return array Ассоциативный массив с запрошенными значениями.
     *              Если ключ не существует, его значение будет `null`.
     */
    public function only(array $keys, bool $useShortKeys = true): array
    {
        $result = [];
        
        foreach ($keys as $key) {
            $value = self::get($key);
            $resultKey = $key;
            
            if ($useShortKeys) {
                $parts = explode('.', $key);
                $resultKey = $useShortKeys ? end($parts) : $key;
            }
            
            $result[$resultKey] = $value;
        }
        
        return $result;
    }

    /**
     * Проверяет существование ключа в сессии.
     * Поддерживает вложенные ключи через точечную нотацию.
     *
     * @param string $key Ключ для проверки.
     * 
     * @return bool True если ключ существует, false в противном случае.
     */
    public function has(string $key): bool
    {
        $keys = explode('.', $key);
        $array = $_SESSION;
        
        foreach ($keys as $part) {
            if (!isset($array[$part])) {
                return false;
            }
            $array = $array[$part];
        }
        
        return true;
    }

    /**
     * Удаляет значение из сессии по ключу.
     * Поддерживает вложенные ключи через точечную нотацию.
     *
     * @param string $key Ключ для удаления.
     * 
     * @return void
     */
    public function remove(string $key): void
    {
        $keys = explode('.', $key);
        $array = &$_SESSION;
        $lastKey = array_pop($keys);
        
        foreach ($keys as $part) {
            if (!isset($array[$part])) {
                return;
            }
            $array = &$array[$part];
        }

        unset($array[$lastKey]);
    }

    /**
     * Уничтожает текущую сессию и все связанные с ней данные.
     * 
     * @return void
     */
    public function destroy(): void
    {
        $_SESSION = [];

         if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), 
                '', 
                time() - 42000,
                $params['path'], 
                $params['domain'],
                $params['secure'], 
                $params['httponly']
            );
        }

        session_destroy();
    }

    /**
     * Завершает запись в сессию и сохраняет данные.
     * После вызова этого метода сессия будет доступна только для чтения.
     * 
     * @return void
     */
    public function save(): void
    {
        session_write_close();
    }

    /**
     * Устанавливает flash-сообщение (живёт только до первого чтения).
     *
     * Flash-сообщения хранятся в специальном разделе сессии и автоматически удаляются
     * после первого обращения через метод getFlash().
     *
     * @param string $key Ключ, по которому будет доступно сообщение.
     * @param mixed $value Значение сообщения (может быть любого типа).
     * 
     * @return void
     */
    public function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    /**
     * Проверяет наличие flash-сообщения с указанным ключом.
     * 
     * @param string $key Ключ flash-сообщения для проверки
     * 
     * @return bool Возвращает `true`, если flash-сообщение с таким ключом существует,
     *             и `false` в противном случае.
     */
    public function hasFlash(string $key): bool
    {
        return isset($_SESSION['_flash'][$key]);
    }

    /**
     * Получает flash-сообщение и удаляет его из сессии.
     *
     * Если сообщение с указанным ключом отсутствует, вернёт null.
     * После вызова этого метода сообщение удаляется из сессии.
     *
     * @param string $key Ключ сообщения.
     * 
     * @return mixed Значение сообщения или null, если сообщение не найдено.
     */
    public function getFlash(string $key): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? null;
        if (isset($_SESSION['_flash'])) {
            unset($_SESSION['_flash'][$key]);
        }
        return $value;
    }

    /**
     * Очищает все flash-сообщения из сессии.
     * 
     * Flash-сообщения хранятся в специальном разделе `_flash` и обычно удаляются
     * после первого чтения через `getFlash()`. Этот метод позволяет принудительно
     * удалить все flash-сообщения, не дожидаясь их чтения.
     * 
     * @return void
     */
    public function clearFlash(): void
    {
        unset($_SESSION['_flash']);
    }
}