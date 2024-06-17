<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width">
    <title>従業員情報</title>
    <link rel="stylesheet" href="../../public/css/template.css">
    <link rel="stylesheet" href="../../public/css/employee_info.css">
</head>
<body>

<pageTitle>従業員情報</pageTitle>

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

$logName = "employee_info";
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


$employees = $database->getEmployees();

echo '<div class="container">';

echo "<h1>従業員一覧</h1>";

echo '<form action="../../user/userData_operation_branch.php" method="POST">';

echo '<div class="table-wrappaer">';

echo "<table>";
echo "<tr>";
echo '<th class="left-aligned">選択</th><th>氏名</th><th>連絡先</th><th>契約形態</th>';
echo "</tr>";
for ($i = 0; $i < count($employees); $i++) {

    $employeeId = $employees[$i]["id"];
    $employeeName = $database->getEmployeeNameByEmployeeId($employeeId);

    echo "<tr>";
    echo '<td class="left-aligned"><input type="radio" name="employeeId" value="'.$employeeId.'"></td>';
    echo "<td>".$employeeName."</td>"; // 氏名
    echo "<td>".$employees[$i]["contact_info"]."</td>"; // 連絡先
    echo "<td>".$employees[$i]["employment_type"]."</td>"; // 契約形態
    echo "</tr>";
}
echo "</table>";

echo "</div>";

echo '<input type="submit" name="displayEmployeeShift" value="シフト参照">';
echo '<input type="submit" name="userAdd" value="従業員追加">';
echo '<input type="submit" name="userDelete" value="従業員削除">';

echo "</form>";

$database->closeConnection(); // データベース切断
?>

<br/>
<a href="../../main.php">TOPページに戻る</a>
</div>

</body>
</html>
