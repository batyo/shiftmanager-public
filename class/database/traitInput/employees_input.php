<?php

/**
 * employees テーブルに情報を書き込むメソッド群
 */
trait InputEmployees
{
    /** @var PDO データベース接続のインスタンス */
    protected $connection;

    /**
     * 従業員情報をデータベースに保存する
     *
     * @param string $name 従業員の名前
     * @param string $contactInfo 従業員の連絡先情報
     * @param string $employmentType 従業員の雇用形態
     * @param array $preferredShift シフト希望の情報が格納された配列
     *                              形式: [['shift_name' => シフト番号, 'priority' => 優先順位], ...]
     * @return bool 成功した場合は true、失敗した場合は false
     */
    public function saveEmployee($name, $contactInfo, $employmentType, $preferredShift)
    {
        try {
            $this->connection->beginTransaction();

            $sql = "INSERT INTO employees (name, contact_info, employment_type, preferred_Shift) VALUES (:name, :contact_info, :employment_type, :preferred_Shift)";
            $stmt = $this->connection->prepare($sql);

            // bindParam() は参照渡しなので引数には値ではなく変数で渡す必要がある
            $preferredShift = json_encode($preferredShift);

            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':contact_info', $contactInfo, PDO::PARAM_STR);
            $stmt->bindParam(':employment_type', $employmentType, PDO::PARAM_STR);
            $stmt->bindParam(':preferred_Shift', $preferredShift, PDO::PARAM_STR);

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

    /**
     * 指定された ID の従業員情報をデータベースで更新する
     *
     * @param int       $employeeId     従業員のID
     * @param string    $name           更新後の従業員の名前
     * @param string    $contactInfo    更新後の従業員の連絡先情報
     * @param string    $employmentType 更新後の従業員の雇用形態
     * @param array     $preferredShift シフト希望の情報が含まれた配列
     * 
     * @return bool 更新が成功した場合は true、失敗した場合は false
     */
    public function updateEmployee($employeeId, $name, $contactInfo, $employmentType, $preferredShift)
    {
        try {
            $this->connection->beginTransaction();

            $sql = "UPDATE employees SET name = :name, contact_info = :contactInfo, employment_type = :employmentType, preferred_shift = :preferredShift WHERE id = :employeeId";
            $stmt = $this->connection->prepare($sql);

            $preferredShift = json_encode($preferredShift);

            $stmt->bindParam(':employeeId', $employeeId, PDO::PARAM_INT);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':contactInfo', $contactInfo, PDO::PARAM_STR);
            $stmt->bindParam(':employmentType', $employmentType, PDO::PARAM_STR);
            $stmt->bindParam(':preferredShift', $preferredShift, PDO::PARAM_STR);

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

    /**
     * 指定された 従業員ID の status カラムの値を切り替える
     *
     * @param int $employeeId 従業員ID
     * 
     * @return bool 保存が成功した場合は true、失敗した場合は false
     */
    public function toggleStatusValue($employeeId)
    {
        try {
            $this->connection->beginTransaction();

            // status の値が、 "standby" の場合は "ready" に変更し "ready" の場合は "standby" に変更する。
            $sql = "UPDATE employees SET status = CASE WHEN status = 'standby' THEN 'ready' WHEN status = 'ready' THEN 'standby' END WHERE id = :employeeId";
            $stmt = $this->connection->prepare($sql);

            $stmt->bindParam(":employeeId", $employeeId, PDO::PARAM_INT);

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

    /**
     * 指定された 従業員ID の preferred_shift カラムを更新する
     *
     * @param int   $employeeId     従業員ID
     * @param array $preferredShift 希望シフト
     * 
     * @return bool 保存が成功した場合は true、失敗した場合は false
     */
    public function updataPreferredShiftByEmployeeId($employeeId, $preferredShift)
    {
        try {
            $this->connection->beginTransaction();

            $sql = "UPDATE employees SET preferred_shift = :preferredShift WHERE id = :employeeId";
            $stmt = $this->connection->prepare($sql);

            $preferredShift = json_encode($preferredShift);

            $stmt->bindParam(':employeeId', $employeeId, PDO::PARAM_INT);
            $stmt->bindParam(':preferredShift', $preferredShift, PDO::PARAM_STR);

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

    /**
     * 指定された ID の従業員情報をデータベースで削除する
     * @see TraitFacade::deleteUserByUserId()
     *
     * @param int $employeeId 従業員ID
     * @return bool 削除が成功した場合は true、失敗した場合は false
     */
    protected function deleteEmployeeById($employeeId)
    {
        if ( !$this->connection->inTransaction() ) {
            $this->connection->beginTransaction();
        }

        $sql = "DELETE FROM employees WHERE id = :employeeId";
        $stmt = $this->connection->prepare($sql);

        $stmt->bindParam(':employeeId', $employeeId);

        $stmt->execute();
    }
}
