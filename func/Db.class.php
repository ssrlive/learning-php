<?php
if (version_compare(PHP_VERSION, '7.4', '<')) {
    die('This Application requires PHP version 7.4 or higher.');
}

class Db
{
    public static ?PDO $pdo = null;

    private string $tablename;
    private array $executeData = [];
    private string $fields = "*";
    private string $where = "";
    private string $orderBy = "";
    private string $limit = "";
    // 判斷是否在 where 組內 ()
    private $isGrouping = false;
    private $whereAndOrNot = "AND";

    public function __construct()
    {
        self::connect();
        self::setAttr();
    }

    public static function connect()
    {
        $config = require __DIR__ . "/../config/database.php";
        // $config = require $_SERVER['DOCUMENT_ROOT'] . "/config/database.php";
        $dbms = $config["dbms"];
        $host = $config["host"];
        $dbname = $config["dbname"];
        $user = $config["user"];
        $password = $config["password"];
        $dsn = "$dbms:host=$host;dbname=$dbname;charset=utf8";
        try {
            self::$pdo = new PDO($dsn, $user, $password);
            // echo "连接成功\n";
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    private static function setAttr()
    {
        self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // self::$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    }

    public static function table($tablename)
    {
        $obj = new self();
        $obj->tablename = $tablename;
        return $obj;
    }

    public function where(array|Closure $condition, string $andOrNot = "AND")
    {
        $andOrNot = strtoupper($andOrNot);
        $where = "";
        if (!empty($condition)) {
            if ($condition instanceof Closure) {
                $this->isGrouping = true;
                $this->whereAndOrNot = $andOrNot;
                $condition($this);
                $this->isGrouping = false;
                return $this;
            }
            $whereArray = [];
            $executeData = [];
            foreach ($condition as $value) {
                $verb = strtoupper($value[1]);
                if ($verb === "BETWEEN") {
                    $whereArray[] = "$value[0] $verb ? AND ?";
                    $executeData[] = $value[2][0];
                    $executeData[] = $value[2][1];
                } else if ($verb === "IN") {
                    $in = implode(",", array_fill(0, count($value[2]), "?"));
                    $whereArray[] = "$value[0] $verb ($in)";
                    $executeData = array_merge($executeData, $value[2]);
                } else {
                    $whereArray[] = "$value[0] $verb ?";
                    $executeData[] = $value[2];
                }
            }

            if ($andOrNot === "AND" || $andOrNot === "OR") {
                $where = implode(" $andOrNot ", $whereArray);
            } else if ($andOrNot === "NOT") {
                // NOT (a AND b   c)
                $where = "NOT (" . implode(" AND ", $whereArray) . ")";
            } else if ($andOrNot === "OR NOT") {
                // NOT (a OR b OR c)
                $where = "NOT (" . implode(" OR ", $whereArray) . ")";
            } else {
                throw new Exception("The second parameter of the where method must be 'AND', 'OR' or 'NOT' or 'OR NOT'.");
            }

            if ($this->isGrouping) {
                $where = "( $where )";
            }

            if ($this->executeData !== []) {
                $this->executeData = array_merge($this->executeData, $executeData);
            } else {
                $this->executeData = $executeData;
            }
        }

        if ($this->isGrouping) {
            $this->buildWhere($where, $this->whereAndOrNot);
        } else {
            $this->buildWhere($where, $andOrNot);
        }

        return $this;
    }

    public function whereOr(array|Closure $condition)
    {
        return $this->where($condition, "OR");
    }

    public function whereNot(array|Closure $condition)
    {
        return $this->where($condition, "NOT");
    }

    public function whereOrNot(array|Closure $condition)
    {
        return $this->where($condition, "OR NOT");
    }

    public function whereNull($field, string $andOrNot = "AND")
    {
        $where = "$field IS NULL";
        $this->buildWhere($where, $andOrNot);
        return $this;
    }

    public function whereNotNull($field, string $andOrNot = "AND")
    {
        $where = "$field IS NOT NULL";
        $this->buildWhere($where, $andOrNot);
        return $this;
    }

    private function buildWhere($where, string $andOrNot = "AND")
    {
        if ($where !== "") {
            if (strpos($this->where, "WHERE") === false) {
                $this->where = "WHERE " . $where;
            } else {
                $this->where .= " " . $andOrNot . " " . $where;
            }
        }
    }

    public function orderBy(string $field, string $sort = "ASC")
    {
        $sort = strtoupper($sort);
        if ($sort !== "ASC" && $sort !== "DESC") {
            throw new Exception("The second parameter of the orderBy method must be 'ASC' or 'DESC'.");
        }
        $this->orderBy .= " ORDER BY $field $sort";
        return $this;
    }

    public function limit(int $limit, int $offset = null)
    {
        if ($offset !== null) {
            $this->limit = " LIMIT $offset, $limit";
        } else {
            $this->limit = " LIMIT $limit";
        }
        return $this;
    }

    public function find()
    {
        $result = $this->limit(1)->select();
        return isset($result[0]) ? $result[0] : false;
    }

    public function fields(array $fields)
    {
        $this->fields = implode(",", $fields);
        return $this;
    }

    public function getSqlString()
    {
        $sql = "SELECT " . $this->fields . " FROM " . $this->tablename;
        if (!empty($this->where)) {
            $sql .= " " . $this->where;
        }
        if (!empty($this->orderBy)) {
            $sql .= $this->orderBy;
        }
        if (!empty($this->limit)) {
            $sql .= $this->limit;
        }
        return $sql;
    }

    public function count()
    {
        $this->fields = "COUNT(*) AS count";
        $result = $this->find();
        return $result["count"];
    }

    public function select()
    {
        try {
            $sql = $this->getSqlString();
            $stmt = self::$pdo->prepare($sql);
            if ($this->executeData !== []) {
                $stmt->execute($this->executeData);
            } else {
                $stmt->execute();
            }
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            return $result;
        } catch (PDOException $e) {
            throw new Exception("Select failed: " . $e->getMessage());
        }
    }

    public function delete(): int
    {
        try {
            $sql = "DELETE FROM " . $this->tablename;
            if (!empty($this->where)) {
                $sql .= " " . $this->where;
            }
            $stmt = self::$pdo->prepare($sql);
            if ($this->executeData !== []) {
                $stmt->execute($this->executeData);
            } else {
                $stmt->execute();
            }
            $result = $stmt->rowCount();
            $stmt->closeCursor();
            return $result;
        } catch (PDOException $e) {
            throw new Exception("Delete failed: " . $e->getMessage());
        }
    }

    /**
     * Insert data to the table
     * @param array $data
     * @return int The last insert id
     */
    public function insert(array $data): int
    {
        if (empty($data)) {
            throw new Exception("Insert: the data parameter cannot be empty.");
        }
        $fields = implode(",", array_keys($data));
        $values = implode(",", array_fill(0, count($data), "?"));
        $sql = "INSERT INTO " . $this->tablename . " ($fields) VALUES ($values)";
        try {
            $stmt = self::$pdo->prepare($sql);
            $stmt->execute(array_values($data));
            $result = self::$pdo->lastInsertId();
            $stmt->closeCursor();
            return $result;
        } catch (PDOException $e) {
            throw new Exception("Insert failed: " . $e->getMessage());
        }
    }

    /**
     * Update data
     * @param array $data
     * @return int The number of rows affected
     */
    public function update(array $data): int
    {
        if (empty($data)) {
            throw new Exception("Update: the data parameter cannot be empty.");
        }
        $executeData = array_values($data);
        $set = implode("=?, ", array_keys($data)) . "=?";
        $sql = "UPDATE " . $this->tablename . " SET $set";
        if (!empty($this->where)) {
            $sql .= " " . $this->where;
        }
        try {
            $stmt = self::$pdo->prepare($sql);
            if (!$stmt->execute(array_merge($executeData, $this->executeData))) {
                throw new Exception("Update failed: " . $stmt->errorInfo()[2]);
            }
            $result = $stmt->rowCount();
            $stmt->closeCursor();
            return $result;
        } catch (PDOException $e) {
            throw new Exception("Update failed: " . $e->getMessage());
        }
    }
}

// $obj = Db::table("users")
//     ->where(
//         function ($query) {
//             $query->where([["createtime", "between", ["2024-01-01", "2024-12-31"]]]);
//         },
//         "OR"
//     )
//     ->whereOr(
//         function ($query) {
//             $query->where([["id", "in", [1, 2, 3]]]);
//         }
//     )
//     ->whereNotNull("createtime")
//     ->orderBy("id", "DESC");
// $result = $obj->select();
// $sql = $obj->getSqlString();
// echo $sql . "\n";

// $obj = Db::table("users")
//     ->fields(["id", "username", "password"])
//     ->orderBy("id", "DESC");
// $result = $obj->count();
// $sql = $obj->getSqlString();
// echo $sql . "\n";
// var_dump($result);

// $result = Db::table("users")->insert(["username" => "test3", "password" => "123456"]);
// var_dump($result);

// $result = Db::table("users")
//     ->where([["id", "=", 1]])
//     ->update(["username" => "tezcxzxcst99", "password" => "123zxczxc456"]);
// var_dump($result);

// $result = Db::table("users")
//     ->where([["id", "=", 3]])
//     ->delete();
// var_dump($result);
