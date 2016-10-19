<?php

require_once('BaseMModels.class.php');

class Class_Model_MDepartments extends BaseMModels
{
	/**
     * @var string 対応テーブル名
     */
	const TABLE_NAME = 'm_departments';
	protected $_name   = Class_Model_MDepartments::TABLE_NAME;

	public static function fieldArray($prefix = '')
	{
		if ($prefix == '')
			$prefix = Class_Model_MDepartments::TABLE_NAME;

		return array(
			$prefix . '_id' => 'id',

			$prefix . '_name' => 'name',
			$prefix . '_m_faculty_id' => 'm_faculty_id',

			$prefix . '_createdate' => 'createdate',
			$prefix . '_creator' => 'creator',
			$prefix . '_lastupdate' => 'lastupdate',
			$prefix . '_lastupdater' => 'lastupdater',
			$prefix . '_display_flg' => 'display_flg',
			$prefix . '_order_num' => 'order_num',
		);
	}
	// 学部IDと一致するものを返す
	public function selectFromFacultyId($facultyid)
	{
		$select = $this->select();
		$select->where('m_faculty_id = ?', $facultyid);
		return $this->fetchAll($select);
	}
}