<?php

require_once(dirname(__FILE__) . '/../BaseTModels.class.php');

class Class_Model_Tecfolio_TRubricLicense extends BaseTModels
{
	/**
     * @var string 対応テーブル名
     */
	const TABLE_NAME = 't_rubric_license';
	protected $_name   = Class_Model_Tecfolio_TRubricLicense::TABLE_NAME;

	public static function fieldArray($prefix = '')
	{
		if ($prefix == '')
			$prefix = Class_Model_Tecfolio_TRubricLicense::TABLE_NAME;

		return array(
			$prefix . '_id' => 'id',
			
			$prefix . '_name' => 'name',
			$prefix . '_export_flag' => 'export_flag',
			$prefix . '_secondary_use' => 'secondary_use',
		);
	}
}

