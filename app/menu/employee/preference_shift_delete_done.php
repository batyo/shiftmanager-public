<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>希望シフト変更</title>
    <link rel="stylesheet" href="../../public/css/template.css">
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

$logName = "preference_shift_delete_done";
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

$employeeId = $_SESSION["employee_id"]; // シフトを提出した従業員ID
$shifts = $_SESSION["eventData"]; // 希望シフトデータ

$preferenceShifts = $_POST["preference"]; // 提出された希望シフトのイベントID

// 希望シフト登録
for ($i = 0; $i < count($preferenceShifts); $i++) {
    $eventId = $preferenceShifts[$i];

    $key = array_search($eventId, array_column($shifts, "id"));

    $shiftDate = $shifts[$key]["description"][1];
    $shiftName = $shifts[$key]["title"];

    $isSucces = $inputDatabase->cancellShiftPreference($employeeId, $shiftDate, $shiftName);
    
    if (!$isSucces) {
        echo "<p>現在メンテナンス中です。復旧までしばらくお待ちください。</p>";
        echo '<a href="../../main.php">TOPページに戻る</a>';
        exit();
    }
}

// データベース切断
$database->closeConnection();
$inputDatabase->closeConnection();
?>

<h3>希望シフトの変更が完了しました</h3>

<a href="../../main.php">TOPページに戻る</a>

</body>
</html>