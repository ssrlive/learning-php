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
            echo "连接成功\n";
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
            foreach ($condition as $key => $value) {
                $whereArray[] = "$value[0] $value[1] ?";
                $executeData[] = $value[2];
            }
            $where = implode(" AND ", $whereArray);

            if (self::$executeData !== []) {
                self::$executeData = array_merge(self::$executeData, $executeData);
            } else {
                self::$executeData = $executeData;
            }
        }

        if ($where !== "") {
            if (strpos(self::$where, "WHERE") === false) {
                self::$where = "WHERE " . $where;
            } else {
                self::$where .= " AND " . $where;
            }
        }

        return $this;
    }

    public function select()
    {
        $sql = "SELECT * FROM " . self::$tablename;
        if (!empty(self::$where)) {
            $sql .= " " . self::$where;
        }
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

$result = Db::table("users")->where([["username", "=", "admin"]])
    ->where([["password", "=", "123456"]])
    ->select();
var_dump($result);
