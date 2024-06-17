<?php
require_once(__DIR__. "\\traitsFacade.php");

/**
 * class InputDatabase
 * 
 * データベースへの登録操作を行うクラス
 * トレイトメソッドをファサードパターンで管理する TraitsFacade クラスを継承
 * 
 * @todo Employees テーブルの name カラム削除に伴うメソッドの修正
 */
class InputDatabase extends TraitsFacade
{
    /**
     * コンストラクター
     *
     * @param PDO $pdo PDOオブジェクト
     */
    public function __construct($pdo)
    {
        parent::__construct($pdo);
    }

    /**
     * データベースを切断する
     */
    public function closeConnection()
    {
        $this->connection = null;
    }
}
