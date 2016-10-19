<?php

require_once('BaseMModels.class.php');

/**
 * 大分類（マスター）モデルクラス
 *
 * 対応テーブル : m_shiftclasses
 *
 * @version		0.0.1
 */
class Class_Model_MShiftclasses extends BaseMModels
{
	/**
     * @var string 対応テーブル名
     */
	const TABLE_NAME = 'm_shiftclasses';
	protected $_name   = Class_Model_MShiftclasses::TABLE_NAME;

	public static function fieldArray($prefix = '')
	{
		if ($prefix == '')
			$prefix = Class_Model_MShiftclasses::TABLE_NAME;

		return array(
			$prefix . '_id' => 'id',

			$prefix . '_class_name' => 'class_name',

			$prefix . '_createdate' => 'createdate',
			$prefix . '_creator' => 'creator',
			$prefix . '_lastupdate' => 'lastupdate',
			$prefix . '_lastupdater' => 'lastupdater',
			$prefix . '_display_flg' => 'display_flg',
			$prefix . '_order_num' => 'order_num',
		);
	}
	
	public function selectFromMultiId($id)
	{
		$shiftclasses = explode(",", $id);
		
		$select = $this->select();
		$select->where('id = ?', $shiftclasses[0]);
		if(count($shiftclasses) > 1)
		{
			$select->orWhere("id = ?", $shiftclasses[1]);
		}
	
		return $this->fetchAll($select);
	}
}

