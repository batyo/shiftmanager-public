<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width">
    <title>マッチング削除</title>
    <link rel="stylesheet" href="../../public/css/template.css">
    <link rel="stylesheet" href="../../public/css/template-container-center.css">
</head>
<body>

<pageTitle>マッチング削除</pageTitle>

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

$logName = "shift_delete";
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
$inputDatabase = $database->getInputDatabaseObject();


echo '<div class="container">';

// matching_operarion_branch.php から URL パラメータを 取得
if (isset($_GET["eventId"])) {

    $eventId = $_GET["eventId"];
    $eventData = $_SESSION["eventData"];

    $key = array_search($eventId, array_column($eventData, "id"));

    $startDateTime = $eventData[$key]["start"];
    $endDateTime = $eventData[$key]["end"];
    $shiftName = $eventData[$key]["title"];
    $employeeId = $eventData[$key]["description"][0];
    $employeeName = $eventData[$key]["description"][1];

    echo "<h3>以下の割り当てをキャンセルしますか？</h3>";
    echo "<p>シフト名 : $shiftName</p>";
    echo "<p>開始時間 : $startDateTime</p>";
    echo "<p>終了時間 : $endDateTime</p>";
    echo "<p>従業員名 : $employeeName</p>";

    echo '<form action="'.$_SERVER["SCRIPT_NAME"].'" method="POST">';
    echo '<input type="hidden" name="startDateTime" value="'.$startDateTime.'">';
    echo '<input type="hidden" name="shiftName" value="'.$shiftName.'">';
    echo '<input type="hidden" name="employeeId" value="'.$employeeId.'">';
    echo '<input type="submit" name="deleteDone" value="OK">';
    echo '</form>';
}

// シフト情報の削除
if (isset($_POST["deleteDone"])) {

    $startDateTime = $_POST["startDateTime"];
    $shiftName = $_POST["shiftName"];
    $employeeId = $_POST["employeeId"];

    list($shiftDate, $shiftTime) = explode(" ", $startDateTime);

    $shiftId = $database->getShiftIdByDateAndName($shiftDate, $shiftName);

    if (is_null($shiftId)) {
        echo "<p>シフト情報が見つかりません</p>";
        echo '<a href="./shift_info.php">戻る</a>';
        exit();
    }

    $isSucces = $inputDatabase->cancellShiftAssignment($employeeId, $shiftDate, $shiftName);

    if ($isSucces) echo "<h3>シフト割り当てのキャンセルが完了しました</h3>";
    if ( !$isSucces ) echo "<h3>現在メンテナンス中です。復旧までしばらくお待ちください。</h3>";
}

$database->closeConnection(); // データベース切断
?>

<br/>
<a class="calendar-page-anchor" href="../../main.php">TOPページに戻る</a>
</div>

</body>
</html>