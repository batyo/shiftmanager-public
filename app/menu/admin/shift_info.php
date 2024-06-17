<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width">
    <title>シフト情報</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.10.0/main.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../../public/css/fullCalendar_template.css">
    <script src="../../../vendor/fullcalendar/index.global.min.js"></script>
    <style>
        :root {
            --fc-list-event-hover-bg-color: "none"; /** ホバー効果 none */
        }
        .highlight-event {
            background-color: #ffcc00; /* ハイライト色 */
        }
        td.fc-list-event-shiftName {
            cursor: pointer;
        }
        #label-radio {
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

<pageTitle>シフト一覧</pageTitle>

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

$logName = "shift_info";
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

$database = new Database($host, $userName, $password, $tableName);

/** @var array $shifts シフト情報 */
$shifts = $database->getCalendarShifts();

/**
 * @var array FullCalendar ライブラリに渡す配列
 * 
 * FullCalendar ライブラリ
 * @see https://fullcalendar.io/docs 公式ドキュメント
 */
$eventData = [];

for ($i = 0; $i < count($shifts); $i++) {
    $eventId = $i;
    $shiftName = $shifts[$i]["shift_name"];
    $startDateTime = $shifts[$i]["start_date_time"];
    $endDateTime = $shifts[$i]["end_date_time"];
    $availableSlots = $shifts[$i]["available_slots"];
    $assignmentCount = $shifts[$i]["current_assignment_count"];

    $eventData[] = [
        "id" => $i,
        "title" => $shiftName,
        "start" => $startDateTime,
        "end" => $endDateTime,
        "description" => [$availableSlots, $assignmentCount]
    ];
}

$_SESSION["eventData"] = $eventData;

$encodedEventData = json_encode($eventData); // JS に渡すために JSON 化

// データベース切断
$database->closeConnection();
?>

<form action="./shift_operation_branch.php" method="POST">
    <div id="calendar"></div>
    <input type="submit" name="shiftAdd" value="シフト追加">
    <input type="submit" name="shiftDelete" value="シフト削除">
    <input type="submit" name="pastShiftDelete" value="アーカイブ化">
</form>

<script>var eventData = <?php echo $encodedEventData; ?>;</script>
<script src="../../public/javascript/fullcalendar/shift_info.js"></script>

<br/>
<a class="calendar-page-anchor" href="../../main.php">TOPページに戻る</a>

</body>
</html>
