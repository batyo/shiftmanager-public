<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width">
    <title>マッチング情報</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.10.0/main.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../../public/css/fullCalendar_template.css">
    <script src="../../../vendor/fullcalendar/index.global.min.js"></script>
</head>
<body>

<pageTitle>マッチング一覧</pageTitle>

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

// マッチング情報
$assignedShift = $database->getShiftAssignments();

/**
 * @var array FullCalendar ライブラリに渡す配列
 * 
 * FullCalendar ライブラリ
 * @see https://fullcalendar.io/docs 公式ドキュメント
 */
$eventData = [];

for ($i = 0; $i < count($assignedShift); $i++) {
    $employeeId = $assignedShift[$i]["employee_id"];
    $employeeName = $database->getEmployeeNameByEmployeeId($employeeId);

    $shiftDate = $assignedShift[$i]["shift_date"];
    $shiftName = $assignedShift[$i]["shift_name"];

    $shiftId = $database->getShiftIdByDateAndName($shiftDate, $shiftName);
    $shiftInfo = $database->getCalendarShiftsByShiftId($shiftId);

    $startDateTime = $shiftInfo["start_date_time"];
    $endDateTime = $shiftInfo["end_date_time"];

    $eventData[] = [
        "id" => $i,
        "title" => $shiftName,
        "start" => $startDateTime,
        "end" => $endDateTime,
        "description" => [$employeeId, $employeeName]
    ];
}

$_SESSION["eventData"] = $eventData;

$encodedEventData = json_encode($eventData); // JS に渡すために JSON 化

$database->closeConnection(); // データベース切断
?>

<form action="./matching_operation_branch.php" method="POST">
    <div id="calendar"></div>
    <input type="submit" name="matchingDelete" value="解除">
    <input type="submit" name="matchingReset" value="リセット">
    <input type="submit" name="matchingArchive" value="アーカイブ">
</form>

<script>var eventData = <?php echo $encodedEventData; ?>;</script>
<script src="../../public/javascript/fullcalendar/main.js"></script>

<br/>
<a class="calendar-page-anchor" href="../../main.php">TOPページに戻る</a>

</body>
</html>