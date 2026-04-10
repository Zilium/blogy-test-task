<?php

declare(strict_types=1);

use Core\Support\Str;

if (!function_exists('upperCamelCase')) {
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
    function upperCamelCase(string $name): string {
        return Str::upperCamelCase($name);
    }
}

if (!function_exists('lowerCamelCase')) {
    /**
     * Преобразует строку в lowerCamelCase (первая буква строчная, остальные слова слитно с заглавной).
     * 
     * Примеры:
     * - "user-name" → "userName"
     * - "user_name" → "userName"
     * - "user name" → "userName"
     * - "UserName" → "userName" (если входная строка уже в PascalCase)
     * 
     * @param string $name Исходное имя (может содержать дефисы, подчёркивания или пробелы).
     * @return string Имя в формате lowerCamelCase.
     */
    function lowerCamelCase(string $name): string {
        return Str::lowerCamelCase($name);
    }
}

if (!function_exists('snakeToPascal')) {
    /**
     * Преобразует строку из snake_case в PascalCase (UpperCamelCase).
     * 
     * Примеры:
     * - "hello_world" → "HelloWorld"
     * - "user_name"   → "UserName"
     * - "some_name"   → "SomeName"
     *
     * @param string $string Строка в snake_case (слова разделены подчёркиванием).
     * @return string Строка в PascalCase (слитные слова с заглавными буквами).
     */
    function snakeToPascal(string $string): string {
        return Str::snakeToPascal($string);
    }
}

if (!function_exists('toKebabCase')) {
    /**
     * Преобразует строку из CamelCase (PascalCase или camelCase) в kebab-case.
     * 
     * Примеры:
     * - "kebabCase"  → "kebab-case"
     * - "KebabCase"  → "kebab-case"
     * - "someName"   → "some-name"
     * - "XMLParser"  → "xml-parser"
     *
     * @param string $string Строка в CamelCase (слитные слова с заглавными буквами).
     * @return string Строка в kebab-case (слова с дефисами в нижнем регистре).
     */
    function toKebabCase(string $string): string {
        return Str::toKebabCase($string);
    }
}

if (!function_exists('camelToSnake')) {
    /**
     * Преобразует строку из CamelCase (PascalCase или camelCase) в snake_case.
     * 
     * Примеры:
     * - "camelCase"  → "camel_case"
     * - "CamelCase"  → "camel_case"
     * - "someName"   → "some_name"
     * - "XMLParser"  → "xml_parser"
     *
     * @param string $string Строка в CamelCase (слитные слова с заглавными буквами).
     * @return string Строка в snake_case (слова с подчёркиваниями в нижнем регистре).
     */
    function camelToSnake(string $string): string {
        return Str::camelToSnake($string);
    }
}

if (!function_exists('snakeToKebab')) {
    /**
     * Преобразует строку из snake_case в kebab-case, заменяя подчёркивания на дефисы.
     * 
     * Примеры:
     * - "hello_world" → "hello-world"
     * - "user_name"   → "user-name"
     * - "Test_String" → "test-string"
     *
     * @param string $string Строка в snake_case.
     * @return string Строка в kebab-case.
     */
    function snakeToKebab(string $string): string {
        return Str::snakeToKebab($string);
    }
}

if (!function_exists('kebabToSnake')) {
    /**
     * Преобразует строку из kebab-case в snake_case.
     * 
     * Примеры:
     * - "per-person" → "per_person"
     * - "user-name"  → "user_name"
     * - "some-key"   → "some_key"
     *
     * @param string $string Строка в kebab-case.
     * @return string Строка в snake_case.
     */
    function kebabToSnake(string $string): string {
        return Str::kebabToSnake($string);
    }
}

if (!function_exists('method_operation_name')) {
    /**
     * Преобразует текущее имя метода (__FUNCTION__) в snake_case.
     *
     * Примеры:
     * - checkClient → check_client
     * - registerClient → register_client
     * - getProfile → get_profile
     *
     * @param string $methodName Имя метода (__FUNCTION__).
     * @return string Snake-case версия имени метода.
     */
    function method_operation_name(string $methodName): string {
        return Str::camelToSnake($methodName);
    }
}