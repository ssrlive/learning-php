<?php
class Db
{
    public static string $tablename;
    public static string $where = "";
    public static ?PDO $pdo = null;
    public static ?PDOStatement $stmt = null;
    public static array $executeData = [];

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

    public function where(array $condition)
    {
        $where = "";
        if (!empty($condition)) {
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
            $where = implode(" AND ", $whereArray);

            if (self::$executeData !== []) {
                self::$executeData = array_merge(self::$executeData, $executeData);
            } else {
                self::$executeData = $executeData;
            }
        }

        $this->buildWhere($where);

        return $this;
    }

    public function whereNull($field)
    {
        $where = "$field IS NULL";
        $this->buildWhere($where);
        return $this;
    }

    public function whereNotNull($field)
    {
        $where = "$field IS NOT NULL";
        $this->buildWhere($where);
        return $this;
    }

    private function buildWhere($where)
    {
        if ($where !== "") {
            if (strpos(self::$where, "WHERE") === false) {
                self::$where = "WHERE " . $where;
            } else {
                self::$where .= " AND " . $where;
            }
        }
    }

    public function select()
    {
        $sql = "SELECT * FROM " . self::$tablename;
        if (!empty(self::$where)) {
            $sql .= " " . self::$where;
        }
        // echo $sql . "\n";
        self::$stmt = self::$pdo->prepare($sql);
        if (self::$executeData !== []) {
            self::$stmt->execute(self::$executeData);
        } else {
            self::$stmt->execute();
        }
        $result = self::$stmt->fetchAll(PDO::FETCH_ASSOC);
        self::$stmt->closeCursor();
        return $result;
    }
}

$result = Db::table("users")
    ->where([
        ["createtime", "between", ["2024-01-01", "2024-12-31"]]
    ])
    ->where([
        ["id", "in", [1, 2, 3]]
    ])
    ->whereNotNull("createtime")
    ->select();
var_dump($result);
