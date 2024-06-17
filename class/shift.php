<?php

/**
 * class Shifts
 * 
 * シフト情報に関するメソッド
 */
class Shift
{
    /**
     * シフトデータ
     *
     * @var int     $id             ID番号
     * @var string  $shiftName      シフト名
     * @var string  $periodTime     シフトの期間/時間
     * @var int     $availableSlots シフトの空き枠数
     */
    private $id;
    private $shiftName;
    private $periodTime;
    private $availableSlots;

    public function __construct($id, $shiftName, $periodTime, $availableSlots)
    {
        $this->id = $id;
        $this->shiftName = $shiftName;
        $this->periodTime = $periodTime;
        $this->availableSlots = $availableSlots;
    }
}