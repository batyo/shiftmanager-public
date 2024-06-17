<?php

/**
 * archive_calendar_shifts テーブルの読み取り専用メソッド群
 */
trait OutputArchives
{
    /** @var PDO データベース接続のインスタンス */
    protected $connection;
    
    /**
     * 過去の割り当て情報を取得する
     *
     * @return array アーカイブ情報
     */
    public function getArchiveCalendarShifts($from, $until)
    {
        $fromDateTimeObject = new DateTimeImmutable($from);
        $fromDate = $fromDateTimeObject->format("Y-m-d");

        $untilDateTimeObject = new DateTimeImmutable($until);
        $untilDate = $untilDateTimeObject->format("Y-m-d");

        $sql = "SELECT id, employee_id, shift_date, shift_name FROM archive_calendar_shifts WHERE shift_date < :fromDate AND shift_date > :untilDate";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(":fromDate", $fromDate, PDO::PARAM_STR);
        $stmt->bindParam(":untilDate", $untilDate, PDO::PARAM_STR);
        $stmt->execute();
        $archives = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $archives;
    }
}
