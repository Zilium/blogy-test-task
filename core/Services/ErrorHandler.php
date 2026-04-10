<?php

declare(strict_types=1);

namespace Core\Services;

use Core\Contracts\ErrorRendererInterface;

use Throwable;

/**
 * -----------------------------------------------------------------------------
 * Класс ErrorHandler
 * -----------------------------------------------------------------------------
 * Отвечает за глобальную обработку ошибок и исключений в приложении.
 * 
 * Основные задачи:
 * - регистрация обработчиков ошибок, исключений и фатальных сбоев;
 * - логирование ошибок в файл;
 * - делегирование визуализации классу {@see ErrorRendererInterface};
 * - управление уровнем отображения ошибок в зависимости от флага `debug`.
 * 
 * Архитектурно `ErrorHandler` не рендерит представления напрямую -
 * он передаёт данные специализированному рендереру (например, AppErrorRenderer),
 * что обеспечивает независимость ядра от структуры приложения.
 * 
 * https://habr.com/ru/post/161483/
 * 
 * @package Core\Services
 */
class ErrorHandler
{
    /**
     * Флаг режима отладки (1 — включён, 0 — выключен).
     *
     * @var int
     */
    private int $debug = 0;

    /**
     * Конструктор ErrorHandler.
     *
     * Выполняет настройку уровня `error_reporting` и регистрирует
     * пользовательские обработчики исключений, ошибок и фатальных сбоев.
     *
     * @param Config $config Глобальная конфигурация приложения.
     * @param ErrorRendererInterface $renderer Рендерер, отвечающий за визуализацию ошибок.
     */
    public function __construct(
        private readonly Config $config,
        private readonly ErrorRendererInterface $renderer,
    ) {
        $this->debug = (int) $this->config->get('app.debug', 0);

        // Настройка уровня отображения ошибок.
        error_reporting($this->debug ? -1 : 0);

        // Регистрация пользовательских обработчиков.
        set_exception_handler([$this, 'exceptionHandler']);
        set_error_handler([$this, 'errorHandler']);
        register_shutdown_function([$this, 'fatalErrorHandler']);

        ob_start();
    }
    
    /**
     * Глобальный обработчик необработанных исключений.
     *
     * @param Throwable $e Исключение, которое не было перехвачено.
     *
     * @return void
     */
    public function exceptionHandler(Throwable $e): void
    {
        $this->logErrors($e->getMessage(), $e->getFile(), $e->getLine());
        $this->displayErrors('Исключение', $e->getMessage(), $e->getFile(), $e->getLine(), $e->getCode());
    }

    /**
     * Обработчик ошибок PHP (E_WARNING, E_NOTICE, E_USER_WARNING и т.п.).
     *
     * @param int $errno Код ошибки (например, E_WARNING).
     * @param string $errstr Текст ошибки.
     * @param string $errfile Файл, где произошла ошибка.
     * @param int $errline Номер строки, где произошла ошибка.
     *
     * @return void
     */
    public function errorHandler(int $errno, string $errstr, string $errfile, int $errline): void
    {
        $this->logErrors($errstr, $errfile, $errline);
        $this->displayErrors($errno, $errstr, $errfile, $errline);
    }
    
    /**
     * Обработчик фатальных ошибок.
     *
     * Выполняется при завершении скрипта.
     *
     * @return void
     */
    public function fatalErrorHandler(): void
    {
        $error = error_get_last();
        if (!empty($error) && $error['type'] & (E_ERROR | E_PARSE | E_COMPILE_ERROR | E_CORE_ERROR)) {
            $this->logErrors($error['message'], $error['file'], $error['line']);
            ob_end_clean();
            $this->displayErrors($error['type'], $error['message'], $error['file'], $error['line']);
        } else {
            ob_end_flush();
        }
    }

    /**
     * Логирует сообщение об ошибке в файл.
     *
     * @param string $message Текст ошибки.
     * @param string $file Путь к файлу, где произошла ошибка.
     * @param int $line Номер строки, где произошла ошибка.
     *
     * @return void
     */
    private function logErrors(string $message, string $file, int $line): void
    {
        if (!is_dir(LOGS_DIR)) {
            mkdir(LOGS_DIR);
        }

        file_put_contents(
            LOGS_DIR . '/errors.log', 
            "[" . date('Y-m-d H:i:s') . "] Текст ошибки: {$message} | Файл: {$file} | Строка: {$line}\n=================\n", 
            FILE_APPEND
        );
    }

    /**
     * Делегирует отображение ошибки рендереру.
     *
     * @param int|string $errno Код или тип ошибки.
     * @param string $errstr Текст ошибки.
     * @param string $errfile Путь к файлу, где произошла ошибка.
     * @param int $errline Номер строки.
     * @param int $response HTTP-код ответа (по умолчанию 500).
     *
     * @return void
     */
    private function displayErrors(
        int|string $errno,
        string $errstr,
        string $errfile,
        int $errline,
        int $response = 500
    ): void {
        $data = [
            'errno' => $errno,
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline,
            'status' => $response,
        ];
    
        $response = $this->renderer->render($data, $response, (bool) $this->debug);
        $response->send();
    }
}