<?php

require_once("fordfulkerson_matrix.php");

/**
 * class TwoPartMatching
 * 
 * フォードファルカーソン法を応用した二部グラフマッチングに関するクラス
 * 「従業員」と「シフト」のマッチングを行うメソッド
 */
class TwoPartMatching extends FordFulkerson
{
    /**
     * @var array   $priority   優先順位情報を保持した配列
     */
    private $priority;

    public function __construct($graph, $priority)
    {
        parent::__construct($graph);
        $this->priority = $priority;
    }

    /**
     * 従業員ノードを条件に従って並び替えるメソッド
     * 
     * 1. シフトの選択肢が少ない従業員のノードから探索するようにする
     * 2. 優先順位の高いノードにノード番号が小さいシフトを持つ従業員から探索するようにする
     *
     * @param int $employeeCount    従業員数
     * @param int $shiftCount       シフト数
     * 
     * @return array 探索順に従業員番号が格納された配列
     */
    public function rearrangingNode($employeeCount, $shiftCount)
    {
        // シフトの選択肢が少ない従業員のノードから探索するようにする
        $choiceCount_list = [];
        for ($employee = 1; $employee <= $employeeCount; $employee++) {
            $choiceCount = 0; // 従業員が持つ選択肢の総数
            for ($shift = 1; $shift <= $shiftCount; $shift++) {
                if ($this->graph[$employee][$shift+$employeeCount] == 1) $choiceCount++;;
                if ($shift == $shiftCount) $choiceCount_list[$employee] = $choiceCount;
            } 
        }

        asort($choiceCount_list);
        
        // 優先順位の高いノードにノード番号が小さいシフトを持つ従業員から探索するようにする
        $maxChoiceCount = max($choiceCount_list);
        $minChoiceCount = min($choiceCount_list);

        // 同じ選択肢数を持つノードごとに配列内でまとめる
        $setChoiceCount = [];
        $currentCount = 0;
        foreach ($choiceCount_list as $employee => $count) {
            // 優先順位が一番高いシフトのノード番号
            $mostPriprityNode = array_search(1, $this->priority[$employee]);

            if ($count != $currentCount) $currentCount = $count;

            $setChoiceCount[$currentCount][$employee] = [];
            array_push($setChoiceCount[$currentCount][$employee], $mostPriprityNode);
        }

        // 同じ選択肢数をもつ配列ごとに昇順ソートする
        $lastSort = [];
        for ($i = 0; $i+$minChoiceCount <= $maxChoiceCount; $i++) {
            asort($setChoiceCount[$i+$minChoiceCount]);

            foreach ($setChoiceCount[$i+$minChoiceCount] as $employee => $shift) {
                $lastSort[$employee] = [];
                array_push($lastSort[$employee], $shift[0]);
            }
        }

        // 探索順に並び替えられた従業員番号
        $priorityOrder = array_keys($lastSort);

        // 並び替えた従業員に合わせたシフトをセットする
        $saveShift = [];
        for ($i = 1; $i <= count($priorityOrder); $i++) {
            $employeeNumber = $priorityOrder[$i-1];
            $saveShift[$i] = $this->graph[$i]; // 上書きする前に保存
            
            // シフト情報取得
            $shiftInfo;
            if (isset($saveShift[$employeeNumber])) $shiftInfo = $saveShift[$employeeNumber];
            if ( !isset($saveShift[$employeeNumber]) ) $shiftInfo = $this->graph[$employeeNumber];
            
            $this->graph[$i] = $shiftInfo;
        }
        
        return $priorityOrder;
    }

