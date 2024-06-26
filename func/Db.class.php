<?php
if (version_compare(PHP_VERSION, '7.4', '<')) {
    die('This Application requires PHP version 7.4 or higher.');
}

class Db
{
    public static string $tablename;
    public static string $fields = "*";
    public static string $where = "";
    public static string $orderBy = "";
    public static string $limit = "";
    public static ?PDO $pdo = null;
    public static ?PDOStatement $stmt = null;
    public static array $executeData = [];
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
        self::$tablename = $tablename;
        return new self();
    }

    public function where(array|Closure $condition, string $andOrNot = "AND")
    {
        $_andOrNot = strtoupper($andOrNot);
        $where = "";
        if (!empty($condition)) {
            if ($condition instanceof Closure) {
                $this->isGrouping = true;
                $this->whereAndOrNot = $_andOrNot;
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

            if ($_andOrNot === "AND" || $_andOrNot === "OR") {
                $where = implode(" $_andOrNot ", $whereArray);
            } else if ($_andOrNot === "NOT") {
                // NOT (a AND b   c)
                $where = "NOT (" . implode(" AND ", $whereArray) . ")";
            } else if ($_andOrNot === "OR NOT") {
                // NOT (a OR b OR c)
                $where = "NOT (" . implode(" OR ", $whereArray) . ")";
            } else {
                throw new Exception("The second parameter of the where method must be 'AND', 'OR' or 'NOT' or 'OR NOT'.");
            }

            if ($this->isGrouping) {
                $where = "( $where )";
            }

            if (self::$executeData !== []) {
                self::$executeData = array_merge(self::$executeData, $executeData);
            } else {
                self::$executeData = $executeData;
            }
        }

        if ($this->isGrouping) {
            $this->buildWhere($where, $this->whereAndOrNot);
        } else {
            $this->buildWhere($where, $_andOrNot);
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
            if (strpos(self::$where, "WHERE") === false) {
                self::$where = "WHERE " . $where;
            } else {
                self::$where .= " " . $andOrNot . " " . $where;
            }
        }
    }

    public function orderBy(string $field, string $sort = "ASC")
    {
        $sort = strtoupper($sort);
        if ($sort !== "ASC" && $sort !== "DESC") {
            throw new Exception("The second parameter of the orderBy method must be 'ASC' or 'DESC'.");
        }
        self::$orderBy .= " ORDER BY $field $sort";
        return $this;
    }

    public function limit(int $limit, int $offset = null)
    {
        if ($offset !== null) {
            self::$limit = " LIMIT $offset, $limit";
        } else {
            self::$limit = " LIMIT $limit";
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
        self::$fields = implode(",", $fields);
        return $this;
    }

    public function getSqlString()
    {
        $sql = "SELECT " . self::$fields . " FROM " . self::$tablename;
        if (!empty(self::$where)) {
            $sql .= " " . self::$where;
        }
        if (!empty(self::$orderBy)) {
            $sql .= self::$orderBy;
        }
        if (!empty(self::$limit)) {
            $sql .= self::$limit;
        }
        return $sql;
    }

    public function count()
    {
        self::$fields = "COUNT(*) AS count";
        $result = $this->find();
        return $result["count"];
    }

    public function select()
    {
        try {
            $sql = $this->getSqlString();
            self::$stmt = self::$pdo->prepare($sql);
            if (self::$executeData !== []) {
                self::$stmt->execute(self::$executeData);
            } else {
                self::$stmt->execute();
            }
            $result = self::$stmt->fetchAll(PDO::FETCH_ASSOC);
            self::$stmt->closeCursor();
            return $result;
        } catch (PDOException $e) {
            throw new Exception("Select failed: " . $e->getMessage());
        }
    }

    public function delete(): int
    {
        try {
            $sql = "DELETE FROM " . self::$tablename;
            if (!empty(self::$where)) {
                $sql .= " " . self::$where;
            }
            self::$stmt = self::$pdo->prepare($sql);
            if (self::$executeData !== []) {
                self::$stmt->execute(self::$executeData);
            } else {
                self::$stmt->execute();
            }
            $result = self::$stmt->rowCount();
            self::$stmt->closeCursor();
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
        $sql = "INSERT INTO " . self::$tablename . " ($fields) VALUES ($values)";
        try {
            self::$stmt = self::$pdo->prepare($sql);
            self::$stmt->execute(array_values($data));
            $result = self::$pdo->lastInsertId();
            self::$stmt->closeCursor();
            return $result;
        } catch (PDOException $e) {
            throw new Exception("Insert failed: " . $e->getMessage());
        }
    }

    /**
     * Update data
     * @param array $data
     * @return int
     */
    public function update(array $data): int
    {
        if (empty($data)) {
            throw new Exception("Update: the data parameter cannot be empty.");
        }
        $executeData = array_values($data);
        $set = implode("=?, ", array_keys($data)) . "=?";
        $sql = "UPDATE " . self::$tablename . " SET $set";
        if (!empty(self::$where)) {
            $sql .= " " . self::$where;
        }
        try {
            self::$stmt = self::$pdo->prepare($sql);
            self::$stmt->execute(array_merge($executeData, self::$executeData));
            $result = self::$stmt->rowCount();
            self::$stmt->closeCursor();
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
