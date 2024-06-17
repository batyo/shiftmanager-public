<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width">
    <title>マッチング準備</title>
    <link rel="stylesheet" href="../../public/css/template.css">
    <link rel="stylesheet" href="../../public/css/matching_preparation.css">
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

$logName = "matching_preparation";
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

$employees = $database->getEmployees();

// 従業名とステータスの一覧
// ステータスの横にチェックボックスを用意しチェックを入れた従業員のステータスを切り替える
echo '<div class="container">';

echo "<h1>マッチング準備</h1>";
echo '<form method="POST" action="matching_execution.php">';
echo '<div class="table-wrapper">';
echo "<table>";
echo "<tr>";
echo "<th>従業員名</th><th>ステータス</th><th>ステータス切り替え</th>";
echo "</tr>";
for ($i = 0; $i < count($employees); $i++) {

    $employeeName = $database->getEmployeeNameByEmployeeId($employees[$i]["id"]);

    echo "<tr>";
    echo '<td>'.$employeeName.'</td>'; // 従業員名
    echo '<td>'.$employees[$i]["status"].'</td>'; // ステータス
    echo '<td><input type="checkbox" name="employees_changeStatus[]" value="'.$employeeName.'"></td>'; // チェックボックス
    echo "</tr>";
}
echo "</table>";
echo "</div>";
echo '<div class="button-container">';
echo '<input type="submit" value="実行">';
echo "</form>";
echo '<form action="./matching_manual_preparation.php">';
echo '<input type="submit" value="手動で実行">';
echo '</form>';
echo '</div>';

$database->closeConnection(); // データベース切断
?>

<br/>
<a href="../../main.php">TOPページに戻る</a>

</div>

</body>
</html>