<?php

/**
 * employee_attendances テーブルに情報を書き込むメソッド群
 */
trait InputAttendances
{
    /** @var PDO データベース接続のインスタンス */
    protected $connection;

    /**
     * 勤怠情報を追加する
     * 
     * このメソッドは単体では使わない
     *
     * @param int $assignmentId 割り当てシフトID
     * 
     * @return true|false 成功した場合 true、失敗した場合 false
     * 
     * @see InputAssignments::shiftAssignmentRegistration()
     */
    protected function addEmployeeAttendance($assignmentId)
    {
        if ($this->connection->inTransaction()) {

            $sql = "INSERT INTO employee_attendances (assignment_id) VALUES (:assignmentId)";
            $stmt = $this->connection->prepare($sql);

            $stmt->bindParam(":assignmentId", $assignmentId, PDO::PARAM_INT);

            $stmt->execute();
        }
    }

    /**
     * 勤怠情報を削除する
     * 
     * このメソッドは単体では使わない
     *
     * @param int $assignmentId 割り当てシフトID
     * 
     * @return void
     * 
     * @see InputCalendarShifts::deleteFinishedShift()
     * @see InputAssignments::cancellShiftAssignment()
     */
    protected function deleteEmployeeAttendance($assignmentId)
    {
        if ($this->connection->inTransaction()) {

            $sql = "DELETE FROM employee_attendances WHERE assignment_id = :assignmentId";
            $stmt = $this->connection->prepare($sql);

            $stmt->bindParam(":assignmentId", $assignmentId, PDO::PARAM_INT);

            $stmt->execute();
        }
    }

    /**
     * 出勤した時間を登録する
     * 
     * このメソッド単体では使わない
     *
     * @param int $assignmentId 割り当てシフトID
     * @param string $arrivalTime 到着時間
     * 
     * @return void
     * 
     * @see InputAttendances::completedToAttendanceConfirmation()
     */
    protected function registerArrivalTime($assignmentId, $arrivalTime)
    {
        if ($this->connection->inTransaction()) {

            $sql = "UPDATE employee_attendances SET report_arrival_time = :arrivalTime WHERE assignment_id = :assignmentId";
            $stmt = $this->connection->prepare($sql);

            $stmt->bindParam(":assignmentId", $assignmentId, PDO::PARAM_INT);
            $stmt->bindParam(":arrivalTime", $arrivalTime, PDO::PARAM_STR);

            $stmt->execute();
        }
    }

    /**
     * 出勤確認を完了する
     *
     * @param int $assignmentId 割り当てシフトID
     * @param string $arrivalTime 到着時間
     * 
     * @return true|false 登録が成功した場合 true、 失敗した場合は false を返す
     */
    public function completedToAttendanceConfirmation($assignmentId, $arrivalTime)
    {
        try {
            $this->connection->beginTransaction();

            $sql = "UPDATE employee_attendances SET attendance_status = 'confirmed' WHERE assignment_id = :assignmentId";
            $stmt = $this->connection->prepare($sql);

            $stmt->bindParam(":assignmentId", $assignmentId, PDO::PARAM_INT);

            $stmt->execute();

            // 到着時間登録
            $this->registerArrivalTime($assignmentId, $arrivalTime);

            $this->connection->commit();

            return true;

        } catch (PDOException $e) {
            $this->connection->rollback();

            $logMassage = "Database Error in". __FUNCTION__. " :". $e->getMessage();
            $logFile = "../../../log/error_log.txt";
            error_log($logMassage, 3, $logFile);

            echo $logMassage;
            return false;
        }
    }
}
