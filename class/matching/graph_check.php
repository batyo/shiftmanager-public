<?php

/**
 * class GraphCheck
 * 
 * マッチング結果の偏りを確認する
 */
class GraphCheck
{
    /**
     * グラフ調整の必要性を判断する
     * 
     * 調整が勧められる場合は 配列 を返し、そうでない場合は false を返す
     *
     * @param Database  $database               class Database
     * @param array     $matchedGraph           マッチング後のグラフ
     * @param int       $employeeCount          従業員数
     * @param int       $shiftCount             シフト数
     * @param float     $occupancyRateLimit     占有率上限 (判断基準)
     * 
     * @return array|false
     */
    public function graphAdjustment($database, $matchedGraph, $employeeCount, $shiftCount, $occupancyRateLimit = 0.5)
    {
        /** 
         * @var array|int 同じ条件のシフト数とその従業員ペア
         * ["employee_pair" => (array<int>) [従業員ID, 比較対象の従業員ID], "same_shift_count" => (int) 同条件のシフト数]
         */
        $employeePairAndSameCount = $this->searchSameConditionEmployee($database, $employeeCount);
        
        /** @var array シフト占有率 ["node_number"=> (int) 従業員ID, "occupancy_rate"=> (float) シフト占有率] */
        $occupancyRate = $this->shiftOccupancyCalculation($matchedGraph, $employeeCount, $shiftCount);

        $adjustmentHint = ["same_count" => $employeePairAndSameCount, "shift_occupancy" => $occupancyRate];

        if ($occupancyRate["occupancy_rate"] >= $occupancyRateLimit) return $adjustmentHint;
        return false;
    }

    /**
     * getSamePrefereceCount() で取得した配列のインデックス値から該当する情報を持つ従業員IDを取得する
     *
     * @param int   $examineKey     調べるキー (index値)
     * @param array $sameShiftCount getSamePrefereceCount()の返り値
     * @param int   $employeeCount  従業員数
     * 
     * @return int|null 従業員ID 見つからない場合は null
     * 
     * @see "./database.php" Database::getSamePrefereceCount()
     * matching_algorithm_ver2.0 2024/01/30 時点
     */
    protected function sameShiftCountKeyToEmployeeId($examineKey, $sameShiftCount, $employeeCount)
    {
        $employeeId = 1;

        $rightFormula = $employeeCount - 1; // 右辺式
        $currentKey = 0; // 
        while ($currentKey != count($sameShiftCount)) {
            // 従業員IDの更新
            if ($currentKey == $rightFormula) {
                // 右辺式の更新
                $rightFormula += $rightFormula - 1;
                $employeeId++;
            }

            // 調べるキー値が現在のキー値の場合、従業員IDを返す
            if ($currentKey == $examineKey) return $employeeId;

            $currentKey++;
        }
    }

    /**
     * 同じ条件を持つ従業員とその程度を返す
     *
     * @param Database  $database       class Database のインスタンス
     * @param int       $employeeCount  従業員数
     * 
     * @return array
     */
    protected function searchSameConditionEmployee($database, $employeeCount)
    {
        $sameShiftCount = $database->getSamePrefereceCount();

        // 同じ条件を持つ従業員がいない場合
        if (empty($sameShiftCount)) {
            
            $employeePairAndSameCount = [
                "employee_pair" => ["none", "none"],
                "same_shift_count" => 0
            ];

            return $employeePairAndSameCount;
        }

        // 同じシフトの数が最も大きかった値
        $maxSameShiftCount = max(array_column($sameShiftCount, 'same_shift_count'));

        // $maxSameShiftCount が格納されていたキー値 (インデックス値)
        $key = array_search($maxSameShiftCount, array_column($sameShiftCount, 'same_shift_count'));

        $mainEmployeeId = $this->sameShiftCountKeyToEmployeeId($key, $sameShiftCount, $employeeCount); // 比較に使った従業員ID
        $compareTargetEmployeeId = $sameShiftCount[$key]['employee_id']; // 比較した対象の従業員ID

        // 比較した従業員ペアと同じシフト数
        $employeePairAndSameCount = [
            "employee_pair" => [$mainEmployeeId, $compareTargetEmployeeId],
            "same_shift_count" => $maxSameShiftCount
        ];

        return $employeePairAndSameCount;
    }

    /**
     * シフト占有率の計算
     * 
     * マッチングしたシフト数が最も多い従業員のシフト占有率を計算するメソッド
     *
     * @param array $matchedGraph   マッチング後のグラフ
     * @param int   $employeeCount  従業員数
     * @param int   $shiftCount     シフト数
     * 
     * @return int|float シフト占有率
     */
    protected function shiftOccupancyCalculation($matchedGraph, $employeeCount, $shiftCount)
    {
        $matchingShiftCount = 0; // マッチングしたシフトの総数
        /** @var array<int> 従業員ごとのマッチング数 [ [ 従業員ノード番号 => マッチング数 ], ... ] */
        $employeeMatchingCount = array_fill(1, $employeeCount, 0);

        for ($shift = 1; $shift <= $shiftCount; $shift++) {
            for ($employee = 1; $employee <= $employeeCount; $employee++) {
                // 増加パスがない場合はスキップ
                if ($matchedGraph[$shift+$employeeCount][$employee] == 0) continue;
                $employeeMatchingCount[$employee] += 1;
                $matchingShiftCount++;
            }
        }

        $heighestMatch = max($employeeMatchingCount); // 最も多いマッチング数
        $employeeNode = array_search($heighestMatch, $employeeMatchingCount); // 従業員ノード番号

        $occupancyRate = 0; // シフト占有率
        if ($heighestMatch != 0 && $matchingShiftCount != 0) {
            $occupancyRate = $heighestMatch / $matchingShiftCount;
        }

        $shiftOccupancyRate = ["node_number"=>$employeeNode, "occupancy_rate"=>$occupancyRate];

        return $shiftOccupancyRate;
    }
}
