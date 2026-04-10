<?php

declare(strict_types=1);

namespace Core\Contracts;

use Core\Http\Response;

/**
 * -----------------------------------------------------------------------------
 * Интерфейс ErrorRendererInterface
 * -----------------------------------------------------------------------------
 * 
 * Определяет контракт для визуализации ошибок и исключений,
 * возникающих на уровне ядра приложения.
 * 
 * Реализация интерфейса (например, {@see \App\Foundation\Rendering\AppErrorRenderer})
 * отвечает за способ отображения ошибок пользователю.
 *
 * Используется {@see \Core\Services\ErrorHandler}, что позволяет ядру
 * быть независимым от конкретной реализации отображения ошибок в приложении.
 *
 * @package Core\Contracts
 */
interface ErrorRendererInterface
{
    /**
     * Выполняет рендеринг страницы ошибки или исключения.
     *
     * @param array<string, mixed> $data Ассоциативный массив с данными об ошибке:
     *     - `errno`   — код ошибки или её тип;
     *     - `message` — текст ошибки или исключения;
     *     - `file`    — путь к файлу, где возникла ошибка;
     *     - `line`    — номер строки, где произошла ошибка;
     *     - `status`  — HTTP-код ответа.
     * @param int $status HTTP-код ответа (например, 404, 500, 403).
     * @param bool $debug Флаг режима отладки (`true` - показывать подробности).
     *
     * @return Response
     */
    public function render(array $data, int $status, bool $debug): Response;
}