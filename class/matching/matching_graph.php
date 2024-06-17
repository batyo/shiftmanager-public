<?php

/**
 * 「従業員」と「シフト」情報をもとに、Ford-Fulkerson法でのマッチングに使用されるグラフを作成するクラス
 */
class MatchingGraph
{
    /**
     * インスタンス変数
     *
     * @var array   $graph      ノードの繋がりを示すグラフ
     * @var array   $priority   優先順を示すグラフ
     */
    private $graph;
    private $priority;

    /**
     * コンストラクタ
     *
     * @param Database $database Database クラスのインスタンス
     */
    public function __construct($database)
    {
        $this->initializeGraph($database);
    }

    /**
     * ノードの繋がりを示すグラフを作成する
     *
     * @param Database $database Database クラスのインスタンス
     */
    private function initializeGraph(Database $database)
    {
        $employeeStatus = "ready";

        $employees = $database->getEmployeesOfSpecifiedStatus($employeeStatus);
        $shifts = $database->getCalendarShifts();

        $employeeCount = count($employees)-1; // ダミーデータを除く getEmployee()参照
        $shiftCount = count($shifts);

        // ノードの総数 (従業員数 + シフト数 + 始発ノード + 終着ノード)
        $totalCountOfNode = $employeeCount + $shiftCount + 2;

        // グラフの初期化
        $this->graph = array_fill(0, $totalCountOfNode, array_fill(0, $totalCountOfNode, 0));


        // ノードの接続
        for ($employee = 1; $employee <= $employeeCount; $employee++) {

            // 従業員IDから出勤可能シフトを取得する
            $employeeId = $employees[$employee]["id"];
            $shiftPreferences = $database->getShiftPreferenceByEmployeeId($employeeId);

            // 始発ノードから各従業員への接続
            $this->graph[0][$employee] = count($shiftPreferences); // 出勤可能なシフト数;

            for ($i = 0; $i < $shiftCount; $i++) {

                $shift = $i + 1;
                $shiftNodeNumber = $shift + $employeeCount;
                $endNodeNumber = $employeeCount + $shiftCount + 1; // ループ内にある必要はないがコードのまとまりを重視
                $availableSlots = $shifts[$i]["available_slots"]; // シフトの空き枠数

                // 各シフトから終着ノードへの接続
                $this->graph[$shiftNodeNumber][$endNodeNumber] = $availableSlots;

                /**
                 * 出勤可能なシフトの ID を取得
                 * シフトが見つからなければこれ以下はスキップ
                 * 
                 * @var string  $shiftDate  シフトの日付
                 * @var string  $shiftName  シフト名
                 * @var int     $shiftId    シフトID
                 */
                if ( !isset($shiftPreferences[$i]) ) continue; 
                $shiftDate = $shiftPreferences[$i]["shift_date"];
                $shiftName = $shiftPreferences[$i]["shift_name"];
                $shiftId = $database->getShiftIdByDateAndName($shiftDate, $shiftName);

                /**
                 * @var int $shiftIndex        出勤できる シフトID の $shifts 内でのインデックス値
                 * @var int $shiftNodeNumber   シフトノード番号
                 */
                $shiftIndex = array_search($shiftId, array_column($shifts, "id"));
                $shiftNodeNumber = ($shiftIndex + 1) + $employeeCount;
                
                // 従業員ノードから出勤可能な各シフトノードへの接続
                $this->graph[$employee][$shiftNodeNumber] = 1;
            }
        }
    }

    /**
     * 優先順位を示すグラフを作成する
     * 
     * コンストラクターで初期化する際に使用
     * 
     * @deprecated version1.0.0 優先順位の要素を取り扱わなくなったので現在は使用していない
     *
     * @param Database $database Database クラスのインスタンス
     */
    private function initializePriority(Database $database)
    {
        $employeeStatus = "ready";

        $employees = $database->getEmployeesOfSpecifiedStatus($employeeStatus);
        $shifts = $database->getCalendarShifts();

        $employeeCount = count($employees)-1;
        $shiftCount = count($shifts);

        // ノードの総数 (従業員数 + シフト数 + 始発ノード + 終着ノード)
        $totalCountOfNode = $employeeCount + $shiftCount + 2;

        // 優先順位グラフの初期化
        $this->priority = array_fill(0, $totalCountOfNode, array_fill(0, $totalCountOfNode, -1));

        for ($employeeNumber = 1; $employeeNumber <= $employeeCount; $employeeNumber++) {
            for ($i = 0; $i < count($employees[$employeeNumber]["preferred_shift"]); $i++) {
                $shiftName = $employees[$employeeNumber]["preferred_shift"][$i]["shift_name"];
                $shiftNumber = (int) substr($shiftName, strlen("shift_")) + $employeeCount;

                $priority = (int) $employees[$employeeNumber]["preferred_shift"][$i]["priority"];

                $this->priority[$employeeNumber][$shiftNumber] = $priority;
            }
        }
    }

    /**
     * ノードの繋がりを示すグラフを取得します。
     *
     * @return array ノードの繋がりを示すグラフ
     */
    public function getGraph()
    {
        return $this->graph;
    }

    /**
     * 優先順位を示すグラフを取得します。
     * 
     * @deprecated version1.0.0 優先順位の要素を取り扱わなくなったので現在は使用していない
     *
     * @return array 優先順位を示すグラフ
     */
    public function getPriorityGraph()
    {
        return $this->priority;
    }
}
