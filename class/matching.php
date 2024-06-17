<?php

/**
 * class Matching
 * 
 * マッチング情報に関するメソッド
 */
class Matching {
    /**
     * マッチングデータ
     *
     * @var int     $id ID番号
     * @var int     $employeeId 従業員ID
     * @var int     $shiftId    シフトID
     * @var string  $status     マッチングの状態
     */
    private $id;
    private $employeeId;
    private $shiftId;
    private $status;

    public function __construct($id, $employeeId, $shiftId, $status)
    {
        $this->id = $id;
        $this->employeeId = $employeeId;
        $this->shiftId = $shiftId;
        $this->status = $status;
    }
}