<?php

/**
 * User class
 * 
 * ユーザー情報の管理に関するメソッド
 */
class User
{
    /**
     * ランダムな7桁の数値を生成する
     *
     * @param Database $database    Database クラスのインスタンス
     * 
     * @return int|false    生成された数値 または false
     */
    public function generateUnique7DigitNumber(Database $database)
    {
        $maxAttempts = 99; // 生成試行の最大回数
    
        for ($i = 0; $i < $maxAttempts; $i++) {
            $randomNumber = random_int(1000000, 9999999); // 7桁のランダムな数字
    
            // データベース中に重複がない場合は生成した数字を返す
            $duplicate = $database->DuplicateIdCheck($randomNumber);
            if ( !$duplicate ) return $randomNumber;
        }
    
        // 最大試行回数を超えた場合はエラー処理などを行う
        return false;
    }

    /**
     * 適切なパスワードであるかを確認する
     *
     * @param string $password  パスワード
     * 
     * @return true|false   適切な場合 true をそうでない場合 false
     */
    public function checkForProperPasswords($password)
    {
        // パスワードの最低桁数
        $numberOfDigit = 7;
        if (strlen($password) < $numberOfDigit) {
            return false;
        }

        // 半角英数の正規表現パターン
        $pattern = '/^[a-zA-Z0-9]+$/';
        $resultMatch = preg_match($pattern, $password);
        if ($resultMatch !== 1) return false;

        return true;
    }
}
