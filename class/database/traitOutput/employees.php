<?php

/**
 * employees テーブルの読み取り専用メソッド群
 */
trait OutputEmployees
{
    /** @var PDO データベース接続のインスタンス */
    protected $connection;
    
    /**
     * 全ての従業員情報を取得する
     *
     * @return array 従業員情報
     */
    public function getEmployees()
    {
        $sql = "SELECT id, contact_info, employment_type, preferred_shift, status FROM employees";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        $employee = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // JSON形式になっている preferred_shift カラムをエンコードする
        for ($i = 0; $i < count($employee); $i++) {
            $employee[$i]["preferred_shift"] = json_decode($employee[$i]["preferred_shift"], true);
        }

        return $employee;
    }

    /**
     * 指定したステータスの値を持つ従業員を取得する
     *
     * @param string $status ステータス
     * 
     * @return array|null 従業員情報または見つからない場合はnull
     */
    public function getEmployeesOfSpecifiedStatus($status)
    {
        $sql = "SELECT id, preferred_shift FROM employees WHERE status = :status";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(":status", $status, PDO::PARAM_STR);
        $stmt->execute();
        $employee = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // JSON形式になっている preferred_shift カラムをエンコードする
        for ($i = 0; $i < count($employee); $i++) {
            $employee[$i]["preferred_shift"] = json_decode($employee[$i]["preferred_shift"], true);
        }

        /**
         * ダミーデータを配列の先頭に追加する
         * 
         * フォードファルカーソン法を利用したアルゴリズムにおいて
         * $employee のインデックスが従業員ノード番号と同じになるように調整するため
         */
        array_unshift($employee, ["dummyData" => "Index Adjustment"]);
        $employee = array_values($employee);

        return $employee;
    }

    /**
     * 従業員IDによって従業員情報を取得する
     *
     * @param int $employeeId 従業員ID
     *
     * @return array|null 従業員情報または見つからない場合はnull
     */
    public function getEmployeeById($employeeId)
    {
        $sql = "SELECT contact_info, employment_type, preferred_shift FROM employees WHERE id = :id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(":id", $employeeId, PDO::PARAM_INT);
        $stmt->execute();
        $employee = $stmt->fetch(PDO::FETCH_ASSOC);

        return $employee;
    }

    /**
     * 従業員名によって従業員IDを取得する
     * 
     * @todo Employees テーブルの name カラム削除に伴うメソッドの修正
     *                 メソッドはそのままで内容を変更する
     *                 引数を使って users テーブルから ID を取得する
     *
     * @param string $employeeName  従業員名
     * 
     * @return int|null   従業員IDまたは見つからない場合はnull
     */
    public function getEmployeeIdByName($employeeName)
    {
        $sql = "SELECT id FROM employees WHERE name = :name";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(":name", $employeeName, PDO::PARAM_STR);
        $stmt->execute();
        $employee = $stmt->fetch(PDO::FETCH_ASSOC);

        return $employee["id"];
    }
}
