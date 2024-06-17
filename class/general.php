<?php

/**
 * class General
 * 
 * 全般メソッドの集まり
 */
class General
{
    /**
     * ログイン認証確認
     *
     * @param string $sessionKeyLogin       ログインのセッションキー名
     * @param string $sessionKeyName        ユーザー名のセッションキー名
     * @param string $sessionKeyAuthority   権限名のセッションキー名
     * @param string $backPagePath          飛び先 (ログイン画面)
     * @param string $publicPage            権限に関わらず公開するページ (ホーム画面に適用)
     * @return void
     */
    public function login_authentication_confirmation($sessionKeyLogin, $sessionKeyName, $sessionKeyAuthority, $backPagePath, $publicPage = "main.php")
    {
        session_start();
        session_regenerate_id(true); // セッションIDを変更(セキュリティ対策)

        $scriptName = $_SERVER["SCRIPT_NAME"];
        $pathParts = explode('/', $scriptName);
        $deepestPath = end($pathParts); // 最も深いPath

        $isLogin = isset($_SESSION[$sessionKeyLogin]); // ログインチェック
        $isCorrctAuthority = $sessionKeyAuthority == $_SESSION["authority"]; // 権限チェック
        
        // 現在のページがホーム画面だった場合は権限に関わらず許可する
        if ($publicPage == $deepestPath) $isCorrctAuthority = true;
        
        if ($isLogin && $isCorrctAuthority) {
            echo "ログイン：";
            echo $_SESSION[$sessionKeyName]."<br />";
            echo "<br />";
        }else {
            echo "ログインされていません。<br />";
            echo '<a href="'.$backPagePath.'">ログイン画面へ</a>';
            exit();
        }
    }

    /**
     * Shift_JIS形式のCSVファイルを作成する
     *
     * @param string $csvFormatVariable CSVのフォーマットに合わせたカンマや改行文字で区切られた文字列
     * @param string $filePath          ファイルを作成する場所 (ファイルのパス)
     * 
     * @return void
     */
    public function create_csv_file($csvFormatVariable, $filePath)
    {
        $createFile = fopen($filePath, "w");

        // 改行文字の前にHTMLの改行タグを挿入する
        $csv = nl2br($csvFormatVariable);
        // 文字エンコーディングをUTF-8からShift_JISに変換
        $csv = mb_convert_encoding($csv, "SJIS", "UTF-8");

        fputs($createFile, $csv);
        
        fclose($createFile);
    }

    /**
     * POSTメソッドで受け取ったデータをサニタイズする
     *
     * @param array $postData POSTメソッドで受け取ったデータ
     * 
     * @return array サニタイズされたデータを持つ配列
     */
    public function sanitize($postData) {
        foreach($postData  as $key => $value) {
            $sanitizedData[$key] = htmlspecialchars($value, ENT_QUOTES, "UTF-8");
        }

        return $sanitizedData;
    }

