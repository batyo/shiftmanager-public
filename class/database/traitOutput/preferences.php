<?php

/**
 * employee_shift_preferences テーブルの読み取り専用メソッド群
 */
trait OutputPreferences
{
    /** @var PDO データベース接続のインスタンス */
    protected $connection;
    
    /**
     * 希望シフトの提出が無いかどうかを確認する
     *
     * @return true|false 提出が無い場合は true ある場合は false
     */
    public function isEmptyShiftPreference()
    {
        $sql = "SELECT COUNT(*) FROM employee_shift_preferences";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        $shiftPreferences = $stmt->fetchColumn();
        
        if ($shiftPreferences === 0) return true;
        return false;
    }

    /**
     * 従業員IDから出勤可能シフトを取得する
     *
     * @param int $employeeId 従業員ID
     * @return array|null 出勤可能シフトまたは見つからない場合はnull
     */
    public function getShiftPreferenceByEmployeeId($employeeId)
    {
        $sql = "SELECT shift_date, shift_name FROM employee_shift_preferences WHERE employee_id = :employeeId";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(":employeeId", $employeeId, PDO::PARAM_INT);
        $stmt->execute();
        $shiftPreferences = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $shiftPreferences;
    }

    /**
     * 従業員ごとに比較して集計した同じ希望シフトの数を取得する
     *
     * @return array 従業員ごとに比較した集計結果
     */
    public function getSamePrefereceCount()
    {
        $sql = "SELECT
                    sp1.employee_id,
                    sp2.employee_id,
                    COUNT(*) AS same_shift_count
                FROM
                    employee_shift_preferences sp1
                    JOIN employee_shift_preferences sp2
                        ON sp1.shift_date = sp2.shift_date
                        AND sp1.shift_name = sp2.shift_name
                        AND sp1.employee_id < sp2.employee_id
                GROUP BY
                    sp1.employee_id,
                    sp2.employee_id;
        ";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        $sameShiftPreferences = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $sameShiftPreferences;
    }
}
