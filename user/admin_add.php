<?php

// 最初にデータベースへ登録する際にのみ使用する
// adminユーザーを登録後はこのファイルへのアクセスを拒否する

require_once("../class/database/database.php");
require_once("../class/user/user.php");
require_once("../vendor/autoload.php");

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
$databaseName = $_ENV["DB_NAME"];

$database = new Database($host, $userName, $password, $databaseName);
$inputDatabase = $database->getInputDatabaseObject();

$user = new User();

// ユーザー名
$adminName = "iamadmin";

// 従業員ID
$employeeId = null;

// 権限
$authority = "administrator";

// ユーザーID (user_id) 生成
$userId = $user->generateUnique7DigitNumber($database);

if ($userId == false) {
    echo "Failed to generate user ID. Please try again.";
    $database->closeConnection();
    exit();
}

// パスワード
$password = "adminAuthority";

// パスワードをハッシュ化
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// データベースに登録する
$isSucces = $inputDatabase->saveUser($adminName, $userId, $employeeId, $authority, $hashedPassword);

// データベース接続をクローズする
$database->closeConnection();

if ($isSucces) {
    echo "User information registration is complete.";
    exit;
}

echo "Error";
