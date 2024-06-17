<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width">
    <title>マッチング準備</title>
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

<pageTitle>マッチング準備</pageTitle>

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

$logName = "matching_manual_preparation";
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


$employeeStatus = "ready";
$employeeStatusReady = $database->getEmployeesOfSpecifiedStatus($employeeStatus);
?>

<form id="calendar-shifts" action="./matching_manual_execution.php" method="POST">
    <select name="selectEmployee" id="selectEmployee">
        <option value="" selected>従業員を選択して下さい</option>
        <?php for ($i = 1; $i < count($employeeStatusReady); $i++) : ?>
            <?php
            $employeeId = $employeeStatusReady[$i]["id"];
            $employeeName = $database->getEmployeeNameByEmployeeId($employeeId);
            ?>
            <option value="<?php echo $employeeId; ?>"><?php echo $employeeName ?></option>
        <?php endfor; ?>
    </select>
    <div id="calendar"></div>
    <input type="submit" value="マッチング">
</form>

<script src="../../public/javascript/fullcalendar/matching_manual_execution.js"></script>
<script src="../../public/javascript/prevent_empty_submit.js"></script>
<script src="../../public/javascript/async_select_value.js"></script>

<?php $database->closeConnection(); // データベース切断 ?>

<br/>
<a class="calendar-page-anchor" href="../../main.php">TOPページに戻る</a>

</body>
</html>
