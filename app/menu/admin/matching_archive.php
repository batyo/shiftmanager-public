<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width">
    <title>マッチングアーカイブ</title>
    <link rel="stylesheet" href="../../public/css/matching_archive.css">
    <link rel="stylesheet" href="../../public/css/template.css">
    <link rel="stylesheet" href="../../public/css/template-container-center.css">
</head>
<body>

<pageTitle>過去のマッチング一覧</pageTitle>

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
?>

<div class="container">

<?php if ($_SERVER["REQUEST_METHOD"] == "GET") : ?>

    <form action="<?php echo $_SERVER["SCRIPT_NAME"]; ?>" method="POST">
        <select name="year" id="year"></select>
        <label for="year">年</label>

        <select name="month" id="month"></select>
        <label for="month">月</label>

        <select name="day" id="day"></select>
        <label for="day">日</label>

        <p>以前から</p>

        <select name="untilYear" id="untilYear"></select>
        <label for="untilYear">年</label>

        <select name="untilMonth" id="untilMonth"></select>
        <label for="untilMonth">月</label>

        <select name="untilDay" id="untilDay"></select>
        <label for="untilDay">日</label>

        <p>までのデータ</p>

        <input type="submit" value="決定">
    </form>

    <script src="../../public/javascript/calendar_pulldown.js"></script>
    <script src="../../public/javascript/calendar_limit.js"></script>

<?php endif; ?>

<?php if ($_SERVER["REQUEST_METHOD"] == "POST") : ?>

    <?php
    $fromYear = $_POST["year"];
    $fromMonth = $_POST["month"];
    $fromDay = $_POST["day"];
    $untilYear = $_POST["untilYear"];
    $untilMonth = $_POST["untilMonth"];
    $untilDay = $_POST["untilDay"];

    // 検索範囲 YYYY-mm-dd
    $fromDate = $fromYear. "-". $fromMonth. "-". $fromDay;
    $untilDate = $untilYear. "-". $untilMonth. "-". $untilDay;

    /** @var array 過去の割り当て情報 employee_id, shift_date, shift_name */
    $archives = $database->getArchiveCalendarShifts($fromDate, $untilDate);

    if ( !$archives ) {
        echo "<h3>条件に一致する情報は見つかりませんでした</h3>";
        echo '<a href="./matching_info.php">マッチング情報画面に戻る</a>';
        echo "</div>";
        exit();
    }
    ?>

    <h1><?php echo $untilDate. " ~ ". $fromDate; ?></h1>

    <table>
        <tr>
            <th>日付</th><th>シフト名</th><th>従業員名</th>
        </tr>

        <?php for ($i = 0; $i < count($archives); $i++) : ?>

            <?php
            $employeeId = $archives[$i]["employee_id"]; // 従業員ID
            $shiftDate = $archives[$i]["shift_date"]; // シフト日付
            $shiftName = $archives[$i]["shift_name"]; // シフト名

            $employeeName = $database->getEmployeeNameByEmployeeId($employeeId);
            ?>

            <tr>
                <td><?php echo $shiftDate; ?></td>
                <td><?php echo $shiftName; ?></td>
                <td><?php echo $employeeName; ?></td>
            </tr>

        <?php endfor; ?>

    </table>

<?php endif; ?>

<?php $database->closeConnection(); // データベース切断 ?>

<br/>
<a href="./matching_info.php">マッチング情報画面に戻る</a>

</div>

</body>
</html>