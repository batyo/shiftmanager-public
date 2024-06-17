<?php
require_once(dirname(__DIR__). "\\general.php");
require_once(__DIR__. "\\database_input.php");

$general = new General();
spl_autoload_register($general->class_autoload(__DIR__. "\\traitOutput"));

/**
 * Class Database
 *
 * データベースからテーブル情報を取得
 */
class Database
{
    use OutputArchives, OutputAssignments, OutputAttendances, OutputCalendarShifts, OutputEmployees, OutputPreferences, OutputUsers;
    
    /**
     * Database コンストラクタ
     *
     * @param string $host     データベースホスト名
     * @param string $username データベースユーザー名
     * @param string $password データベースパスワード
     * @param string $database データベース名
     */
    public function __construct($host, $username, $password, $database)
    {
        try {
            $this->connection = new PDO("mysql:host=$host;dbname=$database", $username, $password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("接続に失敗しました: " . $e->getMessage());
        }
    }

    /**
     * データベースを切断する
     */
    public function closeConnection()
    {
        $this->connection = null;
    }

    /**
     * PDO の取得
     *
     * @return PDO PDOオブジェクト
     */
    public function getPDO()
    {
        return $this->connection;
    }

    /**
     * InputDatabaseクラスオブジェクトを取得
     * 
     * @return object
     */
    public function getInputDatabaseObject()
    {
        return new InputDatabase($this->getPDO());
    }
}
