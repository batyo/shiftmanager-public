# データベースの説明

## users
ユーザー情報を管理するテーブル

### 構造
|Field|Type|Null|Key|Default|Extra|
|----|----|----|----|----|----|
|id|int(11)|NO|PRI|NULL|auto_increment|
|user_id|varchar(20)|NO||NULL||
|user_name|varchar(255)|NO||NULL||
|employee_id|int(11)|YES|MUL|NULL||
|authority|varchar(50)|NO||NULL||
|password|varchar(255)|NO||NULL||

### オプション
CONSTRAINT `users_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`)

### カラムの説明
**user_id**
- ユーザーID

**user_name**
- ユーザーの名前
- 従業員名も兼ねる

**employee_id**
- 従業員ID
- 従業員のみがもつ

**authority**
- 権限
- "administrator" や "employee"

**password**
- パスワード
- ハッシュ化して登録する

## employees
従業員を管理するテーブル

### 構造
|Field|Type|Null|Key|Default|Extra|
|----|----|----|----|----|----|
|id|int(11)|NO|PRI|NULL|auto_increment|
|contact_info|varchar(255)|NO||NULL||
|employee_type|varchar(255)|NO||NULL||
|status|enum(`standby`, `ready`)|NO||ready||

### レガシー
|Field|Type|Null|Key|Default|Extra|
|----|----|----|----|----|----|
|preferred_shift|json|YES||NULL||

### カラムの説明
**contact_info**
- 連絡先 (email)

**employee_type**
- 契約形態

**status**
- マッチング準備のステータス
- `standby` or `ready` のみを値として受け付ける
- `standby` ... 待機中 (マッチングに参加しない)
- `ready` ... 準備 (マッチングに参加する)
- 値の名前を変えた方が良いかもしれない

## calendar_shifts
シフト情報を管理するテーブル

### 構造
|Field|Type|Null|Key|Default|Extra|
|----|----|----|----|----|----|
|id|int(11)|NO|PRI|NULL|auto_increment|
|shift_date|date|NO|MUL|NULL||
|start_date_time|datetime|NO||NULL||
|end_date_time|datetime|NO||NULL||
|shift_name|varchar(255)|NO||NULL||
|available_slots|int(11)|NO||NULL||
|current_assignemnt_count|int(11)|NO||0||

### オプション
UNIQUE KEY `unique_shift_date_name` (`shift_date`,`shift_name`)

### カラムの説明
**shift_date**
- シフトの日付
- shift_name カラムと合わせてシフトの一意性を担保する
- YYYY-mm-dd

**start_date_time**
- シフトの開始する日付時間
- YYYY-mm-dd hh:mm

**end_date_time**
- シフトの終了する日付時間
- YYYY-mm-dd hh:mm

**shift_name**
- シフト名
- shift_date カラムと合わせてシフトの一意性を担保する

**available_slots**
- シフトの空き枠数

**current_assignment_count**
- シフトに割り当てられている従業員の数

## employee_shift_preferences
従業員の希望シフトを管理するテーブル

### 構造
|Field|Type|Null|Key|Default|Extra|
|----|----|----|----|----|----|
|id|int(11)|NO|PRI|NULL|auto_increment|
|employee_id|int(11)|NO|MUL|NULL||
|shift_date|date|NO||NULL||
|shift_name|varchar(255)|NO||NULL||

### オプション
UNIQUE KEY `unique_employee_shift` (`employee_id`,`shift_date`,`shift_name`)<br>
CONSTRAINT `fk_employee_pref` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`)

### カラムの説明
**employee_id**
- 従業員ID

**shift_date**
- シフトの日付
- shift_name カラムと合わせてシフトの一意性を担保する
- YYYY-mm-dd

**shift_name**
- シフト名
- shift_date カラムと合わせてシフトの一意性を担保する

## employee_shift_assignments
従業員の割り当てシフトを管理するテーブル

### 構造
|Field|Type|Null|Key|Default|Extra|
|----|----|----|----|----|----|
|id|int(11)|NO|PRI|NULL|auto_increment|
|employee_id|int(11)|NO|MUL|NULL||
|shift_date|date|NO||NULL||
|shift_name|varchar(255)|NO||NULL||

### オプション
KEY `fk_employee_assignment` (`employee_id`)<br>
CONSTRAINT `fk_employee_assignment` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`)

### カラムの説明
**employee_id**
- 従業員ID

**shift_date**
- シフトの日付
- shift_name カラムと合わせてシフトの一意性を担保する
- YYYY-mm-dd

**shift_name**
- シフト名
- shift_date カラムと合わせてシフトの一意性を担保する

## employee_attendances
従業員の勤怠情報を管理するテーブル

### 構造
|Field|Type|Null|Key|Default|Extra|
|----|----|----|----|----|----|
|id|int(11)|NO|PRI|NULL|auto_increment|
|assignment_id|int(11)|NO||NULL||
|attendance_status|enum(`comfirmed`,`unconfirmed`)|NO||`unconfirmed`||
|report_arrival_time|time|YES||NULL||
|report_leave_time|time|YES||NULL||

### カラムの説明
**assignment_id**
- 割り当てシフトID

**attendance_status**
- 出勤状況
- 出勤確認を示す `confirmed` と出勤未確認を示す `unconfirmed` からなる

**report_arrival_time**
- 出勤確認時間
- YYYY-mm-dd H:i:s

**report_leave_time**
- 退勤確認時間
- YYYY-mm-dd H:i:s

## archive_calendar_shifts
過去のシフトデータを管理するテーブル

### 構造
|Field|Type|Null|Key|Default|Extra|
|----|----|----|----|----|----|
|id|int(11)|NO|PRI|NULL|auto_increment|
|employee_id|int(11)|NO|MUL|NULL||
|shift_date|date|NO||NULL||
|shift_name|varchar(255)|NO||NULL||

### オプション
CONSTRAINT `fk_archive_calendar` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`)

### カラムの説明
**employee_id**
- シフトに割り当てられていた従業員のID

**shift_date**
- シフトの日付
- YYYY-mm-dd

**shift_name**
- シフト名