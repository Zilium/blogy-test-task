<?php 

declare(strict_types=1);

namespace Core\Traits;

use RuntimeException;

/**
 * Трейт для реализации шаблона Singleton.
 * 
 * Применяется для классов, которым необходимо гарантировать, 
 * что в приложении существует только один их экземпляр.
 * 
 * Основная идея: при первом вызове {@see getInstance()} создаётся объект, 
 * который сохраняется в статическом свойстве и переиспользуется при 
 * последующих обращениях.
 * 
 * Пример использования:
 * ```php
 * class Session {
 *     use TSingleton;
 * }
 * 
 * Session::getInstance()->get(user.name);
 * Session::getInstance()->set(user.name, 'Роман');
 * 
 * $a = Session::getInstance();
 * $b = Session::getInstance();
 * var_dump($a === $b); // true
 * ```
 * 
 * @package Core\Traits
 */
trait TSingleton
{
    /**
     * Единственный экземпляр класса.
     * 
     * @var self|null
     */
    private static ?self $instance = null;

    /**
     * Конструктор объявлен приватным, чтобы предотвратить
     * создание экземпляров класса извне.
     */
    private function __construct() {}

    /**
     * Возвращает единственный экземпляр класса (реализация Singleton).
     * 
     * Если экземпляр ещё не создан - создаёт его, иначе возвращает 
     * уже существующий.
     * 
     * @return static Единственный экземпляр класса.
     */
    public static function getInstance(): static
    {
        return static::$instance ?? static::$instance = new static();
    }

    /**
     * Запрещает клонирование экземпляра.
     * 
     * @return void
     * @throws RuntimeException При попытке клонировать объект.
     */
    private function __clone(): void {}

    /**
     * Запрещает десериализацию экземпляра.
     * 
     * Этот метод вызывается автоматически при `unserialize()`.
     * Он выбрасывает исключение, чтобы предотвратить восстановление
     * объекта Singleton из строки.
     * 
     * @return void
     * @throws RuntimeException При попытке десериализовать объект.
     */
    public function __wakeup(): void
    {
        throw new RuntimeException('Невозможно десериализовать синглтон.');
    }
}