<?php 

declare(strict_types=1);

namespace Core;

use Core\Http\Response;
use Core\Services\ErrorHandler;
use Core\Database\DB;

use Throwable;

/**
 * ---------------------------------------------------------------------
 * Базовое ядро приложения.
 * ---------------------------------------------------------------------
 * 
 * Управляет жизненным циклом обработки запроса:
 * boot → errors → session → routing → shutdown.
 * 
 * Kernel является абстрактным уровнем между системой и прикладным кодом.
 * 
 * @package Core
 */
abstract class Kernel
{
    /**
     * Инициализация ядра приложения.
     * 
     * Принимает готовый контейнер зависимостей, регистрирует функцию завершения работы
     * и обеспечивает корректное освобождение ресурсов при завершении исполнения.
     * 
     * @param Container $container DI-контейнер приложения.
     */
    public function __construct(protected Container $container)
    {
        // Регистрирует функцию корректного завершения работы приложения.
        register_shutdown_function([$this, 'handleShutdown']);
    }

    /**
     * Запускает обработку запроса и возвращает итоговый HTTP-ответ.
     * 
     * Последовательно выполняет:
     *  1. Фазу загрузки окружения ({@see boot()});
     *  2. Регистрацию обработчиков ошибок;
     *  3. Запуск пользовательской сессии;
     *  4. Делегирование управления маршрутизатору.
     * 
     * @return Response
     */
    public function run(): Response
    {
        $this->boot();
        $this->registerErrorHandling();
        $this->startSession();
        
        return $this->handleRouting();
    }

    /**
     * Подготовка окружения приложения.
     *
     * Может быть переопределена в наследнике (например, в {@see \App\AppKernel})
     * для загрузки сервис-профиля, конфигураций, хелперов и прочих
     * инфраструктурных компонентов.
     *
     * @return void
     */
    protected function boot(): void {}

    /**
     * Регистрирует глобальные обработчики ошибок/исключений.
     *
     * @return void
     */
    private function registerErrorHandling(): void
    {
        $this->container->get(ErrorHandler::class);
    }

    /**
     * Запускает сессию, если она ещё не активна.
     * 
     * @return void
     */
    private function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Основная маршрутизация запроса.
     *
     * Метод предназначен для переопределения в дочернем классе.
     * Конкретная реализация должна извлечь Router из контейнера
     * и вернуть Response.
     *
     * @return Response
     */
    abstract protected function handleRouting(): Response;

    /**
     * Завершение работы приложения.
     * 
     * Закрывает активную сессию и соединение с базой данных.
     * Вызывается автоматически при завершении скрипта через
     * {@see register_shutdown_function()}.
     *
     * @return void
     */
    private function handleShutdown(): void
    {
        try {
            // Сохраняет данные сессии перед завершением работы.
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }

            // Закрывает соединение с базой данных, если оно существует.
            if ($this->container->has(DB::class)) {
                $this->container->get(DB::class)->close();
            }
        } catch (Throwable $e) {
            file_put((string) $e, 'handleShutdown');
        }
    }

    /**
     * Возвращает DI-контейнер приложения.
     * 
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }
}