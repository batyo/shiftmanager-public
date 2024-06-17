<?php

/**
 * fetch API 用のスクリプト
 * 
 * @see ./matching_manual_preparation.php
 * @see ../../public/javascript/async/select_value.js
 */

require_once("../../../class/general.php");
require_once("../../../class/database/database.php");
require_once("../../../vendor/autoload.php");

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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    $data = json_decode(file_get_contents("php://input"), true); // 受け取ったJSONデータを連想配列に変換
    $employeeId = $data["selected"];

    /** @var array $shifts シフト情報 */
    $shifts = $database->getShiftPreferenceByEmployeeId($employeeId);

    /**
     * @var array FullCalendar ライブラリに渡す配列
     * 
     * FullCalendar ライブラリ
     * @see https://fullcalendar.io/docs 公式ドキュメント
     */
    $eventData = [];

    for ($i = 0; $i < count($shifts); $i++) {
        
        $preferenceEventId = [];

        $eventId = $i;
        $shiftName = $shifts[$i]["shift_name"];
        $shiftDate = $shifts[$i]["shift_date"];

        $shiftId = $database->getShiftIdByDateAndName($shiftDate, $shiftName);
        $shiftInfo = $database->getCalendarShiftsByShiftId($shiftId);

        $startDateTime = $shiftInfo["start_date_time"];
        $endDateTime = $shiftInfo["end_date_time"];
        $availableSlots = $shiftInfo["available_slots"];
        $assignmentCount = $shiftInfo["current_assignment_count"];

        // 空き枠数が無い場合はスキップ
        if ($availableSlots == 0) continue;

        $eventData[] = [
            "id" => $i,
            "title" => $shiftName,
            "start" => $startDateTime,
            "end" => $endDateTime,
            "description" => [$availableSlots, $assignmentCount, $shiftDate]
        ];
    }

    session_start();
    $_SESSION["eventData"] = $eventData;
    
    // レスポンスを返す
    echo json_encode(["message" => "非同期処理が完了しました", "eventData" => $eventData]);
}
