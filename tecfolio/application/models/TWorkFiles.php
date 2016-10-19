<?php

require_once('BaseTModels.class.php');

// t_work_files テーブルクラス
class Class_Model_TWorkFiles extends BaseTModels
{
	const TABLE_NAME = 't_work_files';
	protected $_name   = Class_Model_TWorkFiles::TABLE_NAME;

	public static function fieldArray($prefix = '')
	{
		if ($prefix == '')
			$prefix = Class_Model_TWorkFiles::TABLE_NAME;

		return array(
			$prefix . '_id' => 'id',
			$prefix . '_m_member_id' => 'm_member_id',
			$prefix . '_t_file_id' => 't_file_id',
			$prefix . '_createdate' => 'createdate',
			$prefix . '_creator' => 'creator',
			$prefix . '_lastupdate' => 'lastupdate',
			$prefix . '_lastupdater' => 'lastupdater',
		);
	}
}
