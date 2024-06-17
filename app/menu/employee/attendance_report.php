<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width">
    <title>勤怠報告</title>
    <link rel="stylesheet" href="../../public/css/template.css">
    <link rel="stylesheet" href="../../public/css/attendance_report.css">
</head>
<body>

<pageTitle>勤怠報告</pageTitle>

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
$inputDatabase = $database->getInputDatabaseObject();

$userId = $_SESSION["user_id"];

// ユーザーID取得
$userInfo = $database->getUserByUserId($userId);
$employeeId = $userInfo["employee_id"];

// 出勤予定のシフト情報
$assignedShift = $database->getShiftAssignmentsByEmployeeId($employeeId);

// 現在の日付時間
$tokyoTimeZone = new DateTimeZone("ASIA/Tokyo");
$dateTime = new DateTimeImmutable("now", $tokyoTimeZone);
$nowDate = $dateTime->format("Y-m-d");

// 本日の出勤シフトの有無
$isWorkDay = false;
?>

<div class="container">

<h1>本日勤務予定のシフト</h1>

<?php if ($_SERVER["REQUEST_METHOD"] == "GET") : ?>

    <?php for ($i = 0; $i < count($assignedShift); $i++) : ?>

        <?php
        $assignmentId = $assignedShift[$i]["id"]; // アサインメントID
        $shiftDate = $assignedShift[$i]["shift_date"]; // シフトの日付 

        // 割り当てシフトの出勤情報を取得
        $attendanceInfo = $database->getAttendanceByAssignmentId($assignmentId);
        $attendanceStatus = $attendanceInfo["attendance_status"];

        // employee_attendances の attendance_status が confirmed の場合
        // <input>タグに属性を追加する
        $inputAttribute = "";
        $inputClass = "";
        if ($attendanceStatus == "confirmed") {
            $inputAttribute = "disabled"; // disabled 属性
            $inputClass = 'class="input-disabled"'; // クラス属性
        }
        ?>

        <?php // 割り当てシフトの日付が今日である場合 ?>
        <?php if ($shiftDate == $nowDate) : ?>

            <?php
            $isWorkDay = true;

            $shiftName = $assignedShift[$i]["shift_name"];
            $shiftId = $database->getShiftIdByDateAndName($shiftDate, $shiftName);
            $shiftInfo = $database->getCalendarShiftsByShiftId($shiftId);

            // シフトの開始日付時間で DateTimeImmutable クラスを初期化
            $startDateTime = $shiftInfo["start_date_time"];
            $shiftDateTime = new DateTimeImmutable($startDateTime);

            // 現在時間とシフト開始時間の差を取得
            $interval = $dateTime->diff($shiftDateTime);

            $lessOneHour = $interval->h == 0;
            $less30minute = $interval->i < 30;
            ?>

            <p><span style="font-size:x-large; font-weight:bold;"><?php echo $shiftName; ?></span> 開始時間:<?php echo $startDateTime; ?></p>

            <?php // シフト開始時間まで30分以内の場合 ?>
            <?php if ($lessOneHour && $less30minute) : ?>

                <form action="<?php echo $_SERVER["SCRIPT_NAME"]; ?>" method="POST">
                    <input type="hidden" name="assignmentId" value="<?php echo $assignmentId; ?>">
                    <input id="attendaceReport" <?php echo $inputClass; ?> type="submit" value="出勤報告" <?php echo $inputAttribute ?>>
                </form>

            <?php endif; ?>

        <?php endif; ?>

    <?php endfor; ?>

    <?php if ($isWorkDay == false) : ?>
        <h3>本日勤務予定のシフトはありません</h3>
    <?php endif ?>

<?php endif; ?>


<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $assignmentId = $_POST["assignmentId"];

    // 現在の日付時間
    $tokyoTimeZone = new DateTimeZone("ASIA/Tokyo");
    $dateTime = new DateTimeImmutable("now", $tokyoTimeZone);
    $arrivalTime = $dateTime->format("Y-m-d H:i:s");

    // 出勤データの登録
    $isSucces = $inputDatabase->completedToAttendanceConfirmation($assignmentId, $arrivalTime);

    if ( !$isSucces ) {
        echo "<h3>現在メンテナンス中です。以下の連絡先までご連絡下さい。</h3>";
        echo "<p>連絡先: ooo-xxxx</p>";
        echo '<a href="../../main.php">TOPページに戻る</a>';
        exit();
    }

    echo "<h3>送信しました</h3>";
}

// データベース切断
$database->closeConnection();
$inputDatabase->closeConnection();
?>

<br/>
<a href="../../main.php">TOPページに戻る</a>

</div>

</body>
</html>
