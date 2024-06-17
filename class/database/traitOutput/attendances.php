<?php

/**
 * employee_attendances テーブルの読み取り専用メソッド群
 */
trait OutputAttendances
{
    /** @var PDO データベース接続のインスタンス */
    protected $connection;
    
    /**
     * 割り当てシフトIDから勤怠情報を取得する
     *
     * @param int $assignmentId
     * @return array|null 勤怠情報、見つからない場合は null
     */
    public function getAttendanceByAssignmentId($assignmentId)
    {
        $sql = "SELECT id, attendance_status, report_arrival_time FROM employee_attendances WHERE assignment_id = :assignmentId";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(":assignmentId", $assignmentId, PDO::PARAM_INT);
        $stmt->execute();
        $attendanceInfo = $stmt->fetch(PDO::FETCH_ASSOC);

        return $attendanceInfo;
    }
}
