<?php
require_once(dirname(__DIR__). "\\general.php");

$general = new General();
spl_autoload_register($general->class_autoload(__DIR__. "\\traitInput"));

/**
 * traitInput ディレクトリのトレイトメソッドを管理するクラス
 */
class TraitsFacade
{
    use InputArchives, InputAssignments, InputAttendances, InputCalendarShifts, InputEmployees, InputPreferences, InputUsers;

    /**
     * コンストラクター
     *
     * @param PDO $pdo PDOオブジェクト
     */
    public function __construct($pdo)
    {
        $this->connection = $pdo;
    }

    /**
     * LAST_INSERT_ID() を実行する
     *
     * @return int
     */
    public function getLastInsertId()
    {
        $sql = "SELECT LAST_INSERT_ID()";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        $id = $stmt->fetch(PDO::FETCH_ASSOC);

        return $id["LAST_INSERT_ID()"];
    }

    /**
     * シフト割り当てをキャンセルする
     * 
     * @param int $employeeId 従業員ID
     * @param string $shiftDate シフトの日付
     * @param string $shiftName シフト名
     * @return void
     * 
     * @see InputAssignments::deleteShiftAssignment()
     * @see InputAttendances::deleteEmployeeAttendance()
     * @see InputPreferences::addShiftPreference()
     * @see InputCalendarShifts::IncreaseAvailableSlots()
     * @see InputCalendarShifts::decreaseAssignmentCount()
     */
    public function cancellShiftAssignment($employeeId, $shiftDate, $shiftName)
    {
        try {
            $this->connection->beginTransaction();

            $deleteId = $this->deleteShiftAssignment($employeeId, $shiftDate, $shiftName); // シフト割り当ての取り消し
            $this->deleteEmployeeAttendance($deleteId, $parentMethodName); // 勤怠情報の削除
            $this->addShiftPreference($employeeId, $shiftDate, $shiftName, $scopeMethodName); // シフト希望の復帰
            $this->IncreaseAvailableSlots($shiftDate, $shiftName, $scopeMethodName); // シフトの残り枠数を増やす(戻す)
            $this->decreaseAssignmentCount($shiftDate, $shiftName, $scopeMethodName); // シフトの現在の割り当て数を減らす

            $this->connection->commit();

            return true;

        } catch (PDOException $e) {
            $this->connection->rollback();

            $logMassage = "Database Error in". __FUNCTION__. " :". $e->getMessage();
            $logFile = "../../log/error_log.txt";
            error_log($logMassage, 3, $logFile);

            echo $logMassage;
            return false;
        }
    }

    /**
     * シフト割り当ての登録 (マッチング完了)
     * 
     * シフトを割り当てると同時に同シフトを希望シフトから削除する
     * 
     * @param int   $employeeId 従業員ID
     * @param string $shiftDate シフトの日付
     * @param string $shiftName シフト名
     * @return bool 登録が完了すれば true (失敗した場合は false)
     * 
     * @see InputAssignments::addShiftAssignment()
     * @see InputAttendances::addEmployeeAttendance()
     * @see InputPreferences::deleteShiftPreference()
     * @see InputCalendarShifts::DecreaseAvailableSlots()
     * @see InputCalendarShifts::increaseAssignmentCount()
     */
    public function shiftAssignmentRegistration($employeeId, $shiftDate, $shiftName)
    {
        try {
            $this->connection->beginTransaction();

            $this->addShiftAssignment($employeeId, $shiftDate, $shiftName); // シフト割り当ての登録
            
            $assignmentId = $this->getLastInsertId(); // LAST_INSERT_ID()
            $this->addEmployeeAttendance($assignmentId); // 勤怠管理データの登録
            
            $this->deleteShiftPreference($employeeId, $shiftDate, $shiftName); // シフト希望の削除
            $this->DecreaseAvailableSlots($shiftDate, $shiftName); // シフトの残り枠数を減らす
            $this->increaseAssignmentCount($shiftDate, $shiftName); // シフトの現在の割り当て数を増やす

            $this->connection->commit();

            return true;

        } catch (PDOException $e) {
            $this->connection->rollback();

            $logMassage = "Database Error in". __FUNCTION__. " :". $e->getMessage();
            $logFile = "../../log/error_log.txt";
            error_log($logMassage, 3, $logFile);

            echo $logMassage;
            return false;
        }
    }