    /**
     * エラーハンドラ
     * 
     * set_error_handler()のコールバック関数として使用する
     * set_error_handler('error_logger')
     * 
     * ハンドラとして指定する際に引数を指定する必要は無い
     *
     * @param int       $error      エラー番号
     * @param string    $massage    エラーメッセージ
     * @param string    $fileName   エラーファイル名
     * @param int       $line       ライン番号
     */
    public function error_logger($error, $massage, $fileName, $line)
    {
        $nowDate = date("Y_m_d__H_i_s");

        $curPath = $_SERVER["SCRIPT_NAME"];
        $dirPath = dirname($curPath);
        $pathParts = explode("/", $dirPath);
        $targetDir = ["shiftmanager"];

        // shiftmanager ディレクトリの相対位置を取得
        $relativePlace = null;
        for ($i = count($pathParts) - 1; $i >= 0; $i--) {
            // 発見
            if (in_array($pathParts[$i], $targetDir)) {
                // -1 は dirname で /shiftmanager/web/... という形で取得するので
                // $pathParts が [0] => "" [1] => shiftmanager ... という様に
                // 余分な空白の要素の数だけ要素数から差し引く必要がある
                $relativePlace = (count($pathParts)-1) - $i;
                break;
            }
        }
        
        // shiftmanager ディレクトリ取得の成否
        $isSuccesGetDir = $relativePlace != null;

        // shiftmanager ディレクトリを取得できた場合
        if ($isSuccesGetDir) {

            // log ディレクトリの相対位置を取得
            $logFileRelativePath = "./"; 
            while ($relativePlace != 0) {
                $logFileRelativePath .= "../";
                $relativePlace--;
            }

            $logFile = $logFileRelativePath . "log/error_log.txt"; // ログファイルのパス
            $renameLogFile = $logFileRelativePath . "log/error_log".$nowDate.".txt"; // リネームしたファイル名

            // ファイルサイズが10Kを超えた場合ローテート処理する
            if (filesize($logFile) > 10240){
                rename($logFile, $renameLogFile);
                clearstatcache(); // ファイルのステータスのキャッシュをクリアする
            }

        }

        // ログ内容
        $logMassage = "error(".$error."):".$massage." file:".$fileName." line:".$line." date:".$nowDate."\n";

        // ログをPHP標準送り先に送る場合は第2引数を 0 にする
        $error_log_arg2 = 3;
        if (is_null($relativePlace)) $error_log_arg2 = 0;

        error_log($logMassage, $error_log_arg2, $logFile);

        // E_NOTICEの判定がE_ERRORやE_WARNINGに及ぶ現象があるため
        if ($error === E_NOTICE) return;
        if ($error === E_WARNING) return;

        // 重大な実行時エラー・警告エラー等のNOTICEエラー以外の場合はエラーページへリダイレクトする
        header("Location: ./error.php");
        exit();
    }

    /**
     * アクセスログ
     *
     * @param string $logName ログ名
     * @param string $logPath ログのパス
     * 
     * @return void
     */
    public function acces_log($logName, $logPath)
    {
        $fileInfo = pathinfo($logPath);
        $fileName = $fileInfo["filename"]; // ファイル名
        $fileExtension = $fileInfo["extension"]; // 拡張子

        $pathDir = dirname($logPath); // ディレクトリ部分

        // リネーム時のファイル名
        $nowDate = date("Y_m_d__H_i_s");
        $renameLogPath = $pathDir . "/" . $fileName . $nowDate . "." . $fileExtension;

        // ファイルサイズが10Kを超えた場合ローテート処理する
        if (filesize($logPath) > 10240){
            rename($logPath, $renameLogPath);
            clearstatcache(); // ファイルのステータスのキャッシュをクリアする
        }

        $time = date("Y/m/d H:i");
        $ip = getenv("REMOTE_ADDR");
        $host = getenv("REMOTE_HOST");
        $referer = getenv("HTTP_REFERER");
        if ($referer == "") {
            $referer = "none";
        }
        error_log($logName." -- ".$time.' -- '.$ip.' -- '.$host.' -- '.$referer."\n",3, $logPath);
    }

    /**
     * spl_autoload_register()のコールバック関数
     *
     * @param string $dirPath   読み込むファイルがあるディレクトリ名
     */
    public function class_autoload($dirPath)
    {
        $filePath = glob($dirPath."/*.*");
        
        for ($i = 0; $i < count($filePath); $i++) {
            require_once $filePath[$i];
        }
    }

    // tukawann
    public function trait_autoload($dirPath)
    {
        $filePath = glob($dirPath."/*.*");

        $trait = get_declared_traits();
        
        for ($i = 0; $i < count($filePath); $i++) {
            $fileDir = explode("/", $filePath[$i]);
            $fileName = pathinfo((end($fileDir)), PATHINFO_FILENAME);
            if (in_array($fileName, $trait)) continue;
            require $filePath[$i];
        }
    }
}
