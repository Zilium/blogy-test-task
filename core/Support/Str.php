<?php

declare(strict_types=1);

namespace Core\Support;

/**
 * Класс StringHelper предоставляет набор универсальных статических методов
 * для преобразования строковых форматов (snake_case, kebab-case, camelCase, PascalCase).
 *
 * Основное назначение — унификация наименований в проекте (например, имена классов, роутов, таблиц и т.д.)
 *
 * Примеры использования:
 * StringHelper::upperCamelCase('menu_category'); // MenuCategory
 * StringHelper::lowerCamelCase('user-name');     // userName
 * StringHelper::camelToSnake('UserProfile');     // user_profile
 * StringHelper::toKebabCase('XMLParser');        // xml-parser
 *
 * @package Core\Support
 */
final class Str
{
    /**
     * Преобразует строку в UpperCamelCase (PascalCase), заменяя разделители (-, _, пробелы) на заглавные слова.
     * 
     * Примеры:
     * - "menu-category" → "MenuCategory"
     * - "menu category" → "MenuCategory"
     * 
     * @param string $name Исходная строка (может содержать дефисы, подчёркивания или пробелы).
     * 
     * @return string Преобразованная строка в формате UpperCamelCase.
     */
    public static function upperCamelCase(string $name): string
    {
        $name = str_replace(['-', '_'], ' ', $name);
        return str_replace(' ', '', ucwords($name));
    }

    /**
     * Преобразует строку в lowerCamelCase (первая буква — строчная).
     *
     * Примеры:
     * - "user-name" → "userName"
     * - "UserName"  → "userName"
     *
     * @param string $name Исходная строка (любой формат).
     * 
     * @return string Строка в lowerCamelCase.
     */
    public static function lowerCamelCase(string $name): string
    {
        return lcfirst(self::upperCamelCase($name));
    }

    /**
     * Преобразует snake_case в PascalCase.
     *
     * Примеры:
     * - "hello_world" → "HelloWorld"
     *
     * @param string $string Строка в snake_case.
     * 
     * @return string В формате PascalCase.
     */
    public static function snakeToPascal(string $string): string
    {
        return implode('', array_map('ucfirst', explode('_', $string)));
    }

   /**
     * Преобразует CamelCase (PascalCase или camelCase) в kebab-case.
     *
     * Примеры:
     * - "camelCase"  → "camel-case"
     * - "XMLParser"  → "xml-parser"
     *
     * @param string $string Строка в CamelCase.
     * 
     * @return string В формате kebab-case.
     */
    public static function toKebabCase(string $string): string {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $string));
    }

    /**
     * Преобразует CamelCase (PascalCase или camelCase) в snake_case.
     *
     * Примеры:
     * - "CamelCase" → "camel_case"
     * - "XMLParser" → "xml_parser"
     *
     * @param string $string Строка в CamelCase.
     * 
     * @return string В формате snake_case.
     */
    public static function camelToSnake(string $string): string {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $string));
    }

    /**
     * Преобразует snake_case в kebab-case.
     *
     * Примеры:
     * - "hello_world" → "hello-world"
     *
     * @param string $string Строка в snake_case.
     * @return string В формате kebab-case.
     */
    public static function snakeToKebab(string $string): string {
        return strtolower(str_replace('_', '-', $string));
    }

    /**
     * Преобразует kebab-case в snake_case.
     *
     * Примеры:
     * - "user-name" → "user_name"
     *
     * @param string $string Строка в kebab-case.
     * @return string В формате snake_case.
     */
    public static function kebabToSnake(string $string): string {
        return str_replace('-', '_', $string);
    }
}