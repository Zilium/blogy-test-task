<?php

declare(strict_types=1);

namespace Core;

use Core\Http\Request;
use Core\Http\Response;

use RuntimeException;
use ReflectionMethod;

/**
 * -------------------------------------------------------------------------
 * Класс Controller
 * -------------------------------------------------------------------------
 * 
 * Этот класс является родительским для всех контроллеров приложения и реализует общую логику:
 * - Инициализацию зависимостей;
 * - Выполнение действий (actions);
 * - Хуки жизненного цикла (init, before, after);
 * - Возвращает представление;
 * 
 * Каждый контроллер приложения должен наследовать этот класс и определять собственные методы действий.
 * 
 * @package Core
 */
abstract class Controller
{
    /**
     * Конструктор базового контроллера.
     * 
     * @param Container $container DI-контейнер приложения.
     * @param Request $request Объект HTTP-запроса.
     * @param Response $response Объект HTTP-ответа.
     * @param View $view Объект представления.
     * @param array $route Информация о маршруте (контроллер, действие, параметры).
     */
    public function __construct(
        protected Container $container,
        protected Request $request,
        protected Response $response,
        protected View $view,
        protected array $route = [],
    ) {
        $this->boot();
    }

    /**
     * Хук инициализации, выполняемый при создании контроллера.
     * 
     * Используется для регистрации зависимостей, загрузки сервисов и других
     * базовых операций, которые должны быть выполнены до `runAction()`.
     * 
     * Может быть переопределён в дочерних классах.
     * 
     * @return void
     */
    protected function boot(): void {}

    /**
     * Основной метод, управляющий выполнением запроса.
     * 
     * Последовательность:
     *  1. Выполняет метод `init()` (если определён);
     *  2. Вызывает `before()` и прерывает выполнение, если тот вернул false;
     *  3. Определяет и выполняет action-метод контроллера;
     *  4. Вызывает `after()`;
     *  5. Возвращает представление.
     * 
     * @throws RuntimeException Если action не найден или недоступен.
     * @return Response Объект HTTP-ответа.
     */
    public function runAction(): Response
    {
        // 1. Инициализация, если она определена в контроллере.
        $this->init();

        // 2. Выполнение перед-экшн логики.
        $beforeResult = $this->before();
        if ($beforeResult instanceof Response) {
            return $beforeResult;
        }

        // 3. Приводим имя метода к lowerCamelCase (формат экшенов).
        $method = lowerCamelCase($this->route['action']);

        // 3.1. Проверяем корректность имени метода.
        if (!preg_match('/^[a-zA-Z0-9]+$/', $method)) {
            throw new RuntimeException("Некорректное имя метода: {$method}", 400);
        }

        // 3.2. Проверяем, что метод существует и публичный.
        if (!method_exists($this, $method) || !(new ReflectionMethod($this, $method))->isPublic()) {
            throw new RuntimeException("Метод ". static::class ."::{$method} не найден", 404);
        }

        // 3.3. Выполнение action-метода
        $methodResult = $this->$method();

        // 4. Выполняем после-экшн логику, если она есть.
        $this->after();

        return $methodResult;
    }

    /**
     * Инициализация общих данных до выполнения action-метода.
     * Может быть переопределён в потомках для загрузки сервисов или предустановки данных.
     * 
     * @return void
     */
    protected function init(): void {}

    /**
     * Хук, выполняющийся перед выполнением action-метода.
     * 
     * Может быть использован для проверки прав доступа или других условий.
     * Если метод возвращает object — выполнение action прекращается.
     * 
     * @return object|bool
     */
    protected function before(): object|bool
    {
        return true;
    }

    /**
     * Выполняется после основного действия (action), 
     * но перед рендерингом представления.
     * 
     * Может быть использован для логирования, очистки данных и других завершающих операций.
     * 
     * @return void
     */
    protected function after(): void {}
}