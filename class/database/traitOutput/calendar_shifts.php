<?php

/**
 * calendar_shifts テーブルの読み取り専用メソッド群
 */
trait OutputCalendarShifts
{
    /** @var PDO データベース接続のインスタンス */
    protected $connection;
    
    /**
     * シフト情報を取得する
     *
     * @return array シフト情報
     */
    public function getCalendarShifts()
    {
        $sql = "SELECT id, shift_date, start_date_time, end_date_time, shift_name, available_slots, current_assignment_count FROM calendar_shifts";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        $shifts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $shifts;
    }

    /**
     * シフトIDからシフト情報を取得する
     * 
     * シフト名とシフト日付は取得しない
     * self::getAssignmentCountByShiftId() と統合してもよいか
     *
     * @param int $shiftId シフトID
     * 
     * @return array|null シフト情報 または見つからない場合は null
     */
    public function getCalendarShiftsByShiftId($shiftId)
    {
        $sql = "SELECT start_date_time, end_date_time, available_slots, current_assignment_count FROM calendar_shifts WHERE id = :shiftId";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(":shiftId", $shiftId, PDO::PARAM_INT);
        $stmt->execute();
        $shiftInfo = $stmt->fetch(PDO::FETCH_ASSOC);

        return $shiftInfo;
    }

    /**
     * 従業員IDからシフトの現在の従業員に割り当てられた数を取得する
     *
     * @param int $shiftId 従業員ID
     * 
     * @return int|null 現在の割り当て数 または見つからない場合は null
     */
    public function getAssignmentCountByShiftId($shiftId)
    {
        $sql = "SELECT current_assignment_count FROM calendar_shifts WHERE id = :shiftId";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(":shiftId", $shiftId, PDO::PARAM_INT);
        $stmt->execute();
        $assignmentCount = $stmt->fetch(PDO::FETCH_ASSOC);

        return $assignmentCount["current_assignment_count"];
    }

    /**
     * 日付・シフト名からシフトIDを取得する
     *
     * @param string $shiftDate シフトの日付
     * @param string $shiftName シフト名
     * 
     * @return int|null シフトID または見つからない場合は null
     */
    public function getShiftIdByDateAndName($shiftDate, $shiftName)
    {
        $sql = "SELECT id FROM calendar_shifts WHERE shift_date = :shiftDate AND shift_name = :shiftName";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(":shiftDate", $shiftDate, PDO::PARAM_STR);
        $stmt->bindParam(":shiftName", $shiftName, PDO::PARAM_STR);
        $stmt->execute();
        $shiftId = $stmt->fetch(PDO::FETCH_ASSOC);

        if ( !$shiftId ) return null;

        return $shiftId["id"];
    }

    /**
     * 過去のシフトデータを取得する
     *
     * @return array シフトデータ
     * 
     * @see InputDatabase::deleteFinishedShift()
     */
    public function getPastRecordOfCalendarShifts()
    {
        // 現在の日付
        $nowDate = date("Y-m-d");
        $nowDateObject = new DateTimeImmutable($nowDate);

        // 昨日の日付
        $pastDateObject = $nowDateObject->modify("-1 day");

        // 日付を返す
        $pastDate = $pastDateObject->format("Y-m-d");

        $sql = "SELECT id, shift_date, shift_name FROM calendar_shifts WHERE shift_date <= :pastDate";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(":pastDate", $pastDate, PDO::PARAM_STR);
        $stmt->execute();
        $pastRecord = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $pastRecord;
    }
}
