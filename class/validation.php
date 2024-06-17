<?php

class Validation
{
    /**
     * 日付と時間を含む datetime 文字列の入力確認
     * YYYY-mm-dd hh:mm:ss に対応
     * YYYY-mm-ddThh:mm:ss には対応していない
     *
     * @param string $dateTimeString 日付と時間を含む文字列
     * 
     * @return true|false 有効な文字列の場合 true または無効な文字列の場合 false を返す
     */
    private function validationDateTime($dateTimeString)
    {
        // 日付と時間の形式を確認する
        $isCorrectFormat = preg_match("/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/", $dateTimeString);
        if ( !$isCorrectFormat ) {
            throw new InvalidArgumentException("無効な形式です -> $dateTimeString");
        }

        // 日付と時間を分割
        list($dateStr, $timeStr) = explode(" ", $dateTimeString);

        // 日付の妥当性をチェックする
        $isCorrectDate = DateTime::createFromFormat('Y-m-d', $dateStr);
        if ( !$isCorrectDate ) {
            throw new InvalidArgumentException("無効な日付です -> $dateTimeString");
        }

        // 時間の妥当性をチェックする
        $isCorrectTime = strtotime($timeStr);
        if ( !$isCorrectTime ) {
            throw new InvalidArgumentException("無効な時間です -> $dateTimeString");
        }

        return $dateTimeString;
    }


    /**
     * 任意のメソッドでバリデーションチェックを行う
     *
     * @param string $methodName メソッド名
     * @param string $validationString バリデーションチェックする文字列
     * 
     * @return true|false 正しい文字列の場合 true または無効な文字列の場合 false を返す
     */
    public function validationCheck($methodName, $validationString)
    {
        try {
            $validationString = call_user_func(array($this, $methodName), $validationString);
            
            return true;

        } catch (InvalidArgumentException $e) {
            echo $e->getMessage();
            return false;
        }
    }
}