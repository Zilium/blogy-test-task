<?php

declare(strict_types=1);

namespace Core\Database;

use Core\Traits\TSingleton;

use mysqli;
use mysqli_result;

use RuntimeException;
use InvalidArgumentException;

/**
 * Класс DB - безопасная и удобная обёртка над mysqli.
 * 
 * Особенности:
 * - Singleton - одно соединение на всё приложение;
 * - Безопасные методы quote(), quoteInt(), quoteFloat(), quoteIN();
 * - Поддержка транзакций (beginTransaction, commit, rollback);
 * - Методы выборки: getOne(), getRow(), getRows(), getColumn(), getPairs();
 * - Логирование SQL при debug_sql = true.
 * 
 * @package Core\Database
 */
class DB
{
    use TSingleton;
    
    /**
     * Соединение с базой данных MySQL.
     * 
     * Инициализируется как `null` и устанавливается при успешном подключении.
     * Может быть `null`, если соединение не установлено или было закрыто.
     * 
     * @var mysqli|null
     */
    public ?mysqli $connection = null;

    /**
     * Флаг, указывающий на активное подключение к базе данных.
     * 
     * - `true` — соединение установлено и активно.
     * - `false` — соединение отсутствует (не было установлено или было разорвано).
     * 
     * @var bool
     */
    protected bool $connected = false;

