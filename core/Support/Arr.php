<?php 

declare(strict_types=1);

namespace Core\Support;

/**
 * 
 * @package Core\Support
 */
class Arr
{
    /**
     * Рекурсивно фильтрует массив, удаляя указанные ключи на всех уровнях вложенности.
     * 
     * Метод обходит массив рекурсивно и исключает элементы, чьи ключи присутствуют
     * в списке $excludeKeys. Сохраняет оригинальную структуру массива для неудаляемых элементов.
     *
     * @param array $data Исходный массив для фильтрации. Может быть многомерным.
     * @param array $excludeKeys Массив ключей, которые нужно удалить. Сравнение строгое (===).
     * 
     * @return array Отфильтрованный массив без указанных ключей.
     */
    public static function excludeKeys(array $data, array $excludeKeys): array
    {
        $filtered = [];
        
        foreach ($data as $key => $value) {
            if (in_array($key, $excludeKeys, true)) {
                continue;
            }
            
            $filtered[$key] = is_array($value) 
                ? self::excludeKeys($value, $excludeKeys) 
                : $value;
        }
        
        return $filtered;
    }
}