    /**
     * 最大フローを計算するメソッド
     *
     * @param int   $startNode        始発点のノード
     * @param int   $endNode          終着点のノード
     * @param int   $employeeCount    従業員数
     * @param array $sortedEmployee   従業員を優先順にソートした配列
     *
     * @return int 最大フローの値
     */
    public function maxMatch($startNode, $endNode, $employeeCount, $sortedEmployee)
    {
        $maxFlow = 0; // 最大流量

        while ($path = $this->findPriorityPath($startNode, $endNode, $employeeCount, $sortedEmployee)) {
            
            $INF = PHP_INT_MAX; // 無限
            $minCapacity = $INF; // 最小容量
            
            $current = $endNode; // 現在のノード

            // 増加パス上で最小容量を見つける
            while ($current !== $startNode) {
                $to_prev = $path[$current]; // 1つ前のノード
                $from_prevCapacity = $this->graph[$to_prev][$current]; // 1つ前のノードからの容量
                $minCapacity = min($minCapacity, $from_prevCapacity);
                $current = $to_prev; // 1つ前のノードへ進む(戻る)
            }

            // 最小容量を最大フローに加え、残余グラフを更新する
            $maxFlow += $minCapacity;
            $current = $endNode;

			// 残余グラフを更新
            while ($current !== $startNode) {
                $to_prev = $path[$current];

                // 順方向の辺の容量を更新 (進んだ容量分減らす)
                $this->graph[$to_prev][$current] -= $minCapacity;

                // 逆向きの辺の容量を更新（存在しない場合は新たに追加）
                if (!isset($this->graph[$current][$to_prev])) {
                    $this->graph[$current][$to_prev] = 0;
                }
				// 進んだ容量分増やす
                $this->graph[$current][$to_prev] += $minCapacity;

                $current = $to_prev;
            }

            // ノードの訪問状態をリセット
            $this->visited = array_fill(0, count($this->graph), false);
        }

        return $maxFlow;
    }

    /**
     * シフトの優先順位を考慮して増加パスを見つけるメソッド
     *
     * @param int   $startNode        始発点のノード
     * @param int   $endNode          終着点のノード
     * @param int   $employeeCount    従業員数
     * @param array $sortedEmployee   従業員を優先順にソートした配列
     *
     * @return array|null 増加パスを表すノードの配列（見つからない場合はnull）
     */
    public function findPriorityPath($startNode, $endNode, $employeeCount, $sortedEmployee)
    {
        $searchReservedNode = [$startNode]; // 探索予定のノードリスト
        $path = []; // 増加パス
        
        $this->visited = array_fill(0, count($this->graph), false);
        $this->visited[$startNode] = true;

        while (!empty($searchReservedNode)) {
            // 探索するノード
            $current = array_shift($searchReservedNode);
            /**
             * 隣接ノードを探索する
             * 
             * 従業員ノードを探索する場合は優先順位の高いシフトノードから探索する
             */
            if ($current >= 1 && $current <= $employeeCount) {
                // 現在のノードが持つ優先グラフを探索(優先)順にソート
                $priorityKey = $sortedEmployee[$current-1];
                asort($this->priority[$priorityKey]);
                
                // 優先グラフを参照して隣接ノードを探索する
                foreach (array_keys($this->priority[$priorityKey]) as $neighbor) {
                    // 終着ノードに到達した場合、増加パスを返す
                    if ($this->nodeSearch($endNode, $searchReservedNode, $path, $neighbor, $current)) {
                        return $path;
                    }
                }
            } else {
                // グラフを参照して隣接ノードを探索する
                foreach (array_keys($this->graph[$current]) as $neighbor) {
                    if ($this->nodeSearch($endNode, $searchReservedNode, $path, $neighbor, $current)) {
                        return $path;
                    }
                }
            }
        }

		// 増加パスが見つからない場合
        return null;
    }

    /**
     * グラフ中のノードを探索するメソッド
     *
     * @param int   $endNode            終着ノード
     * @param array $searchReservedNode 探索予定のノード (参照渡し)
     * @param array $path               増加パス (参照渡し)
     * @param int   $neighbor           隣接ノード番号
     * @param int   $current            現在のノード番号
     * 
     * @return true|null 終着ノードに到達した場合は true 到着しない場合は null
     */
    private function nodeSearch($endNode, &$searchReservedNode, &$path, $neighbor, $current)
    {
        // 未訪問かつ増加パスの隣接ノードである場合
        if (!$this->visited[$neighbor] && $this->graph[$current][$neighbor] > 0) {
            $path[$neighbor] = $current; // 増加パスを格納
            $this->visited[$neighbor] = true; // 訪問済みとする

            // 終着ノードに到達した場合
            if ($neighbor == $endNode) {
                return true;
            }

            // 隣接ノードを探索予定ノードにする
            $searchReservedNode[] = $neighbor;
        }
    }
}
