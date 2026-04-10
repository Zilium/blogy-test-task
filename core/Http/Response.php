<?php

declare(strict_types=1);

namespace Core\Http;

use InvalidArgumentException;
use RuntimeException;

/**
 * Класс Response предназначен для работы с HTTP-ответами сервера.
 * 
 * HTTP-ответ приложения: статус, заголовки и тело.
 * 
 * @package Core\Http
 */
class Response
{
    public const HTTP_OK = 200;
    
    public const HTTP_MOVED_PERMANENTLY = 301;
    public const HTTP_FOUND = 302;

    public const HTTP_NOT_FOUND = 404;

    public const HTTP_INTERNAL_SERVER_ERROR = 500;

    public const DEFAULT_CONTENT_TYPE = 'text/html; charset=UTF-8';
    public const JSON_CONTENT_TYPE = 'application/json; charset=UTF-8';

    /**
     * HTTP-статус код.
     * 
     * @var int 
     */
    protected int $statusCode;

    /**
     * Массив HTTP-заголовков.
     * 
     * @var array<string, string>
     */
    protected array $headers;

    /**
     * Тело ответа.
     * 
     * @var string
     */
    protected string $content;

    /**
     * Путь к файлу для отправки (если задан — Response работает в режиме FileResponse).
     *
     * @var string|null
     */
    protected ?string $filePath = null;

    /**
     * Удалить файл после отправки.
     *
     * @var bool
     */
    protected bool $deleteAfterSend = false;

    /**
     * Конструктор Response
     *
     * @param int $statusCode HTTP-статус ответа (по умолчанию 200).
     * @param array<string, string> $headers Массив заголовков.
     * @param string $content Контент.
     */
    public function __construct(
        int $statusCode = self::HTTP_OK,
        array $headers = [],
        string $content = '',
    ) {
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        $this->content = $content;
    }

    /**
     * Устанавливает HTTP-статус.
     *
     * @param int $code Код статуса HTTP.
     * 
     * @return $this
     */
    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    /**
     * Устанавливает HTTP-заголовок.
     *
     * @param string $name Название заголовка.
     * @param string $value Значение заголовка.
     * 
     * @return $this
     */
    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Устанавливает содержимое ответа.
     *
     * @param string $content Контент ответа.
     * 
     * @return $this
     */
    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Отправляет ответ клиенту и завершает выполнение скрипта.
     * 
     * @return void
     */
    public function send(): void
    {
        $this->sendHeaders();

        if ($this->filePath !== null) {
            readfile($this->filePath);
           
            if ($this->deleteAfterSend) {
                @unlink($this->filePath);
            }

            return;
        }

        echo $this->content;

        exit;
    }

    /**
     * Отправляет HTTP-заголовки, если они еще не отправлены.
     * 
     * @return void
     */
    protected function sendHeaders(): void
    {
        if (!headers_sent()) {
            http_response_code($this->statusCode);
            foreach ($this->headers as $name => $value) {
                header("$name: $value");
            }
        }
    }

    /**
     * Готовит HTML-ответ.
     *
     * @param string $html
     * @param int $status
     * 
     * @return $this
     */
    public function html(string $html, int $status = self::HTTP_OK): self
    {
        return $this->setStatusCode($status)
            ->setHeader('Content-Type', self::DEFAULT_CONTENT_TYPE)
            ->setContent($html);
    }

    /**
     * Генерирует и возвращает успешный JSON-ответ.
     * 
     * @param array<string, mixed> $data Данные для ответа.
     * @param string $message Сообщение.
     * @param array<string, mixed> $extra Дополнительные данные.
     * @param int $status HTTP статус.
     * 
     * @return $this
     */
    public function jsonSuccess(
        array $data = [],
        string $message = 'Success',
        array $extra = [],
        int $status = self::HTTP_OK,
    ): self {

        $payload = array_merge([
            'code' => $status,
            'error' => false,
            'message' => $message,
            'status' => 'success',
            'data' => $data
        ], $extra);

        return $this->json($payload, $status);
    }

    /**
     * Генерирует и возвращает JSON-ответ с ошибкой.
     * 
     * @param int $status HTTP статус.
     * @param string $message Сообщение.
     * @param array<string, mixed> $data Данные для ответа.
     * @param array<string, mixed> $extra Дополнительные данные.
     * 
     * @return $this
     */
    public function jsonError(
        int $status, 
        string $message, 
        array $data = [], 
        array $extra = [],
    ): self {
        $payload = array_merge([
            'code' => $status,
            'error' => true, 
            'message' => $message, 
            'status' => 'error',
            'data' => $data,
        ], $extra);

        return $this->json($payload, $status);
    }

    /**
     * Универсальный JSON-ответ.
     * 
     * @param array<string, mixed> $payload Данные для ответа.
     * @param int $status HTTP статус.
     * @param int $jsonFlags Флаги JSON.
     * 
     * @return self
     */
    protected function json(
        array $payload, 
        int $status = self::HTTP_OK, 
        int $jsonFlags = JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
    ): self {
        $json = json_encode($payload, $jsonFlags);

        return $this->setStatusCode($status)
            ->setHeader('Content-Type', self::JSON_CONTENT_TYPE)
            ->setContent($json);
    }

    /**
     * Перенаправляет пользователя на указанную страницу.
     *
     * @param string $url URL для перенаправления.
     * @param int $statusCode Статус перенаправления.
     * 
     * @return $this
     * @throws InvalidArgumentException если статус-код не находится в диапазоне перенаправлений (3xx).
     */
    public function redirect(
        string $url = '', 
        int $statusCode = self::HTTP_FOUND
    ): self {
        if ($statusCode < 300 || $statusCode >= 400) {
            throw new InvalidArgumentException('Неверный код статуса перенаправления: ' . $statusCode);
        }

        $url = $url ?: $_SERVER['HTTP_REFERER'] ?? '/';

       return $this
            ->setStatusCode($statusCode)
            ->setHeader('Location', $url);
    }

    /**
     * Отправляет файл клиенту.
     * 
     * @param string $filePath Путь к файлу.
     * @param string $mimeType MIME-тип файла.
     * @param string $filename Имя файла.
     * @param bool $forceDownload Принудительное скачивание.
     * @param bool $deleteAfterSend Принудительное удаление.
     * 
     * @return self 
     * @throws RuntimeException Если файл не существует.
     */
    public function file(
        string $filePath,
        string $mimeType,
        string $filename,
        bool $forceDownload = false,
        bool $deleteAfterSend = false
    ): self  {
        if (!file_exists($filePath)) {
            throw new RuntimeException('Файл не найден на сервере', 404);
        }

        $disposition = $forceDownload ? 'attachment' : 'inline';
        $safeFilename = preg_replace('/[\x00-\x1F\x7F]/', '', $filename) ?? 'file';

        $this->filePath = $filePath;
        $this->deleteAfterSend = $deleteAfterSend;

        $this->setHeader('Content-Type', $mimeType);
        $this->setHeader('Content-Length', (string) filesize($filePath));
        $this->setHeader(
            'Content-Disposition',
            sprintf('%s; filename="%s"', $disposition, $safeFilename)
        );

        return $this;
    }

    /**
     * Отдаёт файл "как есть" (без заголовков Content-Type/Disposition).
     * 
     * @param string $path Абсолютный путь к файлу.
     * 
     * @return self
     */
    public function readfile(string $path): self
    {
        $this->filePath = $path;
        $this->deleteAfterSend = false;

        return $this;
    }
}