<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width">
    <title>アーカイブ登録</title>
    <link rel="stylesheet" href="../../public/css/template.css">
    <link rel="stylesheet" href="../../public/css/template-container-center.css">
</head>
<body>

<pageTitle>アーカイブ登録</pageTitle>

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
$inputDatabase = $database->getInputDatabaseObject();

// 過去シフトの削除とアーカイブ登録
$isSucces = $inputDatabase->deleteFinishedShift();


// データベースエラー
if ($isSucces === false) {
    echo '<div class="container">';
    echo "<h3>データベースエラーが発生しました</h3>";
    echo '<a href="./shift_info.php">シフト情報ページに戻る</a>';
    echo "</div>";
    exit();
}

// 該当データなし
if (is_null($isSucces)) {
    echo '<div class="container">';
    echo "<h3>現在削除するシフトはありません</h3>";
    echo '<a href="./shift_info.php">シフト情報ページに戻る</a>';
    echo "</div>";
    exit();
}

// データベース切断
$database->closeConnection();
$inputDatabase->closeConnection();
?>

<div class="container">

<h3>アーカイブ化が完了しました</h3>

<br/>
<a href="./shift_info.php">シフト情報ページに戻る</a>

</div>

</body>
</html>
