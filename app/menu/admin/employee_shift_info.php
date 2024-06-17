<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width">
    <title>従業員のシフト情報</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.10.0/main.min.css" rel="stylesheet" />
    <link href="../../public/css/fullCalendar_template.css" rel="stylesheet">
    <script src="../../../vendor/fullcalendar/index.global.min.js"></script>
</head>
<body>

<pageTitle>従業員のシフト情報</pageTitle>

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

$logName = "employee_shift_info";
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


$employeeId = $_GET["employeeId"];

$preferredShift = $database->getShiftPreferenceByEmployeeId($employeeId); // 希望シフト
$assignmentShift = $database->getShiftAssignmentsByEmployeeId($employeeId); // 割り当てシフト

/**
 * @var array FullCalendar ライブラリに渡す配列
 * 
 * FullCalendar ライブラリ
 * @see https://fullcalendar.io/docs 公式ドキュメント
 */
$eventData = [];

$preferenceCount = 0; // 出勤希望数
$assignmentCount = 0; // 出勤数

for ($i = 0; $i < count($preferredShift); $i++) {
    $eventId = $i;
    $shiftDate = $preferredShift[$i]["shift_date"];
    $shiftName = $preferredShift[$i]["shift_name"];

    $shiftId = $database->getShiftIdByDateAndName($shiftDate, $shiftName);
    $shiftInfo = $database->getCalendarShiftsByShiftId($shiftId);

    $startDateTime = $shiftInfo["start_date_time"];
    $endDateTime = $shiftInfo["end_date_time"];

    $eventData[] = [
        "id" => $eventId,
        "title" => $shiftName,
        "start" => $startDateTime,
        "end" => $endDateTime,
        "description" => "出勤可能"
    ];

    $preferenceCount++;
}

for ($i = 0; $i < count($assignmentShift); $i++) {
    $eventId = $i;
    $shiftDate = $assignmentShift[$i]["shift_date"];
    $shiftName = $assignmentShift[$i]["shift_name"];

    $shiftId = $database->getShiftIdByDateAndName($shiftDate, $shiftName);
    $shiftInfo = $database->getCalendarShiftsByShiftId($shiftId);

    $startDateTime = $shiftInfo["start_date_time"];
    $endDateTime = $shiftInfo["end_date_time"];

    $eventData[] = [
        "id" => $eventId,
        "title" => $shiftName,
        "start" => $startDateTime,
        "end" => $endDateTime,
        "description" => "出勤",
        "backgroundColor" => "red"
    ];

    $assignmentCount++;
}

$encodedEventData = json_encode($eventData); // JS に渡すために JSON 化

$database->closeConnection(); // データベース切断
?>

<div id="calendar"></div>

<script>var eventData = <?php echo $encodedEventData; ?>;</script>
<script src="../../public/javascript/fullcalendar/employee_shift_info.js"></script>

<p>出勤希望数 : <?php echo $preferenceCount; ?></p>
<p>出勤数 : <?php echo $assignmentCount; ?></p>

<a class="calendar-page-anchor" href="./employee_info.php">従業員情報ページに戻る</a><br/>
<a class="calendar-page-anchor" href="../../main.php">TOPページに戻る</a>

</body>
</html>