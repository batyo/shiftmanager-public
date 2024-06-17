<?php

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


echo "Enter your username.".PHP_EOL;
echo "User name:";
$userName = trim(fgets(STDIN));

echo "Select the authority.".PHP_EOL;
echo "1. Administrator".PHP_EOL;
echo "2. Employee".PHP_EOL;
echo "Select number:";
$inputNumber = trim(fgets(STDIN));

// 入力値確認
if ($inputNumber != (1 || 2)) {
    echo "Input is not appropriate.";
    exit();
}

// 権限
$authority;
if ($inputNumber == 1) $authority = "administrator";
if ($inputNumber == 2) $authority = "employee";

// 紐づける従業員ID
$employeeId = null;
if ($authority == "employee") {
    echo "---Register employee information---".PHP_EOL;

    // 従業員情報を新規登録
    echo "Enter contact info.".PHP_EOL;
    echo "contact info:";
    $contactInfo = trim(fgets(STDIN));
    echo "Enter employment type.".PHP_EOL;
    echo "employment type:";
    $employmentType = trim(fgets(STDIN));
    $preferredShift = []; // ここでは希望シフトは入力しません
    $inputDatabase->saveEmployee($userName, $contactInfo, $employmentType, $preferredShift);

    $employeeId = $database->getEmployeeIdByName($userName);
}

// ユーザーID (user_id) 生成
$userId = $user->generateUnique7DigitNumber($database);

if ($userId == false) {
    echo "Failed to generate user ID. Please try again.";
    $database->closeConnection();
    exit();
}

// パスワード
$password;
$properPassword = false;
while ($properPassword == false) {
    echo "Create a password with at least 7 single-byte alphanumeric characters.".PHP_EOL;
    echo "password:";
    $password = trim(fgets(STDIN));
    echo "Enter your password again.".PHP_EOL;
    echo "password:";
    $password2 = trim(fgets(STDIN));

    if ($password != $password2) {
        echo "The password is different. Please enter it again.".PHP_EOL;
        continue;
    }

    // 適切なパスワードであるか確認
    $checkProper = $user->checkForProperPasswords($password);
    
    if ($checkProper == true) {
        $properPassword = true;
    }

    if ($checkProper == false) {
        echo "This password is not appropriate. Please enter it again.".PHP_EOL;
    }
}

// パスワードをハッシュ化
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// データベースに登録する
$inputDatabase->saveUser($userName, $userId, $employeeId, $authority, $hashedPassword);

echo "User information registration is complete.";


// 使用後にデータベース接続をクローズする
$database->closeConnection();
