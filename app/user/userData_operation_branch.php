<?php

require_once("../../class/general.php");

$general = new General();

$sessionKeyLogin = "login";
$sessionKeyName = "user_name";
$sessionKeyAuthority = "administrator";
$backPagePath = "../login.html";

$general->login_authentication_confirmation($sessionKeyLogin, $sessionKeyName, $sessionKeyAuthority, $backPagePath);

$logName = "userData_operation_branch";
$logPath = "../../log/acces_log.txt";
$general->acces_log($logName, $logPath);

set_error_handler(array($general, "error_logger"));

// 「従業員のシフト情報参照」ボタン
if (isset($_POST["displayEmployeeShift"])) {
    
    // ユーザーが選択されていない場合
    if (!isset($_POST["employeeId"])) {
        header("Location: ../menu/admin/employee_info.php");
        exit();
    }

    $employeeId = $_POST["employeeId"];
    header("Location: ../menu/admin/employee_shift_info.php?employeeId=".$employeeId);
    exit();
}

// 「従業員追加」ボタン
if (isset($_POST["userAdd"])) {
    header("Location: employee_user_add.php");
    exit();
}

// 「従業員削除」ボタン
if (isset($_POST["userDelete"])) {

    // ユーザーが選択されていない場合
    if (!isset($_POST["employeeId"])) {
        header("Location: ../menu/admin/employee_info.php");
        exit();
    }

    $employeeId = $_POST["employeeId"];
    header("Location: user_delete.php?employeeId=".$employeeId);
    exit();
}
