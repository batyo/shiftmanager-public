<?php
require_once(dirname(__DIR__)."\\traitOutput\\assignments.php");
require_once(dirname(__DIR__)."\\traitOutput\\calendar_shifts.php");

/**
 * calendar_shifts テーブルに情報を書き込むメソッド群
 */
trait InputCalendarShifts
{
    use OutputAssignments, OutputCalendarShifts;

    /** @var PDO データベース接続のインスタンス */
    protected $connection;

    /**
     * シフト情報を追加する
     * 
     * current_assignment_count カラムは 値を渡す必要はない
     * -> default 値が 0 で設定されいる
     *
     * @param string $shiftDate シフト日付 (date)
     * @param string $startDateTime 開始時間 (datetime)
     * @param string $endDateTime 終了時間 (datetime)
     * @param string $shiftName シフト名
     * @param int $availableSlots 空き枠数
     * 
     * @return bool 登録が成功した場合は true、失敗した場合は false
     */
    public function addCalendarShifts($shiftDate, $startDateTime, $endDateTime, $shiftName, $availableSlots)
    {
        try {
            $this->connection->beginTransaction();

            $sql = "INSERT INTO calendar_shifts (shift_date, start_date_time, end_date_time, shift_name, available_slots, current_assignment_count) VALUES (:shiftDate, :startDateTime, :endDateTime, :shiftName, :availableSlots, 0)";
            $stmt = $this->connection->prepare($sql);

            $stmt->bindParam(":shiftDate", $shiftDate, PDO::PARAM_STR);
            $stmt->bindParam(":startDateTime", $startDateTime, PDO::PARAM_STR);
            $stmt->bindParam(":endDateTime", $endDateTime, PDO::PARAM_STR);
            $stmt->bindParam(":shiftName", $shiftName, PDO::PARAM_STR);
            $stmt->bindParam(":availableSlots", $availableSlots, PDO::PARAM_INT);

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
     * シフトの空き枠数を 1 増加させる
     * 
     * このメソッド単体では使用しない
     *
     * @param string $shiftDate シフトの日付
     * @param string $shiftName シフト名
     * 
     * @return void
     * 
     * @see TraitsFacade::cancellShiftAssignment()
     */
    protected function IncreaseAvailableSlots($shiftDate, $shiftName)
    {
        if ($this->connection->inTransaction()) {

            $sql = "UPDATE calendar_shifts SET available_slots = available_slots + 1 WHERE shift_date = :shiftDate AND shift_name = :shiftName";
            $stmt = $this->connection->prepare($sql);

            $stmt->bindParam(":shiftDate", $shiftDate, PDO::PARAM_STR);
            $stmt->bindParam(":shiftName", $shiftName, PDO::PARAM_STR);

            $stmt->execute();
        }
    }

    /**
     * シフトの空き枠数を 1 減少させる
     * 
     * このメソッド単体では使用しない
     *
     * @param string $shiftDate シフトの日付
     * @param string $shiftName シフト名
     * 
     * @return void
     * 
     * @see TraitsFacade::shiftAssignmentRegistration()
     */
    protected function DecreaseAvailableSlots($shiftDate, $shiftName)
    {
        if ($this->connection->inTransaction()) {

            $sql = "UPDATE calendar_shifts SET available_slots = available_slots - 1 WHERE shift_date = :shiftDate AND shift_name = :shiftName";
            $stmt = $this->connection->prepare($sql);

            $stmt->bindParam(":shiftDate", $shiftDate, PDO::PARAM_STR);
            $stmt->bindParam(":shiftName", $shiftName, PDO::PARAM_STR);

            $stmt->execute();
        }
    }

    /**
     * 現在の割り当て数を 1 増加させる
     * 
     * このメソッド単体では使用しない
     *
     * @param string $shiftDate シフトの日付
     * @param string $shiftName シフト名
     * @return void
     * 
     * @see TraitsFacade::shiftAssignmentRegistration()
     */
    protected function increaseAssignmentCount($shiftDate, $shiftName)
    {
        if ($this->connection->inTransaction()) {

            $sql = "UPDATE calendar_shifts SET current_assignment_count = current_assignment_count + 1 WHERE shift_date = :shiftDate AND shift_name = :shiftName";
            $stmt = $this->connection->prepare($sql);

            $stmt->bindParam(":shiftDate", $shiftDate, PDO::PARAM_STR);
            $stmt->bindParam(":shiftName", $shiftName, PDO::PARAM_STR);

            $stmt->execute();
        }
    }

    /**
     * 現在の割り当て数を 1 減少させる
     * 
     * このメソッド単体では使用しない
     *
     * @param string $shiftDate シフトの日付
     * @param string $shiftName シフト名
     * @return void
     * 
     * @see TraitsFacade::deleteFinishedShift()
     * @see TraitsFacade::cancellShiftAssignment()
     */
    protected function decreaseAssignmentCount($shiftDate, $shiftName)
    {
        if ($this->connection->inTransaction()) {

            $sql = "UPDATE calendar_shifts SET current_assignment_count = current_assignment_count - 1 WHERE shift_date = :shiftDate AND shift_name = :shiftName";
            $stmt = $this->connection->prepare($sql);

            $stmt->bindParam(":shiftDate", $shiftDate, PDO::PARAM_STR);
            $stmt->bindParam(":shiftName", $shiftName, PDO::PARAM_STR);

            $stmt->execute();
        }
    }
}
