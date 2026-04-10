<?php 

declare(strict_types=1);

use \Core\Services\Session;

if (!function_exists('session')) {
    /**
     * -------------------------------------------------------------------------
     * Глобальный хелпер для работы с сессией
     * -------------------------------------------------------------------------
     *
     * Универсальная функция для быстрого доступа к экземпляру сессии.
     * Поддерживает три режима:
     *
     * 1. **Получение экземпляра сессии**
     *    ```php
     *    $session = session();
     *    $session->get('user.id');
     *    ```
     *
     * 2. **Получение значения по ключу**
     *    ```php
     *    $email = session('user.email');
     *    ```
     *
     * 3. **Установка значения**
     *    ```php
     *    session('user.name', 'Роман');
     *    ```
     *
     * Дополнительно можно использовать методы самого объекта `Session`:
     * - `session()->flash('success', 'Данные сохранены');`
     * - `session()->remove('user');`
     * - `session()->destroy();`
     *
     * @param string|null $key Ключ в сессии (опционально).
     * @param mixed|null $value Значение для установки, если передано.
     *
     * @return mixed|Session
     *     Возвращает:
     *     - объект `Session`, если ключ не указан;
     *     - `true`, если значение установлено;
     *     - значение из сессии, если ключ передан без значения.
     *
     * @see Core\Services\Session
     */
    function session(?string $key = null, mixed $value = null)
    {
        $session = Session::getInstance();

        if ($key === null) {
            return $session;
        }

        if ($value !== null) {
            $session->set($key, $value);
            return true;
        }

        return $session->get($key);
    }
}