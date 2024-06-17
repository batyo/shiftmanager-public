<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width">
    <title>マッチング実行</title>
    <link rel="stylesheet" href="../../public/css/template.css">
</head>
<body>

<pageTitle>マッチング実行</pageTitle>

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

$logName = "matching_manual_execution";
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

$employeeId = $_POST["selectEmployee"];
$assignmentShift = $_POST["assignment"];

$eventData = $_SESSION["eventData"];


for ($i = 0; $i < count($assignmentShift); $i++) {

    $eventId = $assignmentShift[$i];
    $key = array_search($eventId, array_column($eventData, "id"));

    $shiftDate = $eventData[$key]["description"][2];
    $shiftName = $eventData[$key]["title"];

    $isSucces = $inputDatabase->shiftAssignmentRegistration($employeeId, $shiftDate, $shiftName);

    if ( !$isSucces ) {
        echo "<p>現在メンテナンス中です。復旧までしばらくお待ちください。</p>";
        echo '<a href="../../main.php">TOPページに戻る</a>';
        exit();
    }
}

/** CSVファイル出力 */
$shiftAssignments = $database->getShiftAssignments();

if ($shiftAssignments != null) {

    $csv = "No.,ID,従業員ID,シフト日付,シフト名\n";
    for ($i = 0; $i < count($shiftAssignments); $i++) {
        $csv .= $i.",";
        $csv .= $shiftAssignments[$i]["id"].",";
        $csv .= $shiftAssignments[$i]["employee_id"].",";
        $csv .= $shiftAssignments[$i]["shift_date"].",";
        $csv .= $shiftAssignments[$i]["shift_name"]."\n";
    }

    $nowDate = date("Y_m_d__H_i_s");
    $filePath = "../../../log/app/matching_result".$nowDate.".csv";

    $general->create_csv_file($csv, $filePath);
}

// データベース切断
$database->closeConnection();
$inputDatabase->closeConnection();
?>

<h3>シフトの割り当てが完了しました</h3>

<a href="../../main.php">TOPページに戻る</a>

</body>
</html>
