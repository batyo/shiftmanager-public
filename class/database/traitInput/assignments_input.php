<?php
require_once(dirname(__DIR__). "\\traitOutput\\assignments.php");

/**
 * シフト割り当て情報の書き込みに関するメソッド
 */
trait InputAssignments
{
    use OutputAssignments;

    /** @var PDO データベース接続のインスタンス */
    protected $connection;

    /**
     * 割り当てたシフトを登録する
     * 
     * このメソッド単体では使用しない
     *
     * @param int $employeeId 従業員ID
     * @param string $shiftDate シフトの日付
     * @param string $shiftName シフト名
     * 
     * @return void
     * 
     * @see TraitsFacade::shiftAssignmentRegistration()
     */
    protected function addShiftAssignment($employeeId, $shiftDate, $shiftName)
    {
        if ($this->connection->inTransaction()) {

            $sql = "INSERT INTO employee_shift_assignments (employee_id, shift_date, shift_name) VALUES (:employeeId, :shiftDate, :shiftName)";
            $stmt = $this->connection->prepare($sql);

            $stmt->bindParam(":employeeId", $employeeId, PDO::PARAM_INT);
            $stmt->bindParam(":shiftDate", $shiftDate, PDO::PARAM_STR);
            $stmt->bindParam(":shiftName", $shiftName, PDO::PARAM_STR);

            $stmt->execute();
        }
    }

    /**
     * 割り当てたシフトを登録キャンセルする
     * 
     * このメソッド単体では使用しない
     *
     * @param int $employeeId 従業員ID
     * @param string $shiftDate シフトの日付
     * @param string $shiftName シフト名
     * 
     * @return int|false 削除する割り当てシフトID
     * 
     * @see TraitsFacace::deleteFinishedShift()
     * @see TraitsFacace::cancellShiftAssignment()
     */
    protected function deleteShiftAssignment($employeeId, $shiftDate, $shiftName)
    {
        if ($this->connection->inTransaction()) {

            $deleteId = $this->getAssignmentId($employeeId, $shiftDate, $shiftName);

            $sql = "DELETE FROM employee_shift_assignments WHERE employee_id = :employeeId AND shift_date = :shiftDate AND shift_name = :shiftName";
            $stmt = $this->connection->prepare($sql);

            $stmt->bindParam(":employeeId", $employeeId, PDO::PARAM_INT);
            $stmt->bindParam(":shiftDate", $shiftDate, PDO::PARAM_STR);
            $stmt->bindParam(":shiftName", $shiftName, PDO::PARAM_STR);

            $stmt->execute();

            return $deleteId;
        }
    }
}
