<?php

/**
 * class Employee
 * 
 * 従業員情報に関するメソッド
 */
class Employee
{
    /**
     * 従業員データ
     *
     * @var int     $id             ID番号
     * @var string  $name           氏名
     * @var string  $contactInfo    連絡先
     * @var string  $employmentType 契約形態
     * @var array   $preferredShift 希望シフト
     */
    private $id;
    private $name;
    private $contactInfo;
    private $employmentType;
    private $preferredShift;

    public function __construct($id, $name, $contactInfo, $employmentType)
    {
        $this->id = $id;
        $this->name = $name;
        $this->contactInfo = $contactInfo;
        $this->employmentType = $employmentType;
        $this->preferredShift = [];
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * 従業員のシフト希望を取得する
     *
     * @return array シフト希望
     */
    public function getPreferredShift()
    {
        return $this->preferredShift;
    }

    /**
     * 従業員のシフト希望をセットする
     *
     * @param int   $preferredShift シフト希望
     * @param int   $priority       優先順位
     */
    public function setPreferredShift($preferredShift, $priority)
    {
        $this->preferredShift[] = ["shift_id" => $preferredShift, "priority" => $priority];
    }
}
