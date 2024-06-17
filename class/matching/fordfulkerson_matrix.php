<?php

/**
 * フォードファルカーソン法
 */
class FordFulkerson
{
    protected $graph;   // グラフの隣接行列
    protected $visited; // ノードの訪問状態

    /**
     * FordFulkerson コンストラクタ
     *
     * @param array $graph グラフの隣接行列
     */
    public function __construct($graph)
    {
        $this->graph = $graph;
        $this->visited = array_fill(0, count($graph), false);
    }

    /**
     * 最大フローを計算するメソッド
     *
     * @param int $startNode 始発点のノード
     * @param int $endNode   終着点のノード
     *
     * @return int 最大フローの値
     */
    public function maxFlow($startNode, $endNode)
    {
        $maxFlow = 0; // 最大流量

        while ($path = $this->findPath($startNode, $endNode)) {
            
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
     * 増加パスを見つけるメソッド
     *
     * @param int $startNode 始発点のノード
     * @param int $endNode   終着点のノード
     *
     * @return array|null 増加パスを表すノードの配列（見つからない場合はnull）
     */
    protected function findPath($startNode, $endNode)
    {
        $searchReservedNode = [$startNode]; // 探索予定のノードリスト
        $path = []; // 増加パス

        $this->visited = array_fill(0, count($this->graph), false);
        $this->visited[$startNode] = true;

        while (!empty($searchReservedNode)) {
            // 探索するノード
            $current = array_shift($searchReservedNode);

			// 隣接ノードを探索する
            foreach (array_keys($this->graph[$current]) as $neighbor) {
				// 未訪問かつ増加パスの隣接ノードである場合
                if (!$this->visited[$neighbor] && $this->graph[$current][$neighbor] > 0) {
                    $path[$neighbor] = $current; // 増加パスを格納
                    $this->visited[$neighbor] = true; // 訪問済みとする

                    // 終着ノードに到達した場合は増加パスを返す
                    if ($neighbor == $endNode) {
                        return $path;
                    }

					// 隣接ノードを探索予定ノードにする
                    $searchReservedNode[] = $neighbor;
                }
            }
        }

		// 増加パスが見つからない場合
        return null;
    }

    /**
     * グラフ取得
     *
     * @return array
     */
    public function getGraph()
    {
        return $this->graph;
    }
}
