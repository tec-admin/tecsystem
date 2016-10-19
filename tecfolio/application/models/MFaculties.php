<?php

require_once('BaseMModels.class.php');

class Class_Model_MFaculties extends BaseMModels
{
	/**
     * @var string 対応テーブル名
     */
	const TABLE_NAME = 'm_faculties';
	protected $_name   = Class_Model_MFaculties::TABLE_NAME;

	public static function fieldArray($prefix = '')
	{
		if ($prefix == '')
			$prefix = Class_Model_MFaculties::TABLE_NAME;

		return array(
			$prefix . '_id' => 'id',

			$prefix . '_name' => 'name',

			$prefix . '_createdate' => 'createdate',
			$prefix . '_creator' => 'creator',
			$prefix . '_lastupdate' => 'lastupdate',
			$prefix . '_lastupdater' => 'lastupdater',
			$prefix . '_display_flg' => 'display_flg',
			$prefix . '_order_num' => 'order_num',
		);
	}
	//学部IDと一致するものがあれば返す
	public function selectFromId($subjectid)
	{
		$select = $this->select();
		$select->where('id = ?', $subjectid);
		return $this->fetchRow($select);
	}
}