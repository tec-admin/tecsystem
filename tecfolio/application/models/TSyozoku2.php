<?php

require_once('BaseTModels.class.php');

class Class_Model_TSyozoku2 extends BaseTModels
{
	/**
     * @var string 対応テーブル名
     */
	const TABLE_NAME		= 't_syozoku2';
	protected $_name		= Class_Model_TSyozoku2::TABLE_NAME;
	protected $_primary		= 'syozkcd2';
	
	public static function fieldArray($prefix = '')
	{
		if ($prefix == '')
			$prefix = Class_Model_TSyozoku2::TABLE_NAME;
		
		return array(
				$prefix . '_setti_cd' => 'setti_cd',
				
				$prefix . '_syozkcd1' => 'syozkcd1',
				$prefix . '_syozkcd2' => 'syozkcd2',
				$prefix . '_szknam_c' => 'szknam_c',
				$prefix . '_szknam_r' => 'szknam_r',
				
				$prefix . '_z008szsrt_no' => 'z008szsrt_no',
		);
	}
	
	public function selectFromId($setti_cd, $syozkcd1, $syozkcd2)
	{
		$select = $this->select();
		$select->where('setti_cd = ?', $setti_cd);
		$select->where('syozkcd1 = ?', $syozkcd1);
		$select->where('syozkcd2 = ?', $syozkcd2);
		return $this->fetchRow($select);
	}
}