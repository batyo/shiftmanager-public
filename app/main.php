<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width">
    <title>管理画面</title>
    <link rel="stylesheet" href="./public/css/main.css">
</head>
<body>

    <pageTitle>管理画面</pageTitle>

    <?php
    require_once("../class/general.php");

    $general = new General();

    $sessionKeyLogin = "login";
    $sessionKeyName = "user_name";
    $sessionKeyAuthority = "administrator";
    $backPagePath = "login.html";
    $general->login_authentication_confirmation($sessionKeyLogin, $sessionKeyName, $sessionKeyAuthority, $backPagePath);

    $logName = "main";
    $logPath = "../log/acces_log.txt";
    $general->acces_log($logName, $logPath);

    set_error_handler(array($general, "error_logger"));

    $authority = $_SESSION["authority"];
    ?>

    <header>
        <h1>管理画面</h1>
    </header>
    
    <div class="menu-container">
        <?php if ($authority == "administrator"): // 管理者権限ユーザー ?>
            <ul>
                <li><a href="./menu/admin/attendance_info.php">勤怠管理</a></li>
                <li><a href="./menu/admin/employee_info.php">従業員情報</a></li>
                <li><a href="./menu/admin/shift_info.php">シフト情報</a></li>
                <li><a href="./menu/admin/matching_info.php">マッチング情報</a></li>
                <li><a href="./menu/admin/matching_preparation.php">マッチングの実施</a></li>
                <li><a href="./menu/admin/matching_download.php">マッチングデータのダウンロード</a></li>
                <li><a href="./logout.php">ログアウト</a></li>
            </ul>
        <?php endif; ?>
    
        <?php if ($authority == "employee"): // 従業員ユーザー ?>
            <ul>
                <li><a href="./menu/employee/attendance_report.php">勤怠報告</a></li>
                <li><a href="./menu/employee/assignment_shift_info.php">勤務予定</a></li>
                <li><a href="./menu/employee/preference_shift_submit.php">希望シフトを提出する</a></li>
                <li><a href="./menu/employee/preference_shift_delete.php">提出した希望シフトを変更する</a></li>
                <li><a href="./logout.php">ログアウト</a></li>
            </ul>
        <?php endif; ?>
    </div>

</body>
</html>
