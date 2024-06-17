<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width">
    <title>マッチング実行</title>
    <link rel="stylesheet" href="../../public/css/template.css">
    <link rel="stylesheet" href="../../public/css/matching_execution.css">
</head>
<body>

<pageTitle>マッチング実行</pageTitle>

<?php

require_once("../../../class/general.php");
require_once("../../../class/matching/matching_graph.php");
require_once("../../../class/matching/twoPart_matching.php");
require_once("../../../class/database/database.php");
require_once("../../../class/matching/graph_adjustment.php");
require_once("../../../vendor/autoload.php");

$general = new General();

$sessionKeyLogin = "login";
$sessionKeyName = "user_name";
$sessionKeyAuthority = "administrator";
$backPagePath = "../../login.html";
$general->login_authentication_confirmation($sessionKeyLogin, $sessionKeyName, $sessionKeyAuthority, $backPagePath);

$logName = "matching_execution";
$logPath = "../../../log/acces_log.txt";
$general->acces_log($logName, $logPath);

set_error_handler(array($general, "error_logger"));

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
$tableName = $_ENV["DB_NAME"];

// データベース操作クラス
$database = new Database($host, $userName, $password, $tableName);
$inputDatabase = $database->getInputDatabaseObject();

// 指定された従業員のステータスを切り替える
if (isset($_POST["employees_changeStatus"])) {
    /**
     * @var array $employeesName 従業員名
     * matching_preparation.php から status カラムを変更する従業員名を受け取る
     */
    $employeesName = $_POST["employees_changeStatus"];

    for ($i = 0; $i < count($employeesName); $i++) {

        $employeeId = $database->getEmployeeIdByName($employeesName[$i]);

        // status の切り替え
        if ( !$inputDatabase->toggleStatusValue($employeeId) ) {
            echo "データベースの更新に失敗しました。";
            exit;
        }
    }
}

// 提出された希望シフトが無い場合
if ($database->isEmptyShiftPreference()) {
    echo "<h3>希望シフトが提出されていません</h3>";
    echo '<a href="../../main.php">TOPページに戻る</a>';
    exit;
}

// 従業員・シフト情報
$employeesStatusReady = $database->getEmployeesOfSpecifiedStatus("ready");
$shifts = $database->getCalendarShifts();

// 各ノード数
$employeeCount = count($employeesStatusReady)-1;
$shiftCount = count($shifts);
$totalNumberNode = $employeeCount + $shiftCount + 2; // 始発ノードと終着ノードを合わせたノード総数


// グラフ作成クラス
$matchingGraph = new MatchingGraph($database);

$graph = $matchingGraph->getGraph();

// 再マッチング時のグラフ調整
$reMatching = isset($_POST["re-matching"]);
if ($reMatching) {
    $startNode = $_POST["startNode"];
    $adjustmentRange = $_POST["adjustmentRange"];

    $graphAdjustment = new GraphAdjustment();
    $graphAdjustment->searchToPathCut($graph, $startNode, $adjustmentRange);
}

// フォードファルカーソン法クラス
$matching = new FordFulkerson($graph);
$maxFlow = $matching->maxFlow(0, $totalNumberNode-1);

// 更新されたグラフ(残余グラフ)
$matchedGraph = $matching->getGraph();

$_SESSION["matchedGraph"] = $matchedGraph;

echo '<div class="container">';
echo "<table>";
echo "<tr>";
echo "<th>従業員名</th><th>日付</th><th>シフト名</th>";
echo "</tr>";

// シフトマッチング結果
for ($shift = 1; $shift <= $shiftCount; $shift++) {
    for ($employee = 1; $employee <= $employeeCount; $employee++) {
        // シフトと従業員の接続が無い場合はスキップ
        if ($matchedGraph[$shift+$employeeCount][$employee] == 0) continue;
        
        // 従業員 ID 取得
        $employeeId = $employeesStatusReady[$employee]["id"];

        // 名前取得
        $employeeName = $database->getEmployeeNameByEmployeeId($employeeId);
        $shiftDate = $shifts[$shift-1]["shift_date"];
        $shiftName = $shifts[$shift-1]["shift_name"];

        echo "<tr>";
        echo '<td>'.$employeeName.'</td><td>'.$shiftDate.'</td><td>'.$shiftName.'</td>';
        echo "</tr>";
    }
}

echo "</table>";

echo "<p>最大マッチ数: " . $maxFlow."</p>";

$graphCheck = new GraphCheck();
$checkData = $graphCheck->graphAdjustment($database, $matchedGraph, $employeeCount, $shiftCount, 0.0);

if ($checkData != null) {
    // 同条件のシフト数とその従業員
    $hasSameShiftEmployee_A = $checkData["same_count"]["employee_pair"][0];
    $hasSameShiftEmployee_B = $checkData["same_count"]["employee_pair"][1];
    $sameShiftCount = $checkData["same_count"]["same_shift_count"];

    // 最もシフト割り当て数が多い従業員と占有率
    $occupancyRate = $checkData["shift_occupancy"]["occupancy_rate"] * 100; // 百分率に直す
    $hasMostShiftsEmployee = $checkData["shift_occupancy"]["node_number"];

    echo "<h4>【マッチング確認】</h4>";
    echo "<p>条件の重なり</p>";
    echo "<p>対象従業員番号 : $hasSameShiftEmployee_A & $hasSameShiftEmployee_B</p>";
    echo "<p>同条件のシフト数 : $sameShiftCount</p><br/>";
    echo "<p>割り当て数が最多の従業員とその占有率</p>";
    echo "<p>従業員番号 : $hasMostShiftsEmployee</p>";
    echo "<p>シフト占有率 : $occupancyRate %</p>";

    // グラフを調整して再マッチングするフォーム
    echo '<div class="button-container">';
    echo '<form action="'.$_SERVER["SCRIPT_NAME"].'" method="POST">';
    echo '<input type="hidden" name="startNode" value="'.$hasMostShiftsEmployee.'">';
    echo '<input type="hidden" name="adjustmentRange" value="'.$employeeCount.'">';
    echo '<input type="submit" name="re-matching" value="自動で再マッチングする">';
    echo '</form>';
    echo '<form action="matching_manual_preparation.php">';
    echo '<input type="submit" value="手動で再マッチングする">';
    echo '</form>';
    echo '</div>';
}

if ($reMatching) {
    echo '<form action="'.$_SERVER["SCRIPT_NAME"].'">';
    echo '<input type="submit" value="変更をキャンセルする">';
    echo '</form>';
}

// データベース切断
$database->closeConnection();
$inputDatabase->closeConnection();
?>

<br/>

<form action="matching_result.php" method="POST">
    <input type="submit" value="このマッチングで決定する">
</form>

<br/>
<a href="../../main.php">TOPページに戻る</a>
</div>

</body>
</html>
