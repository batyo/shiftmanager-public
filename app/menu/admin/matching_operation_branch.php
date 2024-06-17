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

// 「マッチングリセット」ボタン
if (isset($_POST["matchingReset"])) {
    header("Location: matching_reset.php");
    exit();
}

// 「マッチング削除」ボタン
if (isset($_POST["matchingDelete"])) {
    
    // マッチングが選択されていない場合
    if (!isset($_POST["deleteMatchingId"])) {
        header("Location: matching_info.php");
        exit();
    }

    $eventId = $_POST["deleteMatchingId"];
    header("Location: matching_delete.php?eventId=".$eventId);
    exit();
}

// 「アーカイブ」ボタン
if (isset($_POST["matchingArchive"])) {
    header("Location: matching_archive.php");
    exit();
}
