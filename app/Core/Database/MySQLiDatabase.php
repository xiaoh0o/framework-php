<?php
namespace App\Core\Database;

use Closure;
use Exception;
use MySQLi;

/**
 * MySQLi 数据操作类
 */
class MySQLiDatabase implements DatabaseInterface
{
    private string $hostname; //服务器名
    private string $username; //数据库账户
    private string $password; //数据库密码
    private string $database; //数据库名
    private string $charset = 'utf8mb4';

    private int $connect_time = 0;
    private MySQLi $connection;
    private array $sqlQueue = []; // 请求队列
    private int $sqlMaxLength = 200; // 请求队列的最大长度
    private string $error = "";

    public function __construct(string $hostname, string $username, string $password, string $database)
    {
        $this->hostname = $hostname;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;

        $this->connect();
    }

    protected function connect()
    {
        for ($i = 0; $i < 10; $i++) {
            try {
                $this->connection = new MySQLi(
                    $this->hostname,
                    $this->username,
                    $this->password,
                    $this->database
                );
                $this->connection->set_charset($this->charset);
                $this->connection->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, true); // int float 类型
                $this->connect_time = time();
                return $this->connection;
            } catch (\Throwable $e) {
                $this->error = $e->getMessage();
                sleep(1);
            }
        }

