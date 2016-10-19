<?php

require_once('BaseMModels.class.php');

class Class_Model_MNendo extends BaseMModels
{
	/**
     * @var string 対応テーブル名
     */
	const TABLE_NAME = 'm_nendo';
	protected $_name   = Class_Model_MNendo::TABLE_NAME;
	
	// 一行を返す(この表は常に一行のみ)
	public function selectRow()
	{
		$select = $this->select();
		
		return $this->fetchRow($select);
	}
}