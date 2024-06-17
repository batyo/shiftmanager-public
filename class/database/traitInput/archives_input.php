<?php

/**
 * archive_calendar_shifts テーブルへ情報を書き込むメソッド群
 */
trait InputArchives
{
    /** @var PDO データベース接続のインスタンス */
    protected $connection;

    /**
     * シフトのアーカイブデータを登録する
     *
     * @param int $employeeId 従業員ID
     * @param string $shiftDate シフト日付
     * @param string $shiftName シフト名
     * @param bool $isLoop ループ中であるか否か
     * 
     * @return true|false|void 成功した場合 true、 失敗した場合 false、または void
     * 
     * @see CalendarShifts::deleteFinishedShift()
     */
    protected function addArchiveCalendarShifts($employeeId, $shiftDate, $shiftName, $isLoop = false)
    {
        try {
            $this->connection->beginTransaction();

            $sql = "INSERT INTO archive_calendar_shifts (employee_id, shift_date, shift_name) VALUES (:employeeId, :shiftDate, :shiftName)";
            $stmt = $this->connection->prepare($sql);

            $stmt->bindParam(":employeeId", $employeeId, PDO::PARAM_INT);
            $stmt->bindParam(":shiftDate", $shiftDate, PDO::PARAM_STR);
            $stmt->bindParam(":shiftName", $shiftName, PDO::PARAM_STR);

            $stmt->execute();

            $this->connection->commit();

            // ループ中でない場合
            if ( !$isLoop ) return true;

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
