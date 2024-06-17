<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width">
    <title>マッチングリセット</title>
    <link rel="stylesheet" href="../../public/css/template.css">
    <link rel="stylesheet" href="../../public/css/template-container-center.css">
</head>
<body>

<pageTitle>マッチングのリセット</pageTitle>

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

$logName = "matching_reset";
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

?>

<div class="container">

<?php if ($_SERVER["REQUEST_METHOD"] == "GET") : ?>

    <h3>本当にマッチングをリセットしても良いですか？</h3>

    <form action="<?php echo $_SERVER["SCRIPT_NAME"];?>" method="POST">
        <input type="submit" name="resetDone" value="Yes">
        <input type="submit" name="resetCancel" value="No">
    </form>

<?php endif; ?>

<?php

// リセットを実行
if (isset($_POST["resetDone"])) {
    // マッチング情報
    $assignedShift = $database->getShiftAssignments();

    // マッチング中のシフトを全てキャンセルする
    for ($i = 0; $i < count($assignedShift); $i++) {
        $employeeId = $assignedShift[$i]["employee_id"];
        $employeeName = $database->getEmployeeNameByEmployeeId($employeeId);

        $shiftDate = $assignedShift[$i]["shift_date"];
        $shiftName = $assignedShift[$i]["shift_name"];

        $isSucces = $inputDatabase->cancellShiftAssignment($employeeId, $shiftDate, $shiftName);

        // データベース操作失敗
        if ( !$isSucces ) {
            echo "<h3>マッチングのリセットに失敗しました</h3>";
            echo '<a href="../../main.php">TOPページに戻る</a>';
            echo "</div>";
            exit();
        }
    }

    echo "<h3>マッチングをリセットしました</h3>";
}

// リセットを中止
if (isset($_POST["resetCancel"])) {

    echo "<h3>マッチングのリセットをキャンセルしました</h3>";

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