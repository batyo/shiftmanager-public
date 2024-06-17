<?php

require_once("../../../class/general.php");

$general = new General();

$sessionKeyLogin = "login";
$sessionKeyName = "user_name";
$sessionKeyAuthority = "administrator";
$backPagePath = "../../login.html";

$general->login_authentication_confirmation($sessionKeyLogin, $sessionKeyName, $sessionKeyAuthority, $backPagePath);

$logName = "shift_operation_branch";
$logPath = "../../../log/acces_log.txt";
$general->acces_log($logName, $logPath);

set_error_handler(array($general, "error_logger"));

// 「シフト追加」ボタン
if (isset($_POST["shiftAdd"])) {
    header("Location: shift_add.php");
    exit();
}

// 「シフト削除」ボタン
if (isset($_POST["shiftDelete"])) {
    
    // シフトが選択されていない場合
    if (!isset($_POST["deleteShiftId"])) {
        header("Location: shift_info.php");
        exit();
    }

    $eventId = $_POST["deleteShiftId"];
    header("Location: shift_delete.php?eventId=".$eventId);
    exit();
}

// 「アーカイブ」ボタン
if (isset($_POST["pastShiftDelete"])) {
    header("Location: past_shift_delete.php");
    exit();
}
