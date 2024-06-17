<?php

require_once("graph_check.php");

/**
 * グラフを調整する
 */
class GraphAdjustment extends GraphCheck
{
    /**
     * 増加パスをカットする再起メソッド
     * 
     * 従業員の条件が等しい場合にシフト配分の偏りを抑制するためのアルゴリズム
     * シフト全体で勤務が決定した従業員 1 人が占める割合が一定以上を超えた場合 (従業員数も考慮) に適用する
     * 
     * @param array $graph          マッチングするグラフ
     * @param int   $searchNode     探索を開始するノード番号
     * @param int   $range          探索範囲
     *
     * @return void
     */
    public function searchToPathCut(&$graph, $searchNode, $range)
    {
        $currentToShift = null; // 現在のノードと接続されたシフトノード
        foreach (array_keys($graph[$searchNode]) as $shiftNode) {
            // 増加パスである場合
            if ($graph[$searchNode][$shiftNode] > 0) {
                // シフトノードを選択
                $currentToShift = $shiftNode;
                break;
            }
        }
        
        // 選択したシフトノードに接続している従業員ノードを探す
        for ($employeeNode = 1; $employeeNode <= $range; $employeeNode++) {
            if ($employeeNode == $searchNode) continue;
            // 選択したシフトノードへの増加パスをカットし、再び探索
            if ($graph[$employeeNode][$currentToShift] > 0) {
                $graph[$employeeNode][$currentToShift] = 0;
                $this->searchToPathCut($graph, $employeeNode, $range);
                break;
            }
        }
    }
}