<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width">
    <title>ログイン認証</title>
    <link rel="stylesheet" href="./public/css/logout.css">
</head>
<body>

<div class="container">

<?php

require_once("../class/database/database.php");
require_once("../vendor/autoload.php");

/**
 * PHP dotenv ライブラリ
 * 環境変数読み込み
 * @see https://github.com/vlucas/phpdotenv
 */
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 1));
$dotenv->load();

$host = $_ENV["DB_HOST"];
$username = $_ENV["DB_USER_NAME"];
$password = $_ENV["DB_PASSWORD"];
$table = $_ENV["DB_NAME"];

try {

    $database = new Database($host, $username, $password, $table);

    // POSTデータ
    $userId = $_POST["userId"];
    $userPassword = $_POST["password"];

    $userName = $database->getUserNameByUserIdAndPassword($userId, $userPassword);

    if ($userName == null) {
        echo "<h3>ID番号かパスワードが間違っています。</h3>";
    }else {
        $userInfo = $database->getUserByUserId($userId);
        $authority = $userInfo["authority"];

        session_start();
        $_SESSION["login"] = "check";
        $_SESSION["user_id"] = $userId;
        $_SESSION["user_name"] = $userName;
        $_SESSION["authority"] = $authority;
        header("Location: main.php");
        exit();
    }

} catch (Exception $e) {
    echo "<h3>ただいまメンテナンス中です。</h3>";
    echo "<a href='login.html'>ログイン画面へ戻る</a>";
    exit();
}

?>

<a class="button" href="./login.html">ログイン画面へ戻る</a>
</div>

</body>
</html>
