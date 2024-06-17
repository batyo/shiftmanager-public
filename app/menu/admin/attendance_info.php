<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width">
    <title>勤怠管理</title>
    <link rel="stylesheet" href="../../public/css/template.css">
    <link rel="stylesheet" href="../../public/css/attendance_info.css">
</head>
<body>

<pageTitle>勤怠管理</pageTitle>

<?php
require_once("../../../class/general.php");
require_once("../../../class/database/database.php");
require_once("../../../vendor/autoload.php");

$general = new General();

$sessionKeyLogin = "login";
$sessionKeyName = "user_name";
$sessionKeyAuthority = "administrator";
$backPagePath = "../../login.html";
$general->login_authentication_confirmation($sessionKeyLogin, $sessionKeyName, $sessionKeyAuthority, $backPagePath);

$logName = "matching_info";
$logPath = "../../../log/acces_log.txt";
$general->acces_log($logName, $logPath);

set_error_handler(array($general, "error_logger"));

/**
 * PHP dotenv ライブラリ
 * 環境変数読み込み
 * @see https://github.com/vlucas/phpdotenv
 */
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 3));
$dotenv->load();

$host = $_ENV["DB_HOST"];
$userName = $_ENV["DB_USER_NAME"];
$password = $_ENV["DB_PASSWORD"];
$tableName = $_ENV["DB_NAME"];

// データベース操作クラス
$database = new Database($host, $userName, $password, $tableName);

// 現在の日付時間
$tokyoTimeZone = new DateTimeZone("ASIA/Tokyo");
$dateTime = new DateTimeImmutable("now", $tokyoTimeZone);
$nowDate = $dateTime->format("Y-m-d");
$nowDateTime = $dateTime->format("Y-m-d H:i:s");

// 割り当て済みシフト
$assignmentShifts = $database->getShiftAssignments();

// 本日予定のシフト有無
$hasSchedule = false;
?>

<div class="container">

<h1>当日のシフト出勤状況</h1>

<div class="table-wrapper">

<table>

    <tr>
        <th>シフト名</th><th>従業員名</th><th>開始時間</th><th>出勤状況</th><th>出勤時間</th>
    </tr>

    <?php for ($i = 0; $i < count($assignmentShifts); $i++) : ?>
        <?php
        $shiftDate = $assignmentShifts[$i]["shift_date"];
    
        // 今日のシフトでない場合はスキップ
        if ($shiftDate != $nowDate) continue;

        $hasSchedule = true;

        // シフト名を取得
        $shiftName = $assignmentShifts[$i]["shift_name"];

        // 従業員名を取得
        $employeeId = $assignmentShifts[$i]["employee_id"]; // 従業員ID
        $employeeName = $database->getEmployeeNameByEmployeeId($employeeId);

        // シフト開始時間を取得
        $shiftId = $database->getShiftIdByDateAndName($shiftDate, $shiftName); // シフトID
        $shiftInfo = $database->getCalendarShiftsByShiftId($shiftId); // シフト情報
        $startDateTime = $shiftInfo["start_date_time"];

        // 出勤状況を取得
        $assignmentId = $assignmentShifts[$i]["id"]; // 割り当てシフトID
        $attendanceInfo = $database->getAttendanceByAssignmentId($assignmentId); // 勤怠情報
        $attendanceStatus = $attendanceInfo["attendance_status"];
        $arrivalTime = $attendanceInfo["report_arrival_time"];

        if ($arrivalTime == null) $arrivalTime = "";
        ?>
    
        <tr>
            <td><?php echo $shiftName; ?></td>
            <td><?php echo $employeeName; ?></td>
            <td><?php echo $startDateTime; ?></td>
            <td><?php echo $attendanceStatus; ?></td>
            <td><?php echo $arrivalTime; ?></td>
        </tr>

    <?php endfor; ?>

</table>

</div>

<?php $database->closeConnection(); // データベース切断 ?>

<?php if ( !$hasSchedule ) : ?>
    <h3>本日予定のシフトはありません</h3>
<?php endif; ?>

<br/>
<a href="../../main.php">TOPページに戻る</a>
</div>

</body>
</html>
