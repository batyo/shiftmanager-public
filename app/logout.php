<?php

// ログアウト(セッションを破棄する)
session_start();
// セッションを空にする
$_SESSION = array();

// クッキー情報がある場合
if (isset($_COOKIE[session_name()]) == true){
    // PC側のセッションIDをクッキーから削除する
    setcookie(session_name(), "", time() - 42000,"/");
}

// セッションを破棄する
session_destroy();

?>

<!DOCTYPE html>

<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>ログアウト完了</title>
        <link rel="stylesheet" href="./public/css/logout.css">
    </head>
    <body>
        <div class="container">
            <h3>ログアウトしました</h3>
            <br />
            <a href="./login.html" class="button">ログイン画面へ</a>
        </div>
    </body>
</html>