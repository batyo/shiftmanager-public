<?php
require_once(dirname(__DIR__). "\\traitOutput\\users.php");

/**
 * users テーブルに情報を書き込むメソッド群
 */
trait InputUsers
{
    use OutputUsers;

    /** @var PDO データベース接続のインスタンス */
    protected $connection;

    /**
     * ユーザー情報をデータベースに保存する
     *
     * @param string    $userName   ユーザー名
     * @param int       $userId     ユーザーID
     * @param int       $employeeId 従業員ID
     * @param string    $authority  権限
     * @param string    $password   パスワード
     * 
     * @return bool 成功した場合は true、失敗した場合は false
     */
    public function saveUser($userName, $userId, $employeeId, $authority, $password)
    {
        try {
            $this->connection->beginTransaction();

            $sql = "INSERT INTO users (user_name, user_id, employee_id, authority, password) VALUES (:userName, :userId, :employeeId, :authority, :password)";
            $stmt = $this->connection->prepare($sql);

            $stmt->bindParam(":userName", $userName, PDO::PARAM_STR);
            $stmt->bindParam(":userId", $userId, PDO::PARAM_INT);
            $stmt->bindParam(":employeeId", $employeeId, PDO::PARAM_INT);
            $stmt->bindParam(":authority", $authority, PDO::PARAM_STR);
            $stmt->bindParam(":password", $password, PDO::PARAM_STR);

            $stmt->execute();

            $this->connection->commit();

            return true;

        } catch (PDOException $e) {
            $this->connection->rollBack();

            $logMassage = "Database Error in". __FUNCTION__. " :". $e->getMessage();
            $logFile = "../../../log/error_log.txt";
            error_log($logMassage, 3, $logFile);

            echo $logMassage;
            return false;
        }
    }
}
