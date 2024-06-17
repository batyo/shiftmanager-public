<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>希望シフト変更</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.10.0/main.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../../public/css/fullCalendar_template.css">
    <script src="../../../vendor/fullcalendar/index.global.min.js"></script>
    <style>
        .highlight-event {
            background-color: #ffcc00; /* ハイライト色 */
        }
        td.fc-list-event-shiftName {
            cursor: pointer;
        }
        #label-checkbox {
            cursor: pointer;
            width: 100%;
            height: 100%;
        }
        /** ラベルの範囲を div 全体まで広げるためのスタイル */
        .div-label-wrapper {
            display: flex;
            width: 100%;
            height: 100%;
        }
    </style>
</head>
<body>

<pageTitle>希望シフト変更</pageTitle>

<?php
require_once("../../../class/general.php");
require_once("../../../class/database/database.php");
require_once("../../../vendor/autoload.php");

$general = new General();

$sessionKeyLogin = "login";
$sessionKeyName = "user_name";
$sessionKeyAuthority = "employee";
$backPagePath = "../../login.html";
$general->login_authentication_confirmation($sessionKeyLogin, $sessionKeyName, $sessionKeyAuthority, $backPagePath);

$logName = "preference_shift_delete";
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

$userId = $_SESSION["user_id"];

$employeeInfo = $database->getUserByUserId($userId);
$employeeId = $employeeInfo["employee_id"];

$shiftPreferences = $database->getShiftPreferenceByEmployeeId($employeeId); // 希望シフト

$eventData = []; // FullCalendar に JSON 形式で渡す配列

// eventData 作成
for ($i = 0; $i < count($shiftPreferences); $i++) {
    $eventId = $i;
    $shiftName = $shiftPreferences[$i]["shift_name"];
    $shiftDate = $shiftPreferences[$i]["shift_date"];

    $shiftId = $database->getShiftIdByDateAndName($shiftDate, $shiftName);
    $shiftInfo = $database->getCalendarShiftsByShiftId($shiftId);

    $startDateTime = $shiftInfo["start_date_time"];
    $endDateTime = $shiftInfo["end_date_time"];

    $somethingData = "";

    $eventData[] = [
        "id" => $eventId,
        "title" => $shiftName,
        "start" => $startDateTime,
        "end" => $endDateTime,
        "description" => [$somethingData, $shiftDate] // 希望シフトを削除する際に必要になる
    ];
}

$_SESSION["employee_id"] = $employeeId;
$_SESSION["eventData"] = $eventData; // preference_shift_delete.php でデータベース登録のために使う

$encodedEventData = json_encode($eventData); // JSON 化して JS ファイルへ渡す

// データベース切断
$database->closeConnection();
?>

<form id="calendar-shifts" action="preference_shift_delete_done.php" method="POST">
    <div id="calendar"></div>
    <input type="submit" value="送信">
</form>

<script>var eventData = <?php echo $encodedEventData; ?>;</script>
<script src="../../public/javascript/fullcalendar/preference_shift_delete.js"></script>
<script src="../../public/javascript/prevent_empty_submit.js"></script>

<br/>
<a class="calendar-page-anchor" href="../../main.php">TOPページに戻る</a>

</body>
</html>