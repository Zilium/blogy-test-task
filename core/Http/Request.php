<?php

declare(strict_types=1);

namespace Core\Http;

use Core\Services\Session;

/**
 * -------------------------------------------------------------------------
 * Класс Request
 * -------------------------------------------------------------------------
 * 
 * Инкапсулирует HTTP-запрос и обеспечивает безопасный доступ к данным:
 * - GET, POST, PUT, PATCH, DELETE, JSON;
 * - заголовки, куки, файлы;
 * - IP, метод, HTTPS, AJAX и др.;
 * 
 * Класс обеспечивает защиту от XSS, автоматическую нормализацию $_FILES
 * и кэширование JSON-данных.
 * 
 * @package Core\Http
 */
class Request
{
    /** @var array GET-параметры */
    private array $get;

     /** @var array POST-параметры */
    private array $post;

    /** @var array Дополнительные входные данные (PUT, PATCH, DELETE) */
    private array $input = [];

    /** @var array|null JSON-данные запроса */
    private ?array $jsonData = null;

    /** @var array Серверные переменные ($_SERVER) */
    private array $server;

    /** @var array Куки ($_COOKIE) */
    private array $cookies;

    /** @var array Загруженные файлы ($_FILES), нормализованные */
    private array $files;

    /** @var array Заголовки HTTP-запроса */
    private array $headers;

    /** @var Session|null Объект сессии */
    private ?Session $session = null;

    /**
     * Создаёт экземпляр Request из глобальных суперглобальных массивов.
     * 
     * @return self
     */
    public static function capture(): self
    {
        return new self($_GET, $_POST, $_SERVER, $_COOKIE, $_FILES);
    }

    /**
     * Конструктор запроса.
     * 
     * @param array $get GET-параметры.
     * @param array $post POST-параметры.
     * @param array $server Серверные переменные ($_SERVER).
     * @param array $cookies Куки ($_COOKIE).
     * @param array $files Файлы ($_FILES).
     */
    public function __construct(
        array $get,
        array $post,
        array $server,
        array $cookies,
        array $files
    ) {
        $this->get = $this->sanitize($get);
        $this->post = $this->sanitize($post);
        $this->server = $server;
        $this->cookies = $cookies;
        $this->files = $this->normalizeFiles($files);
        $this->headers = $this->parseHeaders($server);
    }

    /**
     * Устанавливает объект сессии.
     *
     * @param Session $session Экземпляр сессии.
     * 
     * @return void
     */
    public function setSession(Session $session): void
    {
        $this->session = $session;
    }

    /**
     * Возвращает текущий объект сессии, создавая его при необходимости.
     *
     * @return Session
     */
    public function getSession(): Session
    {
        return $this->session ??= new Session();
    }

    /**
     * Алиас для {@see getSession()}.
     *
     * @return Session
     */
    public function session(): Session
    {
        return $this->getSession();
    }

