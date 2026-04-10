<?php

declare(strict_types=1);

namespace Core;

use ReflectionClass;
use ReflectionNamedType;
use Exception;
use RuntimeException;

/**
 * -------------------------------------------------------------------------
 * Класс Controller реализует Dependency Injection (DI) контейнер.
 * -------------------------------------------------------------------------
 * 
 * Контейнер позволяет автоматически управлять зависимостями объектов,
 * создавать экземпляры классов и кэшировать их (singleton-поведение).
 * 
 * Поддерживаемые возможности:
 * - Singleton-сервисы (один экземпляр на всё приложение);
 * - Фабрики (callable, создающие экземпляры по требованию);
 * - Привязки интерфейсов к конкретным реализациям (bind);
 * - Автоматическое разрешение зависимостей через Reflection API.
 * 
 * @package Core
 */
class Container
{
    /**
     * Зарегистрированные singleton-сервисы и фабрики.
     * 
     * Ключ — имя класса/интерфейса.
     * Значение — либо готовый объект, либо фабрика (callable), 
     * которая вернёт объект при первом запросе.
     *
     * @var array<string, mixed>
     */
    private array $instances = [];

    /**
     * Привязки интерфейсов к конкретным реализациям.
     * 
     * Позволяет объявить, что при запросе интерфейса 
     * нужно подставить конкретный класс.
     * Например:
     * ```php
     * $container->bind(LoggerInterface::class, FileLogger::class);
     * ```
     * 
     * @var array<string, string>
     */
    private array $bindings = [];

    /**
     * Регистрирует привязку интерфейса или абстрактного класса
     * к конкретной реализации.
     *
     * @param string $abstract Имя интерфейса или абстрактного класса.
     * @param string $concrete Имя конкретного класса, который будет создан.
     * 
     * @return void
     */
    public function bind(string $abstract, string $concrete): void
    {
        $this->bindings[$abstract] = $concrete;
    }

    /**
     * Регистрирует singleton-сервис или фабрику.
     * 
     * Если передан готовый объект — он будет возвращаться при каждом вызове.
     * Если передана функция (callable), она будет вызвана один раз и результат
     * сохранён как singleton.
     *
     * Примеры:
     * ```php
     * $container->singleton(Config::class, fn() => new Config());
     * $container->singleton(Response::class, new Response());
     * ```
     *
     * @param string $abstract Класс или интерфейс.
     * @param mixed $concrete Экземпляр или фабрика (callable).
     * 
     * @return void
     */
    public function singleton(string $abstract, mixed $concrete): void
    {
        $this->instances[$abstract] = $concrete;
    }

    /**
     * Регистрирует фабрику (синоним singleton, но с более читаемым намерением).
     *
     * Используется, если сервис должен создаваться через анонимную функцию.
     * 
     * ```php
     * $container->factory(Mailer::class, fn($c) => new Mailer($c->get(Config::class)));
     * ```
     *
     * @param string $abstract Класс или интерфейс.
     * @param callable $factory Фабрика, создающая объект.
     * 
     * @return void
     */
    public function factory(string $abstract, callable $factory): void
    {
        $this->instances[$abstract] = $factory;
    }

    /**
     * Возвращает экземпляр класса или создаёт его при необходимости.
     *
     * Основной метод для получения singleton-объектов.
     * Автоматически разрешает зависимости через ReflectionClass.
     * 
     * @param string $id Имя класса или интерфейса.
     * 
     * @return mixed Экземпляр класса.
     * @throws Exception Если невозможно создать объект или не найден класс.
     */
    public function get(string $id): mixed
    {
        if (isset($this->instances[$id])) {
            $instance = $this->instances[$id];

            if (is_callable($instance)) {
                $object = $instance($this);
                $this->instances[$id] = $object;
                return $object;
            }

            return $instance;
        }

        if (isset($this->bindings[$id])) {
            $id = $this->bindings[$id];
        }

        if (!class_exists($id)) {
            throw new Exception("Class {$id} not found.");
        }

        $reflection = new ReflectionClass($id);

        if (!$reflection->getConstructor()) {
            $object = new $id();
            $this->instances[$id] = $object;
            return $object;
        }

        $dependencies = [];
        foreach ($reflection->getConstructor()->getParameters() as $param) {
            $type = $param->getType();

            if (!$type || $type->isBuiltin()) {
                if ($param->isDefaultValueAvailable()) {
                    $dependencies[] = $param->getDefaultValue();
                    continue;
                }

                throw new Exception("Cannot resolve parameter \${$param->getName()} in {$id}");
            }

            $dependencies[] = $this->get($type->getName());
        }

        $object = $reflection->newInstanceArgs($dependencies);
        $this->instances[$id] = $object;

        return $object;
    }

    /**
     * Создаёт новый экземпляр класса, разрешая зависимости и подставляя
     * дополнительные параметры, переданные вручную.
     * 
     * В отличие от get(), make() всегда создаёт *новый* объект 
     * (без кэширования как singleton). Используется, например, для контроллеров.
     *
     * Пример:
     * ```php
     * $controller = $container->make(App\Http\Controllers\HomeController::class, [
     *     'route' => $route
     * ]);
     * ```
     *
     * @param string $class Имя класса для создания.
     * @param array $parameters Ассоциативный массив дополнительных параметров.
     * 
     * @return object Новый экземпляр класса.
     * @throws RuntimeException Если класс не найден или зависимость не может быть разрешена.
     */
    public function make(string $class, array $parameters = []): object
    {
        if (!class_exists($class)) {
            throw new RuntimeException("Класс {$class} не найден при попытке создания через контейнер.");
        }

        $reflection = new ReflectionClass($class);
        $constructor = $reflection->getConstructor();

        if (!$constructor) {
            return new $class();
        }

        $dependencies = [];
        foreach ($constructor->getParameters() as $param) {
            $name = $param->getName();

            if (array_key_exists($name, $parameters)) {
                $dependencies[] = $parameters[$name];
                continue;
            }

            $type = $param->getType();
            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $depClass = $type->getName();
                $dependencies[] = $this->get($depClass);
                continue;
            }

            if ($param->isDefaultValueAvailable()) {
                $dependencies[] = $param->getDefaultValue();
                continue;
            }

            throw new RuntimeException("Не удалось разрешить зависимость \${$name} в классе {$class}.");
        }

        return $reflection->newInstanceArgs($dependencies);
    }

    /**
     * Проверяет, зарегистрирован ли сервис в контейнере.
     *
     * @param string $id Класс или интерфейс.
     * @return bool
     */
    public function has(string $id): bool
    {
        return isset($this->instances[$id]) || isset($this->bindings[$id]);
    }
}