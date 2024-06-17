<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width">
    <title>従業員情報登録</title>
    <link rel="stylesheet" href="../public/css/template.css">
    <link rel="stylesheet" href="../public/css/employee_user_add.css">
</head>
<body>

<pageTitle>従業員情報登録</pageTitle>

<?php
require_once("../../class/general.php");
require_once("../../class/database/database.php");
require_once("../../class/user/user.php");
require_once("../../vendor/autoload.php");

$general = new General();

$sessionKeyLogin = "login";
$sessionKeyName = "user_name";
$sessionKeyAuthority = "administrator";
$backPagePath = "login.html";

$general->login_authentication_confirmation($sessionKeyLogin, $sessionKeyName, $sessionKeyAuthority, $backPagePath);

$logName = "employee_user_add";
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

$classUser = new User();

?>

<div class="container">

<?php if ($_SERVER["REQUEST_METHOD"] == "GET"): ?>

    <?php
    // トークンの生成 (CSRF対策)
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $token;
    ?>

    <form method="POST" action="<?php echo $_SERVER["SCRIPT_NAME"]; ?>">
        <label for="userName">ユーザー名:</label>
        <input type="text" name="userName" id="userName" require><br>

        <label for="contact-email">連絡先 (email):</label>
        <input type="text" name="contact-email" id="contact-email" require><br>

        <label for="employment-type">契約形態:</label>
        <select name="employment-type" id="employment-type">
            <option value="">--選択して下さい--</option>
            <option value="part-time">Part Time</option>
            <option value="full-time">Full Time</option>
        </select>
        <br/>
        <label for="password">パスワード (半角英数):</label>
        <input type="password" name="password" placeholder="*半角文字" id="password" required><br>
        <label for="password2">パスワード再入力:</label>
        <input type="password" name="password2" placeholder="*半角文字" id="password2" required><br>

        <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">

        <input type="submit" value="送信">
    </form>

<?php endif; ?>


<?php

// フォームで送信された従業員情報の受け取り
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $sanitizedPost = $general->sanitize($_POST);

    $userName = $sanitizedPost["userName"];
    $email = $sanitizedPost["contact-email"];
    $employmentType = $sanitizedPost["employment-type"];
    $password = $sanitizedPost["password"];
    $password2 = $sanitizedPost["password2"];


    /** @var true|false $correctInputFlag  入力データが全て正常な場合 true 問題がある場合は false*/
    $correctInputFlag = true;

    // メールアドレスのバリデーション
    if ( !filter_var($email, FILTER_VALIDATE_EMAIL) ) {
        echo "<h3>Eメールアドレスが正しくありません。</h3>".PHP_EOL;
        $correctInputFlag = false;
    }

    // 適切なパスワードであるか確認
    if ( !$classUser->checkForProperPasswords($password) ) {
        echo "<h3>このパスワードは適切ではありません。もう一度入力してください。</h3>".PHP_EOL;
        $correctInputFlag = false;
    }

    if ($password != $password2) {
        echo "<h3>パスワードが一致しません</h3>".PHP_EOL;
        $correctInputFlag = false;
    }

    echo "<p>ユーザー名:".$userName."</p>";
    echo "<p>連作先 (email):".$email."</p>";
    echo "<p>契約形態:".$employmentType."</p>";
    $maskedValue  = str_repeat("＊", strlen($password));
    echo "<p>パスワード:".$maskedValue."</p>";

    // 入力が正常な場合はユーザー (従業員) 情報をデータベースに登録する
    if ($correctInputFlag == true) {

        // ユーザーID (user_id) 生成
        $userId = $classUser->generateUnique7DigitNumber($database);

        // ユーザーID生成失敗
        if ($userId == false) {
            echo "ユーザーIDの生成に失敗しました。やり直してください。";
            $database->closeConnection();
            echo '<a href="'.$_SERVER["SCRIPT_NAME"].'">TOPページへ戻る</a>';
            exit();
        }

        // 従業員情報登録
        $preferredShift = [];
        $inputDatabase->saveEmployee($userName, $email, $employmentType, $preferredShift);

        // ユーザー情報登録
        $employeeId = $database->getEmployeeIdByName($userName);
        $authority = "employee";
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $inputDatabase->saveUser($userName, $userId, $employeeId, $authority, $hashedPassword);

        echo PHP_EOL;
        echo "<h3>登録が完了しました</h3>";
    }
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