<?php
require_once(dirname(__DIR__). "\\traitOutput\\preferences.php");

/**
 * employee_shift_preferences テーブルに情報を書き込むメソッド群
 */
trait InputPreferences
{
    use OutputPreferences;

    /** @var PDO データベース接続のインスタンス */
    protected $connection;

    /**
     * 希望シフトを登録する (別メソッド用)
     * 
     * 単体でこのメソッドを使用する場合は別メソッド内で例外処理を行う
     *
     * @param int $employeeId 従業員ID
     * @param string $shiftDate シフト日付
     * @param string $shiftName シフト名
     * 
     * @return void
     * 
     * @see self::shiftPreferenceRegistration()
     * @see TraitsFacade::cancellShiftAssignment()
     */
    protected function addShiftPreference($employeeId, $shiftDate, $shiftName)
    {
        if ($this->connection->inTransaction()) {

            $sql = "INSERT INTO employee_shift_preferences (employee_id, shift_date, shift_name) VALUES (:employeeId, :shiftDate, :shiftName)";
            $stmt = $this->connection->prepare($sql);

            $stmt->bindParam(":employeeId", $employeeId, PDO::PARAM_INT);
            $stmt->bindParam(":shiftDate", $shiftDate, PDO::PARAM_STR);
            $stmt->bindParam(":shiftName", $shiftName, PDO::PARAM_STR);

            $stmt->execute();
        }
    }

    /**
     * 希望シフトを登録する (public ver)
     * 
     * このメソッドは単体で使用可能
     * ただし割り当てシフトをキャンセルする際に使用してはならない
     * 割り当てシフトをキャンセルする場合は以下のメソッドを使用する
     * @see selef::cancellShiftAssignment()
     *
     * @param int $employeeId 従業員ID
     * @param string $shiftDate シフト日付
     * @param string $shiftName シフト名
     * 
     * @return bool 登録が成功した場合は true、失敗した場合は false
     */
    public function shiftPreferenceRegistration($employeeId, $shiftDate, $shiftName)
    {
        try {
            $this->connection->beginTransaction();

            $this->addShiftPreference($employeeId, $shiftDate, $shiftName);

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

    /**
     * 希望シフトを削除する (public ver)
     * 
     * このメソッドは単体で使用可能
     * ただし割り当てシフトを登録する際に使用してはならない
     * 割り当てシフトを登録する場合は以下のメソッドを使用する
     * @see TraitsFacade::shiftAssignmentRegistration()
     *
     * @param int $employeeId 従業員ID
     * @param string $shiftDate シフト日付
     * @param string $shiftName シフト名
     * 
     * @return bool キャンセルが成功した場合は true、失敗した場合は false
     */
    public function cancellShiftPreference($employeeId, $shiftDate, $shiftName)
    {
        try {
            $this->connection->beginTransaction();

            $this->deleteShiftPreference($employeeId, $shiftDate, $shiftName);

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

    /**
     * 希望シフトを削除する
     * 
     * このメソッド単体では使用しない
     *
     * @param int $employeeId 従業員ID
     * @param string $shiftDate シフトの日付
     * @param string $shiftName シフト名
     * 
     * @return void
     * 
     * @see self::cancellShiftPreference()
     * @see TraitsFacade::shiftAssignmentRegistration()
     */
    protected function deleteShiftPreference($employeeId, $shiftDate, $shiftName)
    {
        if ($this->connection->inTransaction()) {

            $sql = "DELETE FROM employee_shift_preferences WHERE employee_id = :employeeId AND shift_date = :shiftDate AND shift_name = :shiftName";
            $stmt = $this->connection->prepare($sql);

            $stmt->bindParam(":employeeId", $employeeId, PDO::PARAM_INT);
            $stmt->bindParam(":shiftDate", $shiftDate, PDO::PARAM_STR);
            $stmt->bindParam(":shiftName", $shiftName, PDO::PARAM_STR);

            $stmt->execute();
        }
    }

    /**
     * 特定の従業員の全ての希望シフトを削除する
     * 
     * このメソッドはユーザー情報を削除する際に使う
     * @see TraitFacade::deleteUserByUserId
     *
     * @param int $employeeId   従業員ID
     * @return void
     */
    protected function deleteAllShiftPreferencesOfEmlployee($employeeId)
    {
        if ( !$this->connection->inTransaction() ) {
            $this->connection->beginTransaction();
        }
        
        $sql = "DELETE FROM employee_shift_preferences WHERE employee_id = :employeeId";
        $stmt = $this->connection->prepare($sql);
        
        $stmt->bindParam(":employeeId", $employeeId, PDO::PARAM_INT);
        
        $stmt->execute();
    }

    /**
     * 特定の希望シフトを全て削除する
     *
     * @param string $shiftDate シフト日付
     * @param string $shiftName シフト名
     * @return void|false
     * 
     * @see TratsFacade::deleteCalendarShiftByShiftId()
     */
    protected function deleteSpecifiedShiftPreference($shiftDate, $shiftName)
    {
        if ($this->connection->inTransaction()) {

            $sql = "DELETE FROM employee_shift_preferences WHERE shift_date = :shiftDate AND shift_name = :shiftName";
            $stmt = $this->connection->prepare($sql);
            
            $stmt->bindParam(":shiftDate", $shiftDate, PDO::PARAM_STR);
            $stmt->bindParam(":shiftName", $shiftName, PDO::PARAM_STR);
            
            $stmt->execute();
        }
    }
}
