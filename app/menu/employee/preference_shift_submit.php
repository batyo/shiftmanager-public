<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>シフト希望提出フォーム</title>
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

<pageTitle>シフト希望提出フォーム</pageTitle>

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

$logName = "preference_shift_submit";
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

$shiftInfo = $database->getCalendarShifts();
$shiftPreferences = $database->getShiftPreferenceByEmployeeId($employeeId); // 希望シフト
$assignmentShift = $database->getShiftAssignmentsByEmployeeId($employeeId); // 出勤予定のシフト

$eventData = []; // FullCalendar に JSON 形式で渡す配列

// eventData 作成
for ($i = 0; $i < count($shiftInfo); $i++) {
    $eventId = $i;
    $shiftName = $shiftInfo[$i]["shift_name"];
    $shiftDate = $shiftInfo[$i]["shift_date"];

    $shiftId = $database->getShiftIdByDateAndName($shiftDate, $shiftName);
    $shiftDatetime = $database->getCalendarShiftsByShiftId($shiftId);

    $startDateTime = $shiftDatetime["start_date_time"]; // シフトの開始時間
    $endDateTime = $shiftDatetime["end_date_time"]; // シフトの終了時間

    $description = "";
    $backColor = "blue";

    // 既に希望提出済みのシフト
    foreach ($shiftPreferences as $key => $shift) {
        $preferredShiftName = $shift["shift_name"]; // 希望提出済みのシフト名
        $preferredShiftDate = $shift["shift_date"]; // 希望提出済みのシフト日付

        $isMatchName = $preferredShiftName == $shiftName; // シフト名の一致
        $isMatchDate = $preferredShiftDate == $shiftDate; // シフト時間の一致

        // 既に希望提出済みの場合
        if ($isMatchName && $isMatchDate) {
            $description = "現在提出中";
            $backColor = "red";
            break;
        }
        
        // 希望シフトの詳細情報を取得
        $preferredShiftId = $database->getShiftIdByDateAndName($preferredShiftDate, $preferredShiftName);
        $preferredShiftDateTime = $database->getCalendarShiftsByShiftId($preferredShiftId);

        $preferredStartDateTime = $preferredShiftDateTime["start_date_time"]; // 希望提出済みのシフトの開始時間
        $preferredEndDateTime = $preferredShiftDateTime["end_date_time"]; // 希望提出済みのシフトの終了時間

        // 時間が重なっているかを確認
        $isTimeOverlap = $startDateTime < $preferredEndDateTime && $preferredStartDateTime < $endDateTime;

        if ($isTimeOverlap) {
            $description = "「現在提出中」または「出勤予定」のシフトと時間が重なっています";
        }
    }

    // 既に出勤予定のシフト
    foreach ($assignmentShift as $key => $shift) {
        $assignedShiftName = $shift["shift_name"]; // 希望提出済みのシフト名
        $assignedShiftDate = $shift["shift_date"]; // 希望提出済みのシフト日付

        $isAssignedName = $shift["shift_name"] == $shiftName;
        $isAssignedDate = $shift["shift_date"] == $shiftDate;

        // 既に出勤予定の場合
        if ($isAssignedName && $isAssignedDate) {
            $description = "出勤予定";
            $backColor = "green";
            break;
        }

        // 割り当てシフトの詳細情報を取得
        $assignedShiftId = $database->getShiftIdByDateAndName($assignedShiftDate, $assignedShiftName);
        $assignedShiftDateTime = $database->getCalendarShiftsByShiftId($assignedShiftId);

        $assignedStartDateTime = $assignedShiftDateTime["start_date_time"]; // 割り当て済みのシフトの開始時間
        $assignedEndDateTime = $assignedShiftDateTime["end_date_time"]; // 割り当て済みのシフトの終了時間

        // 時間が重なっているかを確認
        $isTimeOverlap = $startDateTime < $assignedEndDateTime && $assignedStartDateTime < $endDateTime;

        if ($isTimeOverlap) {
            $description = "「現在提出中」または「出勤予定」のシフトと時間が重なっています";
        }
    }

    $eventData[] = [
        "id" => $eventId,
        "title" => $shiftName,
        "start" => $startDateTime,
        "end" => $endDateTime,
        "description" => [$description, $shiftDate],
        "backgroundColor" => $backColor
    ];
}

$_SESSION["employee_id"] = $employeeId;
$_SESSION["eventData"] = $eventData; // preference_shift_registration.php でデータベース登録のために使う

$encodedEventData = json_encode($eventData); // JSON 化して JS ファイルへ渡す

// データベース切断
$database->closeConnection();
?>

<form id="calendar-shifts" action="preference_shift_registration.php" method="POST">
    <div id="calendar"></div>
    <input type="submit" value="送信">
</form>

<script>var eventData = <?php echo $encodedEventData; ?>;</script>
<script src="../../public/javascript/fullcalendar/preference_shift_submit.js"></script>
<script src="../../public/javascript/prevent_empty_submit.js"></script>

<br/>
<a class="calendar-page-anchor" href="../../main.php">TOPページに戻る</a>

</body>
</html>