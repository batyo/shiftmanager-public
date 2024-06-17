<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width">
    <title>ダウンロード</title>
    <link rel="stylesheet" href="../../public/css/template.css">
    <link rel="stylesheet" href="../../public/css/matching_download.css">
</head>
<body>

<pageTitle>ダウンロード</pageTitle>

<?php
require_once("../../../class/general.php");

$general = new General();

$sessionKeyLogin = "login";
$sessionKeyName = "user_name";
$sessionKeyAuthority = "administrator";
$backPagePath = "../../login.html";

$general->login_authentication_confirmation($sessionKeyLogin, $sessionKeyName, $sessionKeyAuthority, $backPagePath);

$logName = "shift_info";
$logPath = "../../../log/acces_log.txt";
$general->acces_log($logName, $logPath);

set_error_handler(array($general, "error_logger"));
?>

<div class="container">

<h3>マッチングデータのダウンロード</h3>
<br>

<?php if ($_SERVER["REQUEST_METHOD"] == "GET"): ?>

    <p>ダウンロードするデータの保存日を選択して下さい</p>

    <form action="<?php echo $_SERVER["SCRIPT_NAME"]; ?>" method="POST">
        <select name="year" id="year"></select>
        <label for="year">年</label>

        <select name="month" id="month"></select>
        <label for="month">月</label>

        <select name="day" id="day"></select>
        <label for="day">日</label>

        <input type="submit" value="決定">
    </form>

    <script src="../../public/javascript/calendar_pulldown.js"></script>

<?php endif; ?>


<?php if ($_SERVER["REQUEST_METHOD"] == "POST"): ?>

    <?php
    $year = $_POST["year"];
    $month = $_POST["month"];
    $day = $_POST["day"];

    $logDirectoryPath = "../../../log/app";

    $searchPattern = $year."_".$month."_".$day;
    
    $matchingDataFile = glob($logDirectoryPath."/*".$searchPattern."*.csv");

    // 該当するファイルが無い場合
    if (empty($matchingDataFile)) {
        echo "<p>ファイルが見つかりませんでした</p>";
        echo '<a href="../../main.php">TOPページへ戻る</a>';
        exit;
    }

    // 検索に失敗した場合
    if ( !$matchingDataFile ) {
        echo "<p>ファイルの取得に失敗しました</p>";
        echo '<a href="../../main.php">TOPページへ戻る</a>';
        exit;
    }

    // 検索にヒットしたデータ数
    $dataCount = count($matchingDataFile);

    echo "<ul>";
    echo '<li class="result-report">'.$dataCount.' 件のファイルが見つかりました</li>';

    // csv ファイルリンク
    foreach ($matchingDataFile as $csv) {
        $fileName = basename($csv);
        echo '<li><a href="'.$csv.'">'.$fileName.'</a></li>';
    }

    echo "</ul>";
    ?>


<?php endif; ?>

<br/>
<a href="../../main.php">TOPページへ戻る</a>

</div>

</body>
</html>
