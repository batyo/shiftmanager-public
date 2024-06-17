<?php

/**
 * employee_shift_assignments テーブルの読み取り専用メソッド群
 */
trait OutputAssignments
{
    /** @var PDO データベース接続のインスタンス */
    protected $connection;
    
    /**
     * 決定しているシフトの割り当てを取得する
     *
     * @return array|null シフトの割り当て、見つからない場合は null
     */
    public function getShiftAssignments()
    {
        $sql = "SELECT id, employee_id, shift_date, shift_name FROM employee_shift_assignments";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        $shiftAssignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $shiftAssignments;
    }

    /**
     * 指定した従業員の決定しているシフトの割り当てを取得する
     *
     * @param int $employeeId 従業員ID
     * 
     * @return array|null シフトの割り当て、見つからない場合は null
     */
    public function getShiftAssignmentsByEmployeeId($employeeId)
    {
        $sql = "SELECT id, shift_date, shift_name FROM employee_shift_assignments WHERE employee_id = :employeeId";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(":employeeId", $employeeId, PDO::PARAM_INT);
        $stmt->execute();
        $shiftAssignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $shiftAssignments;
    }

    /**
     * 従業員ID、シフト日付、シフト名から割り当てシフトIDを取得する
     *
     * @param int $employeeId 従業員ID
     * @param string $shiftDate シフト日付
     * @param string $shiftName シフト名
     * 
     * @return int|null 割り当てシフトID または null
     */
    public function getAssignmentId($employeeId, $shiftDate, $shiftName)
    {
        $sql = "SELECT id FROM employee_shift_assignments WHERE employee_id = :employeeId AND shift_date = :shiftDate AND shift_name = :shiftName";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(":employeeId", $employeeId, PDO::PARAM_INT);
        $stmt->bindParam(":shiftDate", $shiftDate, PDO::PARAM_STR);
        $stmt->bindParam(":shiftName", $shiftName, PDO::PARAM_STR);
        $stmt->execute();
        $assignmentInfo = $stmt->fetch(PDO::FETCH_ASSOC);

        return $assignmentInfo["id"];
    }

    /**
     * 特定のシフトの割り当て情報を取得する
     *
     * @param int $shiftDate シフト日付
     * @param string $shiftName シフト名
     * 
     * @return array|null 割り当て情報 または null
     */
    public function getAssignmentsInfoByDateAndName($shiftDate, $shiftName)
    {
        $sql = "SELECT id, employee_id FROM employee_shift_assignments WHERE shift_date = :shiftDate AND shift_name = :shiftName";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(":shiftDate", $shiftDate, PDO::PARAM_STR);
        $stmt->bindParam(":shiftName", $shiftName, PDO::PARAM_STR);
        $stmt->execute();
        $assignmentInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $assignmentInfo;
    }
}
