<?php

/**
 * users テーブルの読み取り専用メソッド群
 */
trait OutputUsers
{
    /** @var PDO データベース接続のインスタンス */
    protected $connection;
    
    /**
     * ユーザーID の重複チェックを行う
     *
     * @param int $generateNumber   ランダムに生成された数字
     * @return true|false   重複がある場合 true または重複が無い場合は false
     */
    public function DuplicateIdCheck($generateNumber)
    {
        $sql = "SELECT COUNT(*) FROM users WHERE user_id = :randomNumber";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':randomNumber', $generateNumber, PDO::PARAM_INT);
        $stmt->execute();
        $count = $stmt->fetchColumn();
    
        // 重複が無い場合は false ある場合は true
        if ($count === 0) return false;
        return true;
    }

    /**
     * ユーザーIDによってユーザーを取得する
     *
     * @param int $userId ユーザーID
     *
     * @return array|null ユーザー情報または見つからない場合はnull
     */
    public function getUserByUserId($userId)
    {
        $sql = "SELECT user_id, user_name, employee_id, authority, password FROM users WHERE user_id = :userId";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(":userId", $userId, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user;
    }

    /**
     * 従業員IDによって従業員名を取得する
     *
     * @param int $employeeId 従業員ID
     *
     * @return string|null 従業員名または見つからない場合はnull
     */
    public function getEmployeeNameByEmployeeId($employeeId)
    {
        $sql = "SELECT user_name FROM users WHERE employee_id = :employeeId";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(":employeeId", $employeeId, PDO::PARAM_INT);
        $stmt->execute();
        $employee = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $employeeName = $employee["user_name"];

        return $employeeName;
    }

    /**
     * 従業員IDによってユーザーIDを取得する
     *
     * @param int $employeeId 従業員ID
     * 
     * @return string|null 従業員ID または見つからない場合は null
     */
    public function getUserIdByEmployeeId($employeeId)
    {
        $sql = "SELECT user_id FROM users WHERE employee_id = :employeeId";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(":employeeId", $employeeId, PDO::PARAM_INT);
        $stmt->execute();
        $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $userId = $userInfo["user_id"];

        return $userId;
    }

    /**
     * ユーザーIDとパスワードからユーザー名を取得する
     *
     * @param int       $userId         ユーザーID
     * @param string    $userPassword   パスワード
     * 
     * @return string|null  ユーザー名または見つからない場合はnull
     */
    public function getUserNameByUserIdAndPassword($userId, $userPassword)
    {
        $sql = "SELECT user_name, password FROM users WHERE user_id = :userId";
        $stmt = $this->connection->prepare($sql);

        $stmt->bindParam(":userId", $userId, PDO::PARAM_STR);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // ユーザーIDが異なる
        if ($user == null) return null;

        // パスワードチェック
        // password_hash() 関数は同じ引数でも毎回異なるハッシュ値を生成するため一度取り出して password_verify() で確認する
        $hashedPassword = $user["password"];
        if (!password_verify($userPassword, $hashedPassword)) return null;

        return $user["user_name"];
    }
}
