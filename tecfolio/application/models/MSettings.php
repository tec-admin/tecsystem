<?php

require_once('BaseMModels.class.php');

class Class_Model_MSettings extends BaseMModels
{
	/**
     * @var string 対応テーブル名
     */
	const TABLE_NAME = 'm_settings';
	protected $_name   = Class_Model_MSettings::TABLE_NAME;

	public static function fieldArray($prefix = '')
	{
		if ($prefix == '')
			$prefix = Class_Model_MSettings::TABLE_NAME;

		return array(
			$prefix . '_id' => 'id',

			$prefix . '_name' => 'name',
			$prefix . '_content' => 'content',

			$prefix . '_createdate' => 'createdate',
			$prefix . '_creator' => 'creator',
			$prefix . '_lastupdate' => 'lastupdate',
			$prefix . '_lastupdater' => 'lastupdater',
			$prefix . '_display_flg' => 'display_flg',
		);
	}
	
	public function selectFromName($name)
	{
		$select = $this->select();
		$select->where('name = ?', $name);
	
		return $this->fetchRow($select);
	}
}

