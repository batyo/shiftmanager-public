# Shift Manager

従業員とシフトをマッチングするシフト管理機能

- 一度作成したシフトの基本情報は変更できない仕様 (主に従業員の混乱を防ぐため) にしているため、シフト情報を変更したい場合は新規シフトを作成して下さい

## メニューについて
### 管理画面 (管理者)
#### 勤怠管理
当日に予定されているシフトの勤怠情報が確認できる。
#### 従業員情報
従業員の情報を確認できる。
- シフト情報参照 => 選択した従業員の希望シフト及び勤務予定のシフトを確認できる
- 従業員追加 => 新規の従業員情報を作成する
- 従業員削除 => 既にある従業員情報を削除する
#### シフト情報
現在予定されているシフトの情報を確認できる。
- シフト追加 => 新規のシフト情報を作成する
- シフト削除 => 既にあるシフト情報を削除する
- アーカイブ => 終了したシフトの情報を削除しアーカイブに保存する
#### マッチング情報
マッチングしているシフトの情報を確認できる。
- 解除 => 選択したシフトと特定の従業員の割り当てを取り消す
- リセット => 全てのシフトと従業員の割り当てを取り消す
- アーカイブ => 過去の割り当て情報を確認する
#### マッチングの実施
従業員とシフトをマッチングさせる。
- マッチング実行 => アルゴリズムに基づいて自動でマッチングを行う
- 手動でマッチングする => 自らが選択してマッチングを行う
#### マッチングデータのダウンロード
過去のマッチングデータをダウンロードする。
#### ログアウト
ログアウトする。

### 管理画面 (従業員)
#### 勤怠報告
出勤するシフトの出勤報告を行う。
#### 勤務予定
出勤する予定のシフトの情報を確認できる。
#### 希望シフトを提出する
出勤したいシフトについて出勤希望を提出する。
#### 提出した希望シフトを変更する
提出済みの希望を取り下げる。
#### ログアウト
ログアウトする。

## ファイルについて
### app
- login.html ログインページ (to main)
- login_check.php ログインフォームの入力チェック
- logout.php ログアウト実行
- error.php エラー画面
- main.php  管理画面メインページ

### app/public/css
スタイルシート
- attendance_info.css           attendance_info.php のスタイルシート
- attendance_report.css         attendance_report.php のスタイルシート 
- employee_info.css             employee_info.php のスタイルシート
- employee_user_add.css         employee_user_add.php のスタイルシート
- error.css                     error.php のスタイルシート
- fullcalendar_template.css     fullcalendarライブラリを使用したページのスタイルシート
- login.css                     login.html のスタイルシート
- main.css                      main.php のスタイルシート
- matching_archive.css          matching_archive.php のスタイルシート
- matching_download.css         matching_download.php のスタイルシート
- matching_execution.css        matching_execution.php のスタイルシート
- matching_preparation.css      matching_preparation.php のスタイルシート
- shift_add.css                 shift_add.php のスタイルシート
- shift_delete.css              shift_delete.php のスタイルシート
- template-container-center.css テンプレートスタイル
- template.css                  テンプレートスタイル
- user_delete.css               user_delete.php のスタイルシート

### app/public/javascript
JavaScriptファイル
- async_select_value.js   Fetch API を用いた非同期処理
- calendar_limit.js       `<select>`タグで選択できる年月日が制限されたプルダウンメニューを生成する
- calendar_pulldown.js    `<select>`タグによる年月日のプルダウンメニューを生成する
- prevent_empty_submit.js `<form>`の空送信チェック

### apppublic/javascript/fullcalendar
FullCalendarライブラリを使用したカレンダーを描画するスクリプト(JavaScript)
- employee_shift_info.js        employee_shift_info.php で使用
- main.js                       matching_info.php で使用
- matching_manual_execution.js  matching_manual_execution.php で使用
- preference_shift_delete.js    preference_shift_delete.php で使用
- preference_shift_submit.js    preference_shift_submit.php で使用
- shift_info.js                 shift_info.php で使用

### app/user
ユーザー情報の追加・削除に関するファイル
- employee_user_add.php         従業員情報を追加する
- user_delete.php               ユーザー情報を削除する
- userData_operation_branch.php employee_info.php の`<form>`からのブランチ処理

