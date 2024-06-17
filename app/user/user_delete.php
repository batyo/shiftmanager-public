<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width">
    <title>従業員情報削除</title>
    <pageTitle>従業員情報削除</pageTitle>
    <link rel="stylesheet" href="../public/css/template.css">
    <link rel="stylesheet" href="../public/css/user_delete.css">
</head>
<body>

<?php
require_once("../../class/general.php");
require_once("../../class/database/database.php");
require_once("../../vendor/autoload.php");

$general = new General();

$sessionKeyLogin = "login";
$sessionKeyName = "user_name";
$sessionKeyAuthority = "administrator";
$backPagePath = "../login.html";

$general->login_authentication_confirmation($sessionKeyLogin, $sessionKeyName, $sessionKeyAuthority, $backPagePath);

$logName = "user_delete";
$logPath = "../../log/acces_log.txt";
$general->acces_log($logName, $logPath);

set_error_handler(array($general, "error_logger"));

/**
 * PHP dotenv ライブラリ
 * 環境変数読み込み
 * @see https://github.com/vlucas/phpdotenv
 */
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2));
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
    $employeeId = $_GET["employeeId"];

    $employeeName = $database->getEmployeeNameByEmployeeId($employeeId);
    $matchedShift = $database->getShiftAssignmentsByEmployeeId($employeeId);

    // トークンの生成 (CSRF対策)
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $token;
    ?>

    <h1>ユーザー情報削除</h1>

    <p class="text-bold">次の従業員情報をデータベースから削除します。</p>
    <p>従業員名: <?php echo $employeeName; ?></p>
    <?php if ($matchedShift != null) : ?>
        <p class="text-caution">**現在割り当て中のシフトがあります**</p>
        <a href="../main.php">TOPページに戻る</a>
        <?php exit(); ?>
    <?php endif; ?>

    <br/>

    <p>管理権限者のユーザーID及びパスワードを入力して下さい。</p>

    <form method="POST" action="<?php echo $_SERVER["SCRIPT_NAME"]; ?>">
        <label for="adminId">ユーザー ID:</label>
        <input type="text" name="adminId" id="adminId" require><br>

        <label for="password">パスワード:</label>
        <input type="password" name="password" id="password" required><br>

        <input type="hidden" name="employeeId" value="<?php echo $employeeId; ?>">
        <input type="hidden" name="employeeName" value="<?php echo $employeeName; ?>">

        <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">

        <input type="submit" value="送信">
    </form>

<?php endif; ?>

<?php

// フォームで送信された従業員情報の受け取り
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $sanitizedPost = $general->sanitize($_POST);

    $adminId = $sanitizedPost["adminId"];
    $password = $sanitizedPost["password"];
    $employeeId = $sanitizedPost["employeeId"];
    $employeeName = $sanitizedPost["employeeName"];
    
    $adminName = $database->getUserNameByUserIdAndPassword($adminId, $password);

    // ユーザーID か パスワード が異なる場合は終了
    if ($adminName == null) {
        echo "<h3>ユーザーID か パスワードが間違っています</h3>";
        $database->closeConnection();
        echo '<a href="../main.php">TOPページに戻る</a>';
        exit();
    }

    // 権限の確認 (この時点では入力されたユーザーID及びパスワードが管理者権限者のものであることは確定していない)
    $userInfo = $database->getUserByUserId($adminId);
    $userAuthority = $userInfo["authority"];
    $isAdmin = $userAuthority == "administrator";

    if ( !$isAdmin ) {
        echo "<h3>ユーザー情報の削除は管理者権限で行う必要があります</h3>";
        $database->closeConnection();
        echo '<a href="../main.php">TOPページに戻る</a>';
        exit();
    }

    $userId = $database->getUserIdByEmployeeId($employeeId); // 削除するユーザーのID

    // 削除実行
    $isSucces = $inputDatabase->deleteUserByUserId($userId);

    if ( !$isSucces ) {
        echo "<h3>現在メンテナンス中です。メンテナンスが終了するまでお待ちください。</h3>";
        echo '<a href="../main.php">TOPページに戻る</a>';
        exit();
    }

    echo '<h3>ユーザー 「 '.$employeeName.' 」 の削除が完了しました</h3>';
}

// データベース切断
$database->closeConnection();
$inputDatabase->closeConnection();
?>

<br/>
<a href="../main.php">TOPページに戻る</a>
</div>

</body>
</html>