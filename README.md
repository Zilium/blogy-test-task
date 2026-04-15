# Blogy Test Task

Проект выполнен на PHP 8.3 без использования фреймворков с использованием Smarty и MySQL.

## Функционал

### Главная страница

- вывод категорий, в которых есть статьи
- для каждой категории отображаются 3 статьи (по дате публикации)
- ссылка на страницу всех статей категории

### Страница категории

- название и описание категории
- список статей
- сортировка по дате публикации
- сортировка по количеству просмотров
- пагинация

### Страница статьи

- изображение
- заголовок
- описание
- полный текст статьи
- количество просмотров
- блок из 3 похожих статей

## Стек

- PHP 8.3
- MySQL 8.4
- Smarty
- HTML
- SCSS
- Docker
  
## Установка

### 1. Клонирование репозитория

```bash
git clone https://github.com/Zilium/blogy-test-task.git
cd blogy-test-task
```

### 2. Запуск контейнеров
```bash
docker compose up -d --build
```

### 3. Установка зависимостей
```bash
docker compose exec php composer install
```

### 4. Выполнение миграций и заполнение тестовыми данными
```bash
docker compose exec php php cli.php fresh
```

После запуска проект доступен по адресу: http://localhost:8080

### Другие команды

```bash
docker compose exec php php cli.php migrate   # создание таблиц
docker compose exec php php cli.php seed      # заполнение тестовыми данными
docker compose exec php php cli.php fresh     # пересоздание таблиц + заполнение данными
```

### Структура проекта

```text
app/                  приложение
config/               конфигурация
core/                 ядро
database/migrations/  миграции
database/seeders/     сиды
public/               публичная директория
resources/            шаблоны и frontend-ресурсы
storage/              временные файлы и кэш
vendor/               зависимости Composer
```

## Примечание

Проект выполнен в рамках тестового задания.