    /**
     * Конструктор - устанавливает соединение с БД.
     */
    private function __construct()
    {
        $config = require_once CONFIG_DIR . '/database.php';
        $db = $config['connections']['mysql'];

        $this->connection = new mysqli(
            $db['host'], 
            $db['username'], 
            $db['password'], 
            $db['database'], 
            $db['port'] ?: null
        );

        $this->connection->set_charset($db['charset']);
        $this->connection->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, 1);
        $this->connected = true;
    }

    /**
     * Логирует SQL-запрос (если включен debug_sql).
     * 
     * @param string $sql Запрос.
     */
    private function logQuery(string $sql): void
    {
        file_put_contents(
            LOGS_DIR . '/db.log',
            '[' . date('Y-m-d H:i:s') . "] \n$sql\n\n",
            FILE_APPEND
        );
    }

    /**
     * Строит часть SQL-запроса с полной поддержкой типов данных.
     * Преобразует массив в строку: `name` = 'Роман', sname' => NULL, `bool` => 1, `int` = 12345, `float` = 123.45
     * 
     * @param array $array Ассоциативный массив данных.
     * @param string $devide Разделитель (по умолчанию ',')
     * 
     * @return string
     */
    public function buildPartQuery(array $array, string $devide = ','): string
    {
        $partQuery = '';

        if (is_array($array)) {
            $parts = [];
            foreach ($array as $index => $value) {

                $quotedIndex = '`' . $this->quote($index, true) . '`';
                
                if ($value === null || $value === '') {
                    $parts[] = $quotedIndex . ' = NULL';
                    continue;
                }

                if (is_bool($value)) {
                    $parts[] = $quotedIndex . ' = ' . ($value ? 1 : 0);
                    continue;
                }

                if (is_int($value)) {
                    $parts[] = $quotedIndex . ' = ' . $this->quoteInt($value);
                    continue;
                }

                if (is_float($value)) {
                    $parts[] = $quotedIndex . ' = ' . $this->quoteFloat($value);
                    continue;
                }

                $parts[] = $quotedIndex . ' = ' . $this->quote((string) $value);
            }

            $partQuery = implode($devide . ' ', $parts);
        }

        return $partQuery;
    }

    /**
     * Строит и выполняет SQL-запрос с безопасной обработкой всех типов данных.
     * 
     * DB::buildQuery('INSERT INTO `table` SET', $array);
     * 
     * @param string $query Начало SQL-запроса (например, INSERT INTO `table` SET).
     * @param array $array Ассоциативный массив данных.
     * @param string $devide Разделитель (по умолчанию ',').
     * 
     * @return mysqli_result|bool
     */
    public function buildQuery(string $query, array $array, string $devide = ','): mysqli_result|bool
    {
        if (empty($array)) return false;

        $parts = [];
        foreach ($array as $index => $value) {
            $quotedIndex = '`' . $this->quote($index, true) . '`';
            
            if ($value === null || $value === '') {
                $parts[] = $quotedIndex . ' = NULL';
                continue;
            }

            if (is_bool($value)) {
                $parts[] = $quotedIndex . ' = ' . ($value ? 1 : 0);
                continue;
            }
            
            if (is_int($value)) {
                $parts[] = $quotedIndex . ' = ' . $this->quoteInt($value);
                continue;
            }
            
            if (is_float($value)) {
                $parts[] = $quotedIndex . ' = ' . $this->quoteFloat($value);
                continue;
            }
            
            $parts[] = $quotedIndex . ' = ' . $this->quote((string) $value);
        }
        
        if (!empty($parts)) {
            $query .= ' ' . implode($devide . ' ', $parts);

            return $this->query($query);
        }

        return false;
    }

    /**
     * Возвращает ряд результата запроса в виде ассоциативного массива.
     * 
     * @param object $object
     * 
     * @return array|null|false
     */
    public function fetchAssoc(object $object): array|null|false
    {
        return @mysqli_fetch_assoc($object);
    }

    /**
     * Возвращает ряд результата запроса в виде объекта.
     * 
     * <code>
     *  $result = DB::query($sql);
     *  while ($row = DB::fetchObject($result)) {
     *   viewdata($row);
     *  }
     * </code>
     * 
     * @param object $object
     * 
     * @return array|false|null
     */
    public function fetchObject(object $object): array|false|null
    {
        return @mysqli_fetch_object($object);
    }

    /**
     * Возвращает ряд результата запроса в виде массива с ассоциативными и числовыми ключами.
     * 
     * @param object $object
     * 
     * @return array|false|null
     */
    public function fetchArray(object $object): array|false|null
    {
        return @mysqli_fetch_array($object);
    }

    /**
     * Выполняет запрос к БД.
     * 
     * <code>
     *  $sql = "SELECT * FROM `".PREFIX."product`";
     *  $result = DB::query($sql);
     *  while ($row = DB::fetchAssoc($result)) {
     *   viewdata($row);
     *  }
     * </code>
     * 
     * @param string $sql запрос.
     * 
     * @return mysqli_result|bool
     */
    public function query(string $sql): mysqli_result|bool
    {
        if (($num_args = func_num_args()) > 1) {

            $arg = func_get_args();
            unset($arg[0]);

            // Экранируем кавычки для всех входных параметров.
            foreach ($arg as $argument => $value) {
                $arg[$argument] = mysqli_real_escape_string($this->connection, $value);
            }

            $sql = vsprintf($sql, $arg);
        }
        
        if (function_exists('config') && config('app.debug_sql', false)) {
            $this->logQuery($sql);
        }
        
        return $this->connection ? mysqli_query($this->connection, $sql) : false;
    }

    /**
     * Экранирует кавычки для части запроса.
     * 
     * <code>
     *  // использование с кавычками
     *  $title = 'Чука Крабс';
     *  $sql = "SELECT * FROM `".PREFIX."product` WHERE `title` = ".DB::quote($title);
     *  // использование без кавычек
     *  $title = 'дверь';
     *  $sql = "SELECT * FROM `".PREFIX."product` WHERE `title` LIKE '%".DB::quote($title, true)."%'";
     *  // опасный запрос от пользователя
     *  $_POST['title'] = "Чука Крабс';TRUNCATE mg_setting";
     *  $sql = "SELECT * FROM `".PREFIX."product` WHERE `title` = ".DB::quote($_POST['title']);
     * </code>
     * 
     * @param string $string часть запроса.
     * @param string $noQuote - если true, то не будет выводить кавычки вокруг строки.
     * 
     * @return string
     */
    public function quote(string $string, bool $noQuote = false): string
    {
        $escaped = mysqli_real_escape_string($this->connection, $string);

        return ($noQuote) ? $escaped : "'{$escaped}'";
    }

    /**
     * Экранирует кавычки для части запроса и преобразует экранируемую часть запроса в тип integer.
     * 
     * @param string|int $value часть запроса.
     * @param bool $noQuote - если false, то кавычки будут выводиться вокруг строки.
     * 
     * @return int|string
     */
    public function quoteInt(string|int $value, bool $noQuote = true): int|string
    {
        $intValue = (int) $value;
        if ($noQuote) return $intValue;
        return "'". mysqli_real_escape_string($this->connection, (string) $intValue) ."'";
    }

    /**
     * Экранирует кавычки для части запроса, заменяет запятую на точку и преобразует экранируемую часть запроса в тип float.
     * 
     * @param string|float $value часть запроса.
     * @param string $noQuote - если false, то кавычки будут выводиться вокруг строки.
     * 
     * @return float|string
     */
    public function quoteFloat(string|float $value , $noQuote = true): float|string
    {
        $floatValue = (float) str_replace(',', '.', (string)$value);
        if ($noQuote) return $floatValue;
        return "'". mysqli_real_escape_string($this->connection, (string) $floatValue) ."'";
    }

    /**
     * Экранирует кавычки для части запроса и преобразует экранируемую часть запроса в пригодный вид для условий типа IN.
     * 
     * @param string|array $string часть запроса.
     * @param bool $returnNull если первый аргумент пустой, то возвращает NULL, иначе пустую строку 
     * 
     * @return string
     */
    public function quoteIN(string|array $string, bool $returnNull = true): string
    {
        if (empty($string)) {
            return $returnNull ? 'NULL' : "''";
        }

        if (is_array($string)) {
            $string = implode(',', $string);
        }

        $tmp = explode(',', $string);

        foreach ($tmp as $key => $value) {
            if (is_int($value) || ctype_digit($value)) {
                $tmp[$key] = $this->quoteInt($value);
                continue;
            }
            $tmp[$key] = $this->quote($value);
        }

        return implode(',', $tmp);
    }

    /**
     * Возвращает автоматически сгенерированный ID, 
     * созданный последним INSERT запросом.
     * 
     * @return string|int
     */
    public function insertId(): string|int
    {
        return @mysqli_insert_id($this->connection);
    }

    /**
     * Возвращает количество рядов результата запроса.
     * 
     * @param object $object
     * 
     * @return string|int
     */
    public function numRows(object $object): string|int
    {
        return @mysqli_num_rows($object);
    }

    /**
     * Получить одно значение из результата запроса.
     *
     * @param string $sql SQL-запрос.
     * 
     * @return mixed|null Возвращает значение или null, если результат пуст.
     */
    public function getOne(string $sql): mixed
    {
        $result = $this->query($sql);

        if ($result && $row = mysqli_fetch_row($result)) {
            return $row[0];
        }

        return null;
    }

    /**
     * Возвращает массив значений одного столбца
     * 
     * @param string $sql SQL-запрос
     * @return array
     */
    public function getColumn(string $sql): ?array
    {
        $query = $this->query($sql);
        if ($this->numRows($query) === 0) return null;

        $result = [];
        while ($row = mysqli_fetch_row($query)) {
            $result[] = $row[0];
        }
        
        return $result;
    }

    /**
     * Возвращает ассоциативный массив, где ключи - значения первого столбца запроса.
     * 
     * @param string $sql SQL-запрос
     * @param mixed $defaultValue Определяет формат значений:
     *   - true: все значения будут `true` (по умолчанию)
     *   - false: значения будут последовательными числами (0, 1, 2...)
     *   - любое другое значение: будет использовано как фиксированное значение для всех элементов
     * 
     * @return array|null Ассоциативный массив или null, если нет результатов.
     */
    public function getPairs(string $sql, mixed $defaultValue = false): ?array
    {
        $query = $this->query($sql);
        if ($this->numRows($query) === 0) return null;

        $result = [];
        $counter = 0;

        while ($row = mysqli_fetch_row($query)) {
            $key = $row[0];
            
            if ($defaultValue === false) {
                $result[$key] = $counter++;
            } else {
                $result[$key] = $defaultValue === true ? true : $defaultValue;
            }
        }
    
        return $result;
    }

    /**
     * Возвращает строку из результата поиска.
     * 
     * @param string $sql SQL-запрос.
     * 
     * @return array|null Возвращает массив или null.
     */
    public function getRow(string $sql): ?array
    {
        $query = $this->query($sql);
        if ($this->numRows($query) === 0) return null;
        
        return $this->fetchAssoc($query);
    }

    /**
     * Возвращает результат в виде ассоциативного массива.
     * 
     * @param string $sql SQL-запрос.
     * @param string|null $key Название поля для ключа (если null — обычный массив).
     * 
     * @return array|null Возвращает массив или null.
     */
    public function getRows(string $sql, mixed $key = null): ?array
    {
        $query = $this->query($sql);
        if ($this->numRows($query) === 0) return null;
        
        $result = [];
        while ($row = $this->fetchAssoc($query)) {
           if ($key !== null && isset($row[$key])) {
                $result[$row[$key]] = $row;
            } else {
                $result[] = $row;
            }
        }

        return $result;
    }

    /**
     * Получает число строк, затронутых предыдущей операцией MySQL.
     * 
     * @return string|int
     */
    public function affectedRows(): string|int
    {
        return @mysqli_affected_rows($this->connection);
    }

    /**
     * Начинает транзакцию.
     * 
     * @return void
     */
    public function beginTransaction(): void
    {
        if ($this->connected) {
            $this->connection->begin_transaction();
        }
    }
    
    /**
     * Фиксирует транзакцию.
     * 
     * @return void
     */
    public function commit(): void
    {
        if ($this->connected) {
            $this->connection->commit();
        }
    }

    /**
     * Откатывает транзакцию.
     * 
     * @return void
     */
    public function rollback(): void
    {
        if ($this->connected) {
            $this->connection->rollback();
        }
    }

    /**
     * Закрывает соединение.
     * 
     * @return bool
     */
    public function close(): bool
    {
        if ($this->connected) {
            $this->connected = false;
            return mysqli_close($this->connection);
        }
        
        return false;
    }
}