### app/menu/admin
管理権限者のメニューに関するファイル
- asyncResponse.php               Fetch API から呼び出すファイル (FullCalendar用のイベントデータ作成)
- attendance_info.php             当日の出勤情報一覧を表示する
- employee_info.php               従業員情報一覧および、userData_operation_branch.php へのフォームボタンを表示する
- employee_shift_info.php         従業員のシフト情報を表示する
- matching_archive.php            指定した期間中のシフト情報を表示する
- matching_delete.php             matching_info.php で選択されたマッチングを解除する
- matching_download.php           過去のマッチング情報をダウンロードする
- matching_execution.php          アルゴリズムによる従業員とシフトのマッチングを実行する
- matching_info.php               マッチング情報および、matchin_operation_branch.php へのフォームボタンを表示する
- matching_manual_execution.php   matching_manual_preparation.php で選択されたマッチングを実行する
- matching_manual_preparation.php マッチングさせる従業員とシフトの組み合わせを選択する
- matching_operation_branch.php   matching_info.php で選択されたボタンに応じて、マッチングの解除、全解除、アーカイブ情報閲覧を実行するパスへ分岐させる
- matching_preparation.php        マッチングさせる従業員を選択し自動または手動でマッチング実行するスクリプトへ移行する
- matching_reset.php              全てのマッチングを解除する
- matching_result.php             決定されたマッチング情報をデータベースに登録し、結果画面を表示する
- past_shift_delete.php           過去のシフト情報をデータベースに登録し、結果画面を表示する
- shift_add.php                   シフト情報を追加する
- shift_delete.php                shift_info.php で選択されたシフト情報を削除する
- shift_info.php                  シフト情報一覧および、shift_operation_branch.php へのフォームボタンを表示する
- shift_operation_branch.php      shift_info.php で選択されたボタンに応じて、シフトの追加、削除、アーカイブ化を実行するパスへ分岐

### app/menu/employee
従業員のメニューに関するファイル
- assingment_shift_info.php         当日の出勤予定のシフト情報および、出勤報告ボタンを表示する
- attendance_report.php             出勤報告を行う
- preference_shift_delete_done.php  選択された提出済みの希望シフト届の取り消しを実行する
- preference_shift_delete.php       取り消したい提出済みの希望シフト届を選択する
- preference_shift_registration.php 出勤を希望するシフトを選択する
- preference_shift_submit.php       選択されたシフトの希望シフト届を提出する

### class
クラスに関するファイル
- general.php     汎用メソッドを持つクラス
- validation.php  バリデーションに関するクラス

### class/database
データベース操作に関するファイル
- database_input.php  データベースへ書き込むクラス
- database.php        データベースを読み込むクラス
- traitsFacade.php    traitInput ディレクトリ内のファイルをファサードパターンによる管理するクラス

### class/database/traitInput
各テーブルの書き込み操作を行うファイル
- archives_input.php        archive_calendar_shifts テーブルの書き込みに関するトレイト
- assignments_input.php     employee_shift_assignments テーブルの書き込みに関するトレイト
- attendances_input.php     employee_attendances テーブルの書き込みに関するトレイト
- calendar_shifts_input.php calendar_shifts テーブルの書き込みに関するトレイト
- employees_input.php       employees テーブルの書き込みに関するトレイト
- preferences_input.php     employee_shift_preferences テーブルの書き込みに関するトレイト
- users_input.php           users テーブルの書き込みに関するトレイト

### class/database/traitOutput
各テーブルの読み込み操作を行うファイル
- archives.php        archive_calendar_shifts テーブルの読み込みに関するトレイト
- assignments.php     employee_shift_assignments テーブルの読み込みに関するトレイト
- attendances.php     employee_attendances テーブルの読み込みに関するトレイト
- calendar_shifts.php calendar_shifts テーブルの読み込みに関するトレイト
- employees.php       employees テーブルの読み込みに関するトレイト
- preferences.php     employee_shift_preferences テーブルの読み込みに関するトレイト
- users.php           users テーブルの読み込みに関するトレイト

### class/matching
従業員とシフトのマッチングに関するファイル
- fordfulkerson_matrix.php  Ford-Fulkerson法に基づくマッチングアルゴリズムのクラス
- graph_adjsutment.php      マッチングアルゴリズムに使用するグラフを調整するクラス
- graph_check.php           マッチング後のグラフを確認するクラス
- matching_graph.php        Ford-Fulkerson法でのマッチングに使用されるグラフを作成するクラス
- twoPart_matching.php      Ford-Fulkerson法を応用した二部グラフマッチングに関するクラス

### class/user
ユーザー情報の管理に関するファイル
- user.php  ユーザー情報の作成・管理するクラス

### log
ログ情報に関するファイル
- acces_log.txt アクセス情報が保存される
- error_log.txt エラー情報が保存される

### user
初期設定時に使用するファイル
- admin_add.php ログインするための管理権限者を追加する

### vendor/fullcalendar
FullCalendar ライブラリに関するファイル
カレンダーを描画する ([fullcalendar](https://github.com/fullcalendar))

### vendor/vlucas
PHP dotenv ライブラリに関するファイル
環境変数を読み込む ([vlucas/phpdotenv](https://github.com/vlucas/phpdotenv))