    /**
     * Экранирует HTML-символы и очищает данные от XSS.
     * 
     * @param array $data Исходные данные.
     * 
     * @return array Очищенные данные.
     */
    private function sanitize(array $data): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitize($value);
            }
            elseif (is_string($value)) {
                // Не экранируем HTML-фрагмент, если ключ явно указан.
                if ($key === 'html') {
                    $sanitized[$key] = $value;
                } else {
                    $sanitized[$key] = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                }
            }
            else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Преобразует $_FILES в унифицированный формат.
     *
     * @param array $files Массив $_FILES.
     * @return array Нормализованный массив файлов.
     */
    private function normalizeFiles(array $files): array
    {
        $normalized = [];
        foreach ($files as $key => $file) {
            if (!is_array($file['name'])) {
                $normalized[$key] = $file;
                continue;
            }

            foreach ($file['name'] as $index => $name) {
                $normalized[$key][$index] = [
                    'name' => $name,
                    'type' => $file['type'][$index],
                    'tmp_name' => $file['tmp_name'][$index],
                    'error' => $file['error'][$index],
                    'size' => $file['size'][$index],
                ];
            }
        }
        
        return $normalized;
    }

    /**
     * Преобразует заголовки из $_SERVER в ассоциативный массив.
     *
     * @param array $server Массив $_SERVER.
     * @return array Массив заголовков [header => value].
     */
    private function parseHeaders(array $server): array
    {
        $headers = [];
        foreach ($server as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $name = str_replace('_', '-', strtolower(substr($key, 5)));
                $headers[$name] = $value;
            }
        }

        return $headers;
    }

    /**
     * Возвращает URI запроса (без домена).
     * 
     * @return string URI (например, "/path/to/page?param=value").
     */
    public function getRequestUri(): string
    {
        return $this->server['REQUEST_URI'];
    }

    public function getPath(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH);

        return $path ?: '/';
    }

    public function getQueryParams(): array
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $query = parse_url($uri, PHP_URL_QUERY);

        if (!$query) {
            return [];
        }

        parse_str($query, $params);
        return $params;
    }

    /**
     * Возвращает HTTP-метод запроса (GET, POST, PUT и т. д.).
     * 
     * @return string HTTP-метод в верхнем регистре.
     */
    public function getMethod(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    /**
     * Возвращает true, если метод запроса безопасен (не требует CSRF-проверки).
     * 
     * @return bool
     */
    public function isSafeMethod(): bool
    {
        return in_array($this->getMethod(), ['GET', 'HEAD', 'OPTIONS'], true);
    }

    /**
     * Проверяет, является ли HTTP-метод запроса указанным методом.
     * 
     * @param string $method HTTP-метод (например, GET, POST).
     * 
     * @return bool true, если метод совпадает.
     */
    public function isMethod(string $method): bool
    {
        return $this->getMethod() === strtoupper($method);
    }

    /**
     * Получает значение из GET-параметров.
     * 
     * @param mixed $key Ключ параметра (если null — вернет все GET-параметры)
     * @param mixed $default Значение по умолчанию, если ключ отсутствует.
     * 
     * @return mixed Значение параметра или $default.
     */
    public function get(mixed $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->get;
        }
        return $this->get[$key] ?? $default;
    }

    /**
     * Получает значение из POST-параметров.
     * 
     * @param mixed $key Ключ параметра (если null — вернет все POST-параметры).
     * @param mixed $default Значение по умолчанию, если ключ отсутствует.
     * 
     * @return mixed Значение параметра или $default.
     */
    public function post(mixed $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->post;
        }
        return $this->post[$key] ?? $default;
    }

    /**
     * Получает и фильтрует входные данные запроса.
     * 
     * @param mixed $key Ключ параметра (если null - вернет все параметры).
     * @param mixed $default Значение по умолчанию если ключ не найден.
     * 
     * @return mixed Очищенное значение параметра или весь массив параметров.
     */
    public function input(mixed $key = null, mixed $default = null): mixed
    {
        if (empty($this->input)) {
            $this->input = $this->parseInput();
        }

        if ($key === null) {
            return $this->input;
        }

        return $this->input[$key] ?? $default;
    }

    /**
     * Парсит входные данные в зависимости от метода запроса.
     * 
     * @return array Массив неочищенных входных данных.
     * 
     * @throws RuntimeException Если не удалось декодировать JSON
     */
    private function parseInput(): array
    {
        $method = $this->getMethod();

        if ($method === 'GET') {
            return $this->get;
        }

        if ($method === 'POST') {
            return $this->post;
        }
        
        // Для PUT, PATCH, DELETE и других методов
        $contentType = $this->server['CONTENT_TYPE'] ?? '';

        // Обработка JSON
        if (str_contains($contentType, 'application/json')) {
            return $this->json();
        }
        
        // Обработка FormData и URL-encoded
        $input = file_get_contents('php://input');
        
        if (str_contains($contentType, 'multipart/form-data')) {
            parse_str($input, $data);
            return $this->sanitize($data);
        }

        if (str_contains($contentType, 'application/x-www-form-urlencoded')) {
            parse_str($input, $data);
            return $this->sanitize($data);
        }

        // Если Content-Type не указан, пробуем разобрать как URL-encoded
        parse_str($input, $data);
        return $this->sanitize($data);
    }

    /**
     * Получает JSON-данные запроса с кэшированием результата
     */
    public function json(): array
    {
        if ($this->jsonData === null) {
            $content = file_get_contents('php://input');
            $decoded = json_decode($content, true);
            // Проверяем, не возникла ли ошибка при декодировании
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->jsonData = []; // Пустой массив при ошибке
            } else {
                $this->jsonData = $this->sanitize($decoded); // Очищаем данные
            }
        }
    
        return $this->jsonData;
    }

    /**
     * Получает загруженный файл (или все файлы).
     * 
     * @param mixed $key Ключ файла (если null — вернет все файлы).
     * @param mixed $default Значение по умолчанию, если ключ отсутствует.
     * 
     * @return mixed Данные файла или $default.
     */
    public function file(mixed $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->files;
        }
        return $this->files[$key] ?? $default;
    }

    /**
     * Возвращает все данные запроса (GET, POST FILE, PUT, PATCH, DELETE).
     * 
     * @return array Объединенные данные запроса.
     */
    public function all(): array
    {
        return array_merge($this->get, $this->post, $this->files, $this->input());
    }

    /**
     * Возвращает только указанные ключи из всех данных запроса.
     * 
     * @param array $keys Ключи, которые нужно оставить.
     * 
     * @return array Отфильтрованные данные.
     */
    public function only(array $keys): array
    {
        return array_intersect_key($this->all(), array_flip($keys));
    }

    /**
     * Проверяет, является ли запрос AJAX-запросом.
     * 
     * @return bool true, если запрос AJAX (заголовок X-Requested-With: XMLHttpRequest)
     */
    public function isAjax(): bool
    {
        return isset($this->server['HTTP_X_REQUESTED_WITH']) && 
               strtolower($this->server['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Проверяет, принимает ли клиент JSON-ответ (анализирует заголовок Accept).
     * 
     * @return bool true, если клиент поддерживает application/json
     */
    public function acceptsJson(): bool
    {
        return isset($this->server['HTTP_ACCEPT']) && str_contains($this->server['HTTP_ACCEPT'], 'application/json');
    }

    /**
     * Проверяет, использует ли запрос HTTPS.
     * 
     * @return bool true, если соединение безопасное.
     */
    public function isSecure(): bool
    {
        return (!empty($this->server['HTTPS']) && $this->server['HTTPS'] !== 'off') || 
               ($this->server['SERVER_PORT'] ?? null) === 443;
    }

    /**
     * Возвращает все заголовки HTTP-запроса.
     * 
     * @return array Ассоциативный массив заголовков
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Возвращает значение конкретного заголовка.
     * 
     * @param string $key Имя заголовка (например, "user-agent").
     * @param string $default Значение по умолчанию, если заголовок отсутствует.
     * 
     * @return string Значение заголовка или $default.
     */
    public function getHeader(string $key, string $default = ''): string
    {
        return $this->headers[$key] ?? $default;
    }

    /**
     * Удаляет параметр из POST-данных.
     * 
     * @param string $key Ключ параметра
     */
    public function removePost(string $key): void
    {
        unset($this->post[$key]);
    }

    /**
     * Удаляет несколько параметров из POST-данных.
     * 
     * @param array $keys Массив ключей
     */
    public function removePosts(array $keys): void
    {
        foreach ($keys as $key) {
            if (!isset($key)) continue;
            unset($this->post[$key]);
        }
    }

    /**
     * Получает IP-адрес клиента с учетом прокси.
     * 
     * @return string IP-адрес клиента или '0.0.0.0', если не удалось определить.
     */
    public function getIp(): string
    {
        $ip = $this->server['HTTP_X_FORWARDED_FOR'] 
            ?? $this->server['HTTP_X_REAL_IP'] 
            ?? $this->server['REMOTE_ADDR'] 
            ?? '0.0.0.0';
        
        // Если это список IP через запятую (от прокси), берем первый
        if (str_contains($ip, ',')) {
            $ips = explode(',', $ip);
            $ip = trim($ips[0]);
        }
        
        // Валидация IP адреса
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
    }

    /**
     * Получает User-Agent клиента.
     * 
     * @return string User-Agent строка или пустая строка, если не удалось определить.
     */
    public function getUserAgent(): string
    {
        return $this->server['HTTP_USER_AGENT'] ?? '';
    }
}