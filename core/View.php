<?php

declare(strict_types=1);

namespace Core;

use Smarty;
use RuntimeException;
use Throwable;

/**
 * -------------------------------------------------------------------------
 * Класс View
 * -------------------------------------------------------------------------
 * 
 * Рендерит шаблоны через Smarty.
 * 
 * -------------------------------------------------------------------------
 * Пример использования в контроллере:
 * -------------------------------------------------------------------------
 * ```php
 * $this->view
 *     ->setLayout('app')
 *     ->setView('pages/category/index')
 *     ->setData($category)
 *     ->setMeta($category['title'])
 *     ->render();
 * ```
 * 
 * @package Core
 */
class View
{
    /**
     * Содержимое страницы после рендеринга.
     *
     * @var string
     */
    public string $content = '';

    /**
     * Имя layout-шаблона (или false, если макет не используется).
     *
     * @var string|bool
     */
    private string|bool $layout = false;

    /**
     * Имя представления.
     *
     * @var string
     */
    private string $view = '';

    /**
     * Данные, передаваемые во view.
     *
     * @var array<string, mixed>
     */
    private array $data = [];

    /**
     * Мета-информация страницы (title, description, keywords).
     *
     * @var array{title?: string, description?: string, keywords?: string}
     */
    private array $meta = [];

    /**
     * Конструктор класса View.
     * 
     * @param Smarty $smarty
     */
    public function __construct(private Smarty $smarty) {}

    /**
     * Устанавливает layout, используемый для обёртки контента.
     *
     * @param string|bool $layout Имя макета без расширения
     *                            либо false, чтобы отключить layout.
     *
     * @return self Возвращает текущий экземпляр для fluent-интерфейса.
     */
    public function setLayout(string|bool $layout): self
    {
        $this->layout = $layout;
        return $this;
    }

    /**
     * Устанавливает имя view-файла, который будет отрендерен.
     *
     * @param string $view Имя шаблона без расширения (например: "index", "pages/main/index").
     *
     * @return self Возвращает текущий экземпляр для fluent-интерфейса.
     */
    public function setView(string $view): self
    {
        $this->view = $view;
        return $this;
    }

    /**
     * Передаёт данные, доступные внутри шаблона.
     *
     * Данные объединяются с текущими значениями `$data`.
     *
     * @param array<string, mixed> $data Ассоциативный массив данных.
     *
     * @return self Возвращает текущий экземпляр для fluent-интерфейса.
     */
    public function setData(array $data): self
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    /**
     * Добавляет отдельную переменную в контекст представления.
     * 
     * Метод добавляет или переопределяет одну переменную, доступную во view.
     * 
     * Аналогично {@see setData()}, но используется для передачи единичных значений,
     * например общих переменных приложения — `app`, `page` и т.п.
     * 
     * Пример:
     * ```php
     * $this->view->share('user', $user);
     * $this->view->share('page', ['title' => 'Главная страница']);
     * ```
     * 
     * @param string $key Имя переменной, которая будет доступна в шаблоне.
     * @param mixed $value Значение переменной.
     * 
     * @return self Возвращает текущий экземпляр для fluent-интерфейса.
     */
    public function share(string $key, mixed $value): self
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * Устанавливает мета-данные страницы (title, description, keywords).
     * 
     * @param string $title Заголовок страницы.
     * @param string $description Описание страницы.
     * @param string $keywords Ключевые слова.
     * 
     * @return self Возвращает текущий экземпляр для fluent-интерфейса.
     */
    public function setMeta(string $title = '', string $description = '', string $keywords = ''): self
    {
        $this->meta = [
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords,
        ];
        return $this;
    }

    /**
     * Генерирует HTML-страницу и возвращает строку.
     *
     * @throws RuntimeException Если не найден файл представления или макета.
     *
     * @return string
     */
    public function render(): string
    {
        if ($this->view === '') {
            throw new RuntimeException('Не указано представление для рендера.');
        }

        // На случай повторного использования объекта в одном запросе.
        $this->smarty->clearAllAssign();

        // Общие данные, доступные во всех шаблонах.
        $this->smarty->assign('meta', $this->meta);

        foreach ($this->data as $key => $value) {
            $this->smarty->assign($key, $value);
        }

        $viewTemplate = $this->normalizeTemplate($this->view);

        try {
            $this->content = $this->smarty->fetch($viewTemplate);
        } catch (Throwable $e) {
            throw new RuntimeException(
                sprintf('Ошибка рендера view "%s": %s', $viewTemplate, $e->getMessage()),
                0,
                $e
            );
        }

        if ($this->layout === false) {
            return $this->content;
        }

        $layoutTemplate = 'layouts/' . $this->normalizeTemplate((string) $this->layout);

        $this->smarty->assign('content', $this->content);

        try {
            return $this->smarty->fetch($layoutTemplate);
        } catch (Throwable $e) {
            throw new RuntimeException(
                sprintf('Ошибка рендера layout "%s": %s', $layoutTemplate, $e->getMessage()),
                0,
                $e
            );
        }
    }

    /**
     * Сбрасывает внутреннее состояние объекта View.
     * Полезно при повторном использовании экземпляра в рамках одного запроса.
     *
     * @return void
     */
    public function clear(): void
    {
        $this->layout = false;
        $this->view = '';
        $this->data = [];
        $this->content = '';
        $this->meta = [
            'title' => '',
            'description' => '',
            'keywords' => '',
        ];

        $this->smarty->clearAllAssign();
    }

    /**
     * Нормализует имя шаблона и добавляет .tpl, если нужно.
     * 
     * @param string $template Имя шаблона без расширения.
     * 
     * @return string
     */
    private function normalizeTemplate(string $template): string
    {
        $template = trim($template, '/');

        if ($template === '') {
            throw new RuntimeException('Имя шаблона не может быть пустым.');
        }

        return str_ends_with($template, '.tpl') ? $template : $template . '.tpl';
    }
}