    /**
     * シフトIDを指定してシフト情報を削除する
     * 削除しようとしているシフトに従業員が割り当てられている場合は削除を中止する
     *
     * @param int $shiftId シフトID
     * @param string $shiftDate シフト日付
     * @param string $shiftName シフト名
     * @param bool $isLoop ループ中であるか否か
     * @return true|false|void 削除が成功した場合は true、失敗した場合は false、または void
     * 
     * @see OutputCalendarShifts::getAssignmentCountByShiftId()
     * @see InputPreferences::deleteSpecifiedShiftPreference()
     */
    public function deleteCalendarShiftByShiftId($shiftId, $shiftDate, $shiftName, $isLoop = false)
    {
        try {
            $this->connection->beginTransaction();

            $assignmentCount = $this->getAssignmentCountByShiftId($shiftId);

            // 現在シフトの割り当てが行われている場合は削除しない
            if ($assignmentCount > 0) return false;

            $sql = "DELETE FROM calendar_shifts WHERE id = :shiftId";
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':shiftId', $shiftId, PDO::PARAM_INT);
            $stmt->execute();

            // 希望シフトを削除
            $this->deleteSpecifiedShiftPreference($shiftDate, $shiftName);

            $this->connection->commit();

            // ループ中でない場合
            if ( !$isLoop ) return true;

        } catch (PDOException $e) {
            $this->connection->rollBack();

            $logMassage = "Database Error in". __FUNCTION__. " :". $e->getMessage();
            $logFile = "../../log/error_log.txt";
            error_log($logMassage, 3, $logFile);

            echo $logMassage;
            return false;
        }
    }

    /**
     * 終了したシフトを削除しアーカイブに登録する
     * 
     * @return true|false|null 成功した場合 true 失敗した場合 false 該当データが無い場合は null
     * 
     * @see OutputCalendarShifts::getPastRecordOfCalendarShifts()
     * @see OutputAssignments::getAssignmentsInfoByDateAndName()
     * @see InputArchives::addArchiveCalendarShifts()
     * @see InputAssignments::deleteShiftAssignment()
     * @see InputArchives::deleteEmployeeAttendance()
     * @see InputCalendarShifts::decreaseAssignmentCount()
     */
    public function deleteFinishedShift()
    {
        /** @var array 過去のシフト情報 (int) id, (str) shift_date, (str) shift_name */
        $pastRecord = $this->getPastRecordOfCalendarShifts();
        
        // 該当データが無い場合
        if (empty($pastRecord)) return null;

        for ($i = 0; $i < count($pastRecord); $i++) {

            $shiftDate = $pastRecord[$i]["shift_date"]; // シフト日付
            $shiftName = $pastRecord[$i]["shift_name"]; // シフト名
            
            /** @var array<int>|null 割り当て情報 id, employee_id */
            $assignmentInfo = $this->getAssignmentsInfoByDateAndName($shiftDate, $shiftName);
            
            // 割り当ての削除とシフトのアーカイブ化
            for ($j = 0; $j < count($assignmentInfo); $j++) {
                
                $assignmentId = $assignmentInfo[$j]["id"]; // 割り当てシフトID
                $employeeId = $assignmentInfo[$j]["employee_id"]; // 従業員ID

                // アーカイブ情報に登録
                $this->addArchiveCalendarShifts($employeeId, $shiftDate, $shiftName, true);
                
                try {
                    $this->connection->beginTransaction();

                    $deleteId = $this->deleteShiftAssignment($employeeId, $shiftDate, $shiftName); // 割り当ての削除
                    $this->deleteEmployeeAttendance($deleteId); // 勤怠情報の削除
                    $this->decreaseAssignmentCount($shiftDate, $shiftName); // シフトの現在の割り当て数を減らす

                    $this->connection->commit();

                } catch (PDOException $e) {
                    $this->connection->rollBack();

                    $logMassage = "Database Error in". __FUNCTION__. " :". $e->getMessage();
                    $logFile = "../../log/error_log.txt";
                    error_log($logMassage, 3, $logFile);

                    echo $logMassage;
                    return false;
                }

            }

            // シフト情報を削除
            $deleteRecordId = $pastRecord[$i]["id"];
            $this->deleteCalendarShiftByShiftId($deleteRecordId, $shiftDate, $shiftName, true);
        }

        return true;
    }

    /**
     * 指定された ID のユーザー情報及び従業員情報をデータベースで削除する
     *
     * @param int $userId ユーザーID
     * @return bool 削除が成功した場合は true、失敗した場合は false
     * 
     * @see OutputUsers::getUserByUserId()
     * @see InputEmployees::deleteEmployeeById()
     */
    public function deleteUserByUserId($userId)
    {
        try {
            $this->connection->beginTransaction();

            // ユーザー権限取得
            $authority = "";
            $userInfo = $this->getUserByUserId($userId);
            if (isset($userInfo["authority"])) {
                $authority = $userInfo["authority"];
            }

            $sql = "DELETE FROM users WHERE user_id = :userId";
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':userId', $userId);
            $stmt->execute();

            // 従業員情報の削除
            if ($authority == "employee") {
                // 従業員情報の削除
                $employeeId = $userInfo["employee_id"];
                $this->deleteEmployeeById($employeeId);

                // 希望シフトの削除
                $preferenceShifts = $this->getShiftPreferenceByEmployeeId($employeeId); // 希望シフト
                if ( !empty($preferenceShifts) ) {
                    $this->deleteAllShiftPreferencesOfEmlployee($employeeId);
                }
            }

            $this->connection->commit();

            return true;

        } catch (PDOException $e) {
            $this->connection->rollBack();

            $logMassage = "Database Error in". __FUNCTION__. " :". $e->getMessage();
            $logFile = "../../log/error_log.txt";
            error_log($logMassage, 3, $logFile);

            echo $logMassage;
            return false;
        }
    }
}