        throw new Exception('数据库连接错误！');
    }

    private function assertTable(string $table): void
    {
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $table)) {
            throw new Exception("数据库表名无效！[{$table}]");
        }
    }

    private function assertKey(string $key): void
    {
        $count = preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*(\.[a-zA-Z_][a-zA-Z0-9_]*)?$/', $key);
        if ($count == false) {
            throw new Exception("数据表字段名无效！[{$key}]");
        }
    }

    private function findSQLMethod(string $sql): string
    {
        if (preg_match('/^\s*(?:\/\*.*?\*\/|--[^\n]*\n|\s)*(\w+)/s', $sql, $m)) {
            $method = strtoupper($m[1]);
        } else {
            $method = '';
        }
        return $method;
    }

    // 查询
    public function query(string $sql)
    {
        $this->insertSqlQueue($sql);

        $resource = $this->connection->query($sql);
        if (! $resource && in_array($this->connection->errno, [2006, 2013])) {
            // 重连再次请求
            $this->connect();
            $resource = $this->connection->query($sql);
        }

        if ($resource) {
            $this->error = "";
            // 前面是注释时失效
            $method = $this->findSQLMethod($sql);
            switch ($method) {
                case "INSERT":
                    return $this->connection->insert_id;
                case "UPDATE":
                case "DELETE":
                case "REPLACE":
                    $affected_rows = $this->connection->affected_rows;
                    if ($affected_rows == -1) {
                        throw new Exception("更新数据记录失败！");
                    }
                    return $affected_rows;
                case "SELECT":
                    return $resource;
                case "CREATE":
                    return $resource; // bool
                default:
                    return true;
            }
        }

        $this->error = $this->connection->error;
        throw new Exception('执行数据库请求失败！');
    }

    // 读取一行
    public function row(string $sql): ?array
    {
        $method = $this->findSQLMethod($sql);
        if ($method != "SELECT") {
            throw new Exception("请使用 SELECT 查询语句！");
        }
        $resource = $this->query($sql);
        if ($resource) {
            $row = $resource->fetch_array(MYSQLI_ASSOC);
            if ($row) {
                return $row;
            }
        }
        return null;
    }

    // 读取多行
    public function rows(string $sql): array
    {
        $method = $this->findSQLMethod($sql);
        if ($method != "SELECT") {
            throw new Exception("请使用 SELECT 查询语句！");
        }
        $resource = $this->query($sql);
        if ($resource) {
            $count = $resource->num_rows;
            $rows = [];
            if ($count > 0) {
                for ($i = 0; $i < $count; $i++) {
                    $rows[$i] = $resource->fetch_array(MYSQLI_ASSOC);
                }
                return $rows;
            }
        }
        return [];
    }

    // 获取一行或一个字段
    public function get(string $sql): string|int|null
    {
        $row = $this->row($sql);
        if ($row) {
            return current($row);
        }
        return null;
    }

    // 写入
    public function insert(string $table, array $data): int
    {
        $this->assertTable($table);
        $sql = $this->implodeFieldValue($data);
        return $this->query("INSERT INTO `{$this->database}`.`{$table}` SET {$sql}");
    }

    // 更新
    public function update(string $table, array $data, string|array $condition): int
    {
        $this->assertTable($table);
        $where = $this->where($condition);
        $sql = $this->implodeFieldValue($data);

        return $this->query("UPDATE `{$this->database}`.`{$table}` SET {$sql} WHERE {$where}");
    }

    // 删除
    public function delete(string $table, string|array $condition, int $limit_number = 0): int
    {
        $this->assertTable($table);
        $where = $this->where($condition);
        $limit = $limit_number > 0 ? "LIMIT {$limit_number}" : "";

        return $this->query("DELETE FROM `{$this->database}`.`{$table}` WHERE {$where} {$limit}");
    }

    // 替换, 必须保证有主键
    public function replace(string $table, array $data): int
    {
        $this->assertTable($table);
        $sql = $this->implodeFieldValue($data);
        return $this->query("REPLACE INTO `{$this->database}`.`{$table}` SET {$sql}");
    }

    // 受影响的行数
    public function affectedRows(): int
    {
        return $this->connection->affected_rows;
    }

    // 获取数据库名称
    public function dbname(): string
    {
        return $this->database;
    }

    // 获取错误信息
    public function error(): string
    {
        return $this->error;
    }

    // 获取 insert_id
    public function id(): int
    {
        return $this->connection->insert_id;
    }

    // 字段自增
    public function incr(string $table, array $incrData, array $updateData, string|array $condition): int
    {
        $this->assertTable($table);
        $where = $this->where($condition);

        $fields = [];
        foreach ($incrData as $key => $value) {
            $this->assertKey($key);
            $fieldKey = str_contains($key, '.') ? $key : "`{$key}`";
            if (!is_numeric($value)) {
                throw new Exception("自增字段，参数必须是数字类型！");
            }
            $value = abs($value);
            $fields[] = "{$fieldKey} = {$fieldKey} + {$value}";
        }
        $sql = implode(", ", $fields);
        if (!empty($updateData)) {
            $sql .= ", ". $this->implodeFieldValue($updateData);
        }

        return $this->query("UPDATE `{$this->database}`.`{$table}` SET {$sql} WHERE {$where}");
    }

    // 字段自减
    public function decr(string $table, array $decrData, array $updateData, string|array $condition): int
    {
        $this->assertTable($table);
        $where = $this->where($condition);

        $fields = [];
        foreach ($decrData as $key => $value) {
            $this->assertKey($key);
            $fieldKey = str_contains($key, '.') ? $key : "`{$key}`";
            if (!is_numeric($value)) {
                throw new Exception("自减字段，参数必须是数字类型！");
            }

            $value = abs($value);
            $fields[] = "{$fieldKey} = {$fieldKey} - {$value}";
        }
        $sql = implode(", ", $fields);
        if (!empty($updateData)) {
            $sql .= ", ". $this->implodeFieldValue($updateData);
        }

        return $this->query("UPDATE `{$this->database}`.`{$table}` SET {$sql} WHERE {$where}");
    }

    // 转义字符串
    public function encode(string $content): string
    {
        return $this->connection->real_escape_string($content);
    }

    // where 语句组合
    public function where(string|array $condition, string $separator = 'AND'): string
    {
        if (empty($condition)) {
            return "1";
        }

        // 数组模式
        if (is_array($condition)) {
            $wheres = [];
            foreach ($condition as $keys => $value) {
                if (!is_string($keys)) {
                    throw new Exception("字段名称必须是字符串！");
                }

                $count = preg_match('/([a-z0-9_\.]+)(\[(?<operator>\>\=?|\<\=?|\!|\~|\^|\$)\])?/i', $keys, $match);
                if ($count == false) {
                    throw new Exception("where [key] error!");
                }
                $key = $match[1];
                if (strpos($key, ".") !== false) {
                    $fieldKey = $key;
                } else {
                    $fieldKey = "`{$key}`";
                }

                $value = $this->implodeWhereValue($value);
                if (isset($match['operator'])) {
                    $operator = $match['operator'];
                    if (in_array($operator, ['>', '>=', '<', '<='])) {
                        if (is_int($value) || is_float($value)) {
                            $wheres[] = "{$fieldKey} {$operator} {$value}";
                        } else {
                            throw new Exception("仅支持数值类型的比较！");
                        }
                    } elseif (in_array($operator, ['~', '$', '^'])) {
                        if (is_int($value) || is_float($value)) {
                            $value = strval($value);
                        }
                        if (!is_string($value)) {
                            throw new Exception("数据格式不正确！");
                        }
                        $value = str_replace(['%', '_'], ['\%', '\_'], $value);
                        switch ($operator) {
                            case '~': // LIKE
                                $wheres[] = "{$fieldKey} LIKE '%{$value}%'";
                                break;
                            case '$': // 以 XX 结尾
                                $wheres[] = "{$fieldKey} LIKE '%{$value}'";
                                break;
                            case '^': // 以 XX 开头
                                $wheres[] = "{$fieldKey} LIKE '{$value}%'";
                                break;
                        }
                    } elseif ($operator == '!') {
                        if (is_array($value)) {
                            $wheres[] = "{$fieldKey} NOT IN (". implode(", ", $value) .")";
                        } elseif (is_null($value)) {
                            $wheres[] = "{$fieldKey} IS NOT NULL";
                        } elseif (is_int($value) || is_float($value)) {
                            $wheres[] = "{$fieldKey} != {$value}";
                        } else {
                            $wheres[] = "{$fieldKey} != '{$value}'";
                        }
                    }
                } else {
                    if (is_array($value)) {
                        $wheres[] = "{$fieldKey} IN (". implode(", ", $value) .")";
                    } elseif (is_null($value)) {
                        $wheres[] = "{$fieldKey} IS NULL";
                    } elseif (is_int($value) || is_float($value)) {
                        $wheres[] = "{$fieldKey} = {$value}";
                    } else {
                        $wheres[] = "{$fieldKey} = '{$value}'";
                    }
                }
            }
            return implode(" {$separator} ", $wheres);
        }

        // 字符串
        return $condition;
    }

    /**
     * 组合 Value 值
     *
     * @param int|float|string|array|null|bool $values 当为数组时，必须是一维数组
     * @return mixed
     */
    private function implodeWhereValue($values): mixed
    {
        if (is_int($values) || is_float($values) || is_null($values)) {
            return $values;
        }
        if (is_bool($values)) {
            return $values ? 1 : 0;
        }
        if (is_string($values)) {
            return $this->encode($values);
        }
        if (is_array($values)) {
            $data = [];
            foreach ($values as $value) {
                if (is_array($value)) {
                    throw new Exception("条件参数的值，类型不正确，不支持二维数组！");
                }
                $data[] = $this->implodeWhereValue($value);
            }
            return $data;
        }
        throw new Exception("条件参数的值，类型不正确！");
    }

    // 字段组合
    private function implodeFieldValue(array $array, string $glue = ',')
    {
        $parts = [];
        foreach ($array as $key => $value) {
            $this->assertKey($key);
            $fieldKey = str_contains($key, '.') ? $key : "`{$key}`";

            if (is_null($value)) {
                $parts[] = "{$fieldKey} = NULL";
            } elseif (is_bool($value)) {
                $parts[] = "{$fieldKey} = " . ($value ? 1 : 0);
            } elseif (is_int($value) || is_float($value)) {
                $parts[] = "{$fieldKey} = {$value}"; // 数字不加引号
            } elseif (is_string($value)) {
                $parts[] = "{$fieldKey} = '" . $this->encode($value) . "'";
            } else {
                throw new Exception("字段值类型不支持: " . gettype($value));
            }
        }
        return implode(" {$glue} ", $parts);
    }

    // 获取请求队列
    public function sqls(): array
    {
        return $this->sqlQueue;
    }

    // 将请求 写入队列
    private function insertSqlQueue(string $sql)
    {
        $this->sqlQueue[] = $sql;
        if (count($this->sqlQueue) >= $this->sqlMaxLength) {
            array_shift($this->sqlQueue);
        }
    }

    // 返回一个事务类
    public function transaction(): TransactionInterface
    {
        return new Transaction($this);
    }

    // 执行一个事务闭包
    public function doTransaction(Closure $callback)
    {
        $trans = $this->transaction();
        $trans->begin();
        try {
            $result = $callback();
            $trans->commit();
            return $result;
        } catch (\Throwable $e) {
            $trans->rollback();
            throw $e;
        }
    }
}
