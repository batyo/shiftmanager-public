<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width">
    <title>シフト情報追加</title>
    <link rel="stylesheet" href="../../public/css/template.css">
    <link rel="stylesheet" href="../../public/css/shift_add.css">
</head>
<body>

<pageTitle>シフト情報追加</pageTitle>

<?php
require_once("../../../class/general.php");
require_once("../../../class/database/database.php");
require_once("../../../class/validation.php");
require_once("../../../vendor/autoload.php");

$general = new General();

$sessionKeyLogin = "login";
$sessionKeyName = "user_name";
$sessionKeyAuthority = "administrator";
$backPagePath = "../../login.html";

$general->login_authentication_confirmation($sessionKeyLogin, $sessionKeyName, $sessionKeyAuthority, $backPagePath);

$logName = "shift_add";
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
?>

<div class="container">

<?php if ($_SERVER["REQUEST_METHOD"] == "GET"): ?>

    <?php
    // トークンの生成 (CSRF対策)
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $token;
    ?>

    <form method="POST" action="<?php echo $_SERVER["SCRIPT_NAME"]; ?>">
        <label for="shiftDate">日付 (YYYY-mm-dd):</label>
        <input type="text" name="shiftDate" placeholder="*半角文字" id="shiftDate" require><br>

        <label for="startTime">開始時間 (hh:mm):</label>
        <input type="text" name="startTime" placeholder="*半角文字" id="startTime" require><br>

        <label for="endTime">終了時間 (hh:mm):</label>
        <input type="text" name="endTime" placeholder="*半角文字" id="endTime" require><br>

        <label for="shiftName">シフト名:</label>
        <input type="text" name="shiftName" id="shiftName" require><br>

        <label for="availableSlots">空き枠数:</label>
        <input type="text" name="availableSlots" placeholder="*半角文字" id="availableSlots" require><br>

        <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">

        <input type="submit" value="送信">
    </form>

<?php endif; ?>

<?php

// フォームで送信されたシフト情報の受け取り
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $sanitizedPost = $general->sanitize($_POST);

    $shiftDate = $sanitizedPost["shiftDate"];
    $startTime = $sanitizedPost["startTime"];
    $endTime = $sanitizedPost["endTime"];
    $shiftName = $sanitizedPost["shiftName"];
    $availableSlots = $sanitizedPost["availableSlots"];

    $validation = new Validation();

    $isCorrectAll = true; // 入力チェックフラグ

    // datetime のバリデーションチェック
    $startDateTime = $shiftDate." ".$startTime.":00";
    $endDateTime = $shiftDate." ".$endTime.":00";

    /**
     * @var true|false $isCorrectStartTime 有効な文字列か否かの bool 値を持つ
     * @see Validation::validationCheck($methodName, $validationString)
     */
    $isCorrectStartTime = $validation->validationCheck("validationDateTime", $startDateTime);
    $isCorrectEndTime = $validation->validationCheck("validationDateTime", $endDateTime);

    if ( !$isCorrectStartTime ) $isCorrectAll = false;
    if ( !$isCorrectEndTime ) $isCorrectAll = false;

    // 空き枠数のバリデーションチェック
    // 数字または数値形式の文字列であるか
    if ( !is_numeric($availableSlots) ) {
        echo "<p>空き枠数の入力が正しくありません</p>";
        $isCorrectAll = false;
    }

    // 重複チェック
    // 同じ日付に同じシフト名が無いか
    $nullCheck = $database->getShiftIdByDateAndName($shiftDate, $shiftName);
    
    if ( !is_null($nullCheck) ) {
        echo "<p>既に同じ日付に同じシフト名が登録されています</p>";
        $isCorrectAll = false;
    }

    if ( !$isCorrectAll ) {
        echo "<h3>入力に問題があります。再度入力し直して下さい。</h3>";
        echo '<a href="'.$_SERVER["SCRIPT_NAME"].'">戻る</a>';
        exit();
    }

    // データベースに登録
    $isSucces = $inputDatabase->addCalendarShifts($shiftDate, $startDateTime, $endDateTime, $shiftName, $availableSlots, 0);

    if ($isSucces) echo "<h3>データベースに登録が完了しました</h3>";
    if ( !$isSucces ) echo "<h3>データベース登録に失敗しました</h3>";
}

// データベース切断
$database->closeConnection();
$inputDatabase->closeConnection();
?>

<br/>
<a href="./shift_info.php">シフト情報画面に戻る</a>
</div>

</body>